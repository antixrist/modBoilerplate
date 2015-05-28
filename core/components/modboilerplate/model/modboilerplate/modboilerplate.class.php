<?php

/**
 * The base class for modBoilerplate.
 */
class modBoilerplate {
  /* @var modX $modx */
  public $modx;
  /** @var pdoFetch $pdoTools */
  public $pdoTools;
  /** @var array $initialized */
  private $initialized = array();
  /** @var array $config */
  public $config = array();
  /** @var array $configPlaceholders */
  private $configPlaceholders = array();
  /** @var bool $minifyXexists */
  private $minifyXexists = false;
  /** @var array $loadedModels */
  private $loadedModels = array();
  /** @var array $injectPropertiesToHandlerDefault */
  public $injectPropertiesToHandlerDefault = array('id', 'ids', 'returnIds', 'return', 'resources', 'queryVar', 'minQuery', 'sortby', 'sortdir', 'offset', 'limit');
  /** @var array $classesList */
  private $classesList = array();
  /** @var array $objectsClassesList */
  private $objectsClassesList = array();

  /**
   * @param modX $modx
   * @param array $config
   * @param boolean $isAjax
   */
  function __construct (modX &$modx, array $config = array(), $isAjax = false) {
    $this->modx =& $modx;

    $assetsUrl = $modx->getOption('modboilerplate.assets_url');
    $assetsUrl = ($assetsUrl) ? $assetsUrl : $modx->getOption('assets_url') . 'components/modboilerplate/';
    $corePath = $modx->getOption('modboilerplate.core_path');
    $corePath = ($corePath) ? $corePath : $modx->getOption('core_path') . 'components/modboilerplate/';

    $frontendCss = $this->modx->getOption('modboilerplate.frontendCss');
    $minifyFrontendCss = $this->modx->getOption('modboilerplate.frontendCssMinifyX');
    $frontendJs = $this->modx->getOption('modboilerplate.frontendJs');
    $minifyFrontendJs = $this->modx->getOption('modboilerplate.frontendJsMinifyX');

    $connectorUrl = $assetsUrl . 'connector.php';

    $this->config = array_merge(array(
      'assetsUrl' => $assetsUrl,
      'cssUrl' => $assetsUrl . 'css/',
      'jsUrl' => $assetsUrl . 'js/',
      'imagesUrl' => $assetsUrl . 'images/',
      'connectorUrl' => $connectorUrl,

      'corePath' => $corePath,
      'modelPath' => $corePath . 'model/',
      'chunksPath' => $corePath . 'elements/chunks/',
      'templatesPath' => $corePath . 'elements/templates/',
      'chunkSuffix' => '.chunk.tpl',
      'snippetsPath' => $corePath . 'elements/snippets/',

      'ctx' => 'web',
      'processorsPath' => $corePath .'processors/',

      'frontendCss' => $frontendCss,
      'frontendCssMinifyX' => $minifyFrontendCss,
      'frontendJs' => $frontendJs,
      'frontendJsMinifyX' => $minifyFrontendJs,
      'json_response' => $isAjax,
    ), $config);

    $this->modx->addPackage('modboilerplate', $this->config['modelPath']);
    $this->modx->lexicon->load('modboilerplate:default');
  }

  /**
   * Make placeholders from config values
   *
   * @return array
   */
  public function getConfigPlaceholders () {
    if (!is_array($this->configPlaceholders) ||
      empty($this->configPlaceholders['pl']) ||
      empty($this->configPlaceholders['vl']) ||
      count($this->configPlaceholders['vl']) != count($this->config) ||
      count($this->configPlaceholders['pl']) != count($this->config) ||
      count($this->configPlaceholders['pl']) != count($this->configPlaceholders['vl'])
    ) {
      $this->configPlaceholders = $this->makePlaceholders($this->config);
    }

    return $this->configPlaceholders;
  }

  /**
   * Initialize class in context
   *
   * @param string $ctx
   * @param array  $scriptProperties
   *
   * @return bool
   */
  public function initialize ($ctx = 'web', $scriptProperties = array()) {
    switch ($ctx) {
      case 'mgr': break;
      default:
        $this->config        = array_merge($this->config, $scriptProperties);
        $this->config['ctx'] = $ctx;

        if (!empty($this->config['loadModels'])) {
          $this->loadModels($this->config['loadModels']);
        }

        if (!empty($this->initialized[$ctx])) {
          return true;
        }

        $this->loadPdoTools();

        if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
          if ($this->modx->getCount('modSnippet', array('name' => 'MinifyX'))) {
            $this->minifyXexists = true;
          }

          $this->loadCss();
          $this->loadJs();


          $this->initialized[$ctx] = true;
        }
        break;
    }

    return true;
  }

  /**
   * Load frontend JS files
   *
   * @return void
   */
  public function loadJs () {
    $frontendJs = $this->getArray($this->config['frontendJs']);
    $tmp = array();
    $placeholders = $this->getConfigPlaceholders();
    foreach ($frontendJs as $js) {
      if (preg_match('/\.js/i', $js)) {
        $tmp[] = str_replace($placeholders['pl'], $placeholders['vl'], $js);
      }
    }

    $jQuery = <<<HTML
    <script>window.jQuery || document.write('<script src="{$this->config['jsUrl']}web/libs/jquery-1.11.2.min.js"><\/script>')</script>
HTML;
    $this->modx->regClientScript($jQuery, true);

    $config = $this->getArray($this->config['frontendJsMinifyX']);
    if (count($config) && $this->minifyXexists) {
      $frontendJs = implode(",\n", $tmp);
      $this->modx->runSnippet('MinifyX', array_merge($config, array(
        'jsSources' => $frontendJs
      )));
    } else {
      $frontendJs = $tmp;
      foreach ($frontendJs as $js) {
        $this->modx->regClientScript($js);
      }
    }
  }

  /**
   * Load frontend CSS files
   *
   * @return void
   */
  public function loadCss () {
    $frontendCss = $this->getArray($this->config['frontendCss']);
    $tmp = array();
    $placeholders = $this->getConfigPlaceholders();
    foreach ($frontendCss as $css) {
      if (preg_match('/\.css/i', $css)) {
        $tmp[] = str_replace($placeholders['pl'], $placeholders['vl'], $css);
      }
    }

    $config = $this->getArray($this->config['frontendCssMinifyX']);
    if (count($config) && $this->minifyXexists) {
      $frontendCss = implode(",\n", $tmp);
      $this->modx->runSnippet('MinifyX', array_merge($config, array(
        'cssSources' => $frontendCss
      )));
    } else {
      $frontendCss = $tmp;
      foreach ($frontendCss as $css) {
        $this->modx->regClientCSS($css);
      }
    }
  }

  /**
   * Sanitize MODX tags
   *
   * @param string $string Any string with MODX tags
   *
   * @return string String with html entities
   */
  public function sanitizeString ($string = '') {
    $string = htmlentities(trim($string), ENT_QUOTES, "UTF-8");
    $string = preg_replace('/^@.*\b/', '', $string);
    $arr1 = array('[',']','`');
    $arr2 = array('&#091;','&#093;','&#096;');
    return str_replace($arr1, $arr2, $string);
  }

  /**
   * Method for transform array to placeholders
   *
   * @var array $array With keys and values
   * @var string $prefix Prefix string
   * @return array $array Two nested arrays With placeholders and values
   * */
  public function makePlaceholders (array $array = array(), $prefix = '') {
    $result = array(
      'pl' => array(),
      'vl' => array()
    );
    foreach ($array as $k => $v) {
      if (is_array($v)) {
        $result = array_merge_recursive($result, $this->makePlaceholders($v, $k.'.'));
      }
      else {
        $result['pl'][$prefix.$k] = '[[+'.$prefix.$k.']]';
        $result['vl'][$prefix.$k] = $v;
      }
    }
    return $result;
  }

  /**
   * Loads an instance of pdoTools
   *
   * @return boolean
   */
  public function loadPdoTools () {
    if (!is_object($this->pdoTools) || !($this->pdoTools instanceof pdoTools)) {
      $fqn = $this->modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
      if ($pdoClass = $this->modx->loadClass($fqn, '', false, true)) {
        $this->pdoTools = new $pdoClass($this->modx, $this->config);
      }
      elseif ($pdoClass = $this->modx->loadClass($fqn, MODX_CORE_PATH . 'components/pdotools/model/', false, true)) {
        $this->pdoTools = new $pdoClass($this->modx, $this->config);
      }
      else {
        $this->modx->log(modX::LOG_LEVEL_ERROR, 'Could not load pdoFetch from "MODX_CORE_PATH/components/pdotools/model/".');
      }
    }
    return !empty($this->pdoTools) && $this->pdoTools instanceof pdoTools;
  }

  /**
   * Check for exists component processor
   *
   * @param string $action
   * @return bool
   */
  public function processorExists ($action = '') {
    if (empty($action)) { return false; }
    $options = array('processors_path' => $this->config['processorsPath']);

    $processorsPath = isset($options['processors_path']) && !empty($options['processors_path']) ? $options['processors_path'] : $this->config['processors_path'];
    if (isset($options['location']) && !empty($options['location'])) $processorsPath .= ltrim($options['location'],'/') . '/';

    $processorFile = $processorsPath.ltrim(str_replace('../', '', $action . '.class.php'),'/');
    if (!file_exists($processorFile)) {
      $processorFile = $processorsPath.ltrim(str_replace('../', '', $action . '.php'),'/');
    }

    return (file_exists($processorFile));
  }

  /**
   * Shorthand for the call of processor
   *
   * @access public
   * @param string $action Path to processor
   * @param array $data Data to be transmitted to the processor
   * @return mixed The result of the processor
   */
  public function runProcessor ($action = '', $data = array()) {
    if (empty($action)) {return false;}
    return $this->modx->runProcessor($action, $data, array('processors_path' => $this->config['processorsPath']));
  }

  /**
   * Return prepared processor response
   *
   * @param modProcessorResponse $modResponse
   *
   * @return array
   */
  public function prepareProcessorResponse ($modResponse) {
    $default = array(
      'success' => false,
      'message' => '',
      'errors' => array(),
      'object' => array(),
    );

    $result = $default;
    /** @var modProcessorResponse $modResponse */
    if ($modResponse instanceof modProcessorResponse) {
      $response = $modResponse->getResponse();
      $response = $this->getArray($response);

      $result = array_merge($default, $response);

      if ($modResponse->isError()) {
        $result['success'] = 0;
        if ($modResponse->hasFieldErrors()) {
          $errors = (array) $modResponse->getFieldErrors();
          /** @var modProcessorResponseError $error */
          foreach ($errors as $error) {
            $result['errors'][$error->getField()] = $error->getMessage();
          }
        }
      } else {
        $result['success'] = 1;
        $result['message'] = $modResponse->getMessage();
      }
    }

    return $result;
  }

  /**
   * @param string $snippet
   * @param array $scriptProperties
   *
   * @return mixed|string
   */
  public function processSnippet ($snippet, array $scriptProperties = array()) {
    $snippetPropertiesSet  = array();
    if (strpos($snippet, '@') !== false) {
      list($snippet, $snippetPropertiesSet) = explode('@', $snippet);
    }
    /** @var modSnippet $Snippet */
    if (!empty($snippet) && $Snippet = $this->modx->getObject('modSnippet', array('name' => $snippet))) {
      $elementProperties  = $Snippet->getProperties();
      $elementPropertySet = !empty($snippetPropertiesSet)
        ? $Snippet->getPropertySet($snippetPropertiesSet)
        : array();
      if (!is_array($elementPropertySet)) {
        $elementPropertySet = array();
      }
      $properties = array_merge(
        $elementProperties,
        $elementPropertySet,
        $scriptProperties,
        array(
          'returnData' => 1,
        )
      );

      $Snippet->setCacheable(false);
      return $Snippet->process($properties);
    }

    return '';
  }


  /**
   * Run processor and return prepared response
   *
   * @param string $action
   * @param array  $data
   * @param array  $processorOptions
   *
   * @return array
   */
  public function getProcessorResult ($action = '', array $data = array(), array $processorOptions = array()) {
    $default = array(
      'success' => false,
      'message' => '',
      'errors' => array(),
//      'object' => $data,
    );
    if (empty($action)) { return $default; }

    $result = $default;
    /** @var modProcessorResponse $modResponse */
    if ($modResponse = $this->modx->runProcessor($action, $data, $processorOptions)) {
      $response = $modResponse->getResponse();
      $response = $this->getArray($response);
      $result = array_merge($default, $response);

      if ($modResponse->isError()) {
        $result['success'] = 0;
        if ($modResponse->hasFieldErrors()) {
          $errors = (array) $modResponse->getFieldErrors();
          //          $result['errors'] = array();
          /** @var modProcessorResponseError $error */
          foreach ($errors as $error) {
            $result['errors'][$error->getField()] = $error->getMessage();
          }
        }
      } else {
        $result['success'] = 1;
        $result['message'] = $modResponse->getMessage();
      }
    }

    return $result;
  }

  /**
   * Return array from separated string, json or array
   *
   * @param string|array $input
   * @param string $separator
   *
   * @return array
   */
  public function getArray ($input, $separator = ',') {
    if (is_array($input)) {
      return $input;
    } else
      if (is_string($input)) {
        $input = trim($input);
        if ($input) {
          $firstLetter = substr($input, 0, 1);
          $lastLetter = substr($input, mb_strlen($input,'UTF-8') - 1, 1);
          if (
            ($firstLetter == '{' && $lastLetter == '}') ||
            ($firstLetter == '[' && $lastLetter == ']')
          ) {
            $tmp = json_decode($input, 1);
            if ($tmp) {
              return $tmp;
            }
          } else {
            $tmp = array_map('trim', explode($separator, $input));
            return $tmp;
          }
        }
      }
    return ($input && !is_object($input)) ? array($input) : array();
  }

  /**
   * Collects and processes any set of tags
   *
   * @param mixed $html Source code for parse
   * @param integer $maxIterations
   * @return mixed $html Parsed html
   */
  public function processTags ($html, $maxIterations = 10) {
    $this->modx->getParser()->processElementTags('', $html, false, false, '[[', ']]', array(), $maxIterations);
    $this->modx->getParser()->processElementTags('', $html, true, true, '[[', ']]', array(), $maxIterations);
    return $html;
  }

  /**
   * Function for formatting dates
   *
   * @param string $date Source date
   * @return string $date Formatted date
   * */
  public function formatDate ($date = '') {
    $df = $this->modx->getOption('avk.date_format', null, '%d.%m.%Y %H:%M');
    return (!empty($date) && $date !== '0000-00-00 00:00:00') ? strftime($df, strtotime($date)) : '&nbsp;';
  }

  /**
   * @param        $className
   * @param array  $data
   *
   * @return int
   */
  public function getExistsObject ($className, array $data) {
    $o = $this->modx->newObject($className);

    if ($o instanceof $className) {
      $unique = array();
      if (method_exists($o, 'getPK')) {
        $pk = $o->getPK();
        if (!is_array($pk)) {
          $pk = array($pk);
        }
        $unique[] = $pk;
      }

      if (isset($this->modx->map[$className]['indexes'])) {
        $indexes = $this->modx->map[$className]['indexes'];
        foreach ($indexes as $name => $data) {
          if ($data['unique']) {
            $unique[] = array_keys($data['columns']);
          }
        }
      }

      foreach ($unique as $uniqueFields) {
        $search = array_intersect_key($data, array_flip($uniqueFields));
        $Object = $this->modx->getObject($className, $search);
        if ($Object instanceof $className) {
          return $Object;
        }
      }

    }

    return false;
  }

  /**
   * Loads specified list of packages models
   *
   * @param string|array $models
   */
  public function loadModels($models) {
    if (empty($models)) {return;}

    $_models = array();
    if (strpos(ltrim($models), '{') === 0) {
      $tmp = $this->modx->fromJSON($models);
      foreach ($tmp as $k => $v) {
        $v = trim(strtolower($v), 1);
        $_models[$k] = (strpos($v, MODX_CORE_PATH) === false)
          ? MODX_CORE_PATH . ltrim($v, '/')
          : $v;
      }
    }
    else {
      $tmp = array_map('trim', explode(',', $models));
      foreach ($tmp as $v) {
        $_models[$v] = MODX_CORE_PATH . 'components/'.strtolower($v).'/model/';
      }
    }

    if (!empty($_models)) {
      foreach ($_models as $k => $v) {
        $t = '/' . str_replace(MODX_BASE_PATH, '', $v);
        if (!$this->modx->addPackage(strtolower($k), $v)) {
          $this->modx->log(modX::LOG_LEVEL_ERROR, '[crud] Could not load model "'.$k.'" from "'.$t);
        } else {
          $this->loadedModels[$k] = $t;
        }
      }
    }
  }

    /**
   * Return array of loaded models
   *
   * @return array
   */
  public function getLoadedModels () {
    return $this->loadedModels;
  }

  /**
   * Проверяет права
   *
   * @param array $scriptProperties
   *
   * @return bool
   */
  public function hasPermission (array $scriptProperties = array()) {
    $scriptProperties['permission'] = (!empty($scriptProperties['permission']))
      ? $scriptProperties['permission']
      : false;

    $scriptProperties['handler'] = (!empty($scriptProperties['handler']) && in_array($scriptProperties['handler'], array('processor', 'snippet')))
      ? strtolower(trim($scriptProperties['handler']))
      : '';

    $scriptProperties['default'] = (!empty($scriptProperties['default']))
      ? ($scriptProperties['default'])
      : true;

    $scriptProperties['snippet'] = (!empty($scriptProperties['snippet']) && $scriptProperties['snippet'])
      ? $scriptProperties['snippet']
      : false;
    $scriptProperties['snippetProperties'] = (!empty($scriptProperties['snippetProperties']) && $scriptProperties['snippetProperties'])
      ? $this->getArray($scriptProperties['snippetProperties'])
      : array();

    $scriptProperties['processor'] = (!empty($scriptProperties['processor']) && $scriptProperties['processor'])
      ? $scriptProperties['processor']
      : false;
    $scriptProperties['processorProperties'] = (!empty($scriptProperties['processorProperties']))
      ? $this->getArray($scriptProperties['processorProperties'])
      : array();
    $scriptProperties['processorOptions'] = (!empty($scriptProperties['processorOptions']) && $scriptProperties['processorOptions'])
      ? $this->getArray($scriptProperties['processorOptions'])
      : array();

    $scriptProperties['tplSuccess'] = (!empty($scriptProperties['tplSuccess']) && $scriptProperties['tplSuccess'])
      ? $scriptProperties['tplSuccess']
      : false;
    $scriptProperties['tplError'] = (!empty($scriptProperties['tplError']) && $scriptProperties['tplError'])
      ? $scriptProperties['tplError']
      : false;
    $scriptProperties['tplWrapperSuccess'] = (!empty($scriptProperties['tplWrapperSuccess']) && $scriptProperties['tplWrapperSuccess'])
      ? $scriptProperties['tplWrapperSuccess']
      : false;
    $scriptProperties['tplWrapperError'] = (!empty($scriptProperties['tplWrapperError']) && $scriptProperties['tplWrapperError'])
      ? $scriptProperties['tplWrapperError']
      : false;
    $scriptProperties['wrapIfEmpty'] = (!empty($scriptProperties['wrapIfEmpty']) && $scriptProperties['wrapIfEmpty']);

    $scriptProperties['additionalPlaceholders'] = (!empty($scriptProperties['additionalPlaceholders']) && $scriptProperties['additionalPlaceholders'])
      ? $this->getArray($scriptProperties['additionalPlaceholders'])
      : array();
    $scriptProperties['toPlaceholder'] = (!empty($scriptProperties['toPlaceholder']) && $scriptProperties['toPlaceholder'])
      ? $scriptProperties['toPlaceholder']
      : false;

    $properties['injectPropertiesToHandler'] = (!empty($properties['injectPropertiesToHandler']) && $properties['injectPropertiesToHandler'])
      ? $this->getArray($properties['injectPropertiesToHandler'])
      : array();

    $properties['injectPropertiesToHandler'] = array_merge($properties['injectPropertiesToHandler'], $this->injectPropertiesToHandlerDefault);

    $scriptProperties['fastMode'] = (!empty($scriptProperties['fastMode']) && $scriptProperties['fastMode'])
      ? true
      : false;

    $result = $scriptProperties['default'];
    //    if (($user = $this->modx->getAuthenticatedUser()) && $user->get('sudo')) {
    //      $result = true;
    //    } else {
    switch ($scriptProperties['handler']) {
      case 'snippet':
        if ($scriptProperties['snippet']) {
          $result = $this->processSnippet($scriptProperties['snippet'], $scriptProperties['snippetProperties']);
        }
        break;
      case 'processor':
        if ($scriptProperties['processor']) {
          $result = $this->getProcessorResult($scriptProperties['processor'], $scriptProperties['processorProperties'], $scriptProperties['processorOptions']);
          $result = $this->getArray($result);
          $result = $result['success'];
        }
        break;
      default:
        if ($scriptProperties['permission']) {
          $result = $this->modx->hasPermission($scriptProperties['permission']);
        }
        break;
    }
    //    }

    $output = ($result) ? 1 : 0;
    $tpl = '';
    $tplWrapper = '';
    $wrapIfEmpty = $scriptProperties['wrapIfEmpty'];

    if ($scriptProperties['tplSuccess'] && $result) {
      $tpl = $scriptProperties['tplSuccess'];
      if ($scriptProperties['tplWrapperSuccess']) {
        $tplWrapper = $scriptProperties['tplWrapperSuccess'];
      }
    }
    if ($scriptProperties['tplError'] && !$result) {
      $tpl = $scriptProperties['tplError'];
      if ($scriptProperties['tplWrapperError']) {
        $tplWrapper = $scriptProperties['tplWrapperError'];
      }
    }

    if ($tpl) {
      $output = $this->pdoTools->getChunk($tpl, $scriptProperties['additionalPlaceholders'], $scriptProperties['fastMode']);
    }

    if ($tplWrapper && (!empty($output) || empty($output) && $wrapIfEmpty)) {
      $output = $this->pdoTools->getChunk($tplWrapper, array('output' => $output), $scriptProperties['fastMode']);
    }

    if ($scriptProperties['toPlaceholder']) {
      $this->modx->setPlaceholder($scriptProperties['toPlaceholder'], $output);
      $output = '';
    }

    return $output;
  }

  /**
   * @return array
   */
  public function getPackageObjectsClasses () {
    if (is_array($this->objectsClassesList) && count($this->objectsClassesList)) {
      return $this->objectsClassesList;
    }

    $classes = $this->getPackageClassesList();

    $result = array();
    foreach ($classes as $className) {
      if (strpos($className, 'modBoilerplate') === 0) {
        $object = substr($className, strlen('modBoilerplate'));
      } else {
        $object = $className;
      }
      $object = lcfirst($object);
      $result[$object] = $className;
    }

    $result['manager'] = 'modUser';
    $result['managerdata'] = 'modUserProfile';

    $this->objectsClassesList = $result;

    return $this->objectsClassesList;
  }

  /**
   * @return array
   */
  public function getPackageClassesList () {
    if (is_array($this->classesList) && count($this->classesList)) {
      return $this->classesList;
    }

    $this->classesList = array();

    $mapFile = $this->config['modelPath'] . 'modboilerplate/metadata.' . $this->modx->config['dbtype'] . '.php';
    if (file_exists($mapFile)) {
      $xpdo_meta_map = false;
      include $mapFile;
      if (!empty($xpdo_meta_map) && is_array($xpdo_meta_map)) {
        foreach ($xpdo_meta_map as $className => $extends) {
          foreach ($extends as $className) {
            $this->classesList[] = $className;
          }
        }
      }
    }

    $this->classesList = array_unique($this->classesList);
    return $this->classesList;
  }

  /**
   * @param array $dest
   * @param array $source
   *
   * @return array
   */
  public function array_merge_recursive (array $dest = array(), array $source = array()) {
    //    if (!is_array($dest) &&  is_array($source)) return $source;
    //    if ( is_array($dest) && !is_array($source)) return $dest;
    //    if (!is_array($dest) && !is_array($source)) return array();

    foreach ($source as $k => $v) {
      if (is_array($v) && isset($dest[$k]) && !is_numeric($k)) {
        $dest[$k] = $this->array_merge_recursive($dest[$k], $v);
      } else if (!is_numeric($k)) {
        $dest[$k] = $source[$k];
      } else {
        $dest[] = $source[$k];
      }
    }
    return $dest;
  }

  /**
   * This method returns an error
   *
   * @param string $message A lexicon key for success message
   * @param array $data Additional data
   * @param array $placeholders Array with placeholders for lexicon entry
   *
   * @return array|string $response
   */
  public function error ($message = '', array $data = array(), array $placeholders = array()) {
    return $this->result(false, $message, $data, $placeholders);
  }

  /**
   * This method returns an success
   *
   * @param string $message A lexicon key for success message
   * @param array $data Additional data
   * @param array $placeholders Array with placeholders for lexicon entry
   *
   * @return array|string $response
   */
  public function success ($message = '', array $data = array(), array $placeholders = array()) {
    return $this->result(true, $message, $data, $placeholders);
  }

  /**
   * This method returns an success or error result
   *
   * @param bool $success Success or error
   * @param string $message A lexicon key for success message
   * @param array $data Additional data
   * @param array $placeholders Array with placeholders for lexicon entry
   *
   * @return array|string $response
   */
  public function result ($success, $message = '', array $data = array(), array $placeholders = array()) {
    $response = array(
      'success' => (!!$success) ? true : false,
      'message' => $this->modx->lexicon($message, $placeholders),
      'data' => $data,
    );
    return $this->config['json_response'] ? $this->modx->toJSON($response) : $response;
  }

  /**
   * @param string $input
   */
  function log ($input = '') {
    $this->modx->log(modX::LOG_LEVEL_ERROR, $input);
  }

  /**
   * Ucfirst function with support of cyrillic
   *
   * @param string $str
   *
   * @return string
   */
  public function ucfirst($str = '') {
    if (!preg_match('/[a-zа-я]/iu', $str)) {
      return '';
    }
    elseif (strpos($str, '-') !== false) {
      $tmp = array_map(array($this, __FUNCTION__), explode('-', $str));
      return implode('-', $tmp);
    }

    if (function_exists('mb_substr') && preg_match('/[а-я]/iu',$str)) {
      $tmp = mb_strtolower($str, 'utf-8');
      $str = mb_substr(mb_strtoupper($tmp, 'utf-8'), 0, 1, 'utf-8') . mb_substr($tmp, 1, mb_strlen($tmp)-1, 'utf-8');
    }
    else {
      $str = ucfirst(strtolower($str));
    }

    return $str;
  }

}