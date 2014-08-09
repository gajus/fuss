<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class SignedRequest {
	/**
	 * Indicates that the signed request was created using data from $_POST['signed_request'].
	 */
	const SOURCE_POST = 'POST';
	/**
	 * Indicates that the signed request was created using data from $_SESSION['gajus']['puss'][{APP-ID}]['signed_request'].
	 */
	const SOURCE_SESSION = 'SESSION';
	/**
	 * Indicates that the signed request was provided by the user (e.g. via FB.login).
	 */
	const SOURCE_INPUT = 'INPUT';

	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app,
		/**
		 * @var Gajus\Puss\SignedRequest::SOURCE_POST|Gajus\Puss\SignedRequest::SOURCE_SESSION|Gajus\Puss\SignedRequest::SOURCE_INPUT
		 */
		$source,
		/**
		 * @var array
		 */
		$signed_request;
	
	/**
	 * @param string $raw_signed_request It is base64url encoded and signed with an HMAC version of your App Secret, based on the OAuth 2.0 spec.
	 * @param Gajus\Puss\App $app
	 * @param Gajus\Puss\SignedRequest::SOURCE_POST|Gajus\Puss\SignedRequest::SOURCE_SESSION|Gajus\Puss\SignedRequest::SOURCE_INPUT $source
	 */
	public function __construct (App $app, $raw_signed_request, $source) {
		$this->app = $app;
		$this->source = $source;
		$this->signed_request = $this->parse($raw_signed_request);
	}

	/**
	 * @return null|int
	 */
	public function getUserId () {
		return isset($this->signed_request['payload']['user_id']) ? (int) $this->signed_request['payload']['user_id'] : null;
	}

	/**
	 * @return null|int
	 */
	public function getPageId () {
		return isset($this->signed_request['payload']['page']['id']) ? (int) $this->signed_request['payload']['page']['id'] : null;
	}

	/**
	 * @return null|string
	 */
	public function getAccessToken () {
		return isset($this->signed_request['payload']['oauth_token']) ? $this->signed_request['payload']['oauth_token'] : null;
	}

	/**
	 * @return null|string
	 */
	public function getCode () {
		return isset($this->signed_request['payload']['code']) ? $this->signed_request['payload']['code'] : null;
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
			throw new Exception\SignedRequestException('Invalid signature.');
		}

		$signed_request['payload'] = json_decode(static::decodeBase64Url($signed_request['payload']), true);

		return $signed_request;
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