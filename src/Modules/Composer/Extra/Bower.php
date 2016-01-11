<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;

/**
 * Call the installation dependencies of front-end bower tool. Using extra section of composer config file.
 *
 * Example of using:
 * "extra":{
 *    "bower":{
 *        "files": [
 *            "relative/path/to/bower.json"
 *        ],
 *        "dependencies": [
 *            "bower-dependency": "~1.0.0",
 *        ]
 *    }
 * }
 *
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
		if (!isset($extra['bower'])) return;

		if (isset($extra['bower']['dependencies']) AND is_array($extra['bower']['dependencies'])) {
			foreach ($extra['bower']['dependencies'] as $package => $version) {
				$this->installPackage($package, $version);
			}
		}
		if (isset($extra['bower']['files']) AND is_array($extra['bower']['files'])) {
			foreach ($extra['bower']['files'] as $file) {
				$path = getcwd() . "/" . $file;
				if (!is_file($path)) throw new \OutOfRangeException("File $file defined in composer section extra.bower.files does not exist on path: $path");
				$bower = json_decode(file_get_contents($path));
				if (isset($bower->dependencies)) {
					foreach ($bower->dependencies as $package => $version) {
						$this->installPackage($package, $version);
					}
				}
			}
		}
	}


}