<?php

/**
 * Remove an Items
 */
class modBoilerplateItemRemoveProcessor extends modObjectProcessor {
	public $objectType = 'modBoilerplateItem';
	public $classKey = 'modBoilerplateItem';
	public $languageTopics = array('modboilerplate');
	//public $permission = 'remove';


	/**
	 * @return array|string
	 */
	public function process() {
		if (!$this->checkPermissions()) {
			return $this->failure($this->modx->lexicon('access_denied'));
		}

		$ids = $this->modx->fromJSON($this->getProperty('ids'));
		if (empty($ids)) {
			return $this->failure($this->modx->lexicon('modboilerplate_item_err_ns'));
		}

		foreach ($ids as $id) {
			/** @var modBoilerplateItem $object */
			if (!$object = $this->modx->getObject($this->classKey, $id)) {
				return $this->failure($this->modx->lexicon('modboilerplate_item_err_nf'));
			}

			$object->remove();
		}

		return $this->success();
	}

}

return 'modBoilerplateItemRemoveProcessor';