<?php
/**
 * @link https://github.com/gajus/puss for the canonical source repository
 * @license https://github.com/gajus/puss/blob/master/LICENSE BSD 3-Clause
 */
class AppTest extends PHPUnit_Framework_TestCase {
    public function testGetId () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(\TEST_APP_ID, $app->getId());
    }

    public function testGetSecret () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(\TEST_APP_SECRET, $app->getSecret());
    }

    public function testGetAccessToken () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(\TEST_APP_SECRET . '|' . \TEST_APP_SECRET, $app->getAccessToken()->getPlain());
    }

    public function testGetSignedRequestFromVoid () {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertNull($app->getSignedRequest());
    }

    public function testGetSignedRequestFromPost () {
        $_POST['signed_request'] = self::signData(['foo' => 'bar']);

        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getData());
    }

    public function testGetSignedRequestFromSession () {
        $_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request'] = self::signData(['foo' => 'bar']);

        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertSame(['foo' => 'bar'], $app->getSignedRequest()->getData());
    }

    public function testCacheSignedRequestFromPost () {
        $this->assertFalse(isset($_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']));

        $signed_data = self::signData([]);

        $_POST['signed_request'] = $signed_data;

        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        $this->assertTrue(isset($_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']));
        $this->assertSame($signed_data, $_SESSION['gajus']['puss'][\TEST_APP_ID]['signed_request']);
    }

    /*private function makeSignedRequest (array $data) {
        $app = new Gajus\Puss\App(\TEST_APP_ID, \TEST_APP_SECRET);

        return new Gajus\Puss\SignedRequest($app, self::signData($data), Gajus\Puss\SignedRequest::SOURCE_INPUT);
    }*/

    static private function signData (array $data) {
        $data = json_encode($data, \JSON_UNESCAPED_SLASHES);
        $encoded_data = self::encodeBase64Url($data);
        $encoded_signature = self::encodeBase64Url(hash_hmac('sha256', $encoded_data, \TEST_APP_SECRET, true));

        return $encoded_signature . '.' . $encoded_data;
    }

    static private function encodeBase64Url ($input) {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    static private function decodeBase64Url ($input) {
        return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT)); 
    }
}