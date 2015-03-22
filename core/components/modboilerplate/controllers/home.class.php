<?php

/**
 * The home manager controller for modBoilerplate.
 *
 */
class modBoilerplateHomeManagerController extends modBoilerplateMainController {
	/* @var modBoilerplate $modBoilerplate */
	public $modBoilerplate;


	/**
	 * @param array $scriptProperties
	 */
	public function process(array $scriptProperties = array()) {
	}


	/**
	 * @return null|string
	 */
	public function getPageTitle() {
		return $this->modx->lexicon('modboilerplate');
	}


	/**
	 * @return void
	 */
	public function loadCustomCssJs() {
		$this->addCss($this->modBoilerplate->config['cssUrl'] . 'mgr/main.css');
		$this->addCss($this->modBoilerplate->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/misc/utils.js');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/widgets/items.windows.js');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/widgets/home.panel.js');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/sections/home.js');
		$this->addHtml('<script type="text/javascript">
		Ext.onReady(function() {
			MODx.load({ xtype: "modboilerplate-page-home"});
		});
		</script>');
	}


	/**
	 * @return string
	 */
	public function getTemplateFile() {
		return $this->modBoilerplate->config['templatesPath'] . 'home.tpl';
	}
}