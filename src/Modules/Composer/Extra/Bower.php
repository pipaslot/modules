<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Pipas\Modules\Composer\Extra\Config\BowerConfig;

/**
 * Call the installation dependencies of front-end bower tool. Using extra section of composer config file.
 *
 * @see BowerConfig
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Bower implements IExtra
{
	private $installed = array();

	/**
	 * Call bower install in same folder as composer
	 */
	public function install()
	{
		passthru("bower install");
	}

	/**
	 * Install package
	 * @param $package
	 * @param $version
	 */
	public function installPackage($package, $version)
	{
		$flags = "$package#$version";
		if (isset($this->installed[$flags])) return;
		passthru("bower install $flags");
		$this->installed[$flags] = true;
	}

	function run(PackageInterface $package, $isMain = true)
	{
		$extra = $package->getExtra();
		$config = new BowerConfig($extra);

		foreach ($config->getDependencies() as $package => $version) {
			$this->installPackage($package, $version);
		}
		foreach ($config->getFiles() as $file) {
			$path = getcwd() . "/" . $file;
			if (is_file($path)) {
				$bower = json_decode(file_get_contents($path));
				if (isset($bower->dependencies)) {
					foreach ($bower->dependencies as $package => $version) {
						$this->installPackage($package, $version);
					}
				}
			} elseif ($isMain) {
				throw new \OutOfRangeException("File $file defined in composer section extra.bower.files does not exist on path: $path");
			}
		}
	}


}