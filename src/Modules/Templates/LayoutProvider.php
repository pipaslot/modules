<?php


namespace Pipas\Modules\Templates;

/**
 * Main layout provider. Enable override default layout path to custom
 * @author Petr Štipek <p.stipek@email.cz>
 */
class LayoutProvider
{
	const MODE_DOCUMENT = 'document',
		MODE_MODAL = 'modal';
	protected $default;
	/** @var LayoutDefinition[] */
	protected $definitions = array();
	/**
	 * List of supported modes
	 * @var array
	 */
	public static $modes = array(
		self::MODE_DOCUMENT,
		self::MODE_MODAL
	);

	/**
	 * LayoutProvider constructor.
	 * @param $default
	 */
	public function __construct($default = null)
	{
		if ($default !== null) {
			if (!is_file($default)) throw new \OutOfRangeException("Layout file odes not exist: " . $default);
			$this->default = $default;
		}
	}

	/**
	 * Register layout wit rules and override setting
	 * @param string $layout Path
	 * @param array $rules
	 * @param bool $override
	 * @param string $mode
	 * @return LayoutDefinition
	 */
	public function register($layout, $rules = array(), $override = false, $mode = self::MODE_DOCUMENT)
	{
		$definition = $this->addDefinition($layout, $override, $mode);
		foreach ($rules as $rule) {
			$definition->addRule($rule);
		}
		return $definition;
	}

	/**
	 * Register new layout
	 * @param $path
	 * @param bool $overriding Override existing layout definitions
	 * @param string $mode
	 * @return LayoutDefinition
	 */
	public function addDefinition($path, $overriding = false, $mode = self::MODE_DOCUMENT)
	{
		$definition = new LayoutDefinition($path, $overriding, $mode);
		$this->definitions[] = $definition;
		return $definition;
	}

	/**
	 * @param array $layouts layouts path got from presenter method formatLayoutTemplateFiles()
	 * @param string $name Presenter name
	 * @param string $mode
	 * @return string Path to layout
	 */
	public function prepareLayouts(array $layouts, $name, $mode = self::MODE_DOCUMENT)
	{
		$resolved = null;
		foreach ($this->definitions as $definition) {
			if ($definition->getMode() == $mode AND $definition->match($name)) {
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

		$list[] = $this->default ? $this->default : ($mode == self::MODE_MODAL ? __DIR__ . "/@modal.latte" : __DIR__ . "/@layout.latte");
		return $list;
	}

}