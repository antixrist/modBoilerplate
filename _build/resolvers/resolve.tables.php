<?php

if ($object->xpdo) {
	/** @var modX $modx */
	$modx =& $object->xpdo;

	switch ($options[xPDOTransport::PACKAGE_ACTION]) {
		case xPDOTransport::ACTION_INSTALL:
			$modelPath = $modx->getOption('modboilerplate.core_path', null, $modx->getOption('core_path') . 'components/modboilerplate/') . 'model/';
			$modx->addPackage('modboilerplate', $modelPath);
			$manager = $modx->getManager();

			$objects = array(
				'modBoilerplateItem',
			);
      $dontRemoveObjects = array(
//        'modBoilerplateSomeClass',
      );
      $removeObjects = array(
//        'modBoilerplateAnotherSomeClass',
      );

      foreach ($objects as $tmp) {
        if (!in_array($tmp, $dontRemoveObjects) || in_array($tmp, $removeObjects)) {
          $manager->removeObjectContainer($tmp);
        }
        $manager->createObjectContainer($tmp);
			}

      $modx->removeExtensionPackage('modboilerplate');
      $modx->addExtensionPackage('modboilerplate', $modx->getOption('modboilerplate.core_path', null, '[[++core_path]]components/modboilerplate/') .'model/modboilerplate/');

      $level = $modx->getLogLevel();
      $modx->setLogLevel(xPDO::LOG_LEVEL_FATAL);

//      $manager->addIndex('modBoilerplateItem', 'item_indexgrp');

      $modx->setLogLevel($level);
      break;

		case xPDOTransport::ACTION_UPGRADE:
			break;

		case xPDOTransport::ACTION_UNINSTALL:
      $modx->removeExtensionPackage('modboilerplate');
			break;
	}
}
return true;
