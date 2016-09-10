<?php

namespace Pipas\Modules\DI\Providers;
use Pipas\Modules\DI\Providers\Configurators\IParametersConfig;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IParametersProvider
{
	function setupParameters(IParametersConfig $config);
}