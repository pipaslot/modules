<?php


namespace Pipas\Modules\Results;


/**
 * User interface action result
 * @author Petr Štipek <p.stipek@email.cz>
 */
class UIResult extends ActionResult
{
	/** @var Message[] */
	private $messages = array();

	/**
	 * ActionResult constructor.
	 * @param bool $success
	 * @param null $message
	 * @param Message[] $userInterfaceMessages
	 */
	public function __construct($success = true, $message = null, $userInterfaceMessages = array())
	{
		parent::__construct($success, $message);
		$this->messages = $userInterfaceMessages;
	}

	/**
	 * User interface message
	 * @param string $message
	 * @param int $rate
	 * @return ActionResult
	 */
	public function addSuccess($message, $rate = 1)
	{
		return $this->addMessageText($message, Message::LEVEL_SUCCESS, $rate);
	}

	/**
	 * User interface message
	 * @param string $message
	 * @param int $rate
	 * @return ActionResult
	 */
	public function addInfo($message, $rate = 1)
	{
		return $this->addMessageText($message, Message::LEVEL_INFO, $rate);
	}

	/**
	 * User interface message
	 * @param string $message
	 * @param int $rate
	 * @return ActionResult
	 */
	public function addWarning($message, $rate = 1)
	{
		return $this->addMessageText($message, Message::LEVEL_WARNING, $rate);
	}

	/**
	 * User interface message
	 * @param string $message
	 * @param int $rate
	 * @return ActionResult
	 */
	public function addError($message, $rate = 1)
	{
		return $this->addMessageText($message, Message::LEVEL_ERROR, $rate);
	}

	/**
	 * User interface messages
	 * @return Message[]
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * User interface messages
	 * @param array $messages
	 * @return $this
	 */
	public function setMessages(array $messages)
	{
		$this->messages = array();
		$this->addMessages($messages);
		return $this;
	}

	/**
	 * User interface messages
	 * @param array $messages
	 * @return $this
	 */
	public function addMessages(array $messages)
	{
		foreach ($messages as $message) {
			$this->addMessage($message);
		}
		return $this;
	}

	/**
	 * User interface message
	 * @param Message $actionMessage
	 * @return $this
	 */
	public function addMessage(Message $actionMessage)
	{
		$key = $this->formatMessageKey($actionMessage);
		if (isset($this->messages[$key])) {
			$this->messages[$key]->increaseRate($actionMessage->getRate());
		} else {
			$this->messages[$key] = $actionMessage;
		}

		return $this;
	}


	/**
	 * User interface message
	 * @param string $message
	 * @param int $messageLevel
	 * @param int $rate
	 * @return $this
	 */
	public function addMessageText($message, $messageLevel, $rate = 1)
	{
		$key = $this->formatKey($message, $messageLevel);
		if (isset($this->messages[$key])) {
			$this->messages[$key]->increaseRate($rate);
		} else {
			$this->messages[$key] = new Message($message, $messageLevel, $rate);
		}
		return $this;
	}

	/**
	 * Prepare key name for internal using
	 * @param Message $actionMessage
	 * @return string
	 */
	private function formatMessageKey(Message $actionMessage)
	{
		return $this->formatKey($actionMessage->getText(), $actionMessage->getLevel());
	}

	/**
	 * Prepare key name for internal using
	 * @param string $message
	 * @param int $level
	 * @return string
	 */
	private function formatKey($message, $level)
	{
		return $level . '-' . $message;
	}
}