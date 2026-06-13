<?php
/**
 * GW2 Game Provider
 *
 * Registers Guild Wars 2 as a game plugin with bbGuild core.
 *
 * @package   bbguildgw2 v2.0
 * @copyright 2018 avathar.be
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 */

namespace avathar\bbguildgw2\game;

use avathar\bbguild\model\games\game_provider_interface;

/**
 * Class gw2_provider
 *
 * @package avathar\bbguildgw2\game
 */
class gw2_provider implements game_provider_interface
{
	/** @var gw2_installer */
	private $installer;

	/** @var \phpbb\extension\manager */
	private $ext_manager;

	/**
	 * @param gw2_installer             $installer
	 * @param \phpbb\extension\manager  $ext_manager
	 */
	public function __construct(gw2_installer $installer, \phpbb\extension\manager $ext_manager)
	{
		$this->installer = $installer;
		$this->ext_manager = $ext_manager;
	}

	/**
	 * @inheritdoc
	 */
	public function get_game_id(): string
	{
		return 'gw2';
	}

	/**
	 * @inheritdoc
	 */
	public function get_game_name(): string
	{
		return 'Guild Wars 2';
	}

	/**
	 * @inheritdoc
	 */
	public function get_installer(): game_install_interface
	{
		return $this->installer;
	}

	/**
	 * @inheritdoc
	 */
	public function get_boss_base_url(): string
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_zone_base_url(): string
	{
		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function get_images_path(): string
	{
		return $this->ext_manager->get_extension_path('avathar/bbguildgw2', true) . 'images/';
	}

	/**
	 * @inheritdoc
	 */
	public function has_api(): bool
	{
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function get_api(): ?game_api_interface
	{
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function get_regions(): array
	{
		return array(
			'us' => 'US',
			'eu' => 'EU',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_api_locales(): array
	{
		return array();
	}

	/**
	 * @inheritdoc
	 */
	public function get_armor_types(): array
	{
		return array(
			'CLOTH'   => 'Cloth',
			'LEATHER' => 'Leather',
			'MAIL'    => 'Mail',
			'PLATE'   => 'Plate',
			'ROBE'    => 'Robes',
		);
	}
}
