<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class SessionTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider sessionProvider
     */
    public function testGetSecret ($session, $secret, $access_token) {
        $this->assertSame($secret, $session->getSecret());
    }

    /**
     * @dataProvider sessionProvider
     */
    public function testGetAccessToken ($session, $secret, $access_token) {
        $this->assertSame($access_token, $session->getAccessToken()->getPlain());
    }

    public function sessionProvider () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
        
        return [
            [$app, \TEST_APP_SECRET, \TEST_APP_ID . '|' . \TEST_APP_SECRET],
            #[$user, \TEST_APP_SECRET, $access_token->getPlain()]
        ];
    }
}