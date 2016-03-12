<?php


namespace Pipas\Modules\Presenters\Modal;

use Nette\Application\UI\Presenter;

/**
 * Presenter component
 * @author Petr Štipek <p.stipek@email.cz>
 */
class ModalDialog
{
	/** @var Presenter */
	private $presenter;

	/**
	 * ModalDialog constructor.
	 * @param Presenter $presenter
	 */
	public function __construct(Presenter $presenter)
	{
		$this->presenter = $presenter;
	}

	/**
	 * If current request is targeted to modal dialog
	 * @return bool
	 */
	public function isRequested()
	{
		return $this->presenter->isAjax() AND $this->presenter->getParameter('_target', null) == "modal";
	}

	/**
	 * If modal is requested, send flash message and close modal
	 * @return bool
	 * @throws \Nette\Application\AbortException
	 */
	public function close()
	{
		if ($this->isRequested()) {
			$this->copyMessagesToPayload();
			$this->presenter->terminate();
			return true;
		}
		return false;
	}

	private function copyMessagesToPayload()
	{
		$id = $this->presenter->getParameterId('flash');
		$messages = $this->presenter->getFlashSession()->$id;
		$payload = $this->presenter->payload;
		$payload->messageInfo = [];
		$payload->messageError = [];
		$payload->messageWarning = [];
		$payload->messageSuccess = [];
		foreach ($messages as $flash) {
			$type = strtolower($flash->type);
			if ($type == "success") $payload->messageSuccess[] = $flash->message;
			elseif ($type == "warning") $payload->messageWarning[] = $flash->message;
			elseif ($type == "error") $payload->messageError[] = $flash->message;
			else $payload->messageInfo[] = $flash->message;
		}
		if (count($payload->messageInfo) == 0) unset($payload->messageInfo);
		if (count($payload->messageError) == 0) unset($payload->messageError);
		if (count($payload->messageWarning) == 0) unset($payload->messageWarning);
		if (count($payload->messageSuccess) == 0) unset($payload->messageSuccess);
	}
}