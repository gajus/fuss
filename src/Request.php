<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class Request {
	const AGENT_VERSION = '0.0.1';

	private
		/**
		 * @var string GET|POST|DELETE
		 */
		$method,
		/**
		 * @var Gajus\Puss\AccessToken
		 */
		$access_token,
		/**
		 * @var string
		 */
		$path,
		/**
		 * @var array
		 */
		$query = [],
		/**
		 * @var array
		 */
		$body = [];

	/**
	 * @param Gajus\Puss\Session $session
	 * @param string $method GET|POST|DELETE
	 * @param string $path Path relative to the Graph API.
	 */
	public function __construct (\Gajus\Puss\Session $session, $method, $path) {
		$this->session = $session;

		$this->access_token = $this->session->getAccessToken();

		if (!$this->access_token) {
			throw new Exception\RequestException('Access token is not present.');
		}

		if ($method != 'GET' && $method != 'POST' && $method != 'DELETE') {
			throw new Exception\RequestException('Invalid request method.');
		}

		$this->method = $method;
		
		if (strpos($path, '?') !== false) {
			throw new Exception\RequestException('Path must not have hard-coded query parameters.');
		}

		$this->path = $path;
	}

	/**
	 * @return string GET|POST|UPDATE
	 */
	public function getMethod () {
		return $this->method;
	}

	/**
	 * @param array $query
	 * @return null
	 */
	public function setQuery (array $query) {
		if (isset($query['access_token']) || isset($query['appsecret_proof'])) {
			throw new Exception\RequestException('Cannot overwrite session parameters.');
		}

		$this->query = $query;
	}

	/**
	 * @param array $body
	 * @return null
	 */
	public function setBody (array $body) {
		if ($this->getMethod() !== 'POST') {
			throw new Exception\RequestException($this->getMethod() . ' request method must not have body.');
		}

		$this->body = $body;
	}

	/**
	 * Get URL that will be used to make the request, including the access token and appsecret_proof.
	 *
	 * @return string
	 */
	public function getUrl () {
		$url = 'https://graph.facebook.com/' . trim($this->path, '/');

		$this->query['access_token'] = $this->access_token->getPlain();
		$this->query['appsecret_proof'] = $this->getAppSecretProof();

		// [GraphMethodException] API calls from the server require an appsecret_proof argument
		// [GraphMethodException] Invalid appsecret_proof provided in the API argument

		$url .= '?' . http_build_query($this->query);

		return $url;
	}

	/**
	 * @return array
	 */
	public function make () {	
		$ch = curl_init();
		
		$options = [
			CURLOPT_URL => $this->getUrl(),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
		    CURLOPT_TIMEOUT => 60,
		    CURLOPT_USERAGENT => 'Puss/' . self::AGENT_VERSION,
		];
		
		if ($this->getMethod() === 'POST') {
			$options[CURLOPT_POST] = true;

			if ($this->body !== null) {
				$body = $this->body;

				foreach ($body as $k => $v) {
					if (is_array($v)) {
						$body[$k] = json_encode($p);
					}
				}
				
				$options[CURLOPT_POSTFIELDS] = $body;
			}
		}
		
		curl_setopt_array($ch, $options);
		
		$result	= curl_exec($ch);
		
		if ($result === false) {
			throw new Exception\RequestException('[' . curl_errno($ch) . '] ' . curl_error($ch));
		}
		
		curl_close($ch);
		
		$json = json_decode($result, true);

		if (json_last_error() == JSON_ERROR_NONE) {
			$result = $json;
		} else {
			// The "oauth/access_token" endpoint will return string encoded data:
			// "access_token=CAALpZBF9favMBALi6KuwmoXXo3gEoZCAKniV5xzdwZAUjCNZCZB0NyfZC76BcZCvLqcJyTWBwzj44VNep38uwiXiZBg7VJxwZAxE2uc9ORZA3ZCYbtMtddPdsTDUEtvCA7iAM0EFsmZBynTwZCw7a0mUBUuAddA3Es36p78VJrswdlvhpArVe2VWz14vO&expires=5184000"

			parse_str($result, $result);
		}

		if (isset($result['error'])) {
			throw new Exception\RequestException('[' . $result['error']['type'] . '] ' . $result['error']['message'], empty($result['error']['code']) ? null : $result['error']['code']);
		}
		
		return $result;
	}

	/**
     * appsecret_proof is used as an additional layer of authentication when making
     * Graph API calls to proof that the access_token has not been stolen.
     * Enable appsecret_proof setting in the app advanced settings to make it required.
     *
     * @see https://developers.facebook.com/docs/reference/api/securing-graph-api/
     */
    private function getAppSecretProof () {
       return hash_hmac('sha256', $this->access_token->getPlain(), $this->session->getSecret());
    }
}