<?php
/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class AppTest extends PHPUnit_Framework_TestCase {
    private
        $app;

    public function setUp () {
        $this->app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);
    }

    public function testSetVersionOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_VERSION => 'v2.0'
        ]);
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AppException
     * @expectedExceptionMessage Invalid OPTION_VERSION value format.
     */
    public function testSetInvalidVersionOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_VERSION => '2.0'
        ]);
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AppException
     * @expectedExceptionMessage Invalid OPTION_FORCE_COOKIE value format.
     */
    public function testSetInvalidForceCookieOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_FORCE_COOKIE => 'foo'
        ]);
    }

    public function testGetVersionOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_VERSION => 'v2.0'
        ]);

        $this->assertSame('v2.0', $app->getOption(Gajus\Fuss\App::OPTION_VERSION));
    }

    public function testGetForceCookieOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_FORCE_COOKIE => true
        ]);

        $this->assertTrue($app->getOption(Gajus\Fuss\App::OPTION_FORCE_COOKIE));
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AppException
     * @expectedExceptionMessage Invalid option.
     */
    public function testGetInvalidOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);
        $app->getOption('foobar');
    }

    public function testGetId () {
        $this->assertSame((int) \TEST_APP_ID, $this->app->getId());
    }

    public function testGetSecret () {
        $this->assertSame(\TEST_APP_SECRET, $this->app->getSecret());
    }

    public function testGetAccessToken () {
        $this->assertSame(\TEST_APP_ID . '|' . \TEST_APP_SECRET, $this->app->getAccessToken()->getPlain());
    }

    public function testAccessTokenIsCached () {
        $this->assertSame($this->app->getAccessToken(), $this->app->getAccessToken());
    }

    public function testSetSignedRequest () {
        $raw_signed_request = sign_data(['foo' => 'bar']);

        $this->app->setSignedRequest($raw_signed_request);

        $this->assertSame(['foo' => 'bar'], $this->app->getSignedRequest()->getPayload());
    }

    public function testGetSignedRequestFromVoid () {
        $this->assertNull($this->app->getSignedRequest());
    }

    public function testGetSignedRequestFromPost () {
        $_POST['signed_request'] = sign_data(['foo' => 'bar']);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getPayload());
    }

    public function testSignedRequestFromPostIsCached () {
        $this->assertFalse(isset($_SESSION['gajus']['fuss'][\TEST_APP_ID]['signed_request']));

        $signed_data = sign_data([]);

        $_POST['signed_request'] = $signed_data;

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertTrue(isset($_SESSION['gajus']['fuss'][\TEST_APP_ID]['signed_request']));
        $this->assertSame($signed_data, $_SESSION['gajus']['fuss'][\TEST_APP_ID]['signed_request']);
    }

    public function testGetSignedRequestFromCookie () {
        $_COOKIE['fbsr_' . $this->app->getId()]= sign_data(['foo' => 'bar']);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getPayload());
    }

    public function testGetSignedRequestFromSession () {
        $_SESSION['gajus']['fuss'][\TEST_APP_ID]['signed_request'] = sign_data(['foo' => 'bar']);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getPayload());
    }

    /**
     * @expectedException Gajus\Fuss\Exception\AppException
     * @expectedExceptionMessage App is not loaded in Page Tab or Canvas.
     */
    public function testGetTopUrlInInvalidContext () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);
        $this->app->getTopUrl();
    }

    /**
     * @dataProvider getTopUrlProvider
     */
    public function testGetTopUrl2 (array $query, array $signed_request_data, $top_url) {
        $_SERVER['QUERY_STRING'] = http_build_query($query);
        $_POST['signed_request'] = sign_data($signed_request_data);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame($top_url, $app->getTopUrl());
    }

    public function getTopUrlProvider () {
        return [
            [
                [],
                [],
                'https://apps.facebook.com/' . \TEST_APP_ID . '/'
            ],
            [
                ['foo' => 'bar'],
                ['bar' => 'baz'],
                'https://apps.facebook.com/' . \TEST_APP_ID . '/?foo=bar'
            ],
            [
                [],
                [
                    'page' => [
                        'id' => 123
                    ]
                ],
                'https://www.facebook.com/123/app_' . \TEST_APP_ID
            ],
            [
                ['bar' => 'baz'],
                [
                    'page' => [
                        'id' => 123
                    ],
                    'app_data' => ['foo' => 'bar']
                ],
                'https://www.facebook.com/123/app_' . \TEST_APP_ID . '?app_data%5Bfoo%5D=bar'
            ]
        ];
    }
}