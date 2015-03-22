<?php

$chunks = array();

$tmp = array(
	'tpl.modBoilerplate.item' => 'tpl.modboilerplate.item',
);

// Save chunks for setup options
$BUILD_CHUNKS = array();

foreach ($tmp as $k => $v) {
  /* @avr modChunk $chunk */
  $chunk = $modx->newObject('modChunk');
  $chunk->fromArray(array(
    'id' => 0
    ,'name' => $k
    ,'description' => ''
    ,'snippet' => file_get_contents($sources['source_core'].'/elements/chunks/chunk.'. $v .'.html')
    ,'static' => BUILD_CHUNK_STATIC
    ,'source' => 1
    ,'static_file' => ''
  ),'',true,true);

  if (BUILD_CHUNK_STATIC) {
    $chunkPath = (PKG_DEV)
      ? '/'. PKG_NAME .'/core/components/'. PKG_NAME_LOWER .'/elements/chunks/chunk.'. $v .'.html'
      : MODX_CORE_PATH .'components/'. PKG_NAME_LOWER .'/elements/chunks/chunk.'. $v .'.html';
    $chunk->set('static_file', $chunkPath);
  }

  $chunks[] = $chunk;

  $BUILD_CHUNKS[$k] = file_get_contents($sources['source_core'] . '/elements/chunks/chunk.' . $v . '.html');
}

unset($tmp);
return $chunks;