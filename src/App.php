<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class App implements Session {
    private
        /**
         * @var int App ID.
         */
        $app_id,
        /**
         * @var string App secret.
         */
        $app_secret,
        /**
         * @var Gajus\Puss\SignedRequest
         */
        $signed_request,
        /**
         * @var Gajus\Puss\AccessToken
         */
        $access_token;
    
    /**
     * @param int $app_id App ID.
     * @param string $app_secret App secret.
     */
    public function __construct ($app_id, $app_secret) {
        $this->app_id = (int) $app_id;
        $this->app_secret = (string) $app_secret;

        if (isset($_POST['signed_request'])) {  
            $this->signed_request = new SignedRequest($this, $_POST['signed_request'], SignedRequest::SOURCE_POST);

            $_SESSION['gajus']['puss'][$this->getId()]['signed_request'] = $_POST['signed_request'];
        } else if (isset($_SESSION['gajus']['puss'][$this->getId()]['signed_request'])) {
            $this->signed_request = new SignedRequest($this, $_SESSION['gajus']['puss'][$this->getId()]['signed_request'], SignedRequest::SOURCE_SESSION);
        } else if (isset($_COOKIE['fbsr_' . $this->getId()])) {
            $this->signed_request = new SignedRequest($this, $_COOKIE['fbsr_' . $this->getId()], SignedRequest::SOURCE_COOKIE);
        }
    }

    /**
     * Designed to be used for a signed request retrieved via FB.login.
     * 
     * @param string $signed_request
     * @return null
     */
    /*public function setSignedRequest ($signed_request) {
        $this->signed_request = new SignedRequest($this, $signed_request, SignedRequest::SOURCE_INPUT);

        $_SESSION['gajus']['puss'][$this->app_id]['signed_request'] = $signed_request;
    }*/

    /**
     * @return null|Gajus\Puss\SignedRequest
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
     * @return Gajus\Puss\AccessToken
     */
    public function getAccessToken () {
        if (!$this->access_token) {
            $this->access_token = new AccessToken($this, $this->app_id . '|' . $this->app_secret, AccessToken::TYPE_APP);
        }

        return $this->access_token;
    }
}