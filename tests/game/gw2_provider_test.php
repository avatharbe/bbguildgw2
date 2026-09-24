<?php
/**
 * @package bbGuild GW2 Extension
 * @copyright (c) 2026 avathar.be
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace avathar\bbguildgw2\tests\game;

use PHPUnit\Framework\TestCase;
use avathar\bbguildgw2\game\gw2_provider;
use avathar\bbguildgw2\game\gw2_installer;

class gw2_provider_test extends TestCase
{
	/** @var gw2_provider */
	private $provider;

	protected function setUp(): void
	{
		parent::setUp();

		$installer = $this->getMockBuilder(gw2_installer::class)
			->disableOriginalConstructor()
			->getMock();

		$ext_manager = $this->getMockBuilder(\phpbb\extension\manager::class)
			->disableOriginalConstructor()
			->getMock();
		$ext_manager->method('get_extension_path')
			->willReturn('ext/avathar/bbguildgw2/');

		$this->provider = new gw2_provider($installer, $ext_manager);
	}

	public function test_game_id(): void
	{
		$this->assertSame('gw2', $this->provider->get_game_id());
	}

	public function test_game_name(): void
	{
		$this->assertSame('Guild Wars 2', $this->provider->get_game_name());
	}

	public function test_installer(): void
	{
		$this->assertInstanceOf(gw2_installer::class, $this->provider->get_installer());
	}

	public function test_has_no_api(): void
	{
		$this->assertFalse($this->provider->has_api());
		$this->assertNull($this->provider->get_api());
	}

	public function test_images_path(): void
	{
		$this->assertStringContainsString('bbguildgw2', $this->provider->get_images_path());
		$this->assertStringEndsWith('images/', $this->provider->get_images_path());
	}

	public function test_regions(): void
	{
		$regions = $this->provider->get_regions();
		$this->assertCount(2, $regions);
		$this->assertSame(array(
			'us' => 'US',
			'eu' => 'EU',
		), $regions);
	}

	public function test_api_locales_empty(): void
	{
		$this->assertEmpty($this->provider->get_api_locales());
	}

	public function test_every_elite_spec_has_an_icon_asset(): void
	{
		$missing_value = array();
		$missing_file  = array();

		foreach (gw2_provider::spec_catalog() as $class_id => $specs)
		{
			foreach ($specs as $spec)
			{
				if ($spec['spec_icon'] === '')
				{
					$missing_value[] = $spec['spec_name'];
					continue;
				}

				$file = dirname(__DIR__, 2) . '/images/spec_icons/' . $spec['spec_icon'] . '.png';

				if (!file_exists($file))
				{
					$missing_file[] = $spec['spec_name'] . ' => ' . $spec['spec_icon'] . '.png';
				}
			}
		}

		$this->assertSame(array(), $missing_value, 'Specs with an empty spec_icon: ' . implode(', ', $missing_value));
		$this->assertSame(array(), $missing_file, 'Specs whose icon file is missing: ' . implode(', ', $missing_file));
	}

	public function test_armor_types(): void
	{
		$armor = $this->provider->get_armor_types();
		$this->assertCount(5, $armor);
		$this->assertSame(array(
			'CLOTH' => 'Cloth',
			'LEATHER' => 'Leather',
			'MAIL' => 'Mail',
			'PLATE' => 'Plate',
			'ROBE' => 'Robes',
		), $armor);
	}
}
