<?php

$events = array();

$tmp = array(
  'modBoilerplateItemBeforeSave',
  'modBoilerplateItemAfterSave',
  'modBoilerplateItemBeforeRemove',
  'modBoilerplateItemAfterRemove',
  'modBoilerplateItemBeforeSave',
  'modBoilerplateItemAfterSave',
  'modBoilerplateItemBeforeActivate',
  'modBoilerplateItemAfterActivate',
  'modBoilerplateItemBeforeDeactivate',
  'modBoilerplateItemAfterDeactivate',
  'modBoilerplateItemBeforeDelete',
  'modBoilerplateItemAfterDelete',
  'modBoilerplateItemBeforeRestore',
  'modBoilerplateItemAfterRestore',
  'modBoilerplateItemBeforePublish',
  'modBoilerplateItemAfterPublish',
  'modBoilerplateItemBeforeUnpublish',
  'modBoilerplateItemAfterUnpublish',
);

foreach ($tmp as $k => $v) {
  /* @var modEvent $event */
  $event = $modx->newObject('modEvent');
  $event->fromArray(array(
    'name' => $k
    ,'service' => 6
    ,'groupname' => PKG_NAME
  ),'', true, true);

  $events[] = $event;
}

return $events;