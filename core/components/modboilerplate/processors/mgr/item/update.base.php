<?php

/**
 * Base Update an Item
 */
class modBoilerplateItemUpdateBaseProcessor extends modObjectUpdateProcessor {
  public $objectType = 'item';
  public $classKey = 'modBoilerplateItem';
  public $languageTopics = array('modboilerplate:item');
  public $primaryKeyField = 'id';
  public $checkSavePermission = false;

  public $defaultOwnerField = '';
  public $defaultOwnerClassKey = '';
  //=//
  public $permission = 'modboilerplate_item_edit';
  public $permission_activate = 'modboilerplate_item_activate';
  public $permission_deactivate = 'modboilerplate_item_deactivate';
  public $permission_publish = 'modboilerplate_item_publish';
  public $permission_unpublish = 'modboilerplate_item_unpublish';
  public $permission_delete = 'modboilerplate_item_delete';
  public $permission_restore = 'modboilerplate_item_restore';
  //или//
  public $permission_owner = 'modboilerplate_item_edit_owner';
  public $permission_colleague = 'modboilerplate_item_edit_colleague';
  public $permission_another = 'modboilerplate_item_edit_another';
  public $permission_activate_owner = 'modboilerplate_item_activate_owner';
  public $permission_activate_colleague = 'modboilerplate_item_activate_colleague';
  public $permission_activate_another = 'modboilerplate_item_activate_another';
  public $permission_deactivate_owner = 'modboilerplate_item_deactivate_owner';
  public $permission_deactivate_colleague = 'modboilerplate_item_deactivate_colleague';
  public $permission_deactivate_another = 'modboilerplate_item_deactivate_another';
  public $permission_publish_owner = 'modboilerplate_item_publish_owner';
  public $permission_publish_colleague = 'modboilerplate_item_publish_colleague';
  public $permission_publish_another = 'modboilerplate_item_publish_another';
  public $permission_unpublish_owner = 'modboilerplate_item_unpublish_owner';
  public $permission_unpublish_colleague = 'modboilerplate_item_unpublish_colleague';
  public $permission_unpublish_another = 'modboilerplate_item_unpublish_another';
  public $permission_delete_owner = 'modboilerplate_item_delete_owner';
  public $permission_delete_colleague = 'modboilerplate_item_delete_colleague';
  public $permission_delete_another = 'modboilerplate_item_delete_another';
  public $permission_restore_owner = 'modboilerplate_item_restore_owner';
  public $permission_restore_colleague = 'modboilerplate_item_restore_colleague';
  public $permission_restore_another = 'modboilerplate_item_restore_another';
  //=//
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

    // проверять кастомные права лучше после родительской инициализации,
    // чтобы родительский процессор создал/получил нужные объект/ы, с которыми можно будет работать
    $initialized = $this->initializeForCustomPermissions();
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
   * @return bool
   */
  public function checkByRelatedObjects () {
    return true;
  }

  /**
   * Check custom permissions here
   *
   * @return bool|null|string
   */
  public function initializeForCustomPermissions () {
    $this->setCheckbox('deleted', 1);
    $this->setCheckbox('active', 1);
    $this->setCheckbox('published', 1);

//    if (($currentUser = $this->modx->getAuthenticatedUser()) && $currentUser->get('sudo')) { return true; }

    if ($this->getProperty('checkByRelatedObjects', false)) {
      $checkParents = $this->checkByRelatedObjects();
      if ($checkParents !== true) {
        return $checkParents;
      }
    }

    $ownerField = $this->defaultOwnerField;
    if (empty($ownerField) || !$ownerField) {
      $result = $this->checkCommonCustomPermissions();
    } else {
      $result = $this->checkCustomPermissionsByOwner();
    }

    return $result;
  }

