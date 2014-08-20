<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AppTest extends PHPUnit_Framework_TestCase {
    private
        $app;

    public function setUp () {
        $this->app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);
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
        $this->assertFalse(isset($_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']));

        $signed_data = sign_data([]);

        $_POST['signed_request'] = $signed_data;

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertTrue(isset($_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']));
        $this->assertSame($signed_data, $_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']);
    }

    public function testGetSignedRequestFromCookie () {
        $_COOKIE['fbsr_' . $this->app->getId()]= sign_data(['foo' => 'bar']);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getPayload());
    }

    public function testGetSignedRequestFromSession () {
        $_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request'] = sign_data(['foo' => 'bar']);

        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getPayload());
    }
}