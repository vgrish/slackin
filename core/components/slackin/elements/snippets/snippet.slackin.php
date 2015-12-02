<?php

/** @var array $scriptProperties */
$scriptProperties['snippetName'] = $modx->getOption('snippetName', $scriptProperties, $this->get('name'), true);
$scriptProperties['objectName'] = $modx->getOption('objectName', $scriptProperties, 'el', true);

$scriptProperties['tplInvite'] = $modx->getOption('tplInvite', $scriptProperties, 'tpl.slackin.invite', true);
$scriptProperties['ttlLock'] = $modx->getOption('ttlLock', $scriptProperties, 60, true);

$scriptProperties['namespace'] = $modx->getOption('namespace', $scriptProperties, 'slackin', true);
$scriptProperties['path'] = $modx->getOption('path', $scriptProperties, 'controllers/web/slackininvite', true);
$scriptProperties['location'] = $modx->getOption('location', $scriptProperties, 1, true);

$propkey = array();
foreach ($scriptProperties as $k => $v) {
	if (!in_array($k, array('id'))) {
		$propkey[$k] = $v;
	}
}
$scriptProperties['propkey'] = $modx->getOption('propkey', $scriptProperties, sha1(serialize($propkey)), true);

/** @var modSnippet $snippet */
if ($snippet = $modx->getObject('modSnippet', array('name' => 'ecc'))) {
	$snippet->_cacheable = false;
	$snippet->_processed = false;
	return $snippet->process($scriptProperties);
}
