<?php

class modBoilerplateFiltersHandler extends mse2FiltersHandler {
  /** @var modBoilerplate $modBoilerplate */
  public $modBoilerplate;

  public function __construct(mSearch2 &$mse2,array $config = array()) {
    parent::__construct($mse2, $config);

    if (!$this->modBoilerplateLoadClass()) {
      $this->modx->log(modX::LOG_LEVEL_ERROR, '[modBoilerplate] Couldn\'t load modBoilerplate class to modBoilerplateFiltersHandler.');
    } else {
      $modBoilerplateObjectsClassesList = $this->modBoilerplate->getPackageObjectsClasses();
      foreach ($modBoilerplateObjectsClassesList as $object => $className) {
        $this->config['sortAliases'][$object] = $className;
      }
    }
  }

  /**
   * Load modBoilerplate to processor
   *
   * @return bool
   */
  public function modBoilerplateLoadClass() {
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
      $modBoilerplate = $this->modx->getService('modBoilerplate', 'modBoilerplate', $path);
      if (!($modBoilerplate instanceof modBoilerplate)) {
        return 'Could not initialize modBoilerplate';
      }
    }

    return $this->modBoilerplate instanceof modBoilerplate;
  }


  public function modBoilerplateObjectValues($processor, array $fields, array $ids) {
    $filters = array();

    $response = $this->modBoilerplate->runProcessor($processor, array(
      'ids' => $ids,
    ));
    if ($response) {
      $response = $this->modBoilerplate->prepareProcessorResponse($response);
    }

    $fields[] = 'id';
    if (is_array($response['results'])) {
      foreach ($response['results'] as $row) {
        foreach ($row as $k => $v) {
          $v = (is_string($v)) ? trim($v) : $v;
          if ($v === '' || $k == 'id' || !in_array($k, $fields)) { continue; }

          if (!isset($filters[$k][$v]) || !is_array($filters[$k][$v])) {
            $filters[$k][$v] = array();
          }
          $filters[$k][$v][] = $row['id'];
        }
      }
    }

    return $filters;
  }

  public function getItemValues(array $fields, array $ids) {
    return $this->modBoilerplateObjectValues('web/item/getlist', $fields, $ids);
  }


  /**
   * Prepares values for filter
   * Retrieves names of permissions
   *
   * @param array $values IDs of resources
   * @param string $name Name of template variable
   * @param string $field
   *
   * @return array Prepared values
   */
  public function buildOfficeFilterTpl(array $values, $name = '', $field = 'deleted') {
    $ids = array_keys($values);
    if (empty($ids) || (count($ids) < 2 && empty($this->config['showEmptyFilters']))) {
      return array();
    }

    $response = $this->modBoilerplate->runProcessor('web/office/getlist', array(
      'limit' => 0,
      'needCounters' => 0,
      'checkCustomPermissions' => 0,
      'checkByRelatedObjects' => 0,
    ));
    if ($response) {
      $response = $this->modBoilerplate->prepareProcessorResponse($response);
    }

    $results = array();
    if (is_array($response['results'])) {
      $tokens = array();
      foreach ($response['results'] as $row) {
        $tokens[$row['id']] = $row[$field];
      }
      foreach ($values as $id => $ids) {
        $title = !isset($tokens[$id]) ? $this->modx->lexicon('dacharai_mse2_filter_office_'. $field) : $tokens[$id];
        $results[$title] = array(
          'title' => $title
        ,'value' => $id
        ,'type' => $field
        ,'resources' => $ids
        );
      }
    }

    ksort($results);

    return $results;
  }

  /**
   * &filters=`manager|office_id:office_name`
   *
   * @param array  $values
   * @param string $name
   *
   * @return array
   */
  public function buildOffice_nameFilter(array $values, $name = '') {
    return $this->buildOfficeFilterTpl($values, $name, 'name');
  }

  /**
   * Returns array with rounded minimum and maximum value
   *
   * @param array $values
   * @param string $name
   *
   * @return array
   */
  public function buildDaterangeFilter(array $values, $name = '') {
    $tmp = array_keys($values);
    if (empty($values) || (count($tmp) < 2 && empty($this->config['showEmptyFilters']))) {
      return array();
    }

    $min = null;
    $max = null;
    foreach ($values as $date => $ids) {
      if (!is_numeric($date)) { $date = strtotime($date); }
      if ($max === null || $date > $max) { $max = $date; }
      if ($min === null || $date < $min) { $min = $date; }
    }

    $min = floor($min);
    $max = ceil($max);

    $filters = array(
      array(
        'title' => $this->modx->lexicon('mse2_filter_number_min')
      ,'value' => $min
      ,'type' => 'number'
      ,'resources' => null
      )
    ,array(
        'title' => $this->modx->lexicon('mse2_filter_number_max')
      ,'value' => $max
      ,'type' => 'number'
      ,'resources' => null
      )
    );

    return $filters;
  }

  /**
   * Filters numbers. Values must be between min and max number
   *
   * @param array $requested Filtered ids of resources
   * @param array $values Filter data with min and max number
   * @param array $ids Ids of currently active resources
   *
   * @return array
   */
  public function filterDaterange(array $requested, array $values, array $ids) {
    $matched = array();

    $min = null;
    $max = null;
    foreach ($requested as $date) {
      if (!is_numeric($date)) { $date = strtotime($date); }
      if ($max === null || $date > $max) { $max = $date; }
      if ($min === null || $date < $min) { $min = $date; }
    }

    $min = floor($min);
    $max = ceil($max);

    $tmp = array_flip($ids);
    foreach ($values as $date => $resources) {
      if (!is_numeric($date)) { $date = strtotime($date); }
      if ($date >= $min && $date <= $max) {
        foreach ($resources as $id) {
          if (isset($tmp[$id])) {
            $matched[] = $id;
          }
        }
      }
    }

    return $matched;
  }

}