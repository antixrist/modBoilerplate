<?php

$events = array();

$tmp = array(
//  'someEventName',
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