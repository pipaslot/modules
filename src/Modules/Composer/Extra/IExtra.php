<?php

namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IExtra
{
	/**
	 * Run all extra sections
	 * @param PackageInterface $package
	 * @param bool $isMain - Package is not as vendor dependency
	 * @return
	 */
	function run(PackageInterface $package, $isMain = false);
}