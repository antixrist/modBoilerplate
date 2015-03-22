<?php

$policies = array();

/* @var modAccessPolicy $policy */
$policy= $modx->newObject('modAccessPolicy');
$policy->fromArray(array (
  'name' => 'modBoilerplateManagerPolicy',
  'description' => 'A policy for modBoilerplate object.',
  'parent' => 0,
  'class' => '',
  'lexicon' => PKG_NAME_LOWER .':permissions',
  'data' => json_encode(array(
    'someobject_policy' => true,
  ))
), '', true, true);

$policies[] = $policy;

return $policies;
