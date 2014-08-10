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
		 * @var self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE
		 */
		$type,
		/**
		 * @var int
		 */
		$issued_at,
		/**
		 * @var int
		 */
		$expires_at,
		/**
		 * @var array
		 */
		$scope;

	/**
	 * @param Gajus\Puss\App $app
	 * @param string $access_token A string that identifies a user, app, or page and can be used by the app to make graph API calls.
	 * @param self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE $type
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

			if (isset($response['data']['issued_at'])) {
				$this->issued_at = $response['data']['issued_at'];
			}

			if (isset($response['data']['expires_at'])) {
				$this->expires_at = $response['data']['expires_at'];
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
		return $this->expires_at;
	}

	/**
	 * @see https://developers.facebook.com/docs/facebook-login/permissions/v2.1 
	 * @return array Permissions granted to the access token.
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
	 * Extend a short-lived access token for a long-lived access token.
	 * 
	 * @see https://developers.facebook.com/docs/facebook-login/access-tokens#extending
	 * @return null
	 */
	public function extend () {
		if ($this->type != self::TYPE_USER) {
			throw new Exception\AccessTokenException('Only user access token can be extended.');
		}

		if ($this->issued_at) {
			// The issued_at field is not returned for short-lived access tokens.
			// @sse https://developers.facebook.com/docs/facebook-login/access-tokens#debug
			throw new Exception\AccessTokenException('Long-lived access token cannot be extended.');
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
	 * Exchange code for an access token.
	 *
	 * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0#exchangecode
	 * @param string $code The parameter received from the Login Dialog.
	 * @param string $redirect_url This argument is required and must be the same as the original request_uri that you used when starting the OAuth login process. In case of FB.login, it is empty string.
	 * @return Gajus\Puss\AccessToken
	 */
	static public function makeFromCode (\Gajus\Puss\App $app, $code, $redirect_url = '') {
		$request = new \Gajus\Puss\Request($app, 'oauth/access_token');
		$request->setQuery([
			'client_id' => $app->getId(),
			'client_secret' => $app->getSecret(),
			'redirect_uri' => $redirect_url,
			'code' => $code
		]);

		$response = $request->execute();

		return new \Gajus\Puss\AccessToken($app, $response['access_token'], \Gajus\Puss\AccessToken::TYPE_USER);
	}
}