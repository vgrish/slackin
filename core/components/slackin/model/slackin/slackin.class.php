<?php

/**
 * The base class for slackin.
 */
class slackin
{

	/* @var modX $modx */
	public $modx;
	/** @var string $namespace */
	public $namespace = 'slackin';
	/* @var array The array of config */
	public $config = array();

	/** @var array $initialized */
	public $initialized = array();

	/**
	 * @param modX $modx
	 * @param array $config
	 */
	function __construct(modX &$modx, array $config = array())
	{
		$this->modx =& $modx;

		$corePath = $this->modx->getOption('slackin_core_path', $config, $this->modx->getOption('core_path') . 'components/slackin/');
		$assetsUrl = $this->modx->getOption('slackin_assets_url', $config, $this->modx->getOption('assets_url') . 'components/slackin/');
		$connectorUrl = $assetsUrl . 'connector.php';

		$this->config = array_merge(array(
			'assetsUrl' => $assetsUrl,
			'cssUrl' => $assetsUrl . 'css/',
			'jsUrl' => $assetsUrl . 'js/',
			'imagesUrl' => $assetsUrl . 'images/',
			'connectorUrl' => $connectorUrl,
			'actionUrl' => $assetsUrl . 'action.php',

			'corePath' => $corePath,
			'modelPath' => $corePath . 'model/',
			'chunksPath' => $corePath . 'elements/chunks/',
			'templatesPath' => $corePath . 'elements/templates/',
			'chunkSuffix' => '.chunk.tpl',
			'snippetsPath' => $corePath . 'elements/snippets/',
			'processorsPath' => $corePath . 'processors/',

			'prepareResponse' => true,
			'jsonResponse' => true,

		), $config);

		$this->modx->addPackage('slackin', $this->config['modelPath']);
		$this->modx->lexicon->load('slackin:default');
		$this->namespace = $this->getOption('namespace', $config, 'slackin');

	}

	/**
	 * @param $n
	 * @param array $p
	 */
	public function __call($n, array$p)
	{
		echo __METHOD__ . ' says: ' . $n;
	}

	/**
	 * @param $key
	 * @param array $config
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function getOption($key, $config = array(), $default = null)
	{
		$option = $default;
		if (!empty($key) AND is_string($key)) {
			if ($config != null AND array_key_exists($key, $config)) {
				$option = $config[$key];
			} elseif (array_key_exists($key, $this->config)) {
				$option = $this->config[$key];
			} elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
				$option = $this->modx->getOption("{$this->namespace}_{$key}");
			}
		}
		return $option;
	}

	/**
	 * Initializes component into different contexts.
	 *
	 * @param string $ctx The context to load. Defaults to web.
	 * @param array $scriptProperties
	 *
	 * @return boolean
	 */
	public function initialize($ctx = 'web', $scriptProperties = array())
	{
		$this->config = array_merge($this->config, $scriptProperties);
		$this->config['ctx'] = $ctx;

		if (!empty($this->initialized[$ctx])) {
			return true;
		}

		switch ($ctx) {
			case 'mgr':
				break;
			default:
				if (!defined('MODX_API_MODE') OR !MODX_API_MODE) {
					$config = $this->modx->toJSON(array(
						'assetsUrl' => $this->config['assetsUrl'],
						'actionUrl' => $this->config['actionUrl'],
						'defaults' => array(
							'yes' => $this->lexicon('yes'),
							'no' => $this->lexicon('no'),
							'message' => array(
								'title' => array(
									'success' => $this->lexicon('title_ms_success'),
									'error' => $this->lexicon('title_ms_error'),
									'info' => $this->lexicon('title_ms_info'),
								),
							),
							'confirm' => array(
								'title' => array(
									'success' => $this->lexicon('title_cms_success'),
									'error' => $this->lexicon('title_cms_error'),
									'info' => $this->lexicon('title_cms_info'),
								)
							),
							'selector' => array(
								'view' => $this->getOption('selector_view', null, '.slackin-view')
							)
						)
					));
					$script = "<script type=\"text/javascript\">slackinConfig={$config}</script>";
					if (!isset($this->modx->jscripts[$script])) {
						$this->modx->regClientStartupScript($script, true);
					}
					$this->initialized[$ctx] = true;
				}
				break;
		}

		return true;
	}

