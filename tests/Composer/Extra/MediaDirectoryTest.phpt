<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Mockery;
use Mockery\MockInterface;
use Nette\Utils\Strings;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

class MediaDirectoryTest extends TestCase
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

	function test_noConfiguration()
	{
		$packageMock1 = $this->createPackageMock();
		$packageMock1->shouldReceive("getExtra")->andReturn(array());
		$packageMock2 = $this->createPackageMock();
		$packageMock2->shouldReceive("getExtra")->andReturn(array("media-directory" => null));

		$media = new MediaDirectory();
		$media->run($packageMock1);
		$media->run($packageMock2);
	}

	function test_badMediaDirName_run_exception()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"media-directory" => array(
					"bad/name" => "existing"
				)));
		$packageMock
			->shouldReceive("getName")
			->andReturn("package/name");

		Assert::exception(function () use ($packageMock) {
			$media = new MediaDirectory();
			$media->run($packageMock);
		}, \OutOfRangeException::class, "Name must be corresponding to expression: a-zA-Z0-9_-");
	}

	function test_noMediaTargetDirectoryExist_run_exception()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"media-directory" => array(
					"name" => "missing"
				)));
		$packageMock
			->shouldReceive("getName")
			->andReturn("package/name");

		Assert::exception(function () use ($packageMock) {
			$media = new MediaDirectory();
			$media->run($packageMock);
		}, \OutOfRangeException::class, "Media directory does not exist for expected path: " . getcwd() . "/www/media");
	}

	function test_noMediaSourceDirectoryExist_run_exception()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"media-directory" => array(
					"name" => "missing"
				)));
		$packageMock
			->shouldReceive("getName")
			->andReturn("package/name");

		Assert::exception(function () use ($packageMock) {
			$media = new MediaDirectory("www", "existing");
			$media->run($packageMock);
		}, \OutOfRangeException::class, "Directory declared by relative path: 'missing' does not exist on absolute path " . getcwd() . "/vendor/package/name/missing");
	}

	function test_noMediaSourceDirectoryExistOnMain_run_exception()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"media-directory" => array(
					"name" => "missing"
				)));
		$packageMock
			->shouldReceive("getName")
			->andReturn("package/name");

		Assert::exception(function () use ($packageMock) {
			$media = new MediaDirectory("www", "existing");
			$media->run($packageMock, true);
		}, \OutOfRangeException::class, "Directory declared by relative path: 'missing' does not exist on absolute path " . getcwd() . "/missing");
	}

	function test_run()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"media-directory" => array(
					"name" => "existing"
				)));
		$packageMock
			->shouldReceive("getName")
			->andReturn("package/name");

		$media = new MediaDirectory("www", "existing");
		$media->run($packageMock);
	}

}

$test = new MediaDirectoryTest();
// system function mocks
function is_dir($filename)
{
	if (Strings::endsWith($filename, "existing")) return true;
	if (Strings::endsWith($filename, "missing")) return false;
	return \is_dir($filename);
}

function file_get_contents($filename)
{
	/*if ($filename == __DIR__ . "/bowerExisting.json") {
		return json_encode(array(
			"dependencies" => array(
				"dep" => "version",
				"dep2" => "version2"
			)));
	}*/
	return \file_get_contents($filename);
}

$test->run();

