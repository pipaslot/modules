<?php

namespace Pipas\Modules\DI\Providers;
use Pipas\Modules\DI\Providers\Configurators\ILatteMacrosConfig;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface ILatteMacrosProvider
{
	/**
	 * Setup names of latte macros classes
	 *
	 * @param ILatteMacrosConfig &$macrosConfig
	 */
	public function setupMacros(ILatteMacrosConfig &$macrosConfig);
}