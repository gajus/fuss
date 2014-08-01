<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class App {
	private
		/**
		 * @var string App ID.
		 */
		$app_id,
		/**
		 * @var string App Secret.
		 */
		$app_secret;
	
	/**
	 * @param string $app_id App ID.
	 * @param string $app_secret App Secret.
	 */
	public function __construct ($app_id, $app_secret) {
		$this->app_id = (string) $app_id;
		$this->app_secret = (string) $app_secret;
	}

	/**
	 * @return string
	 */
	public function getId () {
		return $this->app_id;
	}

	/**
	 * @return string
	 */
	public function getSecret () {
		return $this->app_secret;
	}

	/**
	 * @see https://developers.facebook.com/docs/facebook-login/access-tokens
	 * @return string
	 */
	public function getAccessToken () {
		return $this->app_id . '|' . $this->app_secret;
	}
}