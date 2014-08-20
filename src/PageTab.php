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

        if (!isset($payload['page']['id'])) {
            throw new Exception\PageTabException('Signed request does not describe page tab.');
        }

        $this->page = $payload['page'];
    }

    /**
     * Facebook page ID.
     * 
     * @return int
     */
    public function getPageId () {
        return (int) $this->page['id'];
    }

    /**
     * Returns true if user has liked the Facebook page holding the tab.
     * 
     * @return boolean
     */
    public function isLiked () {
        return (boolean) $this->page['liked'];
    }

    /**
     * Return true if user is admin of the Facebook page holding the tab.
     * 
     * @return boolean
     */
    public function isAdmin () {
        return (boolean) $this->page['admin'];
    }
}