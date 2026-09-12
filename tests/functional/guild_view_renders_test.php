<?php
/**
 * bbGuild GW2 Extension — guild view rendering functional test
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Inserts a GW2 guild fixture (one player with a valid profession/race
 * pair) directly into the DB, then GETs /guild/{id} and asserts the
 * roster portal module rendered the player's class image under this
 * plugin's own images/ path.
 *
 * A guild created this way starts with zero portal modules attached —
 * bb_portal_modules only has rows for bbguild core's own default
 * guild_id=1 (seeded by its base migration); ACP guild creation doesn't
 * auto-attach any module, that's done via ACP Portal Management. So the
 * roster module row below is inserted by hand, mirroring the exact row
 * shape that migration seeds for guild_id=1.
 *
 * @group functional
 */
class avathar_bbguildgw2_guild_view_renders_test extends phpbb_functional_test_case
{
	// Distinct from bbguild core's own default guild_id 0/1, and from any
	// fixture other plugins' test suites might use.
	private const GUILD_ID = 30520;

	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	protected function setUp(): void
	{
		parent::setUp();

		$db = $this->get_db();
		$prefix = $this->get_table_prefix();

		// Guild fixture (game_id='gw2', roster enabled)
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_guild WHERE id = ' . self::GUILD_ID);
		$db->sql_multi_insert($prefix . 'bb_guild', array(array(
			'id'             => self::GUILD_ID,
			'name'           => 'GW2 Test Guild',
			'realm'          => 'Test Realm',
			'region'         => 'eu',
			'roster'         => 1,
			'players'        => 1,
			'emblemurl'      => '',
			'game_id'        => 'gw2',
			'game_edition'   => 'retail',
			'min_armory'     => 0,
			'rec_status'     => 0,
			'guilddefault'   => 0,
			'armory_enabled' => 0,
			'armoryresult'   => '',
			'recruitforum'   => 0,
			'faction'        => 1,
		)));

		// A rank so the roster row has something to display for it
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_ranks WHERE guild_id = ' . self::GUILD_ID);
		$db->sql_multi_insert($prefix . 'bb_ranks', array(array(
			'guild_id'    => self::GUILD_ID,
			'rank_id'     => 0,
			'rank_name'   => 'Member',
			'rank_hide'   => 0,
			'rank_prefix' => '',
			'rank_suffix' => '',
		)));

		// One player: Warrior (class_id=1), Sylvari (race_id=1)
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_players WHERE player_guild_id = ' . self::GUILD_ID);
		$db->sql_multi_insert($prefix . 'bb_players', array(array(
			'game_id'             => 'gw2',
			'player_name'         => 'Testasura',
			'player_region'       => 'eu',
			'player_realm'        => 'Test Realm',
			'player_title'        => '',
			'player_level'        => 80,
			'player_race_id'      => 1,
			'player_class_id'     => 1,
			'player_rank_id'      => 0,
			'player_role'         => 'DPS',
			'player_comment'      => '',
			'player_joindate'     => time(),
			'player_outdate'      => 0,
			'player_guild_id'     => self::GUILD_ID,
			'player_gender_id'    => 0,
			'player_achiev'       => 0,
			'player_armory_url'   => '',
			'player_portrait_url' => '',
			'player_spec'         => '',
			'phpbb_user_id'       => 0,
			'player_status'       => 1,
			'deactivate_reason'   => '',
			'last_update'         => time(),
		)));

		// Attach the roster portal module — mirrors the row shape bbguild
		// core's base migration seeds for its own default guild_id=1.
		$db->sql_query('DELETE FROM ' . $prefix . 'bb_portal_modules WHERE guild_id = ' . self::GUILD_ID);
		$db->sql_multi_insert($prefix . 'bb_portal_modules', array(array(
			'guild_id'            => self::GUILD_ID,
			'module_classname'    => '\avathar\bbguild\portal\modules\roster',
			'module_column'       => 1,
			'module_order'        => 1,
			'module_name'         => 'BBGUILD_PORTAL_ROSTER',
			'module_image_src'    => '',
			'module_icon'         => '',
			'module_icon_size'    => 16,
			'module_image_width'  => 16,
			'module_image_height' => 16,
			'module_group_ids'    => '',
			'module_status'       => 1,
		)));
	}

	public function test_guild_page_renders_gw2_player_with_class_image()
	{
		$this->create_user('bbguildgw2_guildview');
		$this->login('bbguildgw2_guildview');

		self::request('GET', 'app.php/guild/' . self::GUILD_ID, array(), false);
		self::assert_response_status_code(200);

		$content = self::$client->getResponse()->getContent();

		$this->assertStringContainsString('Testasura', $content, 'GW2 player row did not render on the guild page');
		$this->assertStringContainsString('ext/avathar/bbguildgw2/images/', $content, 'no GW2 image path found on the guild page');
		$this->assertStringContainsString('class_images/', $content, 'no class image rendered for the GW2 player row');

		$this->logout();
	}
}
