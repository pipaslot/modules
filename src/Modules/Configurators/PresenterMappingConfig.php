<?php


namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class PresenterMappingConfig implements IPresenterMappingConfig
{
	/** @var array */
	private $mapping = array();

	/**
	 * @return mixed
	 */
	public function getPresenterMapping()
	{
		return $this->mapping;
	}

	/**
	 * @param string $module
	 * @param string $namespace
	 * @return $this
	 */
	public function addPresenterMapping($module, $namespace)
	{
		$this->mapping[(string)$module] = (string)$namespace;
		return $this;
	}
}