<?php

if (!$slackin = $modx->getService('slackin', 'slackin', $modx->getOption('slackin_core_path', null, $modx->getOption('core_path') . 'components/slackin/') . 'model/slackin/', $scriptProperties)) {
	return 'Could not load slackin class!';
}

switch ($modx->event->name) {

}