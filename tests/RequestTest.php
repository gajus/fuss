<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class RequestTest extends PHPUnit_Framework_TestCase {
    private
        /**
         * @var Gajus\Puss\App
         */
        $app;

    public function setUp () {
        $this->app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);
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
        $access_token = create_test_user()['access_token'];

        $user = new Gajus\Puss\User(new Gajus\Puss\AccessToken($this->app, $access_token, Gajus\Puss\AccessToken::TYPE_USER));

        $request = new Gajus\Puss\Request($user, 'GET', 'me');

        $this->assertSame('https://graph.facebook.com/me?access_token=' . urlencode($access_token) . '&appsecret_proof=' . self::getAppSecretProof($access_token), $request->getUrl());
    }
    
    public function testGetUrlWithQuery () {
        $request = new Gajus\Puss\Request($this->app, 'GET', 'me', ['a' => 'b']);

        $access_token = $this->app->getAccessToken()->getPlain();

        $this->assertSame('https://graph.facebook.com/me?a=b&access_token=' . urlencode($access_token) . '&appsecret_proof=' . self::getAppSecretProof($access_token), $request->getUrl());
    }

    /**
     * @dataProvider overwriteSessionQueryProvider
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage Cannot overwrite session parameters.
     */
    public function testOverwriteSessionQuery ($parameter_name) {
        $query = [];
        $query[$parameter_name] = '';

        $request = new Gajus\Puss\Request($this->app, 'GET', 'me', $query);
    }

    public function overwriteSessionQueryProvider () {
        return [
            ['access_token'],
            ['appsecret_proof']
        ];
    }

    public function testSetBody () {
        $request = new Gajus\Puss\Request($this->app, 'POST', 'me');
        $request->setBody(['foo' => 'bar']);

        $reflection_class = new ReflectionClass('Gajus\Puss\Request');
        $reflection_property = $reflection_class->getProperty('body');
        $reflection_property->setAccessible(true);
        $body = $reflection_property->getValue($request);

        $this->assertSame(['foo' => 'bar'], $body);
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage GET request method must not have body.
     */
    public function testSetBodyWithIncompatibleRequestMethod () {
        $request = new Gajus\Puss\Request($this->app, 'GET', 'me');
        $request->setBody(['foo' => 'bar']);
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

    /**
     * @dataProvider nonStringBodyParametersProvider
     */
    public function testNonStringBodyParameters (array $restrictions) {
        $request = new Gajus\Puss\Request($this->app, 'POST', 'app');
        $request->setBody(['restrictions' => $restrictions]);

        $this->assertTrue($request->make());

        $request = new Gajus\Puss\Request($this->app, 'GET', 'app', ['fields' => 'restrictions']);

        $response = $request->make();

        $this->assertSame($restrictions, $response['restrictions']);
    }

    public function nonStringBodyParametersProvider () {
        return [
            [
                ['age' => '17+'],
                ['type' => 'alcohol']
            ],
            [
                ['age' => '17+']
            ]
        ];
    }

    static private function getAppSecretProof ($access_token) {
       return hash_hmac('sha256', $access_token, \TEST_APP_SECRET);
    }
}