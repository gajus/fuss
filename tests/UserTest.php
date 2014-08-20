<?php
/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class UserTest extends PHPUnit_Framework_TestCase {
    private
        $app,
        $raw_user,
        $user;

    public function setUp () {
        $this->raw_user = create_test_user();

        $this->app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $access_token = new Gajus\Fuss\AccessToken($this->app, $this->raw_user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $this->user = new Gajus\Fuss\User($access_token);
    }

    public function testGetUserId () {
        $this->assertSame($this->raw_user['id'], $this->user->getId());
    }

    /**
     * @expectedException Gajus\Fuss\Exception\UserException
     * @expectedExceptionMessage The new access token is for a different user.
     */
    public function testChangeAccessTokenToAnotherUser () {
        $access_token = new Gajus\Fuss\AccessToken($this->app, create_test_user()['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);
        $this->user->setAccessToken($access_token);
    }
}