	/**
	 * return lexicon message if possibly
	 *
	 * @param $message
	 * @param array $placeholders
	 *
	 * @return string
	 */
	public function lexicon($message, $placeholders = array())
	{
		$key = '';
		if ($this->modx->lexicon->exists($message)) {
			$key = $message;
		} elseif ($this->modx->lexicon->exists($this->namespace . '_' . $message)) {
			$key = $this->namespace . '_' . $message;
		}
		if ($key !== '') {
			$message = $this->modx->lexicon->process($key, $placeholders);
		}
		return $message;
	}

	/** @inheritdoc} */
	public function prepareData(array $data = array())
	{
		while (list($key, $val) = each($data)) {
			$keyMethod = 'format' . ucfirst(str_replace('_', '', $key));
			if (!method_exists($this, $keyMethod)) {
				continue;
			}
			$data[$key] = $this->$keyMethod($val);
		}

		return $data;
	}

	/** @inheritdoc} */
	public function validateData(array $data = array())
	{
		while (list($key, $val) = each($data)) {
			$keyMethod = 'validate' . ucfirst(str_replace('_', '', $key));
			if (!method_exists($this, $keyMethod)) {
				continue;
			}
			$data[$key] = $this->$keyMethod($val);
		}

		return $data;
	}

	/** @inheritdoc} */
	public function addLock(array $data = array(), array $options = array())
	{
		$locked = false;
		$lockedBy = $this->getLock($data);
		if (empty($lockedBy)) {
			$ttlLock = $this->getOption('ttlLock', $options, $this->modx->getOption('lock_ttl', null, 360));
			$this->modx->registry->locks->subscribe("/el/{$data['key']}/");
			$this->modx->registry->locks->send("/el/{$data['key']}/", array($data['id'] => $data), array('ttl' => $ttlLock));
			$locked = true;
		}

		return $locked;
	}

	/** @inheritdoc} */
	public function getLock(array $data = array())
	{
		$lock = 0;
		if ($this->modx->getService('registry', 'registry.modRegistry')) {
			$this->modx->registry->addRegister('locks', 'registry.modDbRegister', array('directory' => 'locks'));
			$this->modx->registry->locks->connect();
			$this->modx->registry->locks->subscribe("/el/{$data['key']}/{$data['id']}");
			if ($msgs = $this->modx->registry->locks->read(array('remove_read' => false, 'poll_limit' => 1))) {
				$lock = reset($msgs);
			}
		}
		return $lock;
	}

	/** @inheritdoc} */
	public function removeLock(array $data = array())
	{
		$removed = false;
		if ($this->modx->getService('registry', 'registry.modRegistry')) {
			$this->modx->registry->addRegister('locks', 'registry.modDbRegister', array('directory' => 'locks'));
			$this->modx->registry->locks->connect();
			$this->modx->registry->locks->subscribe("/el/{$data['key']}/{$data['id']}");
			$this->modx->registry->locks->read(array('remove_read' => true, 'poll_limit' => 1));
			$removed = true;
		}

		return $removed;
	}

	/**
	 * This method returns prepared response
	 *
	 * @param mixed $response
	 *
	 * @return array|string $response
	 */
	public function prepareResponse($response)
	{
		if ($response instanceof modProcessorResponse) {
			$output = $response->getResponse();
		} else {
			$message = $response;
			if (empty($message)) {
				$message = $this->lexicon('err_unknown');
			}
			$output = $this->failure($message);
		}
		if ($this->config['jsonResponse'] AND is_array($output)) {
			$output = $this->modx->toJSON($output);
		} elseif (!$this->config['jsonResponse'] AND !is_array($output)) {
			$output = $this->modx->fromJSON($output);
		}
		return $output;
	}

