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
     * @expectedExceptionMessage Invalid option.
     */
    public function testSetInvalidOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            'foobar' => 'v2.0'
        ]);
    }

    public function testGetSetOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET, [
            Gajus\Fuss\App::OPTION_VERSION => 'v2.0'
        ]);

        $this->assertSame('v2.0', $app->getOption(Gajus\Fuss\App::OPTION_VERSION));
    }

    public function testGetUnsetOption () {
        $app = new Gajus\Fuss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertNull($app->getOption(Gajus\Fuss\App::OPTION_VERSION));
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
}