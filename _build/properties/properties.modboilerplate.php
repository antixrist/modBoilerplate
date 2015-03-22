<?php

$properties = array();

$tmp = array(
  'frontendCss' => array(
    'xtype' => 'textfield',
    'value' => '[[+assetsUrl]]css/web/styles.css',
    'area' => 'modboilerplate.frontend',
  ),
  'frontendCssMinifyX' => array(
    'xtype' => 'textfield',
    'value' => '',
//    json_encode(array(
//      'minifyCss' => '0',
//      'cacheFolder' => '/css/',
//      'cssFilename' => 'modboilerplate',
//      'registerCss' => 'default',
//    ))
    'area' => 'modboilerplate.frontend',
  ),
  'frontendJs' => array(
    'xtype' => 'textfield',
    'value' => '[[+assetsUrl]]js/web/scripts.js',
    'area' => 'modboilerplate.frontend',
  ),
  'frontendJsMinifyX' => array(
    'xtype' => 'textfield',
    'value' => json_encode(array(
      'minifyJs' => '0',
      'cacheFolder' => '/js/',
      'cssFilename' => 'modboilerplate',
      'registerJs' => 'default',
    )),
    'area' => 'modboilerplate.frontend',
  ),

  'tpl' => array(
		'type' => 'textfield',
		'value' => 'tpl.modBoilerplate.item',
	),
	'sortby' => array(
		'type' => 'textfield',
		'value' => 'name',
	),
	'sortdir' => array(
		'type' => 'list',
		'options' => array(
			array('text' => 'ASC', 'value' => 'ASC'),
			array('text' => 'DESC', 'value' => 'DESC'),
		),
		'value' => 'ASC'
	),
	'limit' => array(
		'type' => 'numberfield',
		'value' => 10,
	),
	'outputSeparator' => array(
		'type' => 'textfield',
		'value' => "\n",
	),
	'toPlaceholder' => array(
		'type' => 'combo-boolean',
		'value' => false,
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '_prop.' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;