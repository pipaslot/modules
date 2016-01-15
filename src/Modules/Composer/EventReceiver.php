<?php


namespace Pipas\Modules\Composer;

use Composer\Script\Event;
use Pipas\Modules\Composer\Extra\Bower;
use Pipas\Modules\Composer\Extra\Directory;
use Pipas\Modules\Composer\Extra\Grunt;
use Pipas\Modules\Composer\Extra\Media;

/**
 * Calls extra events for all local packages
 * @author Petr Štipek <p.stipek@email.cz>
 */
class EventReceiver
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
	 * Calls all extras
	 * @param Event $event
	 */
	public function runEvent(Event $event)
	{
		$composer = $event->getComposer();

		$this->getDirectory()->run($composer->getPackage());
		$this->getBower()->run($composer->getPackage());
		$this->getGrunt()->run($composer->getPackage());
		$this->getMedia()->run($composer->getPackage());
		foreach ($composer->getRepositoryManager()->getLocalRepository()->getPackages() as $package) {
			$this->getBower()->run($package, false);
			$this->getGrunt()->run($package, false);
			$this->getMedia()->run($package, false);
		}
		$this->getMedia()->writeConfiguration();
	}

	protected function __construct()
	{

	}

	public static function run(Event $event)
	{
		if (!self::$instance) {
			self::$instance = new self();
		}
		self::$instance->runEvent($event);
	}

}