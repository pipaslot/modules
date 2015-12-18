<?php

namespace Pipas\Modules\Configurators;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
interface IPresenterMappingConfig
{

	/**
	 * @return array
	 */
	public function getPresenterMapping();

	/**
	 * @param string $module
	 * @param string $namespace
	 * @example "My", "MyModule\Presenters\*Presenter"
	 * @return $this
	 */
	public function addPresenterMapping($module, $namespace);

}