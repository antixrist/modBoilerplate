<?php

/**
 * Remove an Items
 */
class modBoilerplateItemRemoveProcessor extends modObjectRemoveProcessor {
  public $objectType = '';
  public $classKey = '';
  public $languageTopics = array('modboilerplate');
  public $primaryKeyField = 'id';
  public $permission = '';
  public $checkRemovePermission = false;
  public $beforeSaveEvent = '';
  public $afterSaveEvent = '';
  /** @var modBoilerplate $modBoilerplate */
  public $modBoilerplate;

  /**
   * Load modBoilerplate to processor
   *
   * @return bool
   */
  public function loadClass() {
    /** @noinspection PhpUndefinedFieldInspection */
    if (!empty($this->modx->modBoilerplate) && $this->modx->dachaRai instanceof modBoilerplate) {
      /** @noinspection PhpUndefinedFieldInspection */
      $this->modBoilerplate = & $this->modx->modBoilerplate;
    }
    else {
      $path = $this->modx->getOption('modboilerplate.core_path');
      $path = ($path) ? $path : $this->modx->getOption('core_path') . 'components/modboilerplate/';
      $path .= 'model/modboilerplate/';
      /** @var modBoilerplate $modBoilerplate */
      $modBoilerplate = $this->modx->getService('modBoilerplate', 'modBoilerplate', $path);
      if (!($modBoilerplate instanceof modBoilerplate)) {
        return 'Could not initialize modBoilerplate';
      }
    }

    return $this->modBoilerplate instanceof modBoilerplate;
  }

  /**
   * {@inheritDoc}
   */
  public function run() {
    /** string|bool $loaded */
    $loaded = $this->loadClass();
    if ($loaded !== true) {
      $response = new modProcessorResponse($this->modx, $this->failure($loaded));
      return $response;
    }
    return parent::run();
  }

  /**
   * {@inheritDoc}
   */
  public function cleanup() {
    $tmp = ($this->permission) ? $this->permission : $this->objectType;
    return $this->success($this->modx->lexicon($tmp .'_success'),$this->object);
  }
  /**
   * {@inheritDoc}
   */
  public function failure($msg = '',$object = null) {
    $tmp = ($this->permission) ? $this->permission : $this->objectType;
    $msg = ($msg) ? $msg : $this->modx->lexicon($tmp .'_err');
    return $this->modx->error->failure($msg,$this->getProperties());
  }

}

return 'modBoilerplateItemRemoveProcessor';