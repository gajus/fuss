<?php
/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class AccessTokenTest extends PHPUnit_Framework_TestCase {    
    private
        $app;

    public function setUp () {
        $this->app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);
    }

    public function testGetApp () {
        $this->assertSame($this->app, $this->app->getAccessToken()->getApp());
    }

    public function testGetAppInfo () {
        $info = $this->app->getAccessToken()->getInfo();

        $this->assertSame([
            'data' => [
                'app_id' => \TEST_APP_ID,
                'is_valid' => true
            ]
        ], $info);
    }

    public function testGetPlain () {
        $user = create_test_user();

        $access_token = new Gajus\Fuss\AccessToken($this->app, $user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $this->assertSame($user['access_token'], $access_token->getPlain());
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AccessTokenException
     * @expectedExceptionMessage Invalid Access Token.
     */
    public function testInvalidAccessToken () {
        new Gajus\Fuss\AccessToken($this->app, '123', Gajus\Fuss\AccessToken::TYPE_USER);
    }

    /**
     * @see https://developers.facebook.com/docs/facebook-login/access-tokens#termtokens
     */
    public function testExtendUserAccessToken () {
        $user = create_test_user();

        $access_token = new Gajus\Fuss\AccessToken($this->app, $user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $this->assertGreaterThan(3600, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at least 1 hour.');
        $this->assertLessThan(3600 * 2, $access_token->getExpirationTimestamp() - time(), 'Short-term access token have a lifetime of at most 2 hours.');

        $this->assertFalse($access_token->isLong());

        $access_token->extend();

        $this->assertGreaterThan(86400 * 30, $access_token->getExpirationTimestamp() - time(), 'The long-term access token have a lifetime of at least 30 days.');

        $this->assertTrue($access_token->isLong());

        return $access_token;
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AccessTokenException
     * @expectedExceptionMessage Only user access token can be extended.
     */
    public function testExtendAppAccessToken () {
        $access_token = $this->app->getAccessToken();
        $access_token->extend();
    }

    /**
     * @depends testExtendUserAccessToken
     * @expectedException Gajus\Fuss\Exception\AccessTokenException
     * @expectedExceptionMessage Long-lived access token cannot be extended.
     */
    public function testExtendLongLivedUserAccessToken (\Gajus\Fuss\AccessToken $access_token) {
        $access_token->extend();
    }

    /**
     * @depends testExtendUserAccessToken
     */
    public function testExchangeLongLivedAccessTokenForCode (\Gajus\Fuss\AccessToken $access_token) {
        $this->assertTrue($access_token->isLong());

        return $access_token->getCode($access_token);
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AccessTokenException
     * @expectedExceptionMessage Short-lived access token cannot be used to get code.
     */
    public function testExchangeShortLivedAccessTokenForCode () {
        $user = create_test_user();

        $access_token = new Gajus\Fuss\AccessToken($this->app, $user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $this->assertFalse($access_token->isLong());

        $access_token->getCode();
    }

    /**
     * @depends testExchangeLongLivedAccessTokenForCode
     */
    public function testExchageCodeForAccessToken ($code) {
        $access_token = Gajus\Fuss\AccessToken::makeFromCode($this->app, $code);

        $this->assertInstanceOf('Gajus\Fuss\AccessToken', $access_token);
    }

    public function testGetDefaultScope () {
        $user = create_test_user();

        $access_token = new Gajus\Fuss\AccessToken($this->app, $user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $scope = $access_token->getScope();

        $this->assertCount(3, $scope);
        $this->assertContains('public_profile', $scope);
        $this->assertContains('basic_info', $scope);
        $this->assertContains('user_friends', $scope);
    }

    public function testGetCustomScope () {
        $user = create_test_user('email');

        $access_token = new Gajus\Fuss\AccessToken($this->app, $user['access_token'], Gajus\Fuss\AccessToken::TYPE_USER);

        $scope = $access_token->getScope();

        $this->assertCount(4, $scope);
        $this->assertContains('public_profile', $scope);
        $this->assertContains('basic_info', $scope);
        $this->assertContains('email', $scope);
        $this->assertContains('user_friends', $scope);
    }
}