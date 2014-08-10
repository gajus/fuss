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
        new Gajus\Puss\SignedRequest($this->app, self::signData([], 'abc'), Gajus\Puss\SignedRequest::SOURCE_INPUT);
    }

    public function testGetData () {
        $signed_request = $this->makeSignedRequest(['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $signed_request->getData());
    }

    public function testGetUserId () {
        $signed_request = $this->makeSignedRequest([]);

        $this->assertNull($signed_request->getUserId(), 'User has not authorized the app.');

        $signed_request = $this->makeSignedRequest(['user_id' => 123]);

        $this->assertSame(123, $signed_request->getUserId(), 'User has authorized the app.');
    }

    public function testGetPageId () {
        $signed_request = $this->makeSignedRequest([]);

        $this->assertNull($signed_request->getPageId(), 'Signed request is coming not from canvas.');

        $signed_request = $this->makeSignedRequest(['page' => ['id' => 123]]);

        $this->assertSame(123, $signed_request->getPageId(), 'Signed request is coming from canvas.');
    }

    public function testGetAccessToken () {
        $signed_request = $this->makeSignedRequest([]);

        $this->assertNull($signed_request->getAccessToken());

        $signed_request = $this->makeSignedRequest(['oauth_token' => 'abc']);

        $this->assertSame('abc', $signed_request->getAccessToken());
    }

    public function testGetCode () {
        $signed_request = $this->makeSignedRequest([]);

        $this->assertNull($signed_request->getCode());

        $signed_request = $this->makeSignedRequest(['code' => 'abc']);

        $this->assertSame('abc', $signed_request->getCode());
    }

    private function makeSignedRequest (array $data) {
        return new Gajus\Puss\SignedRequest($this->app, self::signData($data), Gajus\Puss\SignedRequest::SOURCE_INPUT);
    }

    static private function signData (array $data, $secret = null) {
        if ($secret === null) {
            $secret = \TEST_APP_SECRET;
        }

        $data = json_encode($data, \JSON_UNESCAPED_SLASHES);
        $encoded_data = self::encodeBase64Url($data);
        $encoded_signature = self::encodeBase64Url(hash_hmac('sha256', $encoded_data, $secret, true));

        return $encoded_signature . '.' . $encoded_data;
    }

    static private function encodeBase64Url ($input) {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    static private function decodeBase64Url ($input) {
        return base64_decode(str_pad(strtr($input, '-_', '+/'), strlen($input) % 4, '=', STR_PAD_RIGHT)); 
    }
}