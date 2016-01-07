<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

class BowerTest extends TestCase
{
	/** @var Bower */
	private $bower;
	/** @var MockInterface[] */
	private $packages;
	/** @var  array */
	private $passedCommands;
	/** @var  array */
	private $expectedCommands;

	protected function setUp()
	{
		parent::setUp();
		$this->bower = new Bower();
		$this->packages = array();
		$this->passedCommands = array();
		$this->expectedCommands = array();
	}

	protected function tearDown()
	{
		parent::tearDown();
		foreach ($this->packages as $package) {
			$package->mockery_verify();
		}
		Assert::equal($this->expectedCommands, $this->passedCommands);
	}

	/**
	 * @return PackageInterface|MockInterface
	 */
	private function createPackageMock()
	{
		return $this->packages[] = Mockery::mock(PackageInterface::class);
	}

	/**
	 * @param $cmd
	 */
	public function passCommand($cmd)
	{
		$cmd = trim($cmd);
		if (!isset($this->passedCommands[$cmd])) $this->passedCommands[$cmd] = 0;
		$this->passedCommands[$cmd]++;
	}

	/**
	 * @param $cmd
	 * @return $this
	 */
	private function expectCommand($cmd)
	{
		$cmd = trim($cmd);
		if (!isset($this->expectedCommands[$cmd])) $this->expectedCommands[$cmd] = 0;
		$this->expectedCommands[$cmd]++;
		return $this;
	}

	/**************************************************************/
	function test_install()
	{
		$this->expectCommand("bower install");
		$this->bower->install();
	}

	function test_duplicated_installPackage()
	{
		$this->expectCommand("bower install dependency1#version1");
		$this->expectCommand("bower install dependency2#version1");
		$this->expectCommand("bower install dependency3#version2");
		$this->bower->installPackage("dependency1", "version1");
		$this->bower->installPackage("dependency2", "version1");
		$this->bower->installPackage("dependency1", "version1");//duplicated
		$this->bower->installPackage("dependency3", "version2");
		$this->bower->installPackage("dependency3", "version2");//duplicated
	}

	function test_noConfiguration()
	{
		$packageMock1 = $this->createPackageMock();
		$packageMock1->shouldReceive("getExtra")->andReturn(array());
		$packageMock2 = $this->createPackageMock();
		$packageMock2->shouldReceive("getExtra")->andReturn(array("bower" => null));

		$this->bower->run($packageMock1);
		$this->bower->run($packageMock2);
	}

	function test_parseDependencies()
	{
		$packageMock = $this->createPackageMock();
		$packageMock->shouldReceive("getExtra")
			->andReturn(array("bower" => array(
				"dependencies" => array(
					"dep" => "version",
					"dep2" => "version2"
				)
			)));
		$this->expectCommand("bower install dep#version");
		$this->expectCommand("bower install dep2#version2");

		$this->bower->run($packageMock);
	}

	function test_notExist_parseFiles_exception()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"bower" => array(
					"files" => array(
						"nesmysl"
					)
				)));
		Assert::exception(function () use ($packageMock) {
			$this->bower->run($packageMock);
		}, \OutOfRangeException::class);
	}

	function test_path_parseFiles_run()
	{
		$packageMock = $this->createPackageMock();
		$packageMock
			->shouldReceive("getExtra")
			->andReturn(array(
				"bower" => array(
					"files" => array(
						"bowerExisting.json"
					)
				)));
		$this->expectCommand("bower install dep#version");
		$this->expectCommand("bower install dep2#version2");

		$this->bower->run($packageMock);
	}

}

$test = new BowerTest();
// system function mocks
function passthru($cmd)
{
	global $test;
	$test->passCommand($cmd);
}

function is_file($filename)
{
	if ($filename == __DIR__ . "/bowerExisting.json") return true;
	return \is_file($filename);
}

function file_get_contents($filename)
{
	if ($filename == __DIR__ . "/bowerExisting.json") {
		return json_encode(array(
			"dependencies" => array(
				"dep" => "version",
				"dep2" => "version2"
			)));
	}
	return \file_get_contents($filename);
}

$test->run();

