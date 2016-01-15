<?php

namespace Pipas\Modules\Configurators;

use Nette\Application\IRouter;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IRouteConfig
{
	/**
	 * @param string $name
	 * @return $this
	 */
	public function setRootRouteModuleName($name);

	/**
	 * @param IRouter $router
	 * @return $this
	 */
	public function addRoute(IRouter $router);

	/**
	 * @param mixed $mask
	 * @param array $metadata
	 * @param int $flags
	 * @return $this
	 */
	public function addStandardRoute($mask, $metadata = array(), $flags = 0);
}