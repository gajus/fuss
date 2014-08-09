<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AccessToken {
	// @todo
	#const TYPE_USER = 'USER';
	#const TYPE_APP = 'APP';
	#const TYPE_PAGE = 'PAGE';

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
		 * @var int
		 */
		$expires_at;

	/**
	 * @param Gajus\Puss\App
	 * @param string $access_token
	 * @param string $expires_at
	 */
	public function __construct ($app, $access_token) {
		$this->app = $app;
		$this->access_token = $access_token;
	}

	public function verify () {
		$request = new Request($this->app, 'debug_token');
		$request->setQuery(['input_token' => $this->access_token]);
		
		$response = $request->execute();

		if (!$response['data']['is_valid']) {
			throw new Exception\AccessTokenException('Invalid Access Token. ' . $response['data']['error']['message']);
		}

		/*
		if (isset($response['data']['expires_at']) && $response['data']['expires_at'] < time()) {
			throw new Exception\AccessTokenException('Access Token expired.');
		}

		if (isset($response['data']['app_id']) && $response['data']['app_id'] !== $this->app->getId()) {
			throw new Exception\AccessTokenException('Access Token does not belong to this app.');
		}
		*/

		// @todo What if App is suppose to make an action on behalf of another user while this user is logged in?
		/*if (isset($response['data']['user_id'])) {
			$signed_request = $this->app->getSignedRequest();

			if ($signed_request) {
				if ($response['data']['user_id'] !== $signed_request->getUserId()) {
					throw new Exception\AccessTokenException('Access Token does not belong to this app.');
				}
			}
		}*/

		$this->expires_at = $response['data']['expires_at'];
		$this->scope = $response['data']['scopes'];
	}

	// public function debug () {} https://developers.facebook.com/docs/facebook-login/access-tokens#debug

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

	public function __toString () {
		return $this->access_token;
	}
}