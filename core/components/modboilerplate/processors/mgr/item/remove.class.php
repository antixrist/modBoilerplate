<?php

/**
 * Remove an Items
 */
class modBoilerplateItemRemoveProcessor extends modObjectRemoveProcessor {
  public $objectType = 'item';
  public $classKey = 'modBoilerplateItem';
  public $languageTopics = array('modboilerplate:item');
  public $primaryKeyField = 'id';

  public $defaultOwnerField = '';
  public $defaultOwnerClassKey = '';
  //=//
  public $permission = 'modboilerplate_item_remove';
  public $permission_activated = 'modboilerplate_item_remove_activated';
  public $permission_deactivated = 'modboilerplate_item_remove_deactivated';
  public $permission_published = 'modboilerplate_item_remove_published';
  public $permission_unpublished = 'modboilerplate_item_remove_unpublished';
  public $permission_deleted = 'modboilerplate_item_remove_deleted';
  //или//
  public $permission_owner = 'modboilerplate_item_remove_owner';
  public $permission_colleague = 'modboilerplate_item_remove_colleague';
  public $permission_another = 'modboilerplate_item_remove_another';
  public $permission_activated_owner = 'modboilerplate_item_remove_activated_owner';
  public $permission_activated_colleague = 'modboilerplate_item_remove_activated_colleague';
  public $permission_activated_another = 'modboilerplate_item_remove_activated_another';
  public $permission_deactivated_owner = 'modboilerplate_item_remove_deactivated_owner';
  public $permission_deactivated_colleague = 'modboilerplate_item_remove_deactivated_colleague';
  public $permission_deactivated_another = 'modboilerplate_item_remove_deactivated_another';
  public $permission_published_owner = 'modboilerplate_item_remove_published_owner';
  public $permission_published_colleague = 'modboilerplate_item_remove_published_colleague';
  public $permission_published_another = 'modboilerplate_item_remove_published_another';
  public $permission_unpublished_owner = 'modboilerplate_item_remove_unpublished_owner';
  public $permission_unpublished_colleague = 'modboilerplate_item_remove_unpublished_colleague';
  public $permission_unpublished_another = 'modboilerplate_item_remove_unpublished_another';
  public $permission_deleted_owner = 'modboilerplate_item_remove_deleted_owner';
  public $permission_deleted_colleague = 'modboilerplate_item_remove_deleted_colleague';
  public $permission_deleted_another = 'modboilerplate_item_remove_deleted_another';
  //=//
  public $checkRemovePermission = false;
  public $beforeRemoveEvent = 'modBoilerplateItemBeforeRemove';
  public $afterRemoveEvent = 'modBoilerplateItemAfterRemove';
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
      'only_check_permissions' => 0,
      'checkByRelatedObjects' => 1,
      'dontRemoveIfHasChilds' => 1,
    );
  }

  /**
   * @return bool
   */
  public function checkByRelatedObjects () {
    if ($this->getProperty('dontRemoveIfHasChilds', false)) {
      if ($relatedObjs = array_merge($this->object->_aggregates, $this->object->_composites)) {
        foreach ($relatedObjs as $aAlias => $aMeta) {
          if (
            ($aMeta['cardinality'] == 'many' && $aMeta['owner'] == 'local' && count($this->object->getMany($aAlias))) ||
            ($aMeta['cardinality'] == 'one' && $aMeta['owner'] == 'local' && $this->object->getOne($aAlias))
          ) {
            return $this->modx->lexicon($this->objectType . '_has_childs');
          }
        }
      }
    }

    return true;
  }

  /**
   * Check custom permissions here
   *
   * @return bool|null|string
   */
  public function initializeForCustomPermissions () {
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
   * @return array
   */
  public function getConfigForCheckCommonCustomPermissions () {
    $config = array(
      $this->permission_activated => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivated,
      ),
      $this->permission_deactivated => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activated,
      ),
      $this->permission_published => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublished,
      ),
      $this->permission_unpublished => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_published,
      ),
      $this->permission_deleted => array(
        'field' => 'deleted',
        'value' => 1,
      ),
    );

    return $config;
  }

  /**
   * Check custom permissions here
   *
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
            $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCustomPermissionsColleague());
          } else {
            // если нет - выкидываем ошибку
            $result = $this->modx->lexicon($this->permission_colleague . '_access_denied');
          }
        }
        // проверяем на всех остальных
        else {
          if ($this->modx->hasPermission($this->permission_another)) {
            // если есть, то проверяем остальные права
            $result = $this->checkCustomPermissionsFromConfig($this->getConfigForCheckCustomPermissionsAnother());
          } else {
            // если нет - выкидываем ошибку
            $result = $this->modx->lexicon($this->permission_another . '_access_denied');
          }
        }
      }

    } else {
      $result = $this->modx->lexicon($this->objectType . '_err_nfs');
    }

    return $result;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCustomPermissionsOwner () {
    $config = array(
      $this->permission_activated_owner => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivated_owner,
      ),
      $this->permission_deactivated_owner => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activated_owner,
      ),
      $this->permission_published_owner => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublished_owner,
      ),
      $this->permission_unpublished_owner => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_published_owner,
      ),
      $this->permission_deleted_owner => array(
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
      $this->permission_activated_colleague => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivated_colleague,
      ),
      $this->permission_deactivated_colleague => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activated_colleague,
      ),
      $this->permission_published_colleague => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublished_colleague,
      ),
      $this->permission_unpublished_colleague => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_published_colleague,
      ),
      $this->permission_deleted_colleague => array(
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
      $this->permission_activated_another => array(
        'field' => 'active',
        'value' => 1,
        'oppositeFor' => $this->permission_deactivated_another,
      ),
      $this->permission_deactivated_another => array(
        'field' => 'active',
        'value' => 0,
        'oppositeFor' => $this->permission_activated_another,
      ),
      $this->permission_published_another => array(
        'field' => 'published',
        'value' => 1,
        'oppositeFor' => $this->permission_unpublished_another,
      ),
      $this->permission_unpublished_another => array(
        'field' => 'published',
        'value' => 0,
        'oppositeFor' => $this->permission_published_another,
      ),
      $this->permission_deleted_another => array(
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
    $result = true;
    if ($neededValue == $this->object->get($field) && !$this->modx->hasPermission($permission)) {
      $result = $this->modx->lexicon($permission . '_access_denied');
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

return 'modBoilerplateItemRemoveProcessor';