<?php
namespace Gajus\Puss;

class Facebook {	
	/**
	 * Parse signed request and validate the signature. Signed request is received when loading app in the Facebook Page frame.
	 *
	 * @param string $raw_signed_request
	 */
	private function parseSignedRequest ($raw_signed_request) {
		$signed_request = [];
		
		// Prevent session cache in case of an error.
		unset($_SESSION['ay']['facebook'][$this->app_id]['signed_request']);
		
		list($signed_request['encoded_sig'], $signed_request['payload']) = explode('.', $raw_signed_request, 2);
		
		$base64_decode = function ($input) {
			return base64_decode(strtr($input, '-_', '+/'));
		};
		
		$expected_signature = hash_hmac('sha256', $signed_request['payload'], $this->app_secret, true);
		
		if ($base64_decode($signed_request['encoded_sig']) !== $expected_signature) {
			throw new Exception\FacebookException('Invalid signature.');
		}
		
		$signed_request['payload'] = json_decode($base64_decode($signed_request['payload']), true);
		
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
		$this->user_access_token = isset($signed_request['payload']['oauth_token']) ? $signed_request['payload']['oauth_token'] : null;		
	}
}