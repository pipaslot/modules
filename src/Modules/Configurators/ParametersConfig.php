<?php


namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class ParametersConfig implements IParametersConfig
{
	/** @var array */
	private $params = array();

	/**
	 * @return array
	 */
	function getParameters()
	{
		return $this->params;
	}

	/**
	 * @param string $name
	 * @param $value
	 * @return $this
	 */
	function addParameter($name, $value)
	{
		$this->params[(string)$name] = $value;
		return $this;
	}
}