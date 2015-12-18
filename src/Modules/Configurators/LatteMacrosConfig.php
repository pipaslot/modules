<?php


namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class LatteMacrosConfig implements ILatteMacrosConfig
{
	/** @var  array */
	private $macros = array();

	/**
	 * @param string $name
	 * @return $this
	 */
	public function addMacro($name)
	{
		$this->macros[] = (string)$name;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getMacros()
	{
		return $this->macros;
	}
}