<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class Request {
	const VERSION = '0.0.1';

	private
		/**
		 * @var string GET|POST|DELETE
		 */
		$method = 'GET',
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
		$data = [];

	/**
	 * @param Gajus\Puss\Session $session
	 * @param string $path Path relative to the Graph API.
	 */
	public function __construct (\Gajus\Puss\Session $session, $path = '') {
		$this->session = $session;
		$this->path = $path;

		$this->access_token = $this->session->getAccessToken();

		if (!$this->access_token) {
			throw new Exception\RequestException('Access token is not present.');
		}
	}

	/**
	 * Data will turn the request method into HTTP POST.
	 *
	 * @return string GET|POST
	 */
	public function getMethod () {
		return $this->method;
	}

	/**
	 * @param string $method GET|POST
	 * @return null
	 */
	public function setMethod ($method) {
		if ($method != 'GET' && $method != 'POST' && $method != 'DELETE') {
			throw new Exception\RequestException('Invalid request method.');
		}

		$this->method = $method;
	}

	/**
	 * @param array $query
	 * @return null
	 */
	public function setQuery (array $query) {
		if (isset($query['access_token'], $query['appsecret_proof'])) {
			throw new Exception\RequestException('Cannot overwrite access_token and/or appsecret_proof.');
		}

		$this->query = $query;
	}

	/**
	 * @param array $data
	 * @return null
	 */
	public function setData (array $data) {
		$this->data = $data;

		$this->setMethod('POST');
	}

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
	 * 
	 * @return array
	 */
	public function execute () {	
		$ch = curl_init();
		
		$options = [
			CURLOPT_URL => $this->getUrl(),
			CURLOPT_HTTPHEADER => ['Expect:'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
		    CURLOPT_TIMEOUT => 60,
		    CURLOPT_USERAGENT => 'Puss/' . self::VERSION,
		];
		
		if ($this->getMethod() === 'POST') {
			$options[CURLOPT_POST] = true;

			if ($this->data !== null) {
				$data = $this->data;

				foreach ($data as $k => $v) {
					if (is_array($v)) {
						$data[$k] = json_encode($p);
					}
				}
				
				$options[CURLOPT_POSTFIELDS] = $data;
			}
		}
		
		curl_setopt_array($ch, $options);
		
		$result	= curl_exec($ch);
		
		if ($result === false) {
			throw new Exception\RequestException('[' . curl_errno($ch) . '] ' . curl_error($ch));
		}
		
		curl_close($ch);

		// @todo Test handling of different response types.
		
		$json = json_decode($result, true);

		if (json_last_error() == JSON_ERROR_NONE) {
			$result = $json;
		
			if (!empty($result['error'])) {
				throw new Exception\RequestException('[' . $result['error']['type'] . '] ' . $result['error']['message'], empty($result['error']['code']) ? null : $result['error']['code']);
			}
		} else {
			// The "oauth/access_token" endpoint will return string encoded data:
			// "access_token=CAALpZBF9favMBALi6KuwmoXXo3gEoZCAKniV5xzdwZAUjCNZCZB0NyfZC76BcZCvLqcJyTWBwzj44VNep38uwiXiZBg7VJxwZAxE2uc9ORZA3ZCYbtMtddPdsTDUEtvCA7iAM0EFsmZBynTwZCw7a0mUBUuAddA3Es36p78VJrswdlvhpArVe2VWz14vO&expires=5184000"

			parse_str($result, $result);
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
       return hash_hmac('sha256', $this->session->getAccessToken()->getPlain(), $this->session->getSecret());
    }
}