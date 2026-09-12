<?php
/**
 * bbGuild GW2 Extension — disable-keeps-core guardrail test
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2026 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

/**
 * Per tests/functional-tests.md's "Notes for other plugins", this is the
 * single most important guardrail for a non-flagship game plugin:
 * disabling bbguildgw2 must not break bbguild core, or any guild that
 * isn't a gw2 guild.
 *
 * Uses core's own default game_id='custom' guild (guild_id=1, seeded by
 * bbguild core's base migration together with its roster portal module
 * and 3 test players) as the control guild — no dependency on any other
 * game plugin being installed.
 *
 * @group functional
 */
class avathar_bbguildgw2_disable_keeps_core_test extends phpbb_functional_test_case
{
	static protected function setup_extensions()
	{
		return array('avathar/bbguild', 'avathar/bbguildgw2');
	}

	public function test_disabling_gw2_does_not_break_core_or_other_guilds()
	{
		$this->create_user('bbguildgw2_disabletest');
		$this->login('bbguildgw2_disabletest');

		// Sanity: core's own default (game_id='custom') guild renders before disabling.
		self::request('GET', 'app.php/guild/1', array(), false);
		self::assert_response_status_code(200);

		$this->logout();

		$this->disable_ext('avathar/bbguildgw2');

		$this->login('bbguildgw2_disabletest');

		self::request('GET', 'app.php/guild/1', array(), false);
		self::assert_response_status_code(200);

		$this->logout();

		$this->login('admin');
		$this->admin_login();

		self::request('GET', 'adm/index.php?i=-avathar-bbguild-acp-game_module&mode=listgames&sid=' . $this->sid, array(), false);
		self::assert_response_status_code(200);

		$this->logout();

		// Restore state so any test running later in this suite still sees
		// bbguildgw2 enabled.
		$this->install_ext('avathar/bbguildgw2');
	}
}
