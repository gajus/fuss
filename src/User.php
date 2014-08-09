<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class User implements Session {
	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app,
		/**
		 * @var Gajus\Puss\AccessToken
		 */
		$access_token;
	
	/**
	 * @param Gajus\Puss\App $app
	 */
	public function __construct ($app) {
		$this->app = $app;
	}

	/**
	 * @param Gajus\Puss\AccessToken $access_token
	 * @return null
	 */
	public function setAccessToken (Gajus\Puss\AccessToken $access_token) {
		$this->access_token = $access_token;
	}

	/**
	 * @return Gajus\Puss\AccessToken
	 */
	public function getAccessToken () {
		return $this->access_token;
	}

	/**
	 * Get user ID.
	 * As of Graph API v2, the user ID is app-scoped (https://developers.facebook.com/docs/apps/upgrading#upgrading_v2_0_user_ids).
	 * 
	 * @return null|int Facebook user ID
	 */
	public function getId () {
		$signed_request = $this->app->getSignedRequest();

		return $signed_request && $signed_request->getUserId();
	}

	/**
	 * @return string App secret.
	 */
	public function getSecret () {
		return $this->app->getSecret();
	}

	/**
	 * Return generic information about the user.
	 * 
	 * @return array
	 */
	public function getMe () {
		if ($this->access_token) {
			throw new Exception\FacebookException('There is no access token.');
		}

		// @todo
		$this->app->api('me');
	}

	/*public function extendAccessToken ($access_token = null) {
		if ($access_token === null) {
			$access_token = $this->access_token;
		}
		
		if (empty($access_token)) {
			throw new Exception\FacebookException('Missing present access token.');
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
	}*/
}