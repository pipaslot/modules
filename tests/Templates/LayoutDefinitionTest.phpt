<?php

namespace Pipas\Modules\Templates;

use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class LayoutDefinitionTest extends TestCase
{
	public function setUp()
	{
		parent::setUp();
	}

	public function test_fileDoesNotExist_Constructor_exception()
	{
		Assert::exception(function () {
			new LayoutDefinition("");
		}, \OutOfRangeException::class);
	}

	public function test_getters()
	{
		$file = "existing";
		$def1 = new LayoutDefinition($file);
		Assert::false($def1->getOverriding());
		Assert::false($def1->overriding);
		Assert::equal($file, $def1->getPath());
		Assert::equal($file, $def1->path);

		$def2 = new LayoutDefinition($file, true);
		Assert::true($def2->getOverriding());
	}

	public function test_noRules_check_true()
	{
		$def1 = new LayoutDefinition("existing");
		Assert::true($def1->match("whatever"));
	}

	private function getList($without = array())
	{
		$list = array(
			"Sign",
			"Home",
			"Admin",
			"Front:Home",
			"Front:Sign",
			"Front:Admin",
			"Admin:Home",
			"Admin:Sign",
			"Admin:Admin",
			"Extra:Front:Home",
			"Extra:Front:Sign",
			"Extra:Front:Admin",
		);
		foreach ($without as $value) {
			if (($key = array_search($value, $list)) !== false) unset($list[$key]);
		}
		return $list;
	}

	public function test_rules()
	{
		$testValues = $this->getList();
		$tests = array(
			array("Home", array("Home")),
			array("!Home", $this->getList(array("Home"))),
			array("*me", array("Home")),
			array("!*me", $this->getList(array("Home"))),
			array("*:Home", array("Front:Home", "Admin:Home")),
			array("!*:Home", $this->getList(array("Front:Home", "Admin:Home"))),
			array("**:Home", array("Front:Home", "Admin:Home", "Extra:Front:Home")),
			array("!**:Home", $this->getList(array("Front:Home", "Admin:Home", "Extra:Front:Home"))),
			array("***me", array("Home", "Front:Home", "Admin:Home", "Extra:Front:Home")),
			array("!***me", $this->getList(array("Home", "Front:Home", "Admin:Home", "Extra:Front:Home"))),
			array("Admin:*", array("Admin:Home", "Admin:Sign", "Admin:Admin")),
			array("!Admin:*", $this->getList(array("Admin:Home", "Admin:Sign", "Admin:Admin"))),
			array("Extra:Front:*", array("Extra:Front:Home", "Extra:Front:Sign", "Extra:Front:Admin")),
			array("!Extra:Front:*", $this->getList(array("Extra:Front:Home", "Extra:Front:Sign", "Extra:Front:Admin"))),
			array("Extra:*:Home", array("Extra:Front:Home")),
			array("!Extra:*:Home", $this->getList(array("Extra:Front:Home"))),
			array("Extra::Home", array("Extra:Front:Home")),
			array("!Extra::Home", $this->getList(array("Extra:Front:Home"))),
		);
		//Testing
		foreach ($tests as $i => list($ruleList, $expected)) {

			$def = new LayoutDefinition("existing");
			$rules = is_array($ruleList) ? $ruleList : array($ruleList);
			foreach ($rules as $rule) {
				$def->addRule($rule);
			}
			foreach ($testValues as $value) {
				if ($def->match($value)) {
					$key = array_search($value, $expected);
					if ($key !== false) unset($expected[$key]);
					else Assert::fail($i . ". Unexpected value '$value' for rules [" . implode(", ", $rules) . "]");
				}
			}
			if (count($expected) > 0) Assert::fail($i . ". Not matched: [" . implode(", ", $expected) . "] for rules [" . implode(", ", $rules) . "]");
		}
		Assert::true(true);
	}

	function test_forbiddenRules_addRule_exception()
	{
		$rules = array(
			"Admin:",
			":Admin",
			"Admi!n"
		);
		foreach ($rules as $rule) {
			Assert::exception(function () use ($rule) {
				$def = new LayoutDefinition("existing");
				$def->addRule($rule);
			}, \OutOfRangeException::class);
		}
	}


}

function is_file($name)
{
	if ($name == "existing") return true;
	return \is_file($name);
}

$test = new LayoutDefinitionTest();
$test->run();