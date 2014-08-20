<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AccessToken {
    const TYPE_USER = 'USER';
    const TYPE_APP = 'APP';
    const TYPE_PAGE = 'PAGE';

    private
        /**
         * @var Gajus\Fuss\App
         */
        $app,
        /**
         * @var string
         */
        $access_token,
        /**
         * @var self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE
         */
        $type,
        /**
         * @var int
         */
        $issued_at,
        /**
         * @var int
         */
        $expires_at,
        /**
         * @var array
         */
        $scope;

    /**
     * @param Gajus\Fuss\App $app
     * @param string $access_token A string that identifies a user, app, or page and can be used by the app to make graph API calls.
     * @param self::TYPE_USER|self::TYPE_APP|self::TYPE_PAGE $type
     */
    public function __construct ($app, $access_token, $type) {
        $this->app = $app;
        $this->access_token = $access_token;
        $this->type = $type;

        $this->debugToken();
    }

    /**
     * @return Gajus\Fuss\App $app
     */
    public function getApp () {
        return $this->app;
    }

    /**
     * The issued_at field is not returned for short-lived access tokens.
     * 
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#debug
     * @return boolean
     */
    public function isLong () {
        return !!$this->issued_at;
    }

    /**
     * Populate the access token information using data retrieved from Facebook.
     * 
     * @throws Gajus\Fuss\Exception\AccessTokenException If access token is not valid.
     * @return null
     */
    private function debugToken () {
        if ($this->type != AccessToken::TYPE_APP) {
            $info = $this->getInfo();

            if (!$info['data']['is_valid']) {
                // @todo Distinguish $info['data']['error']['message']
                throw new Exception\AccessTokenException('Invalid Access Token.');
            }

            if (isset($info['data']['issued_at'])) {
                $this->issued_at = $info['data']['issued_at'];
            }

            if (isset($info['data']['expires_at'])) {
                $this->expires_at = $info['data']['expires_at'];
            }

            if (isset($info['data']['scopes'])) {
                $this->scope = $info['data']['scopes'];
            }
        }
    }

    /**
     * Fetch info about the access token from Facebook.
     *
     * @return array
     */
    public function getInfo () {
        $request = new \Gajus\Fuss\Request($this->app, 'GET', 'debug_token', ['input_token' => $this->access_token]);
        
        return $request->make();
    }

    /**
     * @return int UNIX timestamp in seconds.
     */
    public function getExpirationTimestamp () {
        return $this->expires_at;
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/permissions/v2.1 
     * @return array Permissions granted to the access token.
     */
    public function getScope () {
        return $this->scope;
    }

    /**
     * @return string The access token as a string.
     */
    public function getPlain () {
        return $this->access_token;
    }

    /**
     * Extend a short-lived access token for a long-lived access token.
     * Upon successfully extending the token, the instance of the object
     * is updated with the long-lived access token.
     *
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#extending
     * @return null
     */
    public function extend () {
        if ($this->type != self::TYPE_USER) {
            throw new Exception\AccessTokenException('Only user access token can be extended.');
        }

        if ($this->isLong()) {
            throw new Exception\AccessTokenException('Long-lived access token cannot be extended.');
        }

        $request = new \Gajus\Fuss\Request($this->app, 'GET', 'oauth/access_token', [
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            'grant_type' => 'fb_exchange_token',
            'fb_exchange_token' => $this->access_token
        ]);
        
        $response = $request->make();

        $this->access_token = $response['access_token'];

        $this->debugToken();
    }

    /**
     * Get the code for the long-lived access token.
     *
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
     * @return string
     */
    public function getCode () {
        if (!$this->isLong()) {
            throw new Exception\AccessTokenException('Short-lived access token cannot be used to get code.');
        }

        // The request must be made on behalf of the user (using user access_token).
        $user = new \Gajus\Fuss\User($this);

        // First we need to get the code using the long-lived access token.
        // @see https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
        $request = new \Gajus\Fuss\Request($user, 'GET', 'oauth/client_code', [
            'client_id' => $this->app->getId(),
            'client_secret' => $this->app->getSecret(),
            'redirect_uri' => ''
        ]);

        $response = $request->make();

        return $response['code'];
    }

    /**
     * Exchange code for an access token.
     *
     * @see https://developers.facebook.com/docs/facebook-login/manually-build-a-login-flow/v2.0#exchangecode
     * @param string $code The parameter received from the Login Dialog.
     * @param string $redirect_url This argument is required and must be the same as the original request_uri that you used when starting the OAuth login process. In case of FB.login, it is empty string.
     * @return Gajus\Fuss\AccessToken
     */
    static public function makeFromCode (\Gajus\Fuss\App $app, $code, $redirect_url = '') {
        $request = new \Gajus\Fuss\Request($app, 'GET', 'oauth/access_token', [
            'client_id' => $app->getId(),
            'client_secret' => $app->getSecret(),
            'redirect_uri' => $redirect_url,
            'code' => $code
        ]);

        $response = $request->make();

        return new \Gajus\Fuss\AccessToken($app, $response['access_token'], \Gajus\Fuss\AccessToken::TYPE_USER);
    }
}