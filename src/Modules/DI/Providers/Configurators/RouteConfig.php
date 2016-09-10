<?php


namespace Pipas\Modules\DI\Providers\Configurators;

use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;
use Nette\Application\IRouter;
/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class RouteConfig implements IRouteConfig
{
	/** @var  null|RouteList */
	private $routeList;
	/** @var  null|string */
	private $rootModuleName;

	/**
	 * @return RouteList
	 */
	public function getRouteList()
	{
		if ($this->routeList === null) {
			$this->routeList = new RouteList($this->rootModuleName);
		}
		return $this->routeList;
	}

	/**
	 * @param IRouter $router
	 * @return $this
	 */
	public function addRoute(IRouter $router)
	{
		$this->getRouteList()[] = $router;
		return $this;
	}

	/**
	 * @param mixed $mask
	 * @param array $metadata
	 * @param int $flags
	 * @return $this
	 */
	public function addStandardRoute($mask, $metadata = array(), $flags = 0)
	{
		$this->getRouteList()[] = new Route($mask, $metadata, $flags);
		return $this;
	}

	/**
	 * @param string $name
	 * @return $this
	 */
	public function setRootRouteModuleName($name)
	{
		$this->rootModuleName = (string)$name;
		return $this;
	}
}