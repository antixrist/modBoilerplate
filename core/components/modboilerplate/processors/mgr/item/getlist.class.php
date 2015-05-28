<?php

/**
 * Get a list of Items
 */
class modBoilerplateItemGetListProcessor extends modObjectGetListProcessor {
  public $objectType = 'item';
  public $classKey = 'modBoilerplateItem';
  public $languageTopics = array('modboilerplate:item');
  public $primaryKeyField = 'id';

  public $defaultOwnerField = '';
  public $defaultOwnerClassKey = '';
  //==//
  public $permission = 'modboilerplate_item_view';
  public $permission_activated = 'modboilerplate_item_view_activated';
  public $permission_deactivated = 'modboilerplate_item_view_deactivated';
  public $permission_published = 'modboilerplate_item_view_published';
  public $permission_unpublished = 'modboilerplate_item_view_unpublished';
  public $permission_deleted = 'modboilerplate_item_view_deleted';
  //или//
  public $permission_owner = 'modboilerplate_item_view_owner';
  public $permission_colleague = 'modboilerplate_item_view_colleague';
  public $permission_another = 'modboilerplate_item_view_another';
  public $permission_activated_owner = 'modboilerplate_item_view_activated_owner';
  public $permission_activated_colleague = 'modboilerplate_item_view_activated_colleague';
  public $permission_activated_another = 'modboilerplate_item_view_activated_another';
  public $permission_deactivated_owner = 'modboilerplate_item_view_deactivated_owner';
  public $permission_deactivated_colleague = 'modboilerplate_item_view_deactivated_colleague';
  public $permission_deactivated_another = 'modboilerplate_item_view_deactivated_another';
  public $permission_published_owner = 'modboilerplate_item_view_published_owner';
  public $permission_published_colleague = 'modboilerplate_item_view_published_colleague';
  public $permission_published_another = 'modboilerplate_item_view_published_another';
  public $permission_unpublished_owner = 'modboilerplate_item_view_unpublished_owner';
  public $permission_unpublished_colleague = 'modboilerplate_item_view_unpublished_colleague';
  public $permission_unpublished_another = 'modboilerplate_item_view_unpublished_another';
  public $permission_deleted_owner = 'modboilerplate_item_view_deleted_owner';
  public $permission_deleted_colleague = 'modboilerplate_item_view_deleted_colleague';
  public $permission_deleted_another = 'modboilerplate_item_view_deleted_another';
  //==//
  public $checkListPermission = false;
  public $defaultSortField = 'id';
  public $defaultSortDirection = 'ASC';
  public $defaultSearchQueryVar = '';
  public $defaultSearchMinQuery = 0;
  public $defaultSearchInFields = '';
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
      'offset' => 0,
      'limit' => 10,
      'sortby' => $this->defaultSortField,
      'sortdir' => $this->defaultSortDirection,
      'minQuery' => $this->defaultSearchMinQuery,
      'queryVar' => $this->defaultSearchQueryVar,
      'searchInFields' => $this->defaultSearchInFields,
      'only_check_permissions' => 0,
      'checkCustomPermissions' => 0,
      'checkByRelatedObjects' => 1,
      'where' => array(),
//      'showInactive' => 0,
//      'showDeleted' => 0,
//      'showUnpublished' => 0,
    );
  }

  /**
   * {@inheritDoc}
   */
  public function getData() {
    $data = array();
    $offset = intval($this->getProperty('offset'));
    $limit = intval($this->getProperty('limit'));

    $c = $this->modx->newQuery($this->classKey);
    $c = $this->_prepareQueryBeforeCount($c);
    $data['total'] = $this->modx->getCount($this->classKey,$c);
    $c = $this->prepareQueryAfterCount($c);

    $sortClassKey = $this->getSortClassKey();
    $sortKey = $this->modx->getSelectColumns($sortClassKey,$this->getProperty('sortAlias',$sortClassKey),'',array($this->getProperty('sortby')));
    if (empty($sortKey)) $sortKey = $this->getProperty('sortby');
    $c->sortby($sortKey,$this->getProperty('sortdir'));

    if ($limit > 0) {
      $c->limit($limit,$offset);
    }
//    $this->queryLog($c);
    $data['results'] = $this->modx->getIterator($this->classKey,$c);

    return $data;
  }

  /**
   * Prepare query for searching, custom permissions, additional conditions, etc
   *
   * @param xPDOQuery $c
   * @return xPDOQuery
   */
  public function _prepareQueryBeforeCount (xPDOQuery $c) {
    $c = $this->prepareQueryForSearch($c);
    $c = $this->prepareQueryForCustomPermissions($c);
    // additionalConditions должен идти ПОСЛЕ кастомной обработки!
    $c = $this->additionalConditions($c);
    $c = $this->additionalWhere($c);
    $c = $this->prepareQueryBeforeCount($c);

    return $c;
  }

  /**
   * Prepare query for searching
   *
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function prepareQueryForSearch (xPDOQuery $c) {
    $queryVar = $this->getProperty('queryVar');

    if ($queryVar) {
      $query = $this->getProperty($queryVar, '');
      $query = !empty($query)
        ? htmlspecialchars(strip_tags(trim($query)))
        : '';
      $minQuery = intval($this->getProperty('minQuery', 0));

      if ($query && mb_strlen($query,'UTF-8') >= $minQuery) {
        $searchQueryInFields = $this->getProperty('searchInFields');
        $searchQueryInFields = $this->modBoilerplate->getArray($searchQueryInFields);
        $i = 0;
        $where = array();
        foreach ($searchQueryInFields as $field) {
          $firstOR = ($i) ? 'OR:' : '';
          $where[$firstOR . $field .':LIKE'] = '%'.$query.'%';
          $i++;
        }

        if (count($where)) {
          $c->where(array($where));
        }
      }
    }

    return $c;
  }

  /**
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function prepareQueryForCustomPermissions (xPDOQuery $c) {
//    if (($currentUser = $this->modx->getAuthenticatedUser()) && $currentUser->get('sudo')) { return $c; }

    if ($this->getProperty('checkByRelatedObjects', false)) {
      $c = $this->prepareQueryForCheckRelatedObjects($c);
    }

    $ownerField = $this->defaultOwnerField;
    if (empty($ownerField) || !$ownerField) {
      $c = $this->prepareQueryForCommonCustomPermissions($c);
    } else {
      $c = $this->prepareQueryForCustomPermissionsByOwner($c);
    }

    return $c;
  }

  /**
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function prepareQueryForCheckRelatedObjects (xPDOQuery $c) {
    return $c;
  }

  /**
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function prepareQueryForCommonCustomPermissions (xPDOQuery $c) {
    $conditions = $this->getConditionsFromCustomPermissionsConfig($this->getConfigForCommonCustomPermissions());
    if (count($conditions)) {
      $c->where(array($conditions));
    }
    return $c;
  }

  /**
   * @return array
   */
  public function getConfigForCommonCustomPermissions () {
    $config = array(
      $this->permission_activated => array(
        'oppositeFor' => $this->permission_deactivated,
        'conditions' => array(
          'active:=' => 0
        )
      ),
      $this->permission_deactivated => array(
        'oppositeFor' => $this->permission_activated,
        'conditions' => array(
          'active:=' => 1
        )
      ),
      $this->permission_published => array(
        'oppositeFor' => $this->permission_unpublished,
        'conditions' => array(
          'published:=' => 0
        )
      ),
      $this->permission_unpublished => array(
        'oppositeFor' => $this->permission_published,
        'conditions' => array(
          'published:=' => 1
        )
      ),
      $this->permission_deleted => array(
        'conditions' => array(
          'deleted:=' => 0
        )
      ),
    );

    return $config;
  }

  /**
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function prepareQueryForCustomPermissionsByOwner (xPDOQuery $c) {
    $ownerField = $this->defaultOwnerField;
    $ownerClassKey = ($this->defaultOwnerClassKey) ? $this->defaultOwnerClassKey : $this->classKey;

    $where = array();
    $usedUsersIds = array();

    $currentUserId = $this->modx->getLoginUserID();
    $colleagueIds = $this->modBoilerplate->getUserColleagues($currentUserId);

    if ($this->modx->hasPermission($this->permission_owner)) {
      $_where = array();
      $_where[$ownerClassKey .'.'. $ownerField .':='] = $currentUserId;
      $conditions = $this->getConditionsFromCustomPermissionsConfig($this->getConfigForCheckCustomPermissionsOwner());
      if (count($conditions)) {
        $_where = array_merge($_where, $conditions);
      }
      if (count($_where)) {
        $where[] = $_where;
      }
    }
    // запоминаем idшник юзера
    $usedUsersIds[] = $currentUserId;

    // теперь проверяем права на просмотр объектов коллег
    if ($this->modx->hasPermission($this->permission_colleague)) {
      $_where = array();
      if (count($colleagueIds)) {
        // добавляем в группу условий idшники коллег
        $_where['OR:' . $ownerClassKey .'.'. $ownerField .':IN'] = $colleagueIds;

        // получаем условия исходя из прав на объекты коллег
        $conditions = $this->getConditionsFromCustomPermissionsConfig($this->getConfigForCheckCustomPermissionsColleague());
        if (count($conditions)) {
          $_where = array_merge($_where, $conditions);
        }
      }
      // добавляем эти условия к остальным
      if (count($_where)) {
        $where[] = $_where;
      }
    }
    // запоминаем idшники коллег
    $usedUsersIds = array_merge($usedUsersIds, $colleagueIds);

    // проверяем у юзера права на просмотр всех остальных объектов
    if ($this->modx->hasPermission($this->permission_another)) {
      $_where = array();
      if (count($usedUsersIds)) {
        // добавляем в группу условий уже использованные idшники коллег,
        // чтобы их исключить для текущей группы условий
        // таким образом мы создаём условия для всех остальных пользователей - для не текущего и для не коллег
        $_where['OR:' . $ownerClassKey .'.'. $ownerField .':NOT IN'] = $usedUsersIds;

        // получаем условия исходя из прав на объекты коллег
        $conditions = $this->getConditionsFromCustomPermissionsConfig($this->getConfigForCheckCustomPermissionsAnother());
        if (count($conditions)) {
          $_where = array_merge($_where, $conditions);
        }
      }
      // добавляем эти условия к остальным
      if (count($_where)) {
        $where[] = $_where;
      }
    }

    if (!count($where)) {
      $where[] = '2 = 1';
    }
    $c->where(array($where));

    return $c;
  }

  /**
   * @return array
   */
  public function getConfigForCheckCustomPermissionsOwner () {
    $config = array(
      $this->permission_activated_owner => array(
        'oppositeFor' => $this->permission_deactivated_owner,
        'conditions' => array(
          'active:=' => 0
        )
      ),
      $this->permission_deactivated_owner => array(
        'oppositeFor' => $this->permission_activated_owner,
        'conditions' => array(
          'active:=' => 1
        )
      ),
      $this->permission_published_owner => array(
        'oppositeFor' => $this->permission_unpublished_owner,
        'conditions' => array(
          'published:=' => 0
        )
      ),
      $this->permission_unpublished_owner => array(
        'oppositeFor' => $this->permission_published_owner,
        'conditions' => array(
          'published:=' => 1
        )
      ),
      $this->permission_deleted_owner => array(
        'conditions' => array(
          'deleted:=' => 0
        )
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
        'oppositeFor' => $this->permission_deactivated_colleague,
        'conditions' => array(
          'active:=' => 0
        )
      ),
      $this->permission_deactivated_colleague => array(
        'oppositeFor' => $this->permission_activated_colleague,
        'conditions' => array(
          'active:=' => 1
        )
      ),
      $this->permission_published_colleague => array(
        'oppositeFor' => $this->permission_unpublished_colleague,
        'conditions' => array(
          'published:=' => 0
        )
      ),
      $this->permission_unpublished_colleague => array(
        'oppositeFor' => $this->permission_published_colleague,
        'conditions' => array(
          'published:=' => 1
        )
      ),
      $this->permission_deleted_colleague => array(
        'conditions' => array(
          'deleted:=' => 0
        )
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
        'oppositeFor' => $this->permission_deactivated_another,
        'conditions' => array(
          'active:=' => 0
        )
      ),
      $this->permission_deactivated_another => array(
        'oppositeFor' => $this->permission_activated_another,
        'conditions' => array(
          'active:=' => 1
        )
      ),
      $this->permission_published_another => array(
        'oppositeFor' => $this->permission_unpublished_another,
        'conditions' => array(
          'published:=' => 0
        )
      ),
      $this->permission_unpublished_another => array(
        'oppositeFor' => $this->permission_published_another,
        'conditions' => array(
          'published:=' => 1
        )
      ),
      $this->permission_deleted_another => array(
        'conditions' => array(
          'deleted:=' => 0
        )
      ),
    );

    return $config;
  }

  /**
   * @param array $config
   *
   * @return array
   */
  public function getConditionsFromCustomPermissionsConfig (array $config) {
    $conditions = array();

    $checked = array();
    foreach ($config as $permission => $tmp) {
      $checked[$permission] = $this->modx->hasPermission($permission);
    }

    foreach ($checked as $permission => $hasPermission) {
      if (isset($config[$permission]['oppositeFor']) && isset($config[$config[$permission]['oppositeFor']])) {
        $one = $checked[$permission];
        $two = $checked[$config[$permission]['oppositeFor']];

        if ( ($one === true && $two === true) || ($one !== true && $two !== true) ) { continue; }
      }

      if (!$hasPermission && is_array($config[$permission]['conditions']) && count($config[$permission]['conditions'])) {
        $conditions[] = $config[$permission]['conditions'];
      }
    }

    return $conditions;
  }

  /**
   * Prepare query for additional conditions
   *
   * @param xPDOQuery $c
   * @return xPDOQuery
   */
  public function additionalConditions (xPDOQuery $c) {
    $config = $this->getProperties();
    $class = $this->classKey;

    $params = array(
      'ids' => array(
        'field' => 'id',
        'multi' => 1,
      ),
      'resources' => array(
        'field' => 'id',
        'multi' => 1,
      ),
      'showDeleted' => array(
        'field' => 'deleted',
        'multi' => 0,
      ),
      'showInactive' => array(
        'field' => 'deleted',
        'multi' => 0,
      ),
      'showUnpublished' => array(
        'field' => 'deleted',
        'multi' => 0,
      ),
    );

    $where = $c->query['where'];
    // Exclude parameters that may already have been processed
    foreach ($params as $param => $paramConfig) {
      $found = false;
      if (isset($config[$param]) && !$paramConfig['multi']) {
        $field = $paramConfig['field'];
        foreach ($where as $k => $v) {
          // Usual condition
          if (!is_numeric($k) && strpos($k, $field) === 0 || strpos($k, $class.'.'.$field) !== false) {
            $found = true;
            break;
          }
          // Array of conditions
          elseif (is_numeric($k) && is_array($v)) {
            foreach ($v as $k2 => $v2) {
              if (strpos($k2, $field) === 0 || strpos($k2, $class.'.'.$field) !== false) {
                $found = true;
                break(2);
              }
            }
          }
          // Raw SQL string
          elseif (is_numeric($k) && strpos($v, $class) !== false && preg_match('/\b'.$field.'\b/i', $v)) {
            $found = true;
            break;
          }
        }
        if ($found) {
          unset($params[$param]);
        }
        else {
          $params[$param] = $config[$param];
        }
      }
      else if (isset($config[$param])) {
        $params[$param] = $config[$param];
      } else {
        unset($params[$param]);
      }
    }

    // Process the remaining parameters
    foreach ($params as $param => $value) {
      switch ($param) {
        case 'ids':
        case 'resources':
          if (!empty($value)) {
            $resources = array();
            if (is_array($value)) {
              $resources = $value;
              $resources = array_map('trim', $resources);
            } else if (is_string($value)) {
              if (($value[0] == '[' || $value[0] == '{')) {
                $resources = $this->modx->fromJSON($value, 1);
                $resources = array_map('trim', $resources);
              } else {
                $resources = array_map('trim', explode(',', $value));
              }
            } else if ($value) {
              $resources = array($value);
            }

            $resources_in = $resources_out = array();
            foreach ($resources as $v) {
              if (!is_numeric($v)) {continue;}
              if ($v < 0) {$resources_out[] = abs($v);}
              else {$resources_in[] = abs($v);}
            }

            if (!empty($resources_in)) {
              $c->where(array($class.'.id:IN' => $resources_in));
            }
            if (!empty($resources_out)) {
              $c->where(array($class.'.id:NOT IN' => $resources_out));
            }
          }
          break;
        case 'showUnpublished':
          if (!empty($value) && !$value && $value !== 'false') {
            $c->where(array($class.'.published' => 1));
          }
          break;
        case 'showDeleted':
          if (!empty($value) && !$value && $value !== 'false') {
            $c->where(array($class.'.deleted' => 0));
          }
          break;
        case 'showInactive':
          if (!empty($value) && !$value && $value !== 'false') {
            $c->where(array($class.'.active' => 1));
          }
          break;
      }
    }

    return $c;
  }

  /**
   * @param xPDOQuery $c
   *
   * @return xPDOQuery
   */
  public function additionalWhere (xPDOQuery $c) {
    $where = $this->getProperty('where', null);
    if ($where) {
      $where = $this->modBoilerplate->getArray($where);
      if (count($where)) {
        $c->where($where);
      }
    }
    return $c;
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
   * @param xPDOQuery $c
   */
  public function queryLog (xPDOQuery $c) {
    $q = clone($c);
    if ($q->prepare()) {
      $this->log($q->toSQL());
    }
  }

  /**
   * @param string $input
   */
  public function log ($input = '') {
    if ($input) {
      $this->modx->log(modX::LOG_LEVEL_ERROR, $input);
    }
  }

  /**
   * @param array $where
   *
   * @return bool
   */
  public function isDeepEmptyArray (array $where = array()) {
    // array('field' => 'value', 'sql string', array('field2' => 'value'));

    foreach ($where as $k => $v) {
      if (is_string($k) && $k) {
        return false;
      }
      if (is_string($v) && $v) {
        return false;
      }
      if (is_numeric($k) && is_array($v)) {
        return $this->isDeepEmptyArray($v);
      }
    }

    return true;
  }

}

return 'modBoilerplateItemGetListProcessor';


