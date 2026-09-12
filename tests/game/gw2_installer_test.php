<?php
/**
 * @package bbGuild GW2 Extension
 * @copyright (c) 2026 avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\bbguildgw2\tests\game;

use PHPUnit\Framework\TestCase;
use avathar\bbguildgw2\game\gw2_installer;

class gw2_installer_test extends TestCase
{
	/** @var gw2_installer */
	protected $installer;

	/** @var array Captured sql_multi_insert calls: array of [table, data] */
	protected $inserted = array();

	/** @var \PHPUnit\Framework\MockObject\MockObject */
	protected $db;

	protected function setUp(): void
	{
		parent::setUp();

		$this->inserted = array();

		$this->db = $this->createMock(\phpbb\db\driver\driver_interface::class);

		// Capture sql_multi_insert calls
		$this->db->method('sql_multi_insert')
			->willReturnCallback(function ($table, $data) {
				$this->inserted[] = array('table' => $table, 'data' => $data);
			});

		// sql_query (DELETE statements) — no-op
		$this->db->method('sql_query')->willReturn(true);
		$this->db->method('sql_escape')->willReturnCallback(function ($v) { return $v; });

		$cache = $this->createMock(\phpbb\cache\driver\driver_interface::class);
		$config = new \phpbb\config\config(array());
		$user = $this->getMockBuilder(\phpbb\user::class)
			->disableOriginalConstructor()
			->getMock();

		$this->installer = new gw2_installer($this->db, $cache, $config, $user);

		// Set table_names and game_id via reflection (normally set by install()).
		// bb_specializations_table is deliberately NOT included here — the
		// install_specs() tests below add/remove it explicitly to exercise
		// both branches of its "is the table wired in" guard.
		$ref = new \ReflectionClass($this->installer);

		$tn = $ref->getProperty('table_names');
		$tn->setAccessible(true);
		$tn->setValue($this->installer, array(
			'bb_factions_table'  => 'phpbb_bb_factions',
			'bb_classes_table'   => 'phpbb_bb_classes',
			'bb_races_table'     => 'phpbb_bb_races',
			'bb_language_table'  => 'phpbb_bb_language',
			'bb_gameroles_table' => 'phpbb_bb_gameroles',
		));

		$gid = $ref->getProperty('game_id');
		$gid->setAccessible(true);
		$gid->setValue($this->installer, 'gw2');
	}

	/**
	 * Invoke a protected method on the installer.
	 */
	private function invoke_protected(string $method_name): void
	{
		$this->inserted = array();
		$method = new \ReflectionMethod(gw2_installer::class, $method_name);
		$method->setAccessible(true);
		$method->invoke($this->installer);
	}

	/**
	 * Set (key => value) or remove (value === null) a single entry in the
	 * installer's table_names map, on top of whatever setUp() put there.
	 */
	private function set_table_name(string $key, ?string $value): void
	{
		$ref = new \ReflectionClass($this->installer);
		$tn = $ref->getProperty('table_names');
		$tn->setAccessible(true);
		$current = $tn->getValue($this->installer);

		if ($value === null)
		{
			unset($current[$key]);
		}
		else
		{
			$current[$key] = $value;
		}

		$tn->setValue($this->installer, $current);
	}

	// ── Factions ───────────────────────────────────────────

	public function test_install_factions_count(): void
	{
		$this->invoke_protected('install_factions');
		$this->assertCount(1, $this->inserted);
		$this->assertCount(2, $this->inserted[0]['data']);
	}

	public function test_install_factions_ids(): void
	{
		$this->invoke_protected('install_factions');
		$factions = $this->inserted[0]['data'];
		$ids = array_column($factions, 'faction_id');
		$this->assertContains(1, $ids, 'Tyria faction_id=1');
		$this->assertContains(2, $ids, 'Zhaitan faction_id=2');
	}

	public function test_install_factions_names(): void
	{
		$this->invoke_protected('install_factions');
		$factions = $this->inserted[0]['data'];
		$names = array_column($factions, 'faction_name');
		$this->assertContains('Tyria', $names);
		$this->assertContains('Zhaitan', $names);
	}

	public function test_install_factions_game_id(): void
	{
		$this->invoke_protected('install_factions');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertSame('gw2', $row['game_id']);
		}
	}

	// ── Classes (professions) ───────────────────────────────

	public function test_install_classes_count(): void
	{
		$this->invoke_protected('install_classes');
		// First insert: class rows, second insert: language rows
		$this->assertCount(2, $this->inserted);
		$this->assertCount(10, $this->inserted[0]['data']);
	}

	public function test_install_classes_valid_armor_types(): void
	{
		$this->invoke_protected('install_classes');
		$valid = array('CLOTH', 'LEATHER', 'MAIL', 'PLATE', 'ROBE');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertContains($row['class_armor_type'], $valid, "class_id {$row['class_id']} has valid armor type");
		}
	}

	public function test_install_classes_valid_faction(): void
	{
		$this->invoke_protected('install_classes');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertContains($row['class_faction_id'], array(1, 2), "class_id {$row['class_id']} references a valid faction_id");
		}
	}

	public function test_install_classes_language_coverage(): void
	{
		$this->invoke_protected('install_classes');
		$lang_rows = $this->inserted[1]['data'];
		$languages = array_unique(array_column($lang_rows, 'language'));
		sort($languages);
		$this->assertSame(array('de', 'en', 'fr', 'it'), $languages);
	}

	public function test_install_classes_language_entries_per_lang(): void
	{
		$this->invoke_protected('install_classes');
		$lang_rows = $this->inserted[1]['data'];
		$per_lang = array_count_values(array_column($lang_rows, 'language'));
		// 10 professions x 4 languages = 40 total
		foreach ($per_lang as $lang => $count)
		{
			$this->assertSame(10, $count, "$lang has 10 class name entries");
		}
	}

	// ── Races ──────────────────────────────────────────────

	public function test_install_races_count(): void
	{
		$this->invoke_protected('install_races');
		// First insert: race rows, second insert: language rows
		$this->assertCount(2, $this->inserted);
		$this->assertCount(6, $this->inserted[0]['data']);
	}

	public function test_install_races_valid_factions(): void
	{
		$this->invoke_protected('install_races');
		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertContains($row['race_faction_id'], array(1, 2), "race_id {$row['race_id']} references a valid faction_id");
		}
	}

	public function test_install_races_language_coverage(): void
	{
		$this->invoke_protected('install_races');
		$lang_rows = $this->inserted[1]['data'];
		$languages = array_unique(array_column($lang_rows, 'language'));
		sort($languages);
		$this->assertSame(array('de', 'en', 'fr', 'it'), $languages);
	}

	public function test_install_races_language_entries_per_lang(): void
	{
		$this->invoke_protected('install_races');
		$lang_rows = $this->inserted[1]['data'];
		$per_lang = array_count_values(array_column($lang_rows, 'language'));
		// 6 races x 4 languages = 24 total
		foreach ($per_lang as $lang => $count)
		{
			$this->assertSame(6, $count, "$lang has 6 race name entries");
		}
	}

	// ── Roles (GW2 overrides the DPS/Healer/Tank default with
	//    Damage/Support/Control) ────────────────────────────

	public function test_install_roles_count(): void
	{
		$this->invoke_protected('install_roles');
		// First insert: role rows, second insert: language rows
		$this->assertCount(2, $this->inserted);
		$this->assertCount(3, $this->inserted[0]['data']);
	}

	public function test_install_roles_ids(): void
	{
		$this->invoke_protected('install_roles');
		$ids = array_column($this->inserted[0]['data'], 'role_id');
		sort($ids);
		$this->assertSame(array(0, 1, 2), $ids);
	}

	public function test_install_roles_language_coverage(): void
	{
		$this->invoke_protected('install_roles');
		$lang_rows = $this->inserted[1]['data'];
		$languages = array_unique(array_column($lang_rows, 'language'));
		sort($languages);
		$this->assertSame(array('de', 'en', 'fr', 'it'), $languages);
	}

	public function test_install_roles_english_names_are_damage_support_control(): void
	{
		$this->invoke_protected('install_roles');
		$lang_rows = $this->inserted[1]['data'];

		$en = array_values(array_filter($lang_rows, function ($row) {
			return $row['language'] === 'en';
		}));
		usort($en, function ($a, $b) {
			return $a['attribute_id'] <=> $b['attribute_id'];
		});

		$this->assertSame(array('Damage', 'Support', 'Control'), array_column($en, 'name'));
	}

	// ── has_api_support() ──────────────────────────────────

	public function test_has_api_support(): void
	{
		$method = new \ReflectionMethod(gw2_installer::class, 'has_api_support');
		$method->setAccessible(true);
		$this->assertFalse($method->invoke($this->installer), 'GW2 has no API integration (unlike WoW)');
	}

	// ── Elite specializations (install_specs) ──────────────
	//
	// gw2_installer already implements install_specs() with a full
	// 9-profession x 3-spec catalog (see game/gw2_provider.php's
	// spec_catalog()), even though bbguild's roadmap still lists GW2
	// Phase 4 spec data as a separate open ticket. Covered here since
	// it's real, reachable, already-shipped code.

	public function test_install_specs_seeds_when_table_wired(): void
	{
		$this->set_table_name('bb_specializations_table', 'phpbb_bb_specializations');

		$this->invoke_protected('install_specs');

		$this->assertCount(1, $this->inserted);
		// 9 professions x 3 elite specs = 27
		$this->assertCount(27, $this->inserted[0]['data']);

		foreach ($this->inserted[0]['data'] as $row)
		{
			$this->assertSame('gw2', $row['game_id']);
			$this->assertContains($row['class_id'], range(1, 9), "spec '{$row['spec_name']}' has a valid class_id");
			$this->assertContains($row['role_id'], array(0, 1, 2), "spec '{$row['spec_name']}' has a valid role_id");
			$this->assertNotSame('', $row['spec_name'], 'spec_name must not be empty');
			$this->assertContains($row['spec_order'], array(1, 2, 3));
		}
	}

	public function test_install_specs_skips_when_table_not_wired(): void
	{
		$this->set_table_name('bb_specializations_table', null);

		$this->invoke_protected('install_specs');

		$this->assertCount(0, $this->inserted, 'install_specs() must no-op when bb_specializations_table is not in table_names');
	}
}
