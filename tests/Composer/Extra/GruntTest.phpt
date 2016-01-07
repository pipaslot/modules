<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Mockery;
use Mockery\MockInterface;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

class GruntTest extends TestCase
{
	/** @var Grunt */
	private $grunt;
	/** @var MockInterface[] */
	private $packages;
	/** @var  array */
	private $passedCommands;
	/** @var  array */
	private $expectedCommands;

	protected function setUp()
	{
		parent::setUp();
		$this->grunt = new Grunt();
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
	function test_noConfiguration()
	{
		$packageMock1 = $this->createPackageMock();
		$packageMock1->shouldReceive("getExtra")->andReturn(array());
		$packageMock2 = $this->createPackageMock();
		$packageMock2->shouldReceive("getExtra")->andReturn(array("grunt" => null));

		$this->grunt->run($packageMock1);
		$this->grunt->run($packageMock2);
	}

	function test_run()
	{
		$packageMock = $this->createPackageMock();
		$packageMock->shouldReceive("getExtra")
			->andReturn(array("grunt" => array(
				"directory1" => "",
				"directory2" => "my-task"
			)));
		$this->expectCommand("cd " . getcwd() . "/directory1 & npm install & grunt");
		$this->expectCommand("cd " . getcwd() . "/directory2 & npm install & grunt my-task");

		$this->grunt->run($packageMock);
	}
}

$test = new GruntTest();
// system function mocks
function passthru($cmd)
{
	global $test;
	$test->passCommand($cmd);
}

$test->run();

