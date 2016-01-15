<?php
namespace Pipas\Modules\Templates;

use Tester\Assert;
use Tester\TestCase;

require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class LayoutProviderTest extends TestCase
{
	const LAYOUT_DEFAULT = "default";
	const LAYOUT_APPEND = "append";
	const LAYOUT_PREPEND = "prepend";

	public function setUp()
	{
		parent::setUp();
	}

	public function test_parameterLess_constructor_defaultLayout()
	{
		$provider = new LayoutProvider();
		$layouts = $provider->prepareLayouts(array(), "name");
		Assert::equal(1, count($layouts));
		Assert::true(is_file($layouts[0]));
	}

	public function test_badPath_constructor_exception()
	{
		Assert::exception(function () {
			new LayoutProvider("nonsense");
		}, \OutOfRangeException::class);
	}

	public function test_formatLayoutTemplateFiles_default()
	{
		$fromPresenter = array();
		$expected = array(self::LAYOUT_DEFAULT);
		$provider = new LayoutProvider(self::LAYOUT_DEFAULT);

		$layouts = $provider->prepareLayouts($fromPresenter, "name");

		Assert::equal($expected, $layouts);
	}

	public function test_formatLayoutTemplateFiles_prepend()
	{
		$fromPresenter = array("pathFromPresenter");
		$expected = array_merge(array(self::LAYOUT_PREPEND), $fromPresenter, array(self::LAYOUT_DEFAULT));
		$provider = new LayoutProvider(self::LAYOUT_DEFAULT);
		$provider->register(self::LAYOUT_PREPEND, true);

		$layouts = $provider->prepareLayouts($fromPresenter, "name");

		Assert::equal($expected, $layouts);
	}

	public function test_formatLayoutTemplateFiles_append()
	{
		$fromPresenter = array("pathFromPresenter");
		$expected = array_merge($fromPresenter, array(self::LAYOUT_APPEND), array(self::LAYOUT_DEFAULT));

		$provider = new LayoutProvider(self::LAYOUT_DEFAULT);
		$provider->register(self::LAYOUT_APPEND);

		$layouts = $provider->prepareLayouts($fromPresenter, "name");

		Assert::equal($expected, $layouts);
	}


	public function test_formatLayoutTemplateFiles_both()
	{
		$fromPresenter = array("pathFromPresenter");
		$expected = array_merge(array(self::LAYOUT_PREPEND), $fromPresenter, array(self::LAYOUT_DEFAULT));

		$provider = new LayoutProvider(self::LAYOUT_DEFAULT);
		$provider->register(self::LAYOUT_PREPEND, true);
		$provider->register(self::LAYOUT_APPEND);

		$layouts = $provider->prepareLayouts($fromPresenter, "name");

		Assert::equal($expected, $layouts);
	}
}

function is_file($name)
{
	if ($name == LayoutProviderTest::LAYOUT_PREPEND OR $name == LayoutProviderTest::LAYOUT_APPEND OR $name == LayoutProviderTest::LAYOUT_DEFAULT) return true;
	return \is_file($name);
}

$test = new LayoutProviderTest();
$test->run();