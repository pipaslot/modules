<?php


namespace Pipas\Modules\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\ServiceDefinition;
use Nette\DI\Statement;
use Nette\FileNotFoundException;
use Nette\Utils\AssertionException;
use Nette\Utils\Validators;
use Pipas\Modules\DI\Providers\Configurators\LatteMacrosConfig;
use Pipas\Modules\DI\Providers\Configurators\ParametersConfig;
use Pipas\Modules\DI\Providers\Configurators\PresenterMappingConfig;
use Pipas\Modules\DI\Providers\ILatteMacrosProvider;
use Pipas\Modules\DI\Providers\INeonProvider;
use Pipas\Modules\DI\Providers\IParametersProvider;
use Pipas\Modules\DI\Providers\IPresenterMappingProvider;
use Pipas\Modules\DI\Providers\IRouterProvider;
use Pipas\Modules\Templates\LayoutProvider;

/**
 * This extension must be loaded before all ohers module extensions
 * @author Petr Štipek <p.stipek@email.cz>
 */
class ModulesExtension extends CompilerExtension
{
	const TAG_ROUTER = 'pipas.modules.router';
	public $defaults = array(
		"layouts" => array()
	);

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		foreach ($this->compiler->getExtensions() as $extension) {
			if ($extension instanceof IParametersProvider) {
				$this->setupParameters($extension);
			}
			if ($extension instanceof IRouterProvider) {
				$this->setupRouter($extension);
			}
			if ($extension instanceof INeonProvider) {
				$this->setupNeon($extension);
			}
			if ($extension instanceof IPresenterMappingProvider) {
				$this->setupPresenterMapping($extension);
			}
			if ($extension instanceof ILatteMacrosProvider) {
				$this->setupMacros($extension);
			}
		}
		$this->setupLayoutProvider($config);
	}

	public function beforeCompile()
	{
		// Loads all services tagged as router
		$this->addRouters();
	}
	/*********************** Setups ************************/
	/**
	 * Prepare service enabling change layout file on runtime
	 * @param array $config
	 */
	private function setupLayoutProvider(array $config)
	{
		$enabledKeys = array("path", "rules", "override", "mode");
		// register LayoutProvider
		$container = $this->getContainerBuilder();
		$provider = $container->addDefinition($this->prefix('layoutProvider'))
			->setClass(LayoutProvider::class);
		if (isset($config['layouts']) AND is_array($config['layouts'])) {
			foreach ($config['layouts'] as $layout) {
				if (!is_array($layout)) throw new \OutOfRangeException("Config section $this->name.layouts must contains array");
				foreach ($layout as $name => $value) {
					if (!in_array($name, $enabledKeys)) throw new \OutOfRangeException("Config section $this->name.layouts: expected parameter name one of [" . implode(", ", $enabledKeys) . "], but $name given.");
				}
				if (!isset($layout['path'])) throw new \OutOfRangeException("Config section $this->name.layouts item must contain property 'path'.");
				$rules = isset($layout['rules']) ? $layout['rules'] : array();
				$override = isset($layout['override']) ? $layout['override'] : false;
				$mode = isset($layout['mode']) ? $layout['mode'] : LayoutProvider::MODE_DOCUMENT;
				$provider->addSetup("register", array($layout['path'], $rules, $override, $mode));
			}
		}

	}

	/**
	 * @param IParametersProvider $extension
	 * @throws AssertionException
	 */
	private function setupParameters(IParametersProvider $extension)
	{
		$config = new ParametersConfig();
		$extension->setupParameters($config);
		$parameters = $config->getParameters();
		Validators::assert($parameters, 'array');
		$builder = $this->getContainerBuilder();
		if (count($parameters) > 0) {
			$builder->parameters = \Nette\DI\Config\Helpers::merge($builder->expand($parameters), $builder->parameters);
		}
	}

	/**
	 * Loads neon file
	 * @param INeonProvider $extension
	 * @throws FileNotFoundException
	 */
	private function setupNeon(INeonProvider $extension)
	{
		$builder = $this->getContainerBuilder();

		$path = $extension->getNeonPath();
		if (!is_file($path)) throw new FileNotFoundException($path);

		$this->compiler->parseServices($builder, $this->loadFromFile($path), $this->name);

	}

	/**
	 * @param IPresenterMappingProvider $extension
	 * @throws AssertionException
	 */
	private function setupPresenterMapping(IPresenterMappingProvider $extension)
	{
		$config = new PresenterMappingConfig();
		$extension->setupPresenterMapping($config);
		$mapping = $config->getPresenterMapping();
		Validators::assert($mapping, 'array', 'mapping');
		if (count($mapping)) {
			$this->getContainerBuilder()->getDefinition('nette.presenterFactory')
				->addSetup('setMapping', array($mapping));
		}
	}

	/**
	 * @param IRouterProvider $extension
	 */
	private function setupRouter(IRouterProvider $extension)
	{
		$builder = $this->getContainerBuilder();
		$router = $builder->getDefinition('router');
		/** @var CompilerExtension $extension */
		$name = $this->addRouteService($extension->getReflection()->name);
		$router->addSetup('offsetSet', array(NULL, $name));
	}

	/**
	 * @param string $class
	 * @return string
	 */
	private function addRouteService($class)
	{
		$serviceName = md5($class);
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('routeService.' . $serviceName))
			->setClass($class)
			->setInject(TRUE);
		$builder->addDefinition('routerServiceFactory.' . $serviceName)
			->setFactory($this->prefix('@routeService.' . $serviceName) . '::getRouteList')
			->setAutowired(FALSE);
		return '@routerServiceFactory.' . $serviceName;
	}

	/**
	 * Loads all services tagged as router and adds them to router service
	 */
	private function addRouters()
	{
		$builder = $this->getContainerBuilder();
		// Get application router
		$router = $builder->getDefinition('router');
		// Init collections
		$routerFactories = array();
		foreach ($builder->findByTag(self::TAG_ROUTER) as $serviceName => $priority) {
			// Priority is not defined...
			if (is_bool($priority)) {
				// ...use default value
				$priority = 100;
			}
			$routerFactories[$priority][$serviceName] = $serviceName;
		}
		// Sort routes by priority
		if (!empty($routerFactories)) {
			krsort($routerFactories, SORT_NUMERIC);
			foreach ($routerFactories as $priority => $items) {
				ksort($items, SORT_STRING);
				$routerFactories[$priority] = $items;
			}
			// Process all routes services by priority...
			foreach ($routerFactories as $priority => $items) {
				// ...and by service name...
				foreach ($items as $serviceName) {
					$factory = new Statement(array('@' . $serviceName, 'createRouter'));
					$router->addSetup('offsetSet', array(NULL, $factory));
				}
			}
		}
	}

	/**
	 * @param ILatteMacrosProvider $extension
	 * @throws AssertionException
	 */
	private function setupMacros(ILatteMacrosProvider $extension)
	{
		$config = new LatteMacrosConfig();
		$extension->setupMacros($config);
		$macros = $config->getMacros();
		Validators::assert($macros, 'array', 'macros');
		$latteFactory = $this->getLatteFactory();
		foreach ($macros as $macro) {
			if (strpos($macro, '::') === FALSE && class_exists($macro)) {
				$macro .= '::install';
			} else {
				Validators::assert($macro, 'callable', 'macro');
			}
			$latteFactory->addSetup('?->onCompile[] = function($engine) { ' . $macro . '($engine->getCompiler()); }', array('@self'));
		}
	}

	/**
	 * @return ServiceDefinition
	 */
	private function getLatteFactory()
	{
		$builder = $this->getContainerBuilder();
		return $builder->hasDefinition('nette.latteFactory') ? $builder->getDefinition('nette.latteFactory') : $builder->getDefinition('nette.latte');
	}


}