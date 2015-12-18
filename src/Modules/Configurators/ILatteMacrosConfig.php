<?php

namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface ILatteMacrosConfig
{
	/**
	 * @return array
	 */
	public function getMacros();
	/**
	 * @param string $name
	 * @return $this
	 */
	public function addMacro($name);
}