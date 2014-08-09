<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AccessToken {
	const TYPE_USER = 'USER';
	const TYPE_APP = 'APP';
	const TYPE_PAGE = 'PAGE';

	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app,
		/**
		 * @var string
		 */
		$access_token,
		/**
		 * @var Gajus\Puss\AccessToken::TYPE_USER|Gajus\Puss\AccessToken::TYPE_APP|Gajus\Puss\AccessToken::TYPE_PAGE
		 */
		$type,
		/**
		 * @var int
		 */
		$expiration_timestamp,
		/**
		 * @var array
		 */
		$scope;

	/**
	 * @param Gajus\Puss\App
	 * @param string $access_token
	 * @param string $expires_at
	 */
	public function __construct ($app, $access_token, $type) {
		$this->app = $app;
		$this->access_token = $access_token;
		$this->type = $type;

		$this->debugToken();
	}

	/**
	 * @return null
	 */
	private function debugToken () {
		if ($this->type != AccessToken::TYPE_APP) {
			$request = new Request($this->app, 'debug_token');
			$request->setQuery(['input_token' => $this->access_token]);
			
			$response = $request->execute();

			if (!$response['data']['is_valid']) {
				// @todo Distinguish
				throw new Exception\AccessTokenException('Invalid Access Token. ' . $response['data']['error']['message']);
			}

			if (isset($response['data']['expires_at'])) {
				$this->expiration_timestamp = $response['data']['expires_at'];
			}

			if (isset($response['data']['scopes'])) {
				$this->scope = $response['data']['scopes'];
			}
		}
	}

	/**
	 * @return int
	 */
	public function getExpirationTimestamp () {
		return $this->expiration_timestamp;
	}

	/**
	 * @return array
	 */
	public function getScope () {
		return $this->scope;
	}

	/**
	 * @return string Plain text access token.
	 */
	public function getPlain () {
		return $this->access_token;
	}

	/**
	 * 
	 */
	public function extendAccessToken () {
		if ($this->type != self::TYPE_USER) {
			throw new \Exception('Not implemented.');
		}

		$request = new \Gajus\Puss\Request($this->app, 'oauth/access_token');
        $request->setQuery([
			'client_id' => $this->app->getId(),
			'client_secret' => $this->app->getSecret(),
			'grant_type' => 'fb_exchange_token',
			'fb_exchange_token' => $this->access_token
		]);
        
        $response = $request->execute();

        $this->access_token = $response['access_token'];

        $this->debugToken();
    }

	/**
	 * When code is received, it has to be exchanged for an access token.
	 *
	 * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0#exchangecode
	 * @param string $code The parameter received from the Login Dialog.
	 * @param string $redirect_url This argument is required and must be the same as the original request_uri that you used when starting the OAuth login process. In case of FB.login, it is empty string.
	 * @return $access_token
	 */
	/*static public function exchangeCodeForAccessToken (\Gajus\Puss\App $app, $code, $redirect_url = '') {
		$request = new Gajus\Puss\Http\Request($app);
		$request->makeUrl('graph', 'oauth/access_token', [
			'client_id' => $app->getId(),
			'client_secret' => $app->getSecret(),
			'redirect_uri' => $redirect_url,
			'code' => $code
		]);

		

		// @todo Handle error.
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		return $access_token;

		#if (isset($signed_request['payload']['code'])) {
		#	$access_token = $this->exchangeCodeForAccessToken($signed_request['payload']['code'], '');
			// $access_token['access_token']
			// expires = $_SERVER['REQUEST_TIME'] + $access_token['expires']
		#}

		// user_access_token = oauth_token ???
	}*/
}