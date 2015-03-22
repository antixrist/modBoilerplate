<?php
/**
 * Resolve creating db tables
 *
 * @var xPDOObject $object
 * @var array $options
 */

if ($object->xpdo) {
  /* @var modX $modx */
  $modx =& $object->xpdo;


  switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:

      break;
    case xPDOTransport::ACTION_UPGRADE:

      break;
    case xPDOTransport::ACTION_UNINSTALL:

      break;
  }
}

/*---------------------------------*/
if (!function_exists('deployResource')) {
  function changeSetting ($key = '', $value, $config = array()) {
    global $modx;
    if (empty($key)) {
      return false;
    }
    /* @var $response modProcessorResponse */
    $response = $modx->runProcessor('system/settings/update', array_merge(array(
      'key'       => $key,
      'value'     => $value,
      'namespace' => 'core'
    ), $config));
    if ($response->isError()) {
      $modx->log(modX::LOG_LEVEL_ERROR, '[modBoilerplate] Cann\'t update setting with messages: ' . print_r($response->getAllErrors(), 1));

      return false;
    }
    $modx->reloadConfig();

    return true;
  }
}

if (!function_exists('deployResource')) {
  function deployResource ($search = array(), $data = array(), $setting = '', $tpl = 'modBoilerplate') {
    global $modx;

    if ($resId = $modx->getOption($setting)) {
      $search = array();
      $search['id'] = (int) $resId;
    }
    if (($resource = $modx->getObject('modResource', $search))) {
      $resource = $resource->toArray();
    } else {
      if ($tpl && $Tpl = $modx->getObject('modTemplate', array(
        'templatename' => $tpl
      ))) {
        $data['template'] = $Tpl->get('id');
      }

      $errorMsg = '';
      /** @var modProcessorResponse $response */
      if ($response = $modx->runProcessor('resource/create', $data)) {
        if (!$response->hasObject()) {
          $errorMsg = 'Could\'t create Profile resource';
        }
        if ($response->isError()) {
          if ($response->hasFieldErrors()) {
            $errors = (array) $response->getFieldErrors();
            /** @var modProcessorResponseError $error */
            $errorMsg .= 'Could\'t create Profile resource with errors:'. PHP_EOL;
            foreach ($errors as $error) {
              $errorMsg .= '"'. $error->getField() .'": '. $error->getMessage() .PHP_EOL;
            }
          }
        }
      }

      if ($errorMsg) {
        $modx->log(modX::LOG_LEVEL_ERROR, $errorMsg);
        $resource = false;
      } else {
        $resource = $response->getObject();
      }
    }

    if ($resource) {
      changeSetting($setting, $resource['id'], array(
        'namespace' => 'modBoilerplate'
      ));
    }
  }
}


return true;
