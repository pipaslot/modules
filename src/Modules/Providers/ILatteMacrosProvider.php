<?php

namespace Pipas\Modules\Providers;
use Pipas\Modules\Configurators\ILatteMacrosConfig;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface ILatteMacrosProvider
{
	/**
	 * Setup names of latte macros classes
	 *
	 *
	 * @param ILatteMacrosConfig &$macrosConfig
	 */
	public function setupMacros(ILatteMacrosConfig &$macrosConfig);
}