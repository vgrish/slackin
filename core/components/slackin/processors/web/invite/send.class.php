<?php


class modslackinInviteSendProcessor extends modObjectProcessor
{
	/** @var slackin $slackin */
	public $slackin;

	/** {@inheritDoc} */
	public function initialize()
	{
		/** @var slackin $slackin */
		$this->slackin = $this->modx->getService('slackin');
		$this->slackin->initialize($this->getProperty('context', $this->modx->context->key));

		return parent::initialize();
	}

	/** {@inheritDoc} */
	public function process()
	{
		$email = $this->getProperty('email');
		if (empty($email)) {
			return $this->slackin->failure($this->slackin->lexicon('err_email_ns'));
		}

		$propKey = $this->getProperty('propkey');
		if (empty($propKey)) {
			return $this->slackin->failure($this->slackin->lexicon('err_propkey_ns'));
		}

		$properties = $this->slackin->getProperties($propKey);
		if (empty($properties)) {
			return $this->slackin->failure($this->slackin->lexicon('err_properties_ns'));
		}

		$locked = $this->slackin->addLock(array('key' => 'email', 'id' => session_id()), array('ttlLock' => $properties['ttlLock']));
		if ($locked !== true) {
			return $this->slackin->failure($this->slackin->lexicon('err_limit_action'));
		}

		$channels = $this->modx->getOption('channels', $properties, 'empty');
		$token = $this->modx->getOption('token', $properties, '');
		$team = $this->modx->getOption('team', $properties, 'modxteam');
		$url = $this->modx->getOption('url', $properties, 'https://[[+team]].slack.com/api/users.admin.invite');
		$url = str_replace('[[+team]]', $team, $url);

		$data = array(
			'email' => $email,
			'first_name' => $email,
			'channels' => $channels,
			'token' => $token,
			'set_active' => true,
			'_attempts' => 1,
		);

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, count($data));
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl);

		if (curl_errno($curl)) {
			throw new ErrorException(curl_error($curl), curl_errno($curl));
		}
		curl_close($curl);

		$response = json_decode($response);
		if ($response === null) {
			throw new Exception('Json parse error');
		}

		if (!$response->ok) {
			return $this->slackin->failure($this->slackin->lexicon('err_'.$response->error, $data));
		}

		$locked = $this->slackin->addLock(array(
			'key' => 'send',
			'id' => session_id()
		), array('ttlLock' => $properties['ttlLock']));
		if ($locked !== true) {
			return $this->slackin->failure($this->slackin->lexicon('err_login_link_send'));
		}

		$array = array(
			'process' => array(
				'id' => '0',
				'type' => 'user',
				'output' => $this->slackin->processSnippet($properties)
			),
			'properties' => array(
			),
		);

		return $this->success($this->slackin->lexicon('invite_send'), $array);
	}
}

return 'modslackinInviteSendProcessor';