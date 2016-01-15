<?php

namespace Pipas\Modules;

use Nette;
use Pipas\Modules\Templates\LayoutProvider;
use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class ModulesExtension_LayoutProviderTest extends TestCase
{
	/** @var Nette\Configurator */
	private $configurator;

	public function setUp()
	{
		parent::setUp();
		$this->configurator = new Nette\Configurator();
		$this->configurator->setTempDirectory(__DIR__ . "/../../temp");
	}

	public function test_badConfigWithLayoutsNotInArray_exception()
	{
		Assert::exception(function () {
			$this->configurator->addConfig(__DIR__ . '/assets/layoutProvider_badConfigWithLayoutsNotInArray.neon');
			$this->configurator->createContainer();
		}, \OutOfRangeException::class, "Config section modules.layouts must contains array");
	}

	public function test_undefinedParameterIntoLayout_exception()
	{
		Assert::exception(function () {
			$this->configurator->addConfig(__DIR__ . '/assets/layoutProvider_undefinedParameterIntoLayout.neon');
			$this->configurator->createContainer();
		}, \OutOfRangeException::class);
	}

	public function test_passing()
	{
		$this->configurator->addConfig(__DIR__ . '/assets/config.neon');
		$container = $this->configurator->createContainer();
		/** @var LayoutProvider $provider */
		$provider = $container->getService("modules.layoutProvider");

		Assert::true($provider instanceof LayoutProvider, get_class($provider));
		Assert::equal(2, count($provider->prepareLayouts(array(), "name")));
	}
}

$test = new ModulesExtension_LayoutProviderTest();
$test->run();