<?php
/**
 * bbGuild GW2 Extension — extension enable functional test
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Enables bbguild core, then bbguildgw2 on top. Asserts the game
 * registers itself in bb_games, its professions get seeded into
 * bb_classes, and the plugin's own version constant matches
 * composer.json.
 *
 * Unlike bbguildwow's equivalent test, there is no ACP module assertion
 * here — bbguildgw2 registers no ACP modules of its own.
 *
 * @group functional
 */
class avathar_bbguildgw2_extension_enable_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	public function test_gw2_game_row_present()
	{
		$db = $this->get_db();
		$sql = 'SELECT * FROM ' . $this->get_table_prefix() . "bb_games WHERE game_id = 'gw2'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		$this->assertNotFalse($row, 'gw2 row missing from bb_games after enabling bbguildgw2');
		$this->assertSame('Guild Wars 2', $row['game_name']);
	}

	public function test_gw2_classes_seeded()
	{
		$db = $this->get_db();
		$sql = 'SELECT class_id, imagename FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'gw2' ORDER BY class_id";
		$result = $db->sql_query($sql);
		$rows = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$db->sql_freeresult($result);

		// class_id 0 (Unknown placeholder) through 9 (Revenant)
		$this->assertCount(10, $rows, 'expected 10 GW2 professions seeded in bb_classes');

		$imagenames = array_column($rows, 'imagename');
		$this->assertContains('gw2_warrior', $imagenames);
		$this->assertContains('gw2_revenant', $imagenames);
	}

	public function test_version_constant_matches_composer_json()
	{
		$composer = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);

		$this->assertSame($composer['version'], \avathar\bbguildgw2\ext::BBGUILDGW2_VERSION);
	}
}
