<?php
class RequestTest extends PHPUnit_Framework_TestCase {
    private
        /**
         * @var Gajus\Puss\App
         */
        $app;

    static private
        $test_users = [];

    public function setUp () {
        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
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
        $request = new Gajus\Puss\Request($this->app, 'POST', 'app/accounts/test-users');
        $request->setQuery(['permissions' => $permissions]);

        $test_user = $request->make();

        self::$test_users[] = $test_user;

        return $test_user;
    }

    public function testUserAgentVersion () {
        $this->assertSame(json_decode(file_get_contents(__DIR__ . '/../composer.json'), true)['version'], Gajus\Puss\Request::AGENT_VERSION);
    }

    public function testGetAppUrl () {
        $request = new Gajus\Puss\Request($this->app, 'GET', 'app');

        $access_token = $this->app->getAccessToken()->getPlain();

        $this->assertSame('https://graph.facebook.com/app?access_token=' . urlencode($access_token) . '&appsecret_proof=' . self::getAppSecretProof($access_token), $request->getUrl());
    }

    public function testGetUserUrl () {
        $access_token = $this->createTestUser()['access_token'];

        $user = new Gajus\Puss\User($this->app);
        $user->setAccessToken(new Gajus\Puss\AccessToken($this->app, $access_token, Gajus\Puss\AccessToken::TYPE_USER));

        $request = new Gajus\Puss\Request($user, 'GET', 'me');

        $this->assertSame('https://graph.facebook.com/me?access_token=' . urlencode($access_token) . '&appsecret_proof=' . self::getAppSecretProof($access_token), $request->getUrl());
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage Access token is not present.
     */
    public function testInvalidSession () {
        $user = new Gajus\Puss\User($this->app);
        
        new Gajus\Puss\Request($user, 'GET', 'me');
    }

    public function testGetUrlWithQuery () {
        $request = new Gajus\Puss\Request($this->app, 'GET', 'me');
        $request->setQuery(['a' => 'b']);

        $access_token = $this->app->getAccessToken()->getPlain();

        $this->assertSame('https://graph.facebook.com/me?a=b&access_token=' . urlencode($access_token) . '&appsecret_proof=' . self::getAppSecretProof($access_token), $request->getUrl());
    }

    /**
     * @dataProvider requestMethodProvider
     */
    public function testRequestMethod ($method_name) {
        $request = new Gajus\Puss\Request($this->app, $method_name, 'me');

        $this->assertSame($method_name, $request->getMethod());
    }

    public function requestMethodProvider () {
        return [
            ['GET'],
            ['POST'],
            ['DELETE']
        ];
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage Invalid request method.
     */
    public function testInvalidRequestMethod () {
        new Gajus\Puss\Request($this->app, 'TEST', 'me');
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage Path must not have hard-coded query parameters.
     */
    public function testExecuteInvalidRequestPath () {
        new Gajus\Puss\Request($this->app, 'GET', 'me?foo=bar');
    }

    public function testMakeRequest () {
        $request = new Gajus\Puss\Request($this->app, 'GET', 'app');

        $response = $request->make();

        $this->assertSame(\TEST_APP_ID, $response['id']);
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage [OAuthException] (#803) Some of the aliases you requested do not exist: 4o4
     */
    public function testMakeInvalidRequestPath () {
        $request = new Gajus\Puss\Request($this->app, 'GET', '4o4');
        
        $request->make();
    }

    static private function getAppSecretProof ($access_token) {
       return hash_hmac('sha256', $access_token, \TEST_APP_SECRET);
    }
}