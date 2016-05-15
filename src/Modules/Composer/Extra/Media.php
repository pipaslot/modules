<?php
namespace Pipas\Modules\Composer\Extra;

use Composer\Package\PackageInterface;
use Pipas\Modules\Composer\Extra\Config\MediaConfig;
use Pipas\Utils\Path;

/**
 * Tool accessing private directories with front-end libraries of modules
 *
 * For Unix will be created symlink to private directory.
 * For IIS will be updated web.config file and configuration will be passed between defined comments <!-- DynamicMediaDirectories --> and <!-- DynamicMediaDirectoriesEnd -->
 *
 * @see MediaConfig
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Media implements IExtra
{
	const WEB_CONFIG = "web.config";
	/** @var string Relative path to IIS web.config file, which will be modified */
	protected $webConfigTarget = self::WEB_CONFIG;
	/** @var string Source web config for modification */
	protected $webConfigSource = self::WEB_CONFIG;
	/** @var string current working directory */
	protected $cwd;
	/** @var string Relative path to www root from directory with composer.json */
	private $wwwRoot;
	/** @var string Path to media directory from URL */
	private $basePath;
	private $ignored = array();

	private $rules = "";
	private $links = array();
	const
		WEB_CONFIG_START_TAG = "<!-- DynamicMediaDirectories -->",
		WEB_CONFIG_END_TAG = "<!-- DynamicMediaDirectoriesEnd -->";

	/**
	 * Media constructor.
	 * @param null $workingDirectory
	 */
	public function __construct($workingDirectory = null)
	{
		$this->cwd = $workingDirectory ? $workingDirectory : getcwd();
	}


	/**
	 * Prepare rules and sym links address. After all packages is ran is required to cal method writeConfiguration()
	 * @param PackageInterface $package
	 * @param bool $isMain
	 * @return string|void
	 */
	function run(PackageInterface $package, $isMain = true)
	{
		$config = new MediaConfig($package->getExtra());
		$this->initPaths($config, $isMain);
		if (count($config->getDirectories()) == 0) return;

		$vendorName = trim("vendor/" . $package->getName(), '\\/');
		foreach ($config->getDirectories() as $name => $path) {
			if (in_array($name, $this->ignored)) continue;

			if (!preg_match("/^[a-zA-Z0-9_-]+$/", $name)) throw new \OutOfRangeException("Name must be corresponding to expression: a-zA-Z0-9_-");
			//generate create config
			$relativePath = "/" . ($isMain ? "" : $vendorName . '/') . trim($path, '\\/');
			$absolutePath = Path::normalize($this->cwd . $relativePath);

			$absoluteMediaPath = Path::normalize($this->cwd . '/' . trim($this->wwwRoot . '/' . $this->basePath, "\\/"));

			if (!is_dir($absoluteMediaPath)) throw new \OutOfRangeException("Media directory does not exist for expected path: $absoluteMediaPath");
			if (!is_dir($absolutePath)) throw new \OutOfRangeException("Directory declared by relative path: '$path' does not exist on absolute path $absolutePath");
			$this->addRule($relativePath, $name);

			//create symlink for unix
			$module = $absoluteMediaPath . '/' . $name;
			if (!is_dir($module) AND !is_link($module)) {
				$this->addLink($absolutePath, $module);
			}
		}
	}

	/**
	 * Register redirection rule
	 * @param $relativePath
	 * @param $name
	 */
	private function addRule($relativePath, $name)
	{
		$this->rules .= "\n\t\t\t<rule name=\"Rewrite media modules to $relativePath/ folder\" stopProcessing=\"true\">"
			. "\n\t\t\t\t<match url=\"^" . $this->basePath . "/$name/(.+)\" ignoreCase=\"false\" />"
			. "\n\t\t\t\t<action type=\"Rewrite\" url=\"$relativePath/{R:1}\" />"
			. "\n\t\t\t</rule>";
	}

	/**
	 * Register path for symlink
	 * @param $absolutePath
	 * @param $module
	 */
	private function addLink($absolutePath, $module)
	{
		$this->links[$absolutePath] = $module;
	}

	public function writeConfiguration()
	{
		$this->updateWebConfig();
		$this->createSymlinks();
	}

	/**
	 * Apply redirection rules for IIS to file web.config
	 */
	private function updateWebConfig()
	{
		$source = $this->cwd . '/' . trim($this->webConfigSource, "\\/");
		$target = $this->cwd . '/' . trim($this->webConfigTarget, "\\/");

		if (!is_file($source)) {
			return;
		}

		$content = file_get_contents($source);
		$tagStart = strpos($content, self::WEB_CONFIG_START_TAG) + strlen(self::WEB_CONFIG_START_TAG);
		$tagEnd = strpos($content, self::WEB_CONFIG_END_TAG);
		$newContent = substr($content, 0, $tagStart)
			. $this->rules
			. "\n\t\t\t" . substr($content, $tagEnd, strlen($content) - $tagEnd);
		file_put_contents($target, $newContent);
		echo "Updated web config file: $this->webConfigTarget\n";
	}

	/**
	 * Create symlinks from public to private directories
	 */
	private function createSymlinks()
	{
		foreach ($this->links as $absolutePath => $module) {
			if (!@symlink($absolutePath, $module)) echo "Symlink was not created. Run command again with privileges of administrator\n";
			$htaccess = $absolutePath . "/.htaccess";
			if (!is_file($htaccess)) file_put_contents($htaccess, "Order Allow,Deny\nAllow from all");
		}
	}

	/**
	 * Initialize paths from main package extra parameters
	 * @param MediaConfig $config
	 * @param $isMain
	 */
	private function initPaths($config, $isMain)
	{
		if ($this->wwwRoot AND $this->basePath AND $isMain) throw new \DomainException("Can not run for package marked as main twice.");
		if ($this->wwwRoot AND $this->basePath) return;
		if (!$isMain) throw new \DomainException("Call at first for main package");
		$this->wwwRoot = (isset($extra['media']['www-root']) AND $isMain) ? trim($extra['media']['www-root'], "\\/") : 'www';
		$this->basePath = (isset($extra['media']['base-path']) AND $isMain) ? trim($extra['media']['base-path'], "\\/") : 'media_modules';
		$this->ignored = isset($extra['media']['ignored']) ? (array)$extra['media']['ignored'] : array();
	}


}