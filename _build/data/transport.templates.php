<?php

$templates = array();

$tmp = array(
  'modBoilerplate' => 'modboilerplate'
);

foreach ($tmp as $k => $v) {
  /* @avr modTemplate $template */
  if (!$template = $modx->getObject('modTemplate', array('templatename' => $k))) {
    $template = $modx->newObject('modTemplate');
    $id = 0;
  } else {
    $id = $template->id;
  }

  $template->fromArray(array(
    'id' => $id,
    'templatename' => $k,
    'description' => '',
    'content' => file_get_contents($sources['source_core'].'elements/templates/template.'. $v .'.html'),
    'static' => BUILD_TEMPLATE_STATIC,
    'source' => 1,
    'properties' => array()
  ),'',true,true);

  if (BUILD_TEMPLATE_STATIC) {
    $path = (PKG_DEV)
      ? '/'. PKG_NAME .'/core/components/'. PKG_NAME_LOWER .'/elements/templates/template.'. $v .'.html'
      : MODX_CORE_PATH .'components/'. PKG_NAME_LOWER .'/elements/templates/template.'. $v .'.html';

    $template->set('static_file', $path);
  }


  $templates[] = $template;
}

unset($tmp);
return $templates;