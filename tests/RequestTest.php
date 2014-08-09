<?php
class RequestTest extends PHPUnit_Framework_TestCase {
    const APP_ID = '820202914671347';
    const APP_SECRET = 'a81411f4d1f8a341c8a97cc7d440c7d0';
    const APP_ACCESS_TOKEN = '820202914671347|a81411f4d1f8a341c8a97cc7d440c7d0';
    const APP_PROOF = 'fd335c4886c9d0a3f7c02e2205c1cbccd4860a48b381c35d5a56f293651d3833';

    private
        /**
         * @var Gajus\Puss\App
         */
        $app,
        /**
         * @var Gajus\Puss\User
         */
        $user;


    public function setUp () {
        $this->app = new Gajus\Puss\App(static::APP_ID, static::APP_SECRET);
        $this->user = new Gajus\Puss\User($this->app);
    }

    public function testUserAgentVersion () {
        $this->assertSame(json_decode(file_get_contents(__DIR__ . '/../composer.json'), true)['version'], Gajus\Puss\Request::VERSION);
    }

    public function testGetUrl () {
        $request = new Gajus\Puss\Request($this->app);

        $this->assertSame('https://graph.facebook.com/?access_token=' . urlencode(static::APP_ACCESS_TOKEN) . '&appsecret_proof=' . static::APP_PROOF, $request->getUrl());
    }

    public function testGetUrlWithPath () {
        $request = new Gajus\Puss\Request($this->app, 'test');

        $this->assertSame('https://graph.facebook.com/test?access_token=' . urlencode(static::APP_ACCESS_TOKEN) . '&appsecret_proof=' . static::APP_PROOF, $request->getUrl());
    }

    public function testGetUrlWithQuery () {
        $request = new Gajus\Puss\Request($this->app);
        $request->setQuery(['a' => 'b']);

        $this->assertSame('https://graph.facebook.com/?a=b&access_token=' . urlencode(static::APP_ACCESS_TOKEN) . '&appsecret_proof=' . static::APP_PROOF, $request->getUrl());
    }

    public function testRequestMethod () {
        $request = new Gajus\Puss\Request($this->app);

        $this->assertSame('GET', $request->getMethod(), 'Request without data must be GET. #1');

        $request->setQuery(['a' => 'b']);

        $this->assertSame('GET', $request->getMethod(), 'Request without data must be GET. #2');

        $request->setData(['a' => 'b']);

        $this->assertSame('POST', $request->getMethod(), 'Request with data must be POST.');
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage [OAuthException] (#803) Some of the aliases you requested do not exist: 4o4
     */
    public function testExecuteInvalidRequestPath () {
        $request = new Gajus\Puss\Request($this->app, '4o4');
        $response = $request->execute();
    }

    /**
     * @expectedException Gajus\Puss\Exception\RequestException
     * @expectedExceptionMessage [OAuthException] Invalid OAuth access token signature.
     */
    public function testExecuteInvalidAccessToken () {
        $app = new Gajus\Puss\App(static::APP_ID, substr_replace(static::APP_SECRET, 'oooooo', 0, 6));

        $request = new Gajus\Puss\Request($app);
        $response = $request->execute();
    }

    public function testExecuteResponse () {
        $request = new Gajus\Puss\Request($this->app, 'app');
        $response = $request->execute();

        $this->assertSame(static::APP_ID, $response['id']);
    }
}