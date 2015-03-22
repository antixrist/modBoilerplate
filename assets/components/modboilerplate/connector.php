<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var modBoilerplate $modBoilerplate */
$modBoilerplate = $modx->getService('modboilerplate', 'modBoilerplate', $modx->getOption('modboilerplate_core_path', null, $modx->getOption('core_path') . 'components/modboilerplate/') . 'model/modboilerplate/');
$modx->lexicon->load('modboilerplate:default');

// handle request
$corePath = $modx->getOption('modboilerplate_core_path', null, $modx->getOption('core_path') . 'components/modboilerplate/');
$path = $modx->getOption('processorsPath', $modBoilerplate->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));