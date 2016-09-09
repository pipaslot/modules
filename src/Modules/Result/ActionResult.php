<?php


namespace Pipas\Modules\Results;

use Nette\Object;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 * @property-read bool $success
 * @property-read string $message
 */
class ActionResult extends Object
{
	/** @var bool */
	private $success;
	/** @var string|null */
	private $message;

	/**
	 * ActionResult constructor.
	 * @param bool $success
	 * @param null $message
	 */
	public function __construct($success = true, $message = null)
	{
		$this->success = $success;
		$this->message = $message;
	}

	/**
	 * @param boolean $success
	 * @return $this
	 */
	public function setSuccess($success)
	{
		$this->success = (bool)$success;
		return $this;
	}

	/**
	 * Operation result
	 * @return boolean
	 */
	public function isSuccess()
	{
		return $this->success;
	}

	/**
	 * Operation result
	 * @return boolean
	 */
	public function getSuccess()
	{
		return $this->success;
	}

	/**
	 * @param string $message
	 * @return $this
	 */
	public function setMessage($message)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Operation message
	 * @return null|string
	 */
	public function getMessage()
	{
		return $this->message;
	}
}