  /**
   * @return bool|null|string
   */
  public function checkCommonCustomPermissions () {
    $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCommonCustomPermissions());
    return $result;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCommonCustomPermissions () {
    $config = array(
      $this->permission_activate => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivate,
      ),
      $this->permission_deactivate => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activate,
      ),
      $this->permission_publish => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublish,
      ),
      $this->permission_unpublish => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_publish,
      ),
      $this->permission_delete => array(
        'field' => 'deleted',
        'value' => 1,
      ),
    );

    return $config;
  }

  /**
   * @param array $config
   *
   * @return bool
   */
  public function checkCustomPermissionsFromConfig (array $config) {
    foreach ($config as $permission => $tmp) {
      $result = $this->checkPermissionByFieldValue($tmp['field'], $tmp['value'], $permission);
      if ($result !== true) {
        return $result;
      }
    }
    return true;
  }

  /**
   * @return bool|null|string
   */
  public function checkCustomPermissionsByOwner () {
    $currentUserId = $this->modx->getLoginUserID();

    $ownerField = $this->defaultOwnerField;
    $ownerClassKey = ($this->defaultOwnerClassKey) ? $this->defaultOwnerClassKey : $this->classKey;
    $objectOwnerId = null;
    if ($ownerClassKey) {
      if ($ownerClassKey !== $this->classKey) {
        $objectOwner = $this->object->getOne($ownerClassKey);
        if ($objectOwner) {
          $objectOwnerId = $objectOwner->get($ownerField);
        }
      } else {
        $objectOwnerId = $this->object->get($ownerField);
      }
    }

    if ($objectOwnerId) {

      if ($objectOwnerId === $currentUserId) {

        // проверяем текущие права
        if ($this->modx->hasPermission($this->permission_owner)) {
          $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCustomPermissionsOwner());
        } else {
          $result = $this->modx->lexicon($this->permission_owner . '_access_denied');
        }

      } else {
        $colleagueIds = $this->modBoilerplate->getUserColleagues($currentUserId);

        // проверяем на коллег
        if (in_array($objectOwnerId, $colleagueIds)) {

          if ($this->modx->hasPermission($this->permission_colleague)) {
            // если есть, то проверяем остальные права
            $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCustomPermissionsOwner());
          } else {
            // если нет - выкидываем ошибку
            $result = $this->modx->lexicon($this->permission_colleague . '_access_denied');
          }

        }
        // проверяем на всех остальных
        else {

          if ($this->modx->hasPermission($this->permission_another)) {
            // если есть, то проверяем остальные права
            $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCustomPermissionsOwner());
          } else {
            // если нет - выкидываем ошибку
            $result = $this->modx->lexicon($this->permission_another . '_access_denied');
          }

        }
      }

    } else {
      return $this->modx->lexicon($this->objectType . '_err_nfs');
    }

    return $result;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCustomPermissionsOwner () {
    $config = array(
      $this->permission_activate_owner => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivate_owner,
      ),
      $this->permission_deactivate_owner => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activate_owner,
      ),
      $this->permission_publish_owner => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublish_owner,
      ),
      $this->permission_unpublish_owner => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_publish_owner,
      ),
      $this->permission_delete_owner => array(
        'field' => 'deleted',
        'value' => 1,
      ),
    );

    return $config;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCustomPermissionsColleague () {
    $config = array(
      $this->permission_activate_colleague => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivate_colleague,
      ),
      $this->permission_deactivate_colleague => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activate_colleague,
      ),
      $this->permission_publish_colleague => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublish_colleague,
      ),
      $this->permission_unpublish_colleague => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_publish_colleague,
      ),
      $this->permission_delete_colleague => array(
        'field' => 'deleted',
        'value' => 1,
      ),
    );

    return $config;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCustomPermissionsAnother () {
    $config = array(
      $this->permission_activate_another => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivate_another,
      ),
      $this->permission_deactivate_another => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activate_another,
      ),
      $this->permission_publish_another => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublish_another,
      ),
      $this->permission_unpublish_another => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_publish_another,
      ),
      $this->permission_delete_another => array(
        'field' => 'deleted',
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
    if ($value !== null && $value != $this->object->get($field)) {
      if ($value == $neededValue && !$this->modx->hasPermission($permission)) {
//        $this->addFieldError($field, $this->modx->lexicon($permission . '_access_denied'));
        return $this->modx->lexicon($permission . '_access_denied');
      }
    } else {
      $this->unsetProperty($field);
    }

    return true;
  }

  /**
   * {@inheritDoc}
   */
  public function beforeSet() {
    $currentUserId = $this->modx->getLoginUserID();
    $time = time();
    $this->unsetProperty('createdby');
    $this->unsetProperty('createdon');

    $this->setProperty('editedby', $currentUserId);
    $this->setProperty('editedon', $time);

    $propertyValue = $this->getProperty('deleted', null);
    if ($propertyValue !== null) {
      if ($propertyValue) {
        $this->setProperty('deletedby', $currentUserId);
        $this->setProperty('deletedon', $time);
      } else {
        $this->setProperty('restoredby', $currentUserId);
        $this->setProperty('restoredon', $time);
      }
    }

    $propertyValue = $this->getProperty('active', null);
    if ($propertyValue !== null) {
      if ($propertyValue) {
        $this->setProperty('activatedby', $currentUserId);
        $this->setProperty('activatedon', $time);
      } else {
        $this->setProperty('deactivatedby', $currentUserId);
        $this->setProperty('deactivatedon', $time);
      }
    }

    $propertyValue = $this->getProperty('published', null);
    if ($propertyValue !== null) {
      if ($propertyValue) {
        $this->setProperty('publishedby', $currentUserId);
        $this->setProperty('publishedon', $time);
      } else {
        $this->setProperty('unpublishedby', $currentUserId);
        $this->setProperty('unpublishedon', $time);
      }
    }

    return !$this->hasErrors();
  }

  /**
   * Unset all properties (exclude primary keys by default).
   *
   * @param bool $excludePrimaries
   */
  public function unsetProperties ($excludePrimaries = true) {
    $primaryKeys = array();
    if ($excludePrimaries) {
      $primaryKeys = $this->getPrimaryKeys();
    }

    foreach ($this->getProperties() as $k => $v) {
      if (in_array($k, $primaryKeys)) {
        continue;
      }
      $this->unsetProperty($k);
    }
  }

  /**
   * @return array
   */
  public function getPrimaryKeys () {
    $primaryKeys = array();
    if (is_array($this->primaryKeyField)) {
      foreach ($this->primaryKeyField as $field) {
        $primaryKeys[] = $field;
      }
    } else if (is_string($this->primaryKeyField) && $this->primaryKeyField) {
      $primaryKeys[] = $this->primaryKeyField;
    }

    return $primaryKeys;
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

