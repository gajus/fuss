<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class App implements Session {
    const OPTION_VERSION = 'version';

    private
        /**
         * @array
         */
        $options = [],
        /**
         * @var int App ID.
         */
        $app_id,
        /**
         * @var string App secret.
         */
        $app_secret,
        /**
         * @var Gajus\Fuss\SignedRequest
         */
        $signed_request,
        /**
         * @var Gajus\Fuss\AccessToken
         */
        $access_token;
    
    /**
     * @param int $app_id App ID.
     * @param string $app_secret App secret.
     * @param array $options
     */
    public function __construct ($app_id, $app_secret, array $options = []) {
        $this->app_id = (int) $app_id;
        $this->app_secret = (string) $app_secret;

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }

        if (isset($_POST['signed_request'])) {  
            $this->setSignedRequest($_POST['signed_request']);
        } else if (isset($_SESSION['gajus']['fuss'][$this->getId()]['signed_request'])) {
            $this->setSignedRequest($_SESSION['gajus']['fuss'][$this->getId()]['signed_request']);
        } else if (isset($_COOKIE['fbsr_' . $this->getId()])) {
            $this->setSignedRequest($_COOKIE['fbsr_' . $this->getId()]);
        }
    }

    /**
     * @param self::OPTION_VERSION $name
     * @param mixed $value
     * @return null
     */
    private function setOption ($name, $value) {
        if ($name !== self::OPTION_VERSION) {
            throw new Exception\AppException('Invalid option.');
        }

        if ($name === self::OPTION_VERSION) {
            if (!preg_match('/^v\d\.\d$/', $value)) {
                throw new Exception\AppException('Invalid OPTION_VERSION value format.');
            }
        }

        $this->options[$name] = $value;
    }

    /**
     * @param self::OPTION_VERSION $name
     * @return mixed
     */
    public function getOption ($name) {
        if ($name !== self::OPTION_VERSION) {
            throw new Exception\AppException('Invalid option.');
        }

        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Designed to be used for a signed request retrieved via the JavaScript SDK.
     * 
     * @see https://developers.facebook.com/docs/reference/javascript/FB.getLoginStatus#response_and_session_objects
     * @param string $signed_request
     * @return null
     */
    public function setSignedRequest ($signed_request) {
        $this->signed_request = new SignedRequest($this, $signed_request);

        $_SESSION['gajus']['fuss'][$this->app_id]['signed_request'] = $signed_request;
    }

    /**
     * @return null|Gajus\Fuss\SignedRequest
     */
    public function getSignedRequest () {
        return $this->signed_request;
    }

    /**
     * @return int
     */
    public function getId () {
        return $this->app_id;
    }

    /**
     * @return string
     */
    public function getSecret () {
        return $this->app_secret;
    }

    /**
     * Deriving the app access token from the app id and secret.
     * The access token of this type bypass the access token validation.
     * 
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#apptokens
     * @return Gajus\Fuss\AccessToken
     */
    public function getAccessToken () {
        if (!$this->access_token) {
            $this->access_token = new AccessToken($this, $this->app_id . '|' . $this->app_secret, AccessToken::TYPE_APP);
        }

        return $this->access_token;
    }
}