<?php

/** slackinInvite */
class slackinInvite extends eccBaseController
{
	/* @var slackin $slackin */
	public $slackin;

	/** @inheritdoc} */
	public function getLanguageTopics()
	{
		return array('slackin:default');
	}

	/** @inheritdoc} */
	public function loadCustomJsCss()
	{
		if (!isset($this->modx->loadedjscripts[$this->config['objectName']])) {
			$pls = $this->makePlaceholders($this->slackin->config);
			foreach ($this->config as $k => $v) {
				if (is_string($v)) {
					$this->config[$k] = str_replace($pls['pl'], $pls['vl'], $v);
				}
			}
			if ($this->config['jqueryJs']) {
				$this->modx->regClientScript(preg_replace('#(\n|\t)#', '', '
				<script type="text/javascript">
					if (typeof jQuery == "undefined") {
						document.write("<script src=\"' . $this->config['jqueryJs'] . '\" type=\"text/javascript\"><\/script>");
					}
				</script>
				'), true);
			}
			if ($this->config['frontendMainCss']) {
				$this->modx->regClientCSS($this->config['frontendMainCss']);
			}
			if ($this->config['frontendMainJs']) {
				$this->modx->regClientScript($this->config['frontendMainJs']);
			}
			if ($this->config['frontendCss']) {
				$this->modx->regClientCSS($this->config['frontendCss']);
			}
			if ($this->config['frontendJs']) {
				$this->modx->regClientScript($this->config['frontendJs']);
			}
		}

		return $this->modx->loadedjscripts[$this->config['objectName']] = 1;
	}

	/** @inheritdoc} */
	public function initialize($ctx = 'web')
	{
		$this->modx->error->errors = array();
		$this->modx->error->message = '';

		$config = $this->modx->toJSON(array(
			'connectorUrl' => $this->config['actionUrl'],
			'namespace' => $this->config['namespace'],
			'controller' => $this->config['controller'],
			'path' => $this->config['path'],
		));
		$this->regTopScript("eccConfig.{$this->config['namespace']}={$config};");

		/* @var slackin $slackin */
		$corePath = $this->modx->getOption('slackin_core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/slackin/');
		if (!$this->slackin = $this->modx->getService('slackin', 'slackin', $corePath . 'model/slackin/', array('core_path' => $corePath))) {
			return false;
		}
		$this->slackin->initialize();


		$this->config = array_merge($this->config, array(
			'jqueryJs' => $this->config['assetsUrl'] . 'vendor/jquery/jquery.min.js',
		));

		$this->slackin->saveProperties($this->config);

		$topics = $this->getLanguageTopics();
		foreach ($topics as $topic) {
			$this->modx->lexicon->load($topic);
		}

		$this->loadCustomJsCss();

		return true;
	}

	/** @inheritdoc} */
	public function DefaultAction()
	{
		$send = $this->slackin->getLock(array('key' => 'send', 'id' => session_id()));
		$send = (int)isset($send['key']);

		$pls = array(
			'send' => $send,
			'propkey' => $this->slackin->getPropertiesKey($this->config),
		);

		return $this->modx->getChunk($this->config['tplInvite'], $pls);
	}

	/** @inheritdoc} */
	public function defaultProcessorAction($data = array())
	{
		$data = $this->slackin->validateData($this->slackin->prepareData($data));

		return $this->slackin->runProcessor($data['action'], $data, $json = true, MODX_CORE_PATH . 'components/slackin/processors/web/');
	}

}
