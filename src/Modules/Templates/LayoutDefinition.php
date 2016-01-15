<?php


namespace Pipas\Modules\Templates;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 * @property-read bool $overriding Override existing layout definitions
 * @property-read string $path Layout file absolute path
 */
class LayoutDefinition extends Object
{
	/** @var string */
	private $path;
	/** @var bool */
	private $overriding;
	/** @var array Match rules */
	private $rules = array();

	/**
	 * LayoutDefinition constructor.
	 * @param string $path
	 * @param bool $overriding Override existing layout definitions
	 */
	public function __construct($path, $overriding = false)
	{
		if (!is_file($path)) throw new \OutOfRangeException("Layout file odes not exist: " . $path);
		$this->path = $path;
		$this->overriding = (bool)$overriding;
	}

	/**
	 * Define rule for layout selection. If no rule defined, then layout is accepted every times
	 * @param string $rule
	 * @return $this
	 *
	 * @example addRule("Home");            //Only for Home presenter without module
	 * @example addRule("!Home");            //Not for Home presenter without module
	 * @example addRule("*me");            //Only for presenter ends with "me" without module
	 * @example addRule("!*me");            //Not for presenter ends with "me" without module
	 * @example addRule("*:Home");            //Only for Home presenter inside whatever first level module
	 * @example addRule("!*:Home");            //Not for Home presenters inside whatever first level module
	 * @example addRule("**:Home");            //Only for Home presenter inside whatever module
	 * @example addRule("!**:Home");        //Not for Home presenters inside whatever module
	 * @example addRule("***me");            //Only for presenter ends with "me" inside whatever module
	 * @example addRule("!***me");        //Not for presenter ends with "me" inside whatever module
	 * @example addRule("Admin:*");            //Only for presenters inside Admin module
	 * @example addRule("!Admin:*");        //Not for presenters inside Admin module
	 * @example addRule("Extra:Admin:*");    //Only for presenters inside Extra:Admin module
	 * @example addRule("!Extra:Admin:*");    //Not for presenters inside Extra:Admin module
	 * @example addRule("Extra:*:Home");    //Only for Home presenters inside Extra module wit whatever sub-module
	 * @example addRule("!Extra:*:Home");    //Not for Home presenters inside Extra module wit whatever sub-module
	 */
	public function addRule($rule)
	{
		$expected = true;
		if ($rule[0] == "!") {
			$rule = substr($rule, 1);
			$expected = false;
		}
		//Verification
		if (Strings::startsWith($rule, ":")) {
			throw new \OutOfRangeException("Rule '$rule' cannot start with char ':'");
		}
		if (Strings::endsWith($rule, ":")) {
			throw new \OutOfRangeException("Rule '$rule' cannot end with char ':'");
		}
		if (strpos($rule, '!') !== false) {
			throw new \OutOfRangeException("Rule cannot contain char '!' in the middle");
		}
		//Translations
		$translations = array(
			"::" => ":[a-zA-Z0-9]+:",
			"**" => "[a-zA-Z0-9:]+",
			"*" => "[a-zA-Z0-9]+"
		);
		$regularExpression = "~^" . strtr($rule, $translations) . "$~";
		$this->rules[$regularExpression] = $expected;
		return $this;
	}

	/**
	 * Try check if name pass through defined rules
	 * @param $name
	 * @return bool
	 */
	public function match($name)
	{
		if (count($this->rules) > 0) {
			foreach ($this->rules as $expression => $expected) {
				if ($expected != (bool)preg_match($expression, $name)) return false;
			}
		}
		return true;
	}

	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Override existing layout definitions
	 * @return bool
	 */
	public function getOverriding()
	{
		return $this->overriding;
	}
}