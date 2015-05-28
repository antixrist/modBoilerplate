<?php

/**
 * Create an Item
 */
class modBoilerplateItemCreateProcessor extends modObjectCreateProcessor {
  public $objectType = 'item';
  public $classKey = 'modBoilerplateItem';
  public $languageTopics = array('modboilerplate:item');
  public $primaryKeyField = 'id';
  public $permission = 'modboilerplate_item_new';
  public $permission_activate = 'modboilerplate_item_activate_owner';
  public $permission_publish = 'modboilerplate_item_publish_owner';
  public $permission_delete = 'modboilerplate_item_delete_owner';
  public $beforeSaveEvent = 'modBoilerplateItemBeforeSave';
  public $afterSaveEvent = 'modBoilerplateItemAfterSave';
  /** @var modBoilerplate $modBoilerplate */
  public $modBoilerplate;

  /**
   * Load modBoilerplate to processor
   *
   * @return bool
   */
  public function loadClass() {
    /** @noinspection PhpUndefinedFieldInspection */
    if (!empty($this->modx->modBoilerplate) && $this->modx->modBoilerplate instanceof modBoilerplate) {
      /** @noinspection PhpUndefinedFieldInspection */
      $this->modBoilerplate = & $this->modx->modBoilerplate;
    }
    else {
      $path = $this->modx->getOption('modboilerplate.core_path');
      $path = ($path) ? $path : $this->modx->getOption('core_path') . 'components/modboilerplate/';
      $path .= 'model/modboilerplate/';
      /** @var modBoilerplate $modBoilerplate */
      $modBoilerplate = $this->modx->getService('modboilerplate', 'modboilerplate', $path);
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
    $this->modx->error->reset();
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
  public function initialize () {
    $defaults = $this->getDefaultProperties();
    $defaults = (is_array($defaults)) ? $defaults : array();
    $this->setDefaultProperties($defaults);

    $initialized = parent::initialize();
    if ($initialized !== true) {
      return $initialized;
    }

    $initialized = $this->alreadyExistsHandler();
    if ($initialized !== true) {
      return $initialized;
    }

    // проверять кастомные права лучше после родительской инициализации,
    // чтобы родительский процессор создал/получил нужные объект/ы, с которыми можно будет работать
    $initialized = $this->checkCustomPermissions();
    if ($initialized !== true) {
      return $initialized;
    }

    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function process () {
    $onlyCheckPermissions = $this->getProperty('only_check_permissions', 0);
    if ($onlyCheckPermissions) {
      return $this->success();
    }

    return parent::process();
  }

  /**
   * Setup default properties here
   *
   * @return array
   */
  public function getDefaultProperties () {
    return array(
      'deleted' => 0,
      'published' => 0,
      'active' => 0,
      'only_check_permissions' => 0,
      'checkByRelatedObjects' => 1,
    );
  }

  /**
   * Check custom permissions here
   *
   * @return bool|null|string
   */
  public function checkCustomPermissions () {
//    if (($currentUser = $this->modx->getAuthenticatedUser()) && $currentUser->get('sudo')) { return true; }

    if ($this->getProperty('checkByRelatedObjects', false)) {
      $checkParents = $this->checkByRelatedObjects();
      if ($checkParents !== true) {
        return $checkParents;
      }
    }

    $this->setCheckbox('deleted', 1);
    $this->setCheckbox('active', 1);
    $this->setCheckbox('published', 1);

    $config = $this->getConfigForCustomPermissions();
    foreach ($config as $permission => $tmp) {
      $result = $this->checkPermissionByFieldValue($tmp['field'], $tmp['value'], $permission);
      if ($result !== true) {
        return $result;
      }
    }

    return true;
  }

  /**
   * @return bool
   */
  public function checkByRelatedObjects () {
    return true;
  }

  /**
   * @return array
   */
  public function getConfigForCustomPermissions () {
    $config = array(
      $this->permission_delete => array(
        'field' => 'deleted',
        'value' => 1,
      ),
      $this->permission_activate => array(
        'field' => 'active',
        'value' => 1,
      ),
      $this->permission_publish => array(
        'field' => 'published',
        'value' => 1,
      ),
    );

    return $config;
  }

  /**
   * If $field in properties equal $neededValue then check $permission
   *
   * @param string $field
   * @param mixed $neededValue
   * @param string $permission
   *
   * @return bool|null|string
   */
  public function checkPermissionByFieldValue ($field, $neededValue, $permission) {
    $value = $this->getProperty($field, null);
    if ($value !== null && $value == $neededValue && !$this->modx->hasPermission($permission)) {
//      $this->addFieldError($field, $this->modx->lexicon($permission . '_access_denied'));
      return $this->modx->lexicon($permission . '_access_denied');
    }

    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function beforeSet() {
    $currentUserId = $this->modx->getLoginUserID();
    $time = time();

    $this->setProperty('createdby', $currentUserId);
    $this->setProperty('createdon', $time);

    $this->setProperty('editedby', 0);
    $this->setProperty('editedon', 0);

    if ($this->getProperty('deleted')) {
      $this->setProperty('deletedby', $currentUserId);
      $this->setProperty('deletedon', $time);
    } else {
      $this->setProperty('deletedby', 0);
      $this->setProperty('deletedon', 0);
    }
    $this->setProperty('restoredby', 0);
    $this->setProperty('restoredon', 0);

    if ($this->getProperty('published')) {
      $this->setProperty('publishedby', $currentUserId);
      $this->setProperty('publishedon', $time);
    } else {
      $this->setProperty('publishedby', 0);
      $this->setProperty('publishedon', 0);
    }
    $this->setProperty('unpublishedby', 0);
    $this->setProperty('unpublishedon', 0);

    if ($this->getProperty('activated')) {
      $this->setProperty('activatedby', $currentUserId);
      $this->setProperty('activatedon', $time);
    }
    $this->setProperty('deactivatedby', 0);
    $this->setProperty('deactivatedon', 0);

    return !$this->hasErrors();
  }

  /**
   * Check for already exists
   */
  public function alreadyExistsHandler () {
    $result = true;
    $condition = $this->getConditionForCheckAlreadyExists();

    if (count($condition)) {
      $alreadyExists = $this->modx->getObject($this->classKey, $condition);
      if ($alreadyExists) {
        foreach (array_keys($condition) as $field) {
          $this->addFieldError($field, $this->modx->lexicon('modboilerplate_'. $this->objectType . '_err_ae'));
          $result = false;
        }
      }
    }

    if (!$result) {
      return $this->modx->lexicon('modboilerplate_'. $this->objectType . '_err_ae');
    }
    return true;
  }

  /**
   * Return condition for alreadyExistsHandler.
   * By default returned primary keys properties.
   *
   * @return array
   */
  public function getConditionForCheckAlreadyExists () {
    return $this->getPrimaryKeysPropertiesArray();
  }

  /**
   * Return properties of primary keys.
   *
   * @return array
   */
  public function getPrimaryKeysPropertiesArray () {
    $result = array();
    if (is_array($this->primaryKeyField)) {
      foreach ($this->primaryKeyField as $field) {
        if ($keyValue = $this->getProperty($field)) {
          $result[$field] = $keyValue;
        }
      }
    } else if ($primaryKeyValue = $this->getProperty($this->primaryKeyField)) {
      $result[$this->primaryKeyField] = $primaryKeyValue;
    }

    return $result;
  }

  /**
   * {@inheritDoc}
   */
  public function cleanup($object = null) {
    $object = (is_array($object)) ? $object : $this->object;
    return $this->success('',$object);
  }

  /**
   * {@inheritDoc}
   */
  public function failure($msg = '', $object = null) {
    $tmp = ($this->permission) ? $this->permission : 'modboilerplate_'. $this->objectType;
    $msg = ($msg) ? $msg : $this->modx->lexicon($tmp .'_err');
    $object = ($object) ? $object : $this->getProperties();
    return $this->modx->error->failure($msg, $object);
  }

}

return 'modBoilerplateItemCreateProcessor';