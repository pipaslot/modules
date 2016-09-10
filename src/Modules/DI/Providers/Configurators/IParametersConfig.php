<?php

namespace Pipas\Modules\DI\Providers\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IParametersConfig
{
	/**
	 * @return array
	 */
	function getParameters();

	/**
	 * @param string $name
	 * @param $value
	 * @return $this
	 */
	function addParameter($name, $value);
}