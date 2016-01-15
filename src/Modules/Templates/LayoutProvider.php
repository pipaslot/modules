<?php


namespace Pipas\Modules\Templates;

/**
 * Main layout provider. Enable override default layout path to custom
 * @author Petr Štipek <p.stipek@email.cz>
 */
class LayoutProvider
{
	protected $default;
	/** @var LayoutDefinition[] */
	protected $definitions = array();

	/**
	 * LayoutProvider constructor.
	 * @param $default
	 */
	public function __construct($default = null)
	{
		if ($default !== null) {
			if (!is_file($default)) throw new \OutOfRangeException("Layout file odes not exist: " . $default);
			$this->default = $default;
		} else {
			$this->default = __DIR__ . "/@layout.latte";
		}
	}

	/**
	 * Register layout wit rules and override setting
	 * @param string $layout Path
	 * @param array $rules
	 * @param bool $override
	 * @return LayoutDefinition
	 */
	public function register($layout, $rules = array(), $override = false)
	{
		$definition = $this->addDefinition($layout, $override);
		foreach ($rules as $rule) {
			$definition->addRule($rule);
		}
		return $definition;
	}

	/**
	 * Register new layout
	 * @param $path
	 * @param bool $overriding Override existing layout definitions
	 * @return LayoutDefinition
	 */
	public function addDefinition($path, $overriding = false)
	{
		$definition = new LayoutDefinition($path, $overriding);
		$this->definitions[] = $definition;
		return $definition;
	}

	/**
	 * @param array $layouts layouts path got from presenter method formatLayoutTemplateFiles()
	 * @param string $name Presenter name
	 * @return string Path to layout
	 */
	public function prepareLayouts(array $layouts, $name)
	{
		$resolved = null;
		foreach ($this->definitions as $definition) {
			if ($definition->match($name)) {
				$resolved = $definition;
				break;
			}
		}
		$list = array();
		if ($resolved AND $resolved->overriding) {
			$list[] = $resolved->path;
		}
		$list = array_merge($list, $layouts);
		if ($resolved AND !$resolved->overriding) {
			$list[] = $resolved->path;
		}
		$list[] = $this->default;
		return $list;
	}

}