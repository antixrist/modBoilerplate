<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

switch ($modx->event->name) {
  case 'OnMODXInit':
    if ($modx->context->key == 'mgr') {
      return;
    }

    $path = $modx->getOption('modboilerplate.core_path', null, $modx->getOption('core_path') . 'components/modboilerplate/') . 'model/modboilerplate/';
    /** @var modBoilerplate $modBoilerplate */
    $modBoilerplate = $modx->getService('modBoilerplate', 'modBoilerplate', $path);
    if (!($modBoilerplate instanceof modBoilerplate)) {
      @session_write_close();
      exit('Could not initialize modBoilerplate');
    }

    break;
  case 'OnHandleRequest':
    if ($modx->context->key == 'mgr') {
      return;
    }

    $url = $modx->getOption('modboilerplate.assets_url', null, $modx->getOption('assets_url') . 'components/modboilerplate/');
    $modx->toPlaceholders(array(
      'assets_url' => $url
    ), 'modboilerplate');

    break;
}

