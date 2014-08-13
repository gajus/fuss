<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class SignedRequest {
	/**
	 * The signed request from $_POST['signed_request'].
	 */
	const SOURCE_POST = 'POST';
	/**
	 * The signed request from $_SESSION['gajus']['puss'][{APP-ID}]['signed_request'].
	 */
	const SOURCE_SESSION = 'SESSION';
	/**
	 * The signed request from cookie set from the Javascript SDK, $_COOKIE['fbsr_' {APP-ID}].
	 */
	const SOURCE_COOKIE = 'COOKIE';
	/**
	 * The signed request from user input (e.g. acquired via FB.login).
	 */
	const SOURCE_INPUT = 'INPUT';

	private
		/**
		 * @var Gajus\Puss\App
		 */
		$app,
		/**
		 * @var self::SOURCE_POST|self::SOURCE_SESSION|self::SOURCE_INPUT
		 */
		$source,
		/**
		 * @var array
		 */
		$signed_request,
		/**
		 * @var Gajus\Puss\AccessToken
		 */
		$access_token;
	
	/**
	 * @param string $raw_signed_request It is base64url encoded and signed with an HMAC version of your App Secret, based on the OAuth 2.0 spec.
	 * @param Gajus\Puss\App $app
	 * @param self::SOURCE_POST|self::SOURCE_SESSION|self::SOURCE_INPUT $source
	 */
	public function __construct (App $app, $raw_signed_request, $source) {
		$this->app = $app;
		$this->source = $source;
		$this->signed_request = $this->parse($raw_signed_request);
	}

	/**
	 * Return the signed request payload.
	 * 
	 * @return array
	 */
	public function getPayload () {
		return $this->signed_request;
	}

	/**
	 * @return null|int
	 */
	public function getUserId () {
		return isset($this->signed_request['user_id']) ? (int) $this->signed_request['user_id'] : null;
	}

	/**
	 * @return null|int
	 */
	public function getPageId () {
		return isset($this->signed_request['page']['id']) ? (int) $this->signed_request['page']['id'] : null;
	}

	/**
	 * Resolve the user access token from the signed request.
	 * The access token is either provided or it can be exchanged for the code.
	 *
	 * @return null|Gajus\Puss\AccessToken
	 */
	public function getAccessToken () {
		if (!$this->access_token) {
			if (isset($this->signed_request['oauth_token'])) {
				$this->access_token = new \Gajus\Puss\AccessToken($this->app, $this->signed_request['oauth_token'], \Gajus\Puss\AccessToken::TYPE_USER);
			} else if (isset($this->signed_request['code'])) {
				$this->access_token = \Gajus\Puss\AccessToken::makeFromCode($this->app, $this->signed_request['code']);
			}
		}

		return $this->access_token;
	}

	/**
	 * Parse signed request and validate the signature.
	 * 
	 * @see https://developers.facebook.com/docs/facebook-login/using-login-with-games
	 * @see https://developers.facebook.com/docs/reference/login/signed-request
	 * @param string $raw_signed_request
	 * @return array
	 */
	private function parse ($raw_signed_request) {
		$signed_request = [];

		list($signed_request['encoded_signature'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);

		$expected_signature = hash_hmac('sha256', $signed_request['payload'], $this->app->getSecret(), true);
		
		if (static::decodeBase64Url($signed_request['encoded_signature']) !== $expected_signature) {
			throw new Exception\SignedRequestException('Invalid signature.');
		}

		return json_decode(static::decodeBase64Url($signed_request['payload']), true);
	}

	/**
	 * The incoming token is encoded using modified base64 encoding for URL, where
	 * +/ is replaced with -_ to avoid percent-encoded hexadecimal representation.
	 * 
	 * @see http://en.wikipedia.org/wiki/Base64#URL_applications
	 * @see http://php.net/manual/en/function.base64-encode.php#103849
	 * @param string $input
	 * @return string
	 */
	static private function decodeBase64Url ($input) {
		return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT));
	}
}