<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class SignedRequest {
	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app,
		/**
		 * @var string
		 */
		$raw_signed_request;
	
	/**
	 * @param string $raw_signed_request It is base64url encoded and signed with an HMAC version of your App Secret, based on the OAuth 2.0 spec.
	 * @param Gajus\Puss\App $app
	 */
	public function __construct (App $app, $raw_signed_request) {
		$this->app = $app;
		$this->raw_signed_request = (string) $raw_signed_request;
		$this->signed_request = $this->parse($this->raw_signed_request);
	}

	/**
	 * Parse signed request and validate the signature.
	 * 
	 * @see https://developers.facebook.com/docs/facebook-login/using-login-with-games
	 * @see https://developers.facebook.com/docs/reference/login/signed-request
	 * @param string $raw_signed_request
	 * @return null
	 */
	private function parse ($raw_signed_request) {
		$signed_request = [];

		list($signed_request['encoded_signature'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);

		$expected_signature = hash_hmac('sha256', $signed_request['payload'], $this->app->getSecret(), true);
		
		if (static::decodeBase64Url($signed_request['encoded_signature']) !== $expected_signature) {
			throw new Exception\FacebookException('Invalid signature.');
		}

		$signed_request['payload'] = json_decode(static::decodeBase64Url($signed_request['payload']), true);

		#if (isset($signed_request['payload']['code'])) {
		#	$access_token = $this->getAccessTokenFromCode($signed_request['payload']['code'], '');

			// $access_token['access_token']
			// expires = $_SERVER['REQUEST_TIME'] + $access_token['expires']
		#}

		// user_access_token = oauth_token ???

		return $signed_request;
	}

	/**
	 * When code is received, it has to be exchanged for an access token.
	 *
	 * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0#exchangecode
	 * @param string $code The parameter received from the Login Dialog.
	 * @param string $redirect_url This argument is required and must be the same as the original request_uri that you used when starting the OAuth login process.
	 * @return $access_token
	 */
	private function exchangeCodeForAccessToken ($code, $redirect_url) {
		$url = $this->makeRequestUrl('graph', 'oauth/access_token', [
			'client_id' => $this->getId(),
			'redirect_uri' => $redirect_url,
			'client_secret' => $this->getSecret(),
			'code' => $code
		]);

		// @todo Handle error.
		
		$response = $this->makeRequest($url);
		
		parse_str($response, $access_token);
		
		return $access_token;
	}

	public function verifyToken ($token) {
		// @todo https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0 (Inspecting Access Token)
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

	/*public function isExpired () {

	}

	public function isoauth_token () {
		
	}*/
}