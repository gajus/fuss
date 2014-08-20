<?php
namespace Gajus\Fuss;

/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class PageTab {
    private
        /**
         * @var array
         */
        $page;
    
    /**
     * @param Gajus\Fuss\SignedRequest $signed_request
     */
    public function __construct (\Gajus\Fuss\SignedRequest $signed_request) {
        $payload = $signed_request->getPayload();

        if (!isset($payload['page'])) {
            throw new Exception\PageTabException('Signed request does not describe page tab.');
        }

        $this->page = $payload['page'];
    }

    /**
     * The page ID.
     * 
     * @return int
     */
    public function getId () {
        return (int) $this->page['id'];
    }

    /**
     * true if the loading user has liked the page, false if not.
     * 
     * @deprecated This field will no longer be included for any app created after the launch of v2.1 (August 7th, 2014), and will be permanently set to true for all other apps on November 5th, 2014.
     * @see https://developers.facebook.com/docs/reference/login/signed-request
     * @return boolean
     */
    public function isLiked () {
        return isset($this->page['liked']) && $this->page['liked'];
    }

    /**
     * true if the loading user is an admin of the page.
     * 
     * @return boolean
     */
    public function isAdmin () {
        return (boolean) $this->page['admin'];
    }
}