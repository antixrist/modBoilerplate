<?php

/**
 * Create an Item
 */
class modBoilerplateItemCreateProcessor extends modObjectCreateProcessor {
	public $objectType = 'modBoilerplateItem';
	public $classKey = 'modBoilerplateItem';
	public $languageTopics = array('modboilerplate');
	//public $permission = 'create';


	/**
	 * @return bool
	 */
	public function beforeSet() {
		$name = trim($this->getProperty('name'));
		if (empty($name)) {
			$this->modx->error->addField('name', $this->modx->lexicon('modboilerplate_item_err_name'));
		}
		elseif ($this->modx->getCount($this->classKey, array('name' => $name))) {
			$this->modx->error->addField('name', $this->modx->lexicon('modboilerplate_item_err_ae'));
		}

		return parent::beforeSet();
	}

}

return 'modBoilerplateItemCreateProcessor';