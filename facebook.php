<?php
namespace ay\facebook;

class Facebook {
	private
		$app_id,
		$app_secret,
		$url_app,
		$access_token,
		$signed_request,
		$locale;
	
	
	public function __construct (array $config) {
		$this->app_id = $config['app_id'];
		$this->app_secret = $config['app_secret'];
		
		if (!empty($_POST['signed_request'])) {
			$this->parseSignedRequest($_POST['signed_request']);
		}
		
		if (!empty($this->signed_request['user']['locale'])) {
			$_SESSION['ay']['facebook'][$this->app_id]['locale'] = $this->signed_request['user']['locale'];
		}
		
		if (empty($_SESSION['ay']['facebook'][$this->app_id]['locale'])) {
			$_SESSION['ay']['facebook'][$this->app_id]['locale'] = 'en_US';
		}
		
		$this->locale = $_SESSION['ay']['facebook'][$this->app_id]['locale'];
		
		if (!empty($config['url_app'])) {
			$this->url_app = $config['url_app'];
		}
	}
	
	public function api ($path, array $parameters = [], $post = null) {	
		if (!empty($this->access_token)) {
			$parameters['access_token']	= $this->access_token;
		}
		
		try {
			$url = $this->getUrl('graph', $path, $parameters);
		
			return $this->makeRequest($url, $post);
		} catch (Facebook_Exception $e) {
			// [OAuthException] Error validating access token: The session has been invalidated because the user has changed the password.
			
			if ($e->getCode() == 190 && !empty($this->access_token) && !empty($this->signed_request['oauth_token']) && $this->access_token != $this->signed_request['oauth_token']) {
				$this->access_token	= $this->signed_request['oauth_token'];
			
				return $this->api($path, $parameters, $post);
			} else {
				throw $e;
			}
		}
	}
	
	public function setAccessToken ($access_token) {
		$this->access_token	= $access_token;
	}
	
	public function getAccessToken () {
		return $this->access_token;
	}
	
	public function parseSignedRequest ($raw_signed_request) {
		$signed_request = [];
	
		list($signed_request['encoded_sig'], $signed_request['payload']) = array_map(function ($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		}, explode('.', $raw_signed_request, 2));
		
		$data = json_decode($signed_request['payload'], true);
		
		if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
			throw new Facebook_Exception('Unknown algorithm. Expected HMAC-SHA256.');
		}
		
		$expected_sig = hash_hmac('sha256', $signed_request['payload'], $this->app_secret, true);
		
		if ($signed_request['encoded_sig'] !== $expected_sig) {
			throw new Facebook_Exception('Invalid signed request.');
		}
		
		$this->signed_request = $data;
		
		return $data;
	}
	
	public function getAccessTokenFromCode ($code) {
		$url = $this->getUrl('graph', 'oauth/access_token', [
			'client_id' => $this->getAppId(),
			'redirect_uri' => '',
			'client_secret' => $this->getAppSecret(),
			'code' => $code
		]);
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		return $access_token;
	}
	
	public function extendAccessToken ($access_token = null) {
		if ($access_token === null) {
			$access_token = $this->access_token;
		}
		
		if (empty($access_token)) {
			throw new Facebook_Exception('Missing present access token.');
		}
	
		$url = $this->getUrl('graph', 'oauth/access_token', [
			'client_id' => $this->getAppId(),
			'client_secret' => $this->getAppSecret(),
			'grant_type' => 'fb_exchange_token',
			'fb_exchange_token' => $access_token
		]);
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		$this->setAccessToken($access_token['access_token']);
		
		$access_token['expires'] += time();
		
		return $access_token;
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
	 * Retrieve API specific URL with custom path and parameters.
	 * @param string $name
	 * @param string $path
	 * @param array $parameters
	 */
	private function getUrl ($name, $path = null, array $parameters = null) {
		$domain_map	= [
			'api' => 'https://api.facebook.com/',
			'api_video' => 'https://api-video.facebook.com/',
			'api_read' => 'https://api-read.facebook.com/',
			'graph' => 'https://graph.facebook.com/',
			'graph_video' => 'https://graph-video.facebook.com/',
			'www' => 'https://www.facebook.com/'
		];
		
		$url = $domain_map[$name] . trim($path, '/');
		
		if ($parameters) {
			$url .= '?' . http_build_query($parameters);
		}
		
		return $url;
	}
	
	/**
	 * @param string $scope Refer to https://developers.facebook.com/docs/reference/dialogs/oauth/
	 * @param array $app_data Refer to https://developers.facebook.com/docs/reference/login/signed-request/
	 * @param string $redirect_uri Refer to https://developers.facebook.com/docs/reference/login/signed-request/
	 */
	public function authorize ($scope = '', $app_data = [], $redirect_url = null) {
		if (!empty($app_data)) {
			$url = parse_url($redirect_uri);
			
			if (empty($url['query'])) {
				$url['query'] = ['app_data' => $app_data];
			} else {
				parse_str($url['query'], $url['query']);
			
				$url['query'] = array_merge($url['query'], array('app_data' => $app_data));
			}
			
			$url['query'] = http_build_str($url['query']);
			
			$redirect_uri = http_build_url($url);
		}
		
		if (!$redirect_url && $this->url_app) {
			$redirect_url = $this->url_app;
		} else {
			throw new \ErrorException('$redirect_url parameter not provided and $url_app parameter is undefined.');
		}
		
		$parameters	= [
			'client_id' => $this->app_id,
			'redirect_uri' => $redirect_url,
			'state' => $_SESSION['ay']['facebook'][$this->app_id]['state'],
			'scope' => $scope
		];
		
		
		$_SESSION['ay']['facebook'][$this->app_id]['state'] = bin2hex(openssl_random_pseudo_bytes(10));
	
		$login_url = $this->getUrl('www', 'dialog/oauth', $parameters);
		
		echo '
			<noscript>JavaScript must be enabled.</noscript>
			<script type="text/javascript">top.location.href = \'' . addslashes($login_url) . '\';</script>
		';
		
		exit;
	}
}