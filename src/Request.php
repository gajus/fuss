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
	 * @param array $query
	 * @return null
	 */
	public function setQuery (array $query) {
		$this->query = $query;
	}

	/**
	 * @param array $data
	 * @return null
	 */
	public function setData (array $data) {
		$this->data = $data;
	}

	/**
	 * Data will turn the request method into HTTP POST.
	 *
	 * @return string GET|POST
	 */
	public function getMethod () {
		return empty($this->data) ? 'GET' : 'POST';
	}

	public function getUrl () {
		$url = 'https://graph.facebook.com/' . trim($this->path, '/');

		$this->query['access_token'] = $this->access_token->getTextAccessToken();
		$this->query['appsecret_proof'] = $this->getAppSecretProof();

		// [GraphMethodException] API calls from the server require an appsecret_proof argument
		// [GraphMethodException] Invalid appsecret_proof provided in the API argument

		$url .= '?' . http_build_query($this->query);
		

		return $url;
	}

	/**
	 */
	public function execute() {	
		$ch = curl_init();
		
		$options = [
			CURLOPT_URL => $this->getUrl(),
			CURLOPT_HTTPHEADER => ['Expect:'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
		    CURLOPT_TIMEOUT => 60,
		    CURLOPT_USERAGENT => 'Puss/' . self::VERSION,
		];
		
		if ($this->getMethod()) {

		}

		/*if ($data === true) {
			$options[CURLOPT_POST] = true;
		} else if($data !== null) {
			foreach ($data as $k => $v) {
				if (is_array($v)) {
					$data[$k] = json_encode($p);
				}
			}
			
			$options[CURLOPT_POSTFIELDS] = $data;
		}*/
		
		curl_setopt_array($ch, $options);
		
		$result	= curl_exec($ch);
		
		if ($result === false) {
			throw new Exception\RequestException('[' . curl_errno($ch) . '] ' . curl_error($ch));
		}
		
		curl_close($ch);
		
		$json = json_decode($result, true);
		
		if ($json !== null) {
			$result = $json;
		
			if (!empty($result['error'])) {
				throw new Exception\RequestException('[' . $result['error']['type'] . '] ' . $result['error']['message'], empty($result['error']['code']) ? null : $result['error']['code']);
			}
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
       return hash_hmac('sha256', $this->session->getAccessToken()->getTextAccessToken(), $this->session->getSecret());
    }
}