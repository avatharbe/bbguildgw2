<?php
/**
 * bbGuild GW2 Extension — game registry functional test
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Asserts the gw2 provider is actually reachable through bbguild core's
 * tagged game_registry service, and that it correctly reports no API
 * support (unlike bbguildwow's provider).
 *
 * No in-process DI container is reachable from a phpbb_functional_test_case
 * (Goutte drives the board under test over real HTTP in a separate PHP
 * process — see tests/integration-tests.md's 2026-09 correction), so this
 * can't call avathar.bbguild.game_registry directly. Two HTTP/DB-observable
 * proxies stand in for it instead:
 *
 *  - controller/admin_games.php::listgames() itself calls
 *    $this->game_registry->has($game_id) to decide between an
 *    "Active"/"Inactive" status and the "Disabled (plugin not enabled)"
 *    string for each row. Finding "Active" (not the disabled string)
 *    next to the gw2 row on the ACP Game List page is therefore a real,
 *    HTTP-observable proof that the bbguild.game_provider-tagged service
 *    resolved through the registry.
 *  - has_api() === false is proxied by bb_games.armory_enabled, which
 *    abstract_game_install::install() sets from the installer's
 *    has_api_support() at install time (0 here, since gw2_installer
 *    never overrides it — see tests/game/gw2_installer_test.php for the
 *    direct unit-level check on that method).
 *
 * @group functional
 */
class avathar_bbguildgw2_game_registry_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	private function get_table_prefix(): string
	{
		return self::$config['table_prefix'];
	}

	public function test_gw2_provider_registered_and_has_no_api()
	{
		$this->login('admin');
		$this->admin_login();

		self::request('GET', 'adm/index.php?i=-avathar-bbguild-acp-game_module&mode=listgames&sid=' . $this->sid, array(), false);
		self::assert_response_status_code(200);

		$content = self::$client->getResponse()->getContent();

		$pos = strpos($content, 'game_id=gw2');
		$this->assertNotFalse($pos, 'gw2 row not found on the ACP game list — provider not registered/reachable?');

		$window = substr($content, max(0, $pos - 600), 1200);
		$this->assertStringNotContainsString(
			'Disabled (plugin not enabled)',
			$window,
			'gw2 shows as plugin-disabled on the ACP game list — bbguild.game_provider tag not resolving through game_registry'
		);

		$this->logout();

		$db = $this->get_db();
		$sql = 'SELECT armory_enabled FROM ' . $this->get_table_prefix() . "bb_games WHERE game_id = 'gw2'";
		$result = $db->sql_query($sql);
		$armory_enabled = (int) $db->sql_fetchfield('armory_enabled');
		$db->sql_freeresult($result);

		$this->assertSame(0, $armory_enabled, 'gw2 has no API — armory_enabled should be 0');
	}
}
