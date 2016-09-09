<?php


namespace Pipas\Modules\Results;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class Message
{
	/** Operation passed without problems */
	const LEVEL_SUCCESS = 1;
	/** Side information for user */
	const LEVEL_INFO = 2;
	/** Users should consider for other actions */
	const LEVEL_WARNING = 3;
	/** Information about Failure */
	const LEVEL_ERROR = 4;

	/** @var int */
	private $level = self::LEVEL_SUCCESS;
	/** @var string */
	private $text;
	/** @var int */
	private $rate;

	/**
	 * Message constructor.
	 * @param $text
	 * @param int $level
	 * @param int $rate
	 */
	public function __construct($text, $level = self::LEVEL_SUCCESS, $rate = 1)
	{
		$this->text = $text;
		$this->level = $level;
		$this->rate = $rate;
	}

	/**
	 * @return int
	 */
	public function getLevel()
	{
		return $this->level;
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}

	/**
	 * @return int
	 */
	public function getRate()
	{
		return $this->rate;
	}

	/**
	 * @param int $number
	 * @return $this
	 */
	public function increaseRate($number = 1)
	{
		$this->rate += $number;
		return $this;
	}

	public function __toString()
	{
		return ($this->rate > 1 ? $this->rate . 'x: ' : '') . $this->text;
	}
}