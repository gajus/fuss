<?php
namespace Gajus\Puss;

/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class User implements Session {
    private
        /**
         * @var array
         */
        $id,
        /**
         * @var Gajus\Puss\AccessToken
         */
        $access_token;
    
    /**
     * @param Gajus\Puss\AccessToken $access_token
     */
    public function __construct (\Gajus\Puss\AccessToken $access_token) {
        $this->setAccessToken($access_token);
    }

    /**
     * Get Facebook user ID.
     * Beware that as of Graph API v2.0, the user ID is app-scoped.
     *
     * @see https://developers.facebook.com/docs/apps/upgrading#upgrading_v2_0_user_ids
     * @return null|int
     */
    public function getId () {
        return $this->id;
    }

    /**
     * @param Gajus\Puss\AccessToken $access_token
     * @return null
     */
    public function setAccessToken (\Gajus\Puss\AccessToken $access_token) {
        $this->access_token = $access_token;

        $request = new \Gajus\Puss\Request($this, 'GET', 'me', ['fields' => 'id']);
        
        $response = $request->make();

        // @todo Check if it is user access token, as oppose to page or whatever.

        if ($this->id && $response['id'] !== $this->id) {
            throw new \Gajus\Puss\Exception\UserException('The new access token is for a different user.');
        }

        $this->id = $response['id'];
    }

    /**
     * @return Gajus\Puss\AccessToken
     */
    public function getAccessToken () {
        return $this->access_token;
    }

    /**
     * @return string App secret.
     */
    public function getSecret () {
        return $this->access_token->getApp()->getSecret();
    }

    /**
     * Return generic information about the user.
     * 
     * @return array
     */
    /*public function getMe () {
        if ($this->access_token) {
            throw new Exception\FacebookException('There is no access token.');
        }

        // @todo
        #$this->app->api('me');
    }*/
}