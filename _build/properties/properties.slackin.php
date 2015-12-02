<?php

$properties = array();

$tmp = array(
	'tplInvite' => array(
		'type' => 'textfield',
		'value' => 'tpl.slackin.invite',
	),
	'url' => array(
		'type' => 'textfield',
		'value' => 'https://[[+team]].slack.com/api/users.admin.invite',
	),
	'team' => array(
		'type' => 'textfield',
		'value' => 'modxteam',
	),
	'channels' => array(
		'type' => 'textfield',
		'value' => 'C0FL7G0JD',
	),
	'token' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'ttlLock' => array(
		'type' => 'textfield',
		'value' => 10,
	),
	'frontendMainCss' => array(
		'type' => 'textfield',
		'value' => '[[+assetsUrl]]css/web/main/default.css',
	),
	'frontendMainJs' => array(
		'type' => 'textfield',
		'value' => '[[+assetsUrl]]js/web/main/default.js',
	),

	'frontendCss' => array(
		'type' => 'textfield',
		'value' => '',
	),
	'frontendJs' => array(
		'type' => 'textfield',
		'value' => '[[+assetsUrl]]js/web/invite/default.js',
	),
);

foreach ($tmp as $k => $v) {
	$properties[] = array_merge(
		array(
			'name' => $k,
			'desc' => PKG_NAME_LOWER . '_prop_' . $k,
			'lexicon' => PKG_NAME_LOWER . ':properties',
		), $v
	);
}

return $properties;