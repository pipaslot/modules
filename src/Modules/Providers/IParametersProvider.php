<?php

namespace Pipas\Modules\Providers;
use Pipas\Modules\Configurators\IParametersConfig;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IParametersProvider
{
	function setupParameters(IParametersConfig $config);
}