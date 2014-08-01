<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AccessToken {
	private
		/**
		 * @var Gajus\Puss\SignedRequest
		 */
		$signed_request;
	
	/**
	 * @param string $raw_signed_request It is base64url encoded and signed with an HMAC version of your App Secret, based on the OAuth 2.0 spec.
	 * @param Gajus\Puss\App $app
	 */
	public function __construct (SignedRequest $signed_request) {
		$this->signed_request = $signed_request;
	}

	public function verifyToken () {
		// @todo https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0 (Inspecting Access Token)
	}

	/*public function isExpired () {

	}

	public function isoauth_token () {
		
	}*/
}