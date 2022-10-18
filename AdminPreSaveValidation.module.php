<?php namespace ProcessWire;
class AdminPreSaveValidation extends WireData implements Module {

	public static function getModuleInfo() {

		return array(
			'title' => 'Pre-Save Validation',
			'version' => '1.0.0',
			'summary' => "Forces admin editors to fix all errors before saving a page.",
			'author' => 'Mike Spooner (thetuningspoon)',
			'href' => 'http://www.solutioninnovators.com',
			'singular' => true,
			'autoload' => 'template=admin, process=ProcessPageEdit',
			'icon' => 'warning',
		);
	}

	public function init() {
		$editingPage = $this->wire->pages->get($this->input->id);
		$editingPageTemplateName = $editingPage->id ? $editingPage->template->name : null;
		if(!$editingPageTemplateName || !$this->enabledOnTemplate($editingPageTemplateName)) return;

		$this->config->scripts->add($this->config->urls->$this . "AdminPreSaveValidation.js");

		$this->wire()->addHookBefore("ProcessPageEdit::execute", function(HookEvent $event) {
			if($event->wire->input->preValidate) {
				$event->wire->config->ajax = false; // Turn off ajax flag so ProcessPageEdit's regular ajax processing isn't triggered
			}
		});

		/*
		 * Example of hook that adds an error to an inputfield
		 */
		/*
		$this->wire()->addHookAfter("Inputfield::processInput", function(HookEvent $event) {
			$event->object->error('Invalid input!');
		});
		*/

		$this->wire()->addHookAfter("ProcessPageEdit::processInput", function(HookEvent $event)  {
			if($event->wire->input->preValidate) {
				$form = $event->arguments(0);
				$level = $event->arguments(1);
				if($level == 0) {

					$fieldErrors = [];

					// Get all errors from all the inputfields on the page, including repeaters and their inputs
					function recurseFields(InputfieldWrapper $form, array &$fieldErrors) {
						$fields = $form->getAll();

						foreach($fields as $field) {
							$errors = $field->getErrors(true);
							if(count($errors)) {
								$fieldErrors[$field->attr('name')] = implode(', ', $errors);
							}
							if($field instanceof InputfieldRepeater) {
								recurseFields($field->buildForm(), $fieldErrors);
							}
						}
					}

					recurseFields($form, $fieldErrors);

					header('Content-Type: application/json; charset=utf-8');
					echo json_encode($fieldErrors);
					exit;
				}
			}
		});
	}

	public function enabledOnTemplate($templateName) {
		$enabled = false;

		if($this->applyMode == 'exclude') {
			if(!in_array($templateName, $this->selectedTemplates)) {
				$enabled = true;
			}
		}
		elseif($this->applyMode == 'include') {
			if(in_array($templateName, $this->selectedTemplates)) {
				$enabled = true;
			}
		}

		return $enabled;
	}
}