<?php
class AccessTokenTest extends PHPUnit_Framework_TestCase {    
    public function setUp () {
        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#termtokens
     */
    public function testExtendUserAccessToken () {
        $user = $this->createTestUser();

        $access_token = new Gajus\Puss\AccessToken($this->app, $user['access_token'], Gajus\Puss\AccessToken::TYPE_USER);

        $this->assertGreaterThan(3600, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at least 1 hour.');
        $this->assertLessThan(3600 * 2, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at most 2 hours.');

        $access_token->extendAccessToken();

        $this->assertGreaterThan(86400 * 30, $access_token->getExpirationTimestamp() - time(), 'The long-term access token have a lifetime of at least 30 days.');
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
        $request = new Gajus\Puss\Request($this->app, \TEST_APP_ID . '/accounts/test-users');
        $request->setQuery(['permissions' => $permissions]);
        $request->setMethod('POST');

        return $request->execute();
    }
}