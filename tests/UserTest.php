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

    static private
        $test_users = [];

    public function setUp () {
        $this->raw_user = $this->createTestUser();

        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $access_token = new Gajus\Puss\AccessToken($this->app, $this->raw_user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $this->user = new Gajus\Puss\User($this->app);
        $this->user->setAccessToken($access_token);
    }

    /**
     * Delete all test users after running the test class.
     */
    static public function tearDownAfterClass () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        foreach (self::$test_users as $test_user) {
            $request = new Gajus\Puss\Request($app, 'DELETE', $test_user['id']);

            $request->make();
        }
    }

    /**
     * @param boolean $installed Automatically installs the app for the test user once it is created or associated.
     */
    private function createTestUser ($permissions = '') {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $request = new Gajus\Puss\Request($app, 'POST', 'app/accounts/test-users');
        $request->setQuery(['permissions' => $permissions]);

        $test_user = $request->make();

        self::$test_users[] = $test_user;

        return $test_user;
    }

    public function testGetUserId () {
        $this->assertSame($this->raw_user['id'], $this->user->getId());
    }
}