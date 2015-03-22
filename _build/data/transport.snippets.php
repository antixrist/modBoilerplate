<?php

$snippets = array();

$tmp = array(
  'modBoilerplate' => 'modboilerplate',
);

foreach ($tmp as $k => $v) {
  /* @avr modSnippet $snippet */
  $snippet = $modx->newObject('modSnippet');
  $snippet->fromArray(array(
    'id' => 0
    ,'name' => $k
    ,'description' => ''
    ,'snippet' => file_get_contents($sources['source_core'].'/elements/snippets/snippet.'. $v .'.php')
    ,'static' => BUILD_SNIPPET_STATIC
    ,'source' => 1
    ,'static_file' => ''
  ),'',true,true);

  if (BUILD_SNIPPET_STATIC) {
    $snippetPath = (PKG_DEV)
      ? '/'. PKG_NAME .'/core/components/'. PKG_NAME_LOWER .'/elements/snippets/snippet.'. $v .'.php'
      : MODX_CORE_PATH .'components/'. PKG_NAME_LOWER .'/elements/snippets/snippet.'. $v .'.php';

    $snippet->set('static_file', $snippetPath);
  }

  $propsFile = $sources['build'] . 'properties/properties.' . $v . '.php';
  if (file_exists($propsFile)) {
    $properties = include ($propsFile);
    $snippet->setProperties($properties);
  }

  $snippets[] = $snippet;
}

unset($tmp, $properties);
return $snippets;