	/**
	 * Shorthand for the call of processor
	 *
	 * @access public
	 *
	 * @param string $action Path to processor
	 * @param array $data Data to be transmitted to the processor
	 *
	 * @return mixed The result of the processor
	 */
	public function runProcessor($action = '', $data = array(), $json = true, $path = '')
	{
		if (empty($action)) {
			return false;
		}

		$this->modx->error->reset();
		/* @var modProcessorResponse $response */
		$response = $this->modx->runProcessor($action, $data, array(
			'processors_path' => !empty($path) ? $path : $this->config['processorsPath']
		));

		if (!$json) {
			$this->setJsonResponse(false);
		}
		$result = $this->config['prepareResponse'] ? $this->prepareResponse($response) : $response;
		$this->setJsonResponse();
		return $result;
	}

	/** @inheritdoc} */
	public function processSnippet(array $data = array(), $name = '')
	{
		$output = '';
		if (isset($data['snippetName'])) {
			$name = $data['snippetName'];
		}
		if ($snippet = $this->modx->getObject('modSnippet', array('name' => $name))) {
			$snippet->_cacheable = false;
			$snippet->_processed = false;
			$properties = $snippet->getProperties();
			$properties = array_merge($properties, $data);
			$output = $snippet->process($properties);
			if (strpos($output, '[[') !== false) {
				$output = $this->processTags($output);
			}
		}
		return $output;
	}

	/**
	 * Collects and processes any set of tags
	 *
	 * @param mixed $html Source code for parse
	 * @param integer $maxIterations
	 *
	 * @return mixed $html Parsed html
	 */
	public function processTags($html, $maxIterations = 10)
	{
		if (strpos($html, '[[') !== false) {
			$this->modx->getParser()->processElementTags('', $html, false, false, '[[', ']]', array(), $maxIterations);
			$this->modx->getParser()->processElementTags('', $html, true, true, '[[', ']]', array(), $maxIterations);
		}
		return $html;
	}

	/** @inheritdoc} */
	public function getPropertiesKey(array $properties = array())
	{
		return !empty($properties['propkey']) ? $properties['propkey'] : false;
	}

	/** @inheritdoc} */
	public function saveProperties(array $properties = array())
	{
		return !empty($properties['propkey']) ? $_SESSION[$this->namespace][$properties['propkey']] = $properties : false;
	}

	/** @inheritdoc} */
	public function getProperties($key = '')
	{
		return !empty($_SESSION[$this->namespace][$key]) ? $_SESSION[$this->namespace][$key] : array();
	}

	/** @inheritdoc} */
	public function formatEmail($value = '')
	{
		return strtolower(trim($value));
	}

	/** @inheritdoc} */
	public function formatPropkey($value = '')
	{
		return trim($value);
	}

	/** @inheritdoc} */
	public function validateEmail($value = '')
	{
		return preg_match('/^[^@а-яА-Я]+@[^@а-яА-Я]+(?<!\.)\.[^\.а-яА-Я]{2,}$/m', $value) ? $value : false;
	}

	/** @inheritdoc} */
	public function validatePropkey($value = '')
	{
		return count($this->getProperties($value)) > 0 ? $value : false;
	}

	/** @inheritdoc} */
	public function setJsonResponse($json = true)
	{
		return ($this->config['jsonResponse'] = $json);
	}

	/**
	 * @param string $message
	 * @param array $data
	 * @param array $placeholders
	 *
	 * @return array|string
	 */
	public function failure($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => false,
			'message' => $this->lexicon($message, $placeholders),
			'data' => $data,
		);
		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

	/**
	 * @param string $message
	 * @param array $data
	 * @param array $placeholders
	 *
	 * @return array|string
	 */
	public function success($message = '', $data = array(), $placeholders = array())
	{
		$response = array(
			'success' => true,
			'message' => $this->lexicon($message, $placeholders),
			'data' => $data,
		);
		return $this->config['jsonResponse'] ? $this->modx->toJSON($response) : $response;
	}

}