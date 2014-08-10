<?php
class AccessTokenTest extends PHPUnit_Framework_TestCase {    
    private
        $app;

    static private
        $test_users = [];

    public function setUp () {
        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
    }

    static public function tearDownAfterClass () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        foreach (self::$test_users as $test_user) {
            $request = new Gajus\Puss\Request($app, $test_user['id']);
            $request->setMethod('DELETE');

            $request->execute();
        }
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#termtokens
     */
    public function testExtendUserAccessToken () {
        $user = $this->createTestUser();

        $access_token = new Gajus\Puss\AccessToken($this->app, $user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $this->assertGreaterThan(3600, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at least 1 hour.');
        $this->assertLessThan(3600 * 2, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at most 2 hours.');

        $access_token->extend();

        $this->assertGreaterThan(86400 * 30, $access_token->getExpirationTimestamp() - time(), 'The long-term access token have a lifetime of at least 30 days.');

        return $access_token;
    }

    /**
     * @depends testExtendUserAccessToken
     * @expectedException Gajus\Puss\Exception\AccessTokenException
     * @expectedExceptionMessage Long-lived access token cannot be extended.
     */
    public function testExtendLongLivedUserAccessToken (\Gajus\Puss\AccessToken $access_token) {
        $access_token->extend();
    }

    /**
     * @depends testExtendUserAccessToken
     * @coversNothing
     */
    public function testExchageAccessTokenForCode (\Gajus\Puss\AccessToken $access_token) {
        // The request must be made on behalf of the user (using user access_token).
        $user = new Gajus\Puss\User($this->app);
        $user->setAccessToken($access_token);

        // First we need to get the code using the long-lived access token.
        // @see https://developers.facebook.com/docs/facebook-login/access-tokens#long-via-code
        $request = new Gajus\Puss\Request($user, 'oauth/client_code');
        $request->setQuery([
            'client_id' => \TEST_APP_ID,
            'client_secret' => \TEST_APP_SECRET,
            'redirect_uri' => ''
        ]);

        $response = $request->execute();

        $this->assertArrayHasKey('code', $response);

        return $response['code'];
    }

    /**
     * @depends testExchageAccessTokenForCode
     */
    public function testExchageCodeForAccessToken ($code) {
        $access_token = Gajus\Puss\AccessToken::exchangeCodeForAccessToken($this->app, $code);

        $this->assertInstanceOf('Gajus\Puss\AccessToken', $access_token);
    }

    public function testDefaultScope () {
        $user = $this->createTestUser();

        $access_token = new Gajus\Puss\AccessToken($this->app, $user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $scope = $access_token->getScope();

        $this->assertCount(3, $scope);
        $this->assertContains('public_profile', $scope);
        $this->assertContains('basic_info', $scope);
        $this->assertContains('user_friends', $scope);
    }

    public function testCustomScope () {
        $user = $this->createTestUser('email');

        $access_token = new Gajus\Puss\AccessToken($this->app, $user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $scope = $access_token->getScope();

        $this->assertCount(4, $scope);
        $this->assertContains('public_profile', $scope);
        $this->assertContains('basic_info', $scope);
        $this->assertContains('email', $scope);
        $this->assertContains('user_friends', $scope);
    }

    /**
     * @param boolean $installed Automatically installs the app for the test user once it is created or associated.
     */
    private function createTestUser ($permissions = '') {
        $request = new Gajus\Puss\Request($this->app, 'app/accounts/test-users');
        $request->setQuery(['permissions' => $permissions]);
        $request->setMethod('POST');

        $test_user = $request->execute();

        self::$test_users[] = $test_user;

        return $test_user;
    }
}