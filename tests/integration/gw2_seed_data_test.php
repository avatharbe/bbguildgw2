<?php
/**
 * @package bbGuild GW2 Extension
 * @copyright (c) 2026 avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

/**
 * GW2 has no external API (unlike bbguildwow), so per
 * tests/integration-tests.md's "Notes for other plugins" the only
 * integration-level value here is fixture-loading correctness: load the
 * seeded data via a real extension enable, then assert structural
 * invariants deeper than the functional tests check — valid armor
 * types, valid faction references, no duplicate ids per game, and full
 * language coverage for every seeded class/race id.
 *
 * Extends phpbb_functional_test_case directly (not
 * phpbb_database_test_case, per tests/integration-tests.md's 2026-09
 * correction) — that's what gives a real DB connection and a real
 * installed extension in this test framework.
 *
 * @group integration
 */
class avathar_bbguildgw2_gw2_seed_data_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	private function fetch_all(string $sql): array
	{
		$db = $this->get_db();
		$result = $db->sql_query($sql);
		$rows = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$rows[] = $row;
		}
		$db->sql_freeresult($result);

		return $rows;
	}

	public function test_every_class_has_a_valid_armor_type()
	{
		$valid = array('CLOTH', 'LEATHER', 'MAIL', 'PLATE', 'ROBE');
		$rows = $this->fetch_all('SELECT class_id, class_armor_type FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'gw2'");

		$this->assertNotEmpty($rows);
		foreach ($rows as $row)
		{
			$this->assertContains($row['class_armor_type'], $valid, "class_id {$row['class_id']} has an invalid armor type '{$row['class_armor_type']}'");
		}
	}

	public function test_every_race_references_a_valid_faction()
	{
		$prefix = $this->get_table_prefix();

		$faction_ids = array_map('intval', array_column(
			$this->fetch_all("SELECT faction_id FROM {$prefix}bb_factions WHERE game_id = 'gw2'"),
			'faction_id'
		));
		$faction_ids[] = 0; // 0 = "no faction" is always a valid reference

		$races = $this->fetch_all("SELECT race_id, race_faction_id FROM {$prefix}bb_races WHERE game_id = 'gw2'");
		$this->assertNotEmpty($races);
		foreach ($races as $row)
		{
			$this->assertContains((int) $row['race_faction_id'], $faction_ids, "race_id {$row['race_id']} references a faction_id not present in bb_factions for game_id='gw2'");
		}
	}

	public function test_no_duplicate_class_id_per_game()
	{
		$rows = $this->fetch_all('SELECT class_id, COUNT(*) AS cnt FROM ' . $this->get_table_prefix() . "bb_classes WHERE game_id = 'gw2' GROUP BY class_id HAVING COUNT(*) > 1");
		$this->assertEmpty($rows, 'duplicate class_id rows found for game_id=gw2');
	}

	public function test_no_duplicate_race_id_per_game()
	{
		$rows = $this->fetch_all('SELECT race_id, COUNT(*) AS cnt FROM ' . $this->get_table_prefix() . "bb_races WHERE game_id = 'gw2' GROUP BY race_id HAVING COUNT(*) > 1");
		$this->assertEmpty($rows, 'duplicate race_id rows found for game_id=gw2');
	}

	public function test_every_class_id_has_language_rows()
	{
		$prefix = $this->get_table_prefix();
		$class_ids = array_map('intval', array_column(
			$this->fetch_all("SELECT DISTINCT class_id FROM {$prefix}bb_classes WHERE game_id = 'gw2'"),
			'class_id'
		));
		$this->assertNotEmpty($class_ids);

		$lang_ids = array_map('intval', array_column(
			$this->fetch_all("SELECT DISTINCT attribute_id FROM {$prefix}bb_language WHERE game_id = 'gw2' AND attribute = 'class' AND language = 'en'"),
			'attribute_id'
		));

		foreach ($class_ids as $class_id)
		{
			$this->assertContains($class_id, $lang_ids, "class_id $class_id has no English bb_language row");
		}
	}

	public function test_every_race_id_has_language_rows()
	{
		$prefix = $this->get_table_prefix();
		$race_ids = array_map('intval', array_column(
			$this->fetch_all("SELECT DISTINCT race_id FROM {$prefix}bb_races WHERE game_id = 'gw2'"),
			'race_id'
		));
		$this->assertNotEmpty($race_ids);

		$lang_ids = array_map('intval', array_column(
			$this->fetch_all("SELECT DISTINCT attribute_id FROM {$prefix}bb_language WHERE game_id = 'gw2' AND attribute = 'race' AND language = 'en'"),
			'attribute_id'
		));

		foreach ($race_ids as $race_id)
		{
			$this->assertContains($race_id, $lang_ids, "race_id $race_id has no English bb_language row");
		}
	}
}
