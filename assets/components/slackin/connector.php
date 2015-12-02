<?php
/** @noinspection PhpIncludeInspection */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var slackin $slackin */
$slackin = $modx->getService('slackin', 'slackin', $modx->getOption('slackin_core_path', null, $modx->getOption('core_path') . 'components/slackin/') . 'model/slackin/');
$modx->lexicon->load('slackin:default');

// handle request
$corePath = $modx->getOption('slackin_core_path', null, $modx->getOption('core_path') . 'components/slackin/');
$path = $modx->getOption('processorsPath', $slackin->config, $corePath . 'processors/');
$modx->request->handleRequest(array(
	'processors_path' => $path,
	'location' => '',
));