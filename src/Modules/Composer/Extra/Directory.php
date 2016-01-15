<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Pipas\Utils\Path;

/**
 * Directory management and security against URL access
 *
 * Example of using:
 * "directory": {
 *    "secure": [
 *        "relative-path"
 *    ],
 *    "create": [
 *        "relative-path"
 *    ],
 *    "empty": [
 *        "relative-path"
 *    ]
 * }
 *
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Directory implements IExtra
{
	private $webConfigDeny = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<configuration>
	<system.webServer>
		<rewrite>
			<rules>
				<rule name=\"Deny Rule 1\" patternSyntax=\"Wildcard\" stopProcessing=\"true\">
					<match url=\"*\" />
					<conditions>
						<add input=\"{URL}\" pattern=\"*\" />
					</conditions>
					<action type=\"CustomResponse\" statusCode=\"403\" statusReason=\"Forbidden: Access is denied.\" statusDescription=\"You do not have permission to view this directory or page using the credentials that you supplied.\" />
				</rule>
			</rules>
		</rewrite>
	</system.webServer>
</configuration>";
	private $htaccessDeny = "Order Deny,Allow\nDeny from all";

	/**
	 * Prepare directories order configuration
	 * @param PackageInterface $package
	 * @param bool $isMain
	 * @return string|void
	 */
	function run(PackageInterface $package, $isMain = true)
	{
		if (!$isMain) throw new \DomainException("Directory extra extension is enabled only for main packages");
		$extra = $package->getExtra();

		if (isset($extra['directory'])) {
			if (isset($extra['directory']['empty']) AND is_array($extra['directory']['empty'])) {
				$this->runEmpty($extra['directory']['empty']);
			}
			if (isset($extra['directory']['create']) AND is_array($extra['directory']['create'])) {
				$this->runCreate($extra['directory']['create']);
			}
			if (isset($extra['directory']['secure']) AND is_array($extra['directory']['secure'])) {
				$this->runSecure($extra['directory']['secure']);
			}
		}
	}

	private function runEmpty(array $directories)
	{
		foreach ($directories as $directory) {
			if (!is_dir($directory)) continue;
			Path::emptyDirectory($directory);
		}
	}

	private function runCreate(array $directories)
	{
		foreach ($directories as $directory) {
			if (is_dir($directory)) continue;
			mkdir($directory);
		}
	}

	private function runSecure(array $directories)
	{
		foreach ($directories as $directory) {
			if (!is_dir($directory)) continue;
			$path = $this->getPath($directory);
			$config = $path . "/web.config";
			if (!is_file($config)) file_put_contents($config, $this->webConfigDeny);

			$htaccess = $path . "/.htaccess";
			if (!is_file($htaccess)) file_put_contents($htaccess, $this->htaccessDeny);
		}
	}

	/**
	 * @param $directory
	 * @return string Path
	 */
	private function getPath($directory)
	{
		return getcwd() . "/" . trim($directory, "\\/");
	}

}