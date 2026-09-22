<?php
/**
 * bbGuild GW2 Extension
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguildgw2;

use phpbb\extension\base;

/**
 * Class ext
 *
 * @package avathar\bbguildgw2
 */
class ext extends base
{
	const BBGUILDGW2_VERSION = '2.0.0-rc2';
	const MIN_PHP_VERSION = '8.1.0';
	const MIN_PHPBB_VERSION = '3.3.0';
	const MIN_BBGUILD_VERSION = '2.1.0';

	/**
	 * Check whether or not the extension can be enabled.
	 * Requires PHP 8.1+, phpBB 3.3+, and bbGuild core extension to be enabled.
	 *
	 * @return bool|array True if enableable, or an array of error messages otherwise
	 */
	public function is_enableable()
	{
		$errors = [];

		$language = $this->container->get('language');
		$language->add_lang('info_ext', 'avathar/bbguildgw2');

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			$errors[] = $language->lang('BBGUILDGW2_PHP_VERSION_FAIL', self::MIN_PHP_VERSION, PHP_VERSION);
		}

		if (phpbb_version_compare(PHPBB_VERSION, self::MIN_PHPBB_VERSION, '<'))
		{
			$errors[] = $language->lang('BBGUILDGW2_PHPBB_VERSION_FAIL', self::MIN_PHPBB_VERSION, PHPBB_VERSION);
		}

		$ext_manager = $this->container->get('ext.manager');
		if (!$ext_manager->is_enabled('avathar/bbguild'))
		{
			$errors[] = $language->lang('BBGUILDGW2_REQUIRES_BBGUILD');
		}
		else
		{
			$core_version = class_exists('\avathar\bbguild\ext') ? \avathar\bbguild\ext::BBGUILD_VERSION : '0';
			if (phpbb_version_compare($core_version, self::MIN_BBGUILD_VERSION, '<'))
			{
				$errors[] = $language->lang('BBGUILDGW2_REQUIRES_BBGUILD_VERSION', self::MIN_BBGUILD_VERSION, $core_version);
			}
		}

		return empty($errors) ? true : $errors;
	}
}
