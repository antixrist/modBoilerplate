<?php

$settings = array();

$tmp = array(
//	'some_setting' => array(
//		'xtype' => 'combo-boolean',
//		'value' => true,
//		'area' => 'modboilerplate.main',
//	),
  'frontendCss' => array(
    'xtype' => 'textfield',
    'value' => '[[+assetsUrl]]css/web/styles.css',
    'area' => 'modboilerplate.frontend',
  ),
  'frontendCssMinifyX' => array(
    'xtype' => 'textfield',
    'value' => '',
    'area' => 'modboilerplate.frontend',
  ),
  'frontendJs' => array(
    'xtype' => 'textfield',
    'value' => '[[+assetsUrl]]js/web/scripts.js',
    'area' => 'modboilerplate.frontend',
  ),
  'frontendJsMinifyX' => array(
    'xtype' => 'textfield',
    'value' => '',
    'area' => 'modboilerplate.frontend',
  ),
);

$tmp['assets_path'] = array(
  'xtype' => 'textfield',
  'value' => '',
  'area' => 'modboilerplate.paths',
);
$tmp['assets_url'] = array(
  'xtype' => 'textfield',
  'value' => '',
  'area' => 'modboilerplate.paths',
);
$tmp['core_path'] = array(
  'xtype' => 'textfield',
  'value' => '',
  'area' => 'modboilerplate.paths',
);

if (PKG_DEV) {
  $tmp['assets_path']['value'] = '{base_path}'. PKG_NAME .'/assets/components/'. PKG_NAME_LOWER .'/';
  $tmp['assets_url']['value'] = '{base_url}'. PKG_NAME .'/assets/components/'. PKG_NAME_LOWER .'/';
  $tmp['core_path']['value'] = '{base_path}'. PKG_NAME .'/core/components/'. PKG_NAME_LOWER .'/';
} else {
  $tmp['frontendCssMinifyX']['value'] = json_encode(array(
    'minifyCss' => '1',
    'cacheFolder' => '/css/',
    'cssFilename' => 'modboilerplate'
  ));
  $tmp['frontendJsMinifyX']['value'] = json_encode(array(
    'minifyJs' => '1',
    'cacheFolder' => '/js/',
    'cssFilename' => 'modboilerplate'
  ));

  $tmp['assets_path']['value'] = '';
  $tmp['assets_url']['value'] = '';
  $tmp['core_path']['value'] = '';
}


foreach ($tmp as $k => $v) {
	/* @var modSystemSetting $setting */
	$setting = $modx->newObject('modSystemSetting');
	$setting->fromArray(array_merge(
		array(
			'key' => 'modboilerplate.' . $k,
			'namespace' => 'modboilerplate'
		), $v
	), '', true, true);

	$settings[] = $setting;
}

unset($tmp);
return $settings;
