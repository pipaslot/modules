<?php


namespace App;


use Nette\Application\UI\Form;
use Pipas\Modules\Presenters\AjaxPresenter;

/**
 * @author Petr Štipek <p.stipek@email.cz>
 */
class DefaultLayoutPresenter extends AjaxPresenter
{
	protected function createComponentTestForm()
	{
		$form = new Form();
		$form->addText("input", "Input");
		$form->addSubmit("submit", "Save")
			->onClick[] = function () {
			$this->flashMessage("Saved", "success");
		};
		$form->addSubmit("sendAndClose", "Save and close")
			->onClick[] = function () {
			$this->flashMessage("Success", "success");
			$this->flashMessage("Error", "error");
			$this->flashMessage("Warning", "warning");
			$this->flashMessage("Info", "info");
			$this->modal->close();
		};

		return $form;
	}
}