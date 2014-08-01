<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class Request {
	private function makeRequest ($url, $post = null) {	
		$ch = curl_init();
		
		$options = [
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => ['Expect:'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
		    CURLOPT_TIMEOUT => 60,
		    CURLOPT_USERAGENT => 'puss-1.0',
		];
		
		if ($post === true) {
			$options[CURLOPT_POST] = true;
		} else if($post !== null) {
			foreach ($post as $k => $v) {
				if (is_array($v)) {
					$post[$k] = json_encode($p);
				}
			}
			
			$options[CURLOPT_POSTFIELDS] = $post;
		}
		
		curl_setopt_array($ch, $options);
		
		$result	= curl_exec($ch);
		
		if ($result === false) {
			throw new Exception\FacebookException('[' . curl_errno($ch) . '] ' . curl_error($ch));
		}
		
		curl_close($ch);
		
		$json = json_decode($result, true);
		
		if ($json !== null) {
			$result = $json;
		
			if (!empty($result['error'])) {
				throw new Exception\FacebookException('[' . $result['error']['type'] . '] ' . $result['error']['message'], empty($result['error']['code']) ? null : $result['error']['code']);
			}
		}
		
		return $result;
	}
	
	/**
	 * Retrieve API specific URL with custom path and GET parameters.
	 *
	 * @param string $endpoint_name ['api', 'video-api', 'api-read', 'graph', 'graph-video', 'www']
	 * @param string $path
	 * @param array $parameters
	 */
	private function makeRequestUrl ($endpoint_name, $path = '', array $parameters = []) {	
		$url = 'https://' . $endpoint_name . '.facebook.com/' . trim($path, '/');
		
		if ($app_secret_proof = $this->getAppSecretProof()) {
			$parameters['appsecret_proof'] = $app_secret_proof;
		}
		
		if ($parameters) {
			$url .= '?' . http_build_query($parameters);
		}
		
		return $url;
	}
}