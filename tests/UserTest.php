<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class UserTest extends PHPUnit_Framework_TestCase {
    private
        $app,
        $raw_user,
        $user;

    public function setUp () {
        $this->raw_user = create_test_user();

        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $access_token = new Gajus\Puss\AccessToken($this->app, $this->raw_user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $this->user = new Gajus\Puss\User($access_token);
    }

    public function testGetUserId () {
        $this->assertSame($this->raw_user['id'], $this->user->getId());
    }

    /**
     * @expectedException Gajus\Puss\Exception\UserException
     * @expectedExceptionMessage The new access token is for a different user.
     */
    public function testChangeAccessTokenToAnotherUser () {
        $access_token = new Gajus\Puss\AccessToken($this->app, create_test_user()['access_token'], Gajus\Puss\AccessToken::TYPE_USER);
        $this->user->setAccessToken($access_token);
    }
}