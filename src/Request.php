<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class Request {
    /**
     * Refers to the Fuss release version.
     */
    const AGENT_VERSION = '2.0.4';

    private
        /**
         * @var Gajus\Fuss\Session
         */
        $session,
        /**
         * @var string GET|POST|DELETE
         */
        $method,
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
     * @param Gajus\Fuss\Session $session
     * @param string $method GET|POST|DELETE
     * @param string $path Path relative to the Graph API.
     * @param array $query GET parameters.
     */
    public function __construct (\Gajus\Fuss\Session $session, $method, $path, array $query = null) {
        $this->session = $session;

        $this->setMethod($method);
        $this->setPath($path);

        if ($query) {
            $this->setQuery($query);;
        }
    }

    /**
     * @param string $method
     * @return null
     */
    private function setMethod ($method) {
        if ($method != 'GET' && $method != 'POST' && $method != 'DELETE') {
            throw new Exception\RequestException('Invalid request method.');
        }

        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod () {
        return $this->method;
    }

    /**
     * @param array $query GET parameters.
     * @return null
     */
    private function setQuery (array $query) {
        if (isset($query['access_token']) || isset($query['appsecret_proof'])) {
            throw new Exception\RequestException('Cannot overwrite session parameters.');
        }

        $this->query = $query;
    }

    /**
     * @param string $path
     * @return null
     */
    private function setPath ($path) {
        if (strpos($path, '?') !== false) {
            throw new Exception\RequestException('Path must not have hard-coded query parameters.');
        }

        $this->path = ltrim($path, '/');
    }

    /**
     * True if user provided path explicitly names Graph API endpoint version, e.g. /v2.1/me.
     * 
     * @return boolean
     */
    private function isPathVersioned () {
        return preg_match('/^v\d\.\d\//', $this->path);
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
        $path = $this->path;

        if (!$this->isPathVersioned() && $version = $this->session->getAccessToken()->getApp()->getOption(\Gajus\Fuss\App::OPTION_VERSION)) {
            $path = $version . '/' . $path;
        }

        $url = 'https://graph.facebook.com/' . $path;

        $this->query['access_token'] = $this->session->getAccessToken()->getPlain();
        $this->query['appsecret_proof'] = $this->getAppSecretProof();

        // [GraphMethodException] API calls from the server require an appsecret_proof argument
        // [GraphMethodException] Invalid appsecret_proof provided in the API argument

        $url .= '?' . http_build_query($this->query);

        return $url;
    }

    /**
     * @throws Gajus\Fuss\RequestException If the Graph API call results in an error.
     * @return array Graph API response.
     */
    public function make () {    
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $this->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'Fuss/' . self::AGENT_VERSION,
        ];
        
        if ($this->getMethod() === 'POST') {
            $options[CURLOPT_POST] = true;

            if ($this->body !== null) {
                $body = $this->body;

                // @see http://stackoverflow.com/a/7979981/368691
                foreach ($body as $k => $v) {
                    if (is_array($v)) {
                        $body[$k] = json_encode($v);
                    }
                }
                
                $options[CURLOPT_POSTFIELDS] = $body;
            }
        }
        
        curl_setopt_array($ch, $options);
        
        $result = curl_exec($ch);
        
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
        $access_token = $this->session->getAccessToken();

        return hash_hmac('sha256', $access_token->getPlain(), $access_token->getApp()->getSecret());
    }
}