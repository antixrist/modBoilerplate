<?php

/**
 * Create an Item
 */
class modBoilerplateItemCreateProcessor extends modObjectCreateProcessor {
  public $objectType = '';
  public $classKey = '';
  public $languageTopics = array('modboilerplate');
  public $primaryKeyField = 'id';
  public $permission = '';
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
   * @return array
   */
  public function getWhereForCheckAlreadyExists () {

    return array();
  }

  /**
   *
   */
  public function alreadyExistsHandler () {
    $keys = array();
    $where = $this->getWhereForCheckAlreadyExists();
    if (!is_array($where) || !count($where)) {
      $where = array();
      if (is_array($this->primaryKeyField)) {
        foreach ($this->primaryKeyField as $field) {
          if ($keyValue = $this->getProperty($field)) {
            $where[$field] = $keyValue;
            $keys[]        = $field;
          }
        }
      } else if ($primaryKeyValue = $this->getProperty($this->primaryKeyField)) {
        $keys[]                        = $this->primaryKeyField;
        $where[$this->primaryKeyField] = $primaryKeyValue;
      }
    } else {
      foreach ($where as $field => $value) {
        $keys[] = $field;
      }
    }

    if (count($where)) {
      $alreadyExists = $this->modx->getObject($this->classKey, $where);
      if ($alreadyExists) {
        foreach ($keys as $field) {
          $this->addFieldError($field, $this->modx->lexicon($this->objectType . '_item_err_ae'));
        }
      }
    }
  }

  /**
   *
   */
  public function beforeSet() {
    $this->setProperty('createdby', $this->modx->getLoginUserID());
    $this->setProperty('createdon', time());

    $this->setProperty('editedby', 0);
    $this->setProperty('editedon', 0);

    $this->unsetProperty('deleted');
    $this->unsetProperty('deletedon');
    $this->unsetProperty('deletedby');

    $this->alreadyExistsHandler();

    return !$this->hasErrors();
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

return 'modBoilerplateItemCreateProcessor';