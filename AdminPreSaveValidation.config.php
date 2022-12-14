<?php namespace ProcessWire;
class AdminPreSaveValidationConfig extends ModuleConfig {
	public function __construct() {

		$selectableTemplates = [];
		foreach($this->wire->templates as $template) {
			$selectableTemplates[$template->name] = $template->getLabel();
		}

		$this->add([
			[
				'name' => 'applyMode',
				'type' => 'InputfieldRadios',
				'label' => 'Enable pre-save validation on...',
				'options' => [
					'exclude' => 'All templates except those I specify below',
					'include' => 'Specified templates only',
				],
				'value' => 'exclude',
			],
			[
				'name' => 'selectedTemplates',
				'type' => 'InputfieldAsmSelect',
				'label' => 'Templates',
				'options' => $selectableTemplates,
				'value' => [],
			]
		]);
	}
}