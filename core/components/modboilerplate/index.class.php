<?php

/**
 * Class modBoilerplateMainController
 */
abstract class modBoilerplateMainController extends modBoilerplateManagerController {
	/** @var modBoilerplate $modBoilerplate */
	public $modBoilerplate;


	/**
	 * @return void
	 */
	public function initialize() {
		$corePath = $this->modx->getOption('modboilerplate_core_path', null, $this->modx->getOption('core_path') . 'components/modboilerplate/');
		require_once $corePath . 'model/modboilerplate/modboilerplate.class.php';

		$this->modBoilerplate = new modBoilerplate($this->modx);
		$this->addCss($this->modBoilerplate->config['cssUrl'] . 'mgr/main.css');
		$this->addJavascript($this->modBoilerplate->config['jsUrl'] . 'mgr/modboilerplate.js');
		$this->addHtml('
		<script type="text/javascript">
			modBoilerplate.config = ' . $this->modx->toJSON($this->modBoilerplate->config) . ';
			modBoilerplate.config.connector_url = "' . $this->modBoilerplate->config['connectorUrl'] . '";
		</script>
		');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('modboilerplate:default');
	}


	/**
	 * @return bool
	 */
	public function checkPermissions() {
		return true;
	}
}


/**
 * Class IndexManagerController
 */
class IndexManagerController extends modBoilerplateMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}