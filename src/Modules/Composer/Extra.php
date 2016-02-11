<?php


namespace Pipas\Modules\Composer;

use Composer\Composer;
use Composer\Script\Event;
use Nette\Object;
use Pipas\Modules\Composer\Extra\Bower;
use Pipas\Modules\Composer\Extra\Directory;
use Pipas\Modules\Composer\Extra\Grunt;
use Pipas\Modules\Composer\Extra\IExtra;
use Pipas\Modules\Composer\Extra\Media;

/**
 * Calls extra events for all local packages
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Extra extends Object
{
	private static $instance;
	/** @var Bower */
	private $bower;

	/** @var Grunt */
	private $grunt;

	/** @var Media */
	private $media;

	/** @var Directory */
	private $directory;

	/**
	 * @return Bower
	 */
	public function getBower()
	{
		if (!$this->bower) {
			$this->bower = new Bower();
		}
		return $this->bower;
	}

	/**
	 * @return Grunt
	 */
	public function getGrunt()
	{
		if (!$this->grunt) {
			$this->grunt = new Grunt();
		}
		return $this->grunt;
	}

	/**
	 * @return Media
	 */
	public function getMedia()
	{
		if (!$this->media) {
			$this->media = new Media();
		}
		return $this->media;
	}

	/**
	 * @return Directory
	 */
	public function getDirectory()
	{
		if (!$this->directory) {
			$this->directory = new Directory();
		}
		return $this->directory;
	}

	/**
	 * Call extra recursive
	 * @param Composer $composer
	 * @param IExtra $extra
	 */
	private function callRecursive(Composer $composer, IExtra $extra)
	{
		$extra->run($composer->getPackage(), true);
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			$extra->run($package, false);
		}
	}

	protected function __construct()
	{

	}

	/**
	 * @return Extra
	 */
	public static function get()
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Run all components
	 * @param Event $event
	 */
	public static function run(Event $event)
	{
		self::runBower($event);
		self::runDirectory($event);
		self::runGrunt($event);
		self::runMedia($event);
	}

	/**
	 * Run Bower
	 * @param Event $event
	 */
	public static function runBower(Event $event)
	{
		$that = self::get();
		$extra = $that->getBower();

		$that->callRecursive($event->getComposer(), $extra);
	}

	/**
	 * Run Directory extra
	 * @param Event $event
	 */
	public static function runDirectory(Event $event)
	{
		$that = self::get();
		$that->getDirectory()->run($event->getComposer()->getPackage());
	}

	/**
	 * Run Grunt
	 * @param Event $event
	 */
	public static function runGrunt(Event $event)
	{
		$that = self::get();
		$extra = $that->getGrunt();

		$that->callRecursive($event->getComposer(), $extra);
	}

	/**
	 * Run Media
	 * @param Event $event
	 */
	public static function runMedia(Event $event)
	{
		$that = self::get();
		$extra = $that->getMedia();

		$that->callRecursive($event->getComposer(), $extra);
		$extra->writeConfiguration();
	}

}