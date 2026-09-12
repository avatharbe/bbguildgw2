<?php
/**
 * bbGuild GW2 Extension — migration idempotency smoke test
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Disables bbguildgw2 (data preserved) and re-enables it, then asserts
 * seeded rows were not duplicated. Disable does not revert schema/data,
 * so re-enabling re-runs every migration's effectively_installed() check
 * against data that's already there — this is the only way to exercise
 * that path without a second fresh install. Catches migrations that
 * mistakenly re-seed or re-create on a second run.
 *
 * Tracks bb_games/bb_classes/bb_races/bb_language for game_id='gw2' plus
 * the migrations table. bb_specializations is intentionally left out of
 * this check per this ticket's stated scope (spec data seeding is a
 * separate, not-yet-implemented ticket) — see the unit test coverage in
 * tests/game/gw2_installer_test.php for a note on the actual state of
 * install_specs().
 *
 * @group smoke
 */
class avathar_bbguildgw2_smoke_migration_idempotency_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	private function count_rows(string $table, string $where): int
	{
		$db = $this->get_db();
		$sql = 'SELECT COUNT(*) AS cnt FROM ' . $table . ' WHERE ' . $where;
		$result = $db->sql_query($sql);
		$count = (int) $db->sql_fetchfield('cnt');
		$db->sql_freeresult($result);

		return $count;
	}

	public function test_reenable_does_not_duplicate_seeded_data()
	{
		$before_games = $this->count_rows($this->get_table_prefix() . 'bb_games', "game_id = 'gw2'");
		$before_classes = $this->count_rows($this->get_table_prefix() . 'bb_classes', "game_id = 'gw2'");
		$before_races = $this->count_rows($this->get_table_prefix() . 'bb_races', "game_id = 'gw2'");
		$before_language = $this->count_rows($this->get_table_prefix() . 'bb_language', "game_id = 'gw2'");
		$before_migrations = $this->count_rows($this->get_table_prefix() . 'migrations', "migration_name LIKE '%bbguildgw2%'");

		$this->disable_ext('avathar/bbguildgw2');
		$this->install_ext('avathar/bbguildgw2');

		$after_games = $this->count_rows($this->get_table_prefix() . 'bb_games', "game_id = 'gw2'");
		$after_classes = $this->count_rows($this->get_table_prefix() . 'bb_classes', "game_id = 'gw2'");
		$after_races = $this->count_rows($this->get_table_prefix() . 'bb_races', "game_id = 'gw2'");
		$after_language = $this->count_rows($this->get_table_prefix() . 'bb_language', "game_id = 'gw2'");
		$after_migrations = $this->count_rows($this->get_table_prefix() . 'migrations', "migration_name LIKE '%bbguildgw2%'");

		$this->assertSame(1, $before_games, 'expected exactly one gw2 row in bb_games before re-enable');
		$this->assertSame($before_games, $after_games, 'bb_games gw2 row was duplicated on re-enable');

		$this->assertSame(10, $before_classes, 'expected 10 gw2 professions in bb_classes before re-enable');
		$this->assertSame($before_classes, $after_classes, 'bb_classes gw2 rows were duplicated on re-enable');

		$this->assertSame(6, $before_races, 'expected 6 gw2 races in bb_races before re-enable');
		$this->assertSame($before_races, $after_races, 'bb_races gw2 rows were duplicated on re-enable');

		$this->assertSame($before_language, $after_language, 'bb_language gw2 rows were duplicated on re-enable');
		$this->assertSame($before_migrations, $after_migrations, 'bbguildgw2 migration rows changed on re-enable');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}
}
