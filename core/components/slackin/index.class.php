<?php

/**
 * Class slackinMainController
 */
abstract class slackinMainController extends modExtraManagerController {
	/** @var slackin $slackin */
	public $slackin;


	/**
	 * @return void
	 */
	public function initialize() {
		$corePath = $this->modx->getOption('slackin_core_path', null, $this->modx->getOption('core_path') . 'components/slackin/');
		require_once $corePath . 'model/slackin/slackin.class.php';

		$this->slackin = new slackin($this->modx);
		$this->addCss($this->slackin->config['cssUrl'] . 'mgr/main.css');
		$this->addJavascript($this->slackin->config['jsUrl'] . 'mgr/slackin.js');
		$this->addHtml('
		<script type="text/javascript">
			slackin.config = ' . $this->modx->toJSON($this->slackin->config) . ';
			slackin.config.connector_url = "' . $this->slackin->config['connectorUrl'] . '";
		</script>
		');

		parent::initialize();
	}


	/**
	 * @return array
	 */
	public function getLanguageTopics() {
		return array('slackin:default');
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
class IndexManagerController extends slackinMainController {

	/**
	 * @return string
	 */
	public static function getDefaultController() {
		return 'home';
	}
}