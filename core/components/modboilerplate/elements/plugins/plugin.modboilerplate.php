<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

switch ($modx->event->name) {
  case 'OnMODXInit':
    if ($modx->context->key == 'mgr') {
      return;
    }

    $path = $modx->getOption('modboilerplate.core_path');
    $path = ($path) ? $path : $modx->getOption('core_path') . 'components/modboilerplate/';
    $path .= 'model/modboilerplate/';
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

    $assetsUrl = $modx->getOption('modboilerplate.assets_url');
    $assetsUrl = ($assetsUrl) ? $assetsUrl : $modx->getOption('assets_url') . 'components/modboilerplate/';

    $corePath = $modx->getOption('modboilerplate.core_path');
    $corePath = ($corePath) ? $corePath : $modx->getOption('core_path') . 'components/modboilerplate/';
    $modx->toPlaceholders(array(
      'assets_url' => $assetsUrl,
      'core_path' => $corePath
    ), 'modboilerplate');

    break;
}

