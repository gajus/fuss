<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class SignedRequestTest extends PHPUnit_Framework_TestCase {
    private
        /**
         * @var Gajus\Puss\App
         */
        $app;


    public function setUp () {
        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
    }

    /**
     * @expectedException Gajus\Puss\Exception\SignedRequestException
     * @exceptedExceptionMessage Invalid signature.
     */
    public function testInvalidSignature () {
        new Gajus\Puss\SignedRequest($this->app, sign_data([], 'abc'));
    }

    public function testGetPayload () {
        $signed_request = make_signed_request(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $signed_request->getPayload());
    }

    /**
     * @dataProvider getAppDataProvider
     */
    public function testGetAppData ($signed_request_payload, $expected_data) {
        $signed_request = make_signed_request($signed_request_payload);

        $this->assertSame($expected_data, $signed_request->getAppData());
    }

    public function getAppDataProvider () {
        return [
            [
                [],
                null
            ],
            [
                ['app_data' => 'foo'], // ?app_data=foo
                'foo'
            ],
            [
                ['app_data' => ['foo' => 'bar']], // ?app_data[foo]=bar
                ['foo' => 'bar']
            ],
            [
                ['app_data' => '{"foo":"bar","baz":"qux"}'], // ?app_data={"foo":"bar","baz":"qux"}
                ['foo' => 'bar', 'baz' => 'qux']
            ]
        ];
    }

    public function testGetUserId () {
        $signed_request = make_signed_request([]);

        $this->assertNull($signed_request->getUserId(), 'User has not authorized the app.');

        $signed_request = make_signed_request(['user_id' => 123]);

        $this->assertSame(123, $signed_request->getUserId(), 'User has authorized the app.');
    }

    public function testGetPageId () {
        $signed_request = make_signed_request([]);

        $this->assertNull($signed_request->getPageId(), 'Signed request is coming not from canvas.');

        $signed_request = make_signed_request(['page' => ['id' => 123]]);

        $this->assertSame(123, $signed_request->getPageId(), 'Signed request is coming from canvas.');
    }

    public function testGetAccessTokenWhenVoid () {
        $signed_request = make_signed_request([]);

        $this->assertNull($signed_request->getAccessToken());
    }

    public function testGetAccessTokenWhenInSignedRequest () {
        $test_user = create_test_user();

        $signed_request = make_signed_request(['oauth_token' => $test_user['access_token']]);

        $access_token = $signed_request->getAccessToken();

        $this->assertInstanceOf('Gajus\Puss\AccessToken', $access_token);
        $this->assertSame($test_user['access_token'], $access_token->getPlain());
    }

    public function testGetAccessTokenWhenFromCode () {
        $test_user = create_test_user();

        $access_token = new \Gajus\Puss\AccessToken($this->app, $test_user['access_token'], \Gajus\Puss\AccessToken::TYPE_USER);

        $access_token->extend();
        $code = $access_token->getCode();

        $signed_request = make_signed_request(['code' => $code]);

        $access_token = $signed_request->getAccessToken();

        $this->assertInstanceOf('Gajus\Puss\AccessToken', $access_token);

        // "In some cases, this newer long-lived token might be identical to the previous one, but we can't guarantee it and your app shouldn't depend upon it."
        // @see https://developers.facebook.com/docs/facebook-login/access-tokens#refreshtokens
        #$this->assertSame($test_user['access_token'], $access_token->getPlain());

        $this->assertSame($test_user['id'], $access_token->getInfo()['data']['user_id']);        
    }
}