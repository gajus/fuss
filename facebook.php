<?php
namespace ay\facebook;

class Facebook {
	private
		$app_id,
		$app_secret,
		$signed_request,
		$user_id,
		$user_access_token,
		$user_locale;
	
	public function __construct (array $config) {
		$this->app_id = $config['app_id'];
		$this->app_secret = $config['app_secret'];
		
		if (!empty($_POST['signed_request'])) {
			$this->parseSignedRequest($_POST['signed_request']);
		} else if (isset($_SESSION['ay']['facebook'][$this->app_id]['signed_request'])) {
			$this->parseSignedRequest($_SESSION['ay']['facebook'][$this->app_id]['signed_request']);
		}
		
		#} else if (isset($_SESSION['ay']['facebook'][$this->app_id]['user']['access_token'])) {
		#	$this->setUserAccessToken($_SESSION['ay']['facebook'][$this->app_id]['user']['access_token']);
		#}
		
		if (!empty($this->signed_request['user']['locale'])) {
			$_SESSION['ay']['facebook'][$this->app_id]['user']['locale'] = $this->signed_request['user']['locale'];
		}
		
		$this->user_locale = empty($_SESSION['ay']['facebook'][$this->app_id]['user']['locale']) ? 'en_US' : $_SESSION['ay']['facebook'][$this->app_id]['user']['locale'];
	}
	
	public function api ($path, array $parameters = null, $post = null) {
		$access_token = $this->getAccessToken();
	
		if ($parameters === null) {
			$parameters = [];
		}
	
		$parameters['access_token']	= $access_token;
		
		try {
			$url = $this->makeRequestUrl('graph', $path, $parameters);
		
			return $this->makeRequest($url, $post);
		} catch (Facebook_Exception $e) {
			// [OAuthException] Error validating access token: The session has been invalidated because the user has changed the password.
			
			/*if ($e->getCode() == 190 && !empty($this->access_token) && !empty($this->signed_request['oauth_token']) && $this->access_token != $this->signed_request['oauth_token']) {
				$this->access_token	= $this->signed_request['oauth_token'];
			
				return $this->api($path, $parameters, $post);
			} else {
				
			}*/
			throw $e;
		}
	}
	
	public function extendAccessToken ($access_token = null) {
		if ($access_token === null) {
			$access_token = $this->access_token;
		}
		
		if (empty($access_token)) {
			throw new Facebook_Exception('Missing present access token.');
		}
	
		$url = $this->makeRequestUrl('graph', 'oauth/access_token', [
			'client_id' => $this->getAppId(),
			'client_secret' => $this->getAppSecret(),
			'grant_type' => 'fb_exchange_token',
			'fb_exchange_token' => $access_token
		]);
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		$this->setUserAccessToken($access_token['access_token']);
		
		$access_token['expires'] += time();
		
		return $access_token;
	}
	
	/**
	 * Return user access token or app access token,
	 * depending on whether the former is available.
	 *
	 * @return string
	 */
	public function getAccessToken () {
		$access_token = $this->getUserAccessToken();
		
		if (!$access_token) {
			$access_token = $this->getAppAccessToken();
		}
	
		return $access_token;
	}
	
	public function getAppId () {
		return $this->app_id;
	}
	
	public function getAppAccessToken () {
		return $this->app_id . '|' . $this->app_secret;
	}
	
	private function getAppSecret () {
		return $this->app_secret;
	}
	
	/**
	 * This is used to prevent CSRF access_token reuse as described in
	 * https://developers.facebook.com/docs/reference/api/securing-graph-api/
	 */
	private function getAppSecretProof () {
		return hash_hmac('sha256', $this->getAccessToken(), $this->getAppSecret());
	}
	
	public function getSignedRequest () {
		return $this->signed_request;
	}
	
	public function getUserAccessToken () {
		return $this->user_access_token;
	}
	
	public function getUserId () {
		return isset($this->signed_request['user_id']) ? $this->signed_request['user_id'] : null;
	}
	
	public function getUserLocale () {
		return $this->user_locale;
	}
	
	private function makeRequest ($url, $post = null) {	
		$ch = curl_init();
		
		$options = [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => ['Expect:'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
		    CURLOPT_TIMEOUT => 60,
		    CURLOPT_USERAGENT => 'anuary-1.0',
		];
		
		if ($post === true) {
			$options[CURLOPT_POST] = true;
		} else if($post !== null) {
			foreach ($post as $k => $v) {
				if (is_array($v)) {
					$post[$k] = json_encode($p);
				}
			}
			
			$options[CURLOPT_POSTFIELDS] = $post;
		}
		
		curl_setopt_array($ch, $options);
		
		$result	= curl_exec($ch);
		
		if ($result === false) {
			throw new Facebook_Exception('[' . curl_errno($ch) . '] ' . curl_error($ch));
		}
		
		curl_close($ch);
		
		$json = json_decode($result, true);
		
		if ($json !== null) {
			$result = $json;
		
			if (!empty($result['error'])) {
				throw new Facebook_Exception('[' . $result['error']['type'] . '] ' . $result['error']['message'], empty($result['error']['code']) ? null : $result['error']['code']);
			}
		}
		
		return $result;
	}
	
	/**
	 * Retrieve API specific URL with custom path and GET parameters.
	 *
	 * @param string $endpoint_name ['api', 'video-api', 'api-read', 'graph', 'graph-video', 'www']
	 * @param string $path
	 * @param array $parameters
	 */
	private function makeRequestUrl ($endpoint_name, $path = '', array $parameters = []) {	
		$url = 'https://' . $endpoint_name . '.facebook.com/' . trim($path, '/');
		
		if ($app_secret_proof = $this->getAppSecretProof()) {
			$parameters['appsecret_proof'] = $app_secret_proof;
		}
		
		if ($parameters) {
			$url .= '?' . http_build_query($parameters);
		}
		
		return $url;
	}
	
	private function getAccessTokenFromCode ($code, $redirect_url) {
		$url = $this->makeRequestUrl('graph', 'oauth/access_token', [
			'client_id' => $this->getAppId(),
			'redirect_uri' => $redirect_url,
			'client_secret' => $this->getAppSecret(),
			'code' => $code
		]);
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		return $access_token;
	}
	
	/**
	 * Parse signed request and validate the signature. Signed request is received when loading app in the Facebook Page frame.
	 *
	 * @param string $raw_signed_request
	 */
	private function parseSignedRequest ($raw_signed_request) {
		$signed_request = [];
		
		// Prevent session cache in case of an error.
		unset($_SESSION['ay']['facebook'][$this->app_id]['signed_request']);
		
		list($signed_request['encoded_sig'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);
		
		$base64_decode = function ($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		};
		
		$expected_signature = hash_hmac('sha256', $signed_request['payload'], $this->app_secret, true);
		
		if ($base64_decode($signed_request['encoded_sig']) !== $expected_signature) {
			throw new Facebook_Exception('Invalid signature.');
		}
		
		$signed_request['payload'] = json_decode($base64_decode($signed_request['payload']), true);
		
		if ($signed_request['payload']['algorithm'] !== 'HMAC-SHA256') {
			throw new Facebook_Exception('Unrecognised algorithm. Expected HMAC-SHA256.');
		}
		
		// This signed_request did not provide oauth_token (e.g. if signed_request is retrieved from FB.getLoginStatus).
		if (isset($signed_request['payload']['code'])) {
			// Don't irritate Facebook with repeated lookups.
			if (!isset($_SESSION['ay']['facebook'][$this->app_id]['_code']) || $_SESSION['ay']['facebook'][$this->app_id]['_code']['code'] !== $signed_request['payload']['code']) {
				$access_token = $this->getAccessTokenFromCode($signed_request['payload']['code'], '');
				
				$_SESSION['ay']['facebook'][$this->app_id]['_code'] = [
					'code' => $signed_request['payload']['code'],
					'oauth_token' => $access_token['access_token'],
					'expires' => $_SERVER['REQUEST_TIME'] + $access_token['expires']
				];
			}
			
			$signed_request['payload']['oauth_token'] = $_SESSION['ay']['facebook'][$this->app_id]['_code']['oauth_token'];
			$signed_request['payload']['expires'] = $_SESSION['ay']['facebook'][$this->app_id]['_code']['expires'];
		}
		
		$_SESSION['ay']['facebook'][$this->app_id]['signed_request'] = $raw_signed_request;
		
		$this->signed_request = $signed_request['payload'];
		$this->user_access_token = isset($signed_request['payload']['oauth_token']) ? $signed_request['payload']['oauth_token'] : null;		
	}
	
	/**
	 * Set the access token for the api calls. Access Token will be either automatically populated using
	 * signed request, $_SESSION['ay']['facebook'][$this->app_id]['user']['access_token']
	 * or overwriten manually.
	 *
	 * @param string $user_access_token
	 */
	private function setUserAccessToken ($user_access_token) {
		$this->user_access_token = $user_access_token;
	}
}