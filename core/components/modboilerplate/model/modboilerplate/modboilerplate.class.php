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
  /** @var bool $minifyXexists */
  private $minifyXexists = false;

  /**
   * @param modX $modx
   * @param array $config
   */
  function __construct (modX &$modx, array $config = array()) {
    $this->modx =& $modx;

    $corePath = $this->modx->getOption('modboilerplate.core_path', $config, $this->modx->getOption('core_path') . 'components/modboilerplate/');
    $assetsUrl = $this->modx->getOption('modboilerplate.assets_url', $config, $this->modx->getOption('assets_url') . 'components/modboilerplate/');

    $frontendCss = $this->modx->getOption('modboilerplate.frontendCss');
    $minifyFrontendCss = $this->modx->getOption('modboilerplate.minifyFrontendCss');
    $frontendJs = $this->modx->getOption('modboilerplate.frontendJs');
    $minifyFrontendJs = $this->modx->getOption('modboilerplate.minifyFrontendJs');

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
      'json_response' => false,
      'processorsPath' => $corePath .'processors/',

      'frontendCss' => $frontendCss,
      'minifyFrontendCss' => $minifyFrontendCss,
      'frontendJs' => $frontendJs,
      'minifyFrontendJs' => $minifyFrontendJs,
    ), $config);

    $this->modx->addPackage('modboilerplate', $this->config['modelPath']);
    $this->modx->lexicon->load('modboilerplate:default');
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
        if (!defined('MODX_API_MODE') || !MODX_API_MODE) {
          $this->config        = array_merge($this->config, $scriptProperties);
          $this->config['ctx'] = $ctx;
          if (!empty($this->initialized[$ctx])) {
            return true;
          }

          if ($this->modx->getCount('modSnippet', array('name' => 'MinifyX'))) {
            $this->minifyXexists = true;
          }

          $this->loadCss();
          $this->loadJs();

          $this->loadPdoTools();

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
    foreach ($frontendJs as $js) {
      if (preg_match('/\.js/i', $js)) {
        $tmp[] = str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $js);
      }
    }

    $assetsUrl = $this->config['assetsUrl'];
    $jQuery = <<<HTML
    <script>window.jQuery || document.write('<script src="${assetsUrl}js/web/jquery-1.11.2.min.js"><\/script>')</script>
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
    foreach ($frontendCss as $css) {
      if (preg_match('/\.css/i', $css)) {
        $tmp[] = str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $css);
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
      if (strpos(ltrim($input), '{') === 0) {
        $tmp = $this->modx->fromJSON($input);
        if ($tmp) {
          return $tmp;
        }
      } else
      // check for not empty string
      if (trim($input)) {
        $tmp = array_map('trim', explode($separator, $input));
        return $tmp;
      }
    }
    return array();
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
   * This method returns an error
   *
   * @param string $message A lexicon key for success message
   * @param array $data Additional data
   * @param array $placeholders Array with placeholders for lexicon entry
   *
   * @return array|string $response
   */
  public function error ($message = '', $data = array(), $placeholders = array()) {
    $response = array(
      'success' => false,
      'message' => $this->modx->lexicon($message, $placeholders),
      'data' => $data,
    );
    return $this->config['json_response'] ? $this->modx->toJSON($response) : $response;
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
  public function success ($message = '', $data = array(), $placeholders = array()) {
    $response = array(
      'success' => true,
      'message' => $this->modx->lexicon($message, $placeholders),
      'data' => $data,
    );
    return $this->config['json_response'] ? $this->modx->toJSON($response) : $response;
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
  public function result ($success, $message = '', $data = array(), $placeholders = array()) {
    $response = array(
      'success' => (!!$success) ? true : false,
      'message' => $this->modx->lexicon($message, $placeholders),
      'data' => $data,
    );
    return $response;
  }

}