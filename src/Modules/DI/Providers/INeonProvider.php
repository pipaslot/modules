<?php

namespace Pipas\Modules\DI\Providers;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface INeonProvider
{
	/**
	 * @return string Absolute path to neon file
	 */
	function getNeonPath();
}