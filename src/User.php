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
		 * @var array
		 */
		$id,
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
	 * Get user ID.
	 *
	 * @return null|int Facebook user ID
	 */
	public function getId () {
		return $this->id;
	}

	/**
	 * @param Gajus\Puss\AccessToken $access_token
	 * @return null
	 */
	public function setAccessToken (\Gajus\Puss\AccessToken $access_token) {
		$this->access_token = $access_token;

		$request = new \Gajus\Puss\Request($this, 'GET', 'me');
        $request->setQuery(['fields' => 'id']);
        
        $response = $request->make();

        // @todo Check if it is user access token, as oppose to page or whatever.

        $this->id = $response['id'];
	}

	/**
	 * @return Gajus\Puss\AccessToken
	 */
	public function getAccessToken () {
		return $this->access_token;
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
	/*public function getMe () {
		if ($this->access_token) {
			throw new Exception\FacebookException('There is no access token.');
		}

		// @todo
		#$this->app->api('me');
	}*/
}