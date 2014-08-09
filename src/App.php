<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class App implements Session {
	private
		/**
		 * @var string App ID.
		 */
		$app_id,
		/**
		 * @var string App secret.
		 */
		$app_secret,
		/**
		 * @var Gajus\Puss\SignedRequest
		 */
		$signed_request;
	
	/**
	 * @param string $app_id App ID.
	 * @param string $app_secret App secret.
	 */
	public function __construct ($app_id, $app_secret) {
		$this->app_id = (string) $app_id;
		$this->app_secret = (string) $app_secret;

		if (isset($_POST['signed_request'])) {  
			$this->signed_request = new SignedRequest($this, $_POST['signed_request'], SignedRequest::SOURCE_POST);
		} else if (isset($_SESSION['gajus']['puss'][$this->app_id]['signed_request'])) {
		    $this->signed_request = new SignedRequest($this, $_SESSION['gajus']['puss'][$this->app_id]['signed_request'], SignedRequest::SOURCE_SESSION);
		}
	}

	/**
	 * Designed to be used for a signed request retrieved via FB.login.
	 * 
	 * @param string $signed_request
	 * @return null
	 */
	public function setSignedRequest ($signed_request) {
		$this->signed_request = new SignedRequest($this, $signed_request, SignedRequest::SOURCE_INPUT);
	}

	/**
	 * @return null|Gajus\Puss\SignedRequest
	 */
	public function getSignedRequest () {
		return $this->signed_request;
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
	 * @return Gajus\Puss\AccessToken
	 */
	public function getAccessToken () {
		return new AccessToken($this, $this->app_id . '|' . $this->app_secret);
	}
}