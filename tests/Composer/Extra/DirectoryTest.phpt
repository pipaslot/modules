<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\Strings;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

class DirectoryTest extends TestCase
{
	/** @var MockInterface[] */
	private $packages;

	protected function setUp()
	{
		parent::setUp();
		$this->packages = array();
	}

	protected function tearDown()
	{
		parent::tearDown();
		foreach ($this->packages as $package) {
			$package->mockery_verify();
		}
	}

	/**
	 * @return PackageInterface|MockInterface
	 */
	private function createPackageMock()
	{
		return $this->packages[] = Mockery::mock(PackageInterface::class);
	}

	/**************************************************************/


}

$test = new DirectoryTest();
// system function mocks
function is_dir($filename)
{
	if (Strings::endsWith($filename, "existing")) return true;
	if (Strings::endsWith($filename, "missing")) return false;
	return \is_dir($filename);
}

function file_get_contents($filename)
{
	return \file_get_contents($filename);
}

$test->run();

