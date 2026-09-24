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
use avathar\bbguild\model\games\specialization_provider_interface;

/**
 * Class gw2_provider
 *
 * @package avathar\bbguildgw2\game
 */
class gw2_provider implements game_provider_interface, specialization_provider_interface
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
	public function get_installer(): \avathar\bbguild\model\games\game_install_interface
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
	public function get_api(): ?\avathar\bbguild\model\games\game_api_interface
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

	/**
	 * Elite Specialization catalog (issue #331 Phase 4), keyed by class_id
	 * (see game/gw2_installer.php's install_classes() for the id map).
	 *
	 * role_id matches this plugin's Damage/Support/Control override of the
	 * standard roles (see install_roles(): 0 Damage, 1 Support, 2 Control),
	 * assigned to each elite spec's primary role in established raid/WvW
	 * meta play, not every possible build — several specs (e.g. Catalyst,
	 * Vindicator) can flex into other roles depending on traits/gear.
	 *
	 * spec_icon names a file in images/spec_icons/ (core resolves it as
	 * spec_icons/<spec_icon>.png, see roster::resolve_spec()). The assets
	 * are the official elite-specialization icons from the Guild Wars 2
	 * API's render service (/v2/specializations), normalised from their
	 * native 64x64 to the 56x56 used by bbguildwow's spec icons.
	 *
	 * @return array<int, list<array{spec_name:string,role_id:int,spec_icon:string,spec_order:int}>>
	 */
	public static function spec_catalog(): array
	{
		[$damage, $support, $control] = [0, 1, 2];

		return array(
			1 => array( // Warrior
				array('spec_name' => 'Berserker',    'role_id' => $damage,  'spec_icon' => 'warrior_berserker', 'spec_order' => 1),
				array('spec_name' => 'Spellbreaker',  'role_id' => $control, 'spec_icon' => 'warrior_spellbreaker', 'spec_order' => 2),
				array('spec_name' => 'Bladesworn',    'role_id' => $damage,  'spec_icon' => 'warrior_bladesworn', 'spec_order' => 3),
			),
			2 => array( // Guardian
				array('spec_name' => 'Dragonhunter', 'role_id' => $damage,  'spec_icon' => 'guardian_dragonhunter', 'spec_order' => 1),
				array('spec_name' => 'Firebrand',    'role_id' => $support, 'spec_icon' => 'guardian_firebrand', 'spec_order' => 2),
				array('spec_name' => 'Willbender',   'role_id' => $damage,  'spec_icon' => 'guardian_willbender', 'spec_order' => 3),
			),
			3 => array( // Engineer
				array('spec_name' => 'Scrapper',   'role_id' => $support, 'spec_icon' => 'engineer_scrapper', 'spec_order' => 1),
				array('spec_name' => 'Holosmith',  'role_id' => $damage,  'spec_icon' => 'engineer_holosmith', 'spec_order' => 2),
				array('spec_name' => 'Mechanist',  'role_id' => $support, 'spec_icon' => 'engineer_mechanist', 'spec_order' => 3),
			),
			4 => array( // Ranger
				array('spec_name' => 'Druid',     'role_id' => $support, 'spec_icon' => 'ranger_druid', 'spec_order' => 1),
				array('spec_name' => 'Soulbeast', 'role_id' => $damage,  'spec_icon' => 'ranger_soulbeast', 'spec_order' => 2),
				array('spec_name' => 'Untamed',   'role_id' => $damage,  'spec_icon' => 'ranger_untamed', 'spec_order' => 3),
			),
			5 => array( // Thief
				array('spec_name' => 'Daredevil', 'role_id' => $damage,  'spec_icon' => 'thief_daredevil', 'spec_order' => 1),
				array('spec_name' => 'Deadeye',   'role_id' => $damage,  'spec_icon' => 'thief_deadeye', 'spec_order' => 2),
				array('spec_name' => 'Specter',   'role_id' => $support, 'spec_icon' => 'thief_specter', 'spec_order' => 3),
			),
			6 => array( // Elementalist
				array('spec_name' => 'Tempest',   'role_id' => $support, 'spec_icon' => 'elementalist_tempest', 'spec_order' => 1),
				array('spec_name' => 'Weaver',    'role_id' => $damage,  'spec_icon' => 'elementalist_weaver', 'spec_order' => 2),
				array('spec_name' => 'Catalyst',  'role_id' => $support, 'spec_icon' => 'elementalist_catalyst', 'spec_order' => 3),
			),
			7 => array( // Mesmer
				array('spec_name' => 'Chronomancer', 'role_id' => $control, 'spec_icon' => 'mesmer_chronomancer', 'spec_order' => 1),
				array('spec_name' => 'Mirage',       'role_id' => $damage,  'spec_icon' => 'mesmer_mirage', 'spec_order' => 2),
				array('spec_name' => 'Virtuoso',     'role_id' => $damage,  'spec_icon' => 'mesmer_virtuoso', 'spec_order' => 3),
			),
			8 => array( // Necromancer
				array('spec_name' => 'Reaper',     'role_id' => $damage,  'spec_icon' => 'necromancer_reaper', 'spec_order' => 1),
				array('spec_name' => 'Scourge',    'role_id' => $control, 'spec_icon' => 'necromancer_scourge', 'spec_order' => 2),
				array('spec_name' => 'Harbinger',  'role_id' => $damage,  'spec_icon' => 'necromancer_harbinger', 'spec_order' => 3),
			),
			9 => array( // Revenant
				array('spec_name' => 'Herald',     'role_id' => $support, 'spec_icon' => 'revenant_herald', 'spec_order' => 1),
				array('spec_name' => 'Renegade',   'role_id' => $control, 'spec_icon' => 'revenant_renegade', 'spec_order' => 2),
				array('spec_name' => 'Vindicator', 'role_id' => $damage,  'spec_icon' => 'revenant_vindicator', 'spec_order' => 3),
			),
		);
	}

	/**
	 * @inheritdoc
	 */
	public function get_spec_label(): string
	{
		return 'Elite Specialization';
	}

	/**
	 * Interface implementation: delegates to the static catalog.
	 *
	 * @return array<int, list<array{spec_name:string,role_id:int,spec_icon:string,spec_order:int}>>
	 */
	public function get_specializations(): array
	{
		return self::spec_catalog();
	}
}
