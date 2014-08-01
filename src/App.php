<?php
namespace Gajus\Puss;

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

	/**
	 * appsecret_proof is used as an additional layer of authentication when making
	 * Graph API calls to proof that the access_token has not been stolen.
	 * Enable appsecret_proof setting in the app advanced settings to make it required.
	 *
	 * @see https://developers.facebook.com/docs/reference/api/securing-graph-api/
	 */
	#private function getSecretProof () {
	#	return hash_hmac('sha256', $this->getAccessToken(), $this->getSecret());
	#}

	/**
	 * Parse signed request and validate the signature.
	 * 
	 * @see https://developers.facebook.com/docs/facebook-login/using-login-with-games
	 * @see https://developers.facebook.com/docs/reference/login/signed-request
	 * @param string $raw_signed_request
	 * @return null
	 */
	public function parseSignedRequest ($raw_signed_request) {
		$signed_request = [];

		list($signed_request['encoded_signature'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);

		$expected_signature = hash_hmac('sha256', $signed_request['payload'], $this->app_secret, true);
		
		if (static::decodeBase64Url($signed_request['encoded_signature']) !== $expected_signature) {
			throw new Exception\FacebookException('Invalid signature.');
		}

		return $signed_request;







/*








		
		// Prevent session cache in case of an error.
		unset($_SESSION['gajus']['puss'][$this->app_id]['signed_request']);
		
		list($signed_request['encoded_sig'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);
		
		
		
		$signed_request['payload'] = json_decode(static::decodeBase64Url($signed_request['payload']), true);
		
		if ($signed_request['payload']['algorithm'] !== 'HMAC-SHA256') {
			throw new Exception\FacebookException('Unrecognised algorithm. Expected HMAC-SHA256.');
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
		$this->user_access_token = isset($signed_request['payload']['oauth_token']) ? $signed_request['payload']['oauth_token'] : null;*/
	}

	/**
	 * The incoming token is encoded using modified Base64 encoding for URL, where
	 * +/ is replaced with -_ to avoid percent-encoded hexadecimal representation.
	 * 
	 * @see http://en.wikipedia.org/wiki/Base64#URL_applications
	 */
	static private function decodeBase64Url ($input) {
		return base64_decode(strtr($input, '-_', '+/'));
	}
}