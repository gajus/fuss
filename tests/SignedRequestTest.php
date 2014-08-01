<?php
class SignedRequestTest extends PHPUnit_Framework_TestCase {
    private
        /**
         * @var Gajus\Puss\App
         */
        $app;

    public function setUp () {
        $this->app = new Gajus\Puss\App(820202914671347, 'a81411f4d1f8a341c8a97cc7d440c7d0');
    }

    /**
     * @expectedException Gajus\Puss\Exception\FacebookException
     * @expectedExceptionMessage Invalid signature.
     */
    public function testParseInvalidSignedRequest () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, '1NmO-EbScdWkvTGHfo-QcdpgrKL7lAVjw6WAXh87BZM.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjg5MjI3NSwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjp0cnVlLCJhZG1pbiI6dHJ1ZX0sInVzZXIiOnsiY291bnRyeSI6Imx0IiwibG9jYWxlIjoiZW5fVVMiLCJhZ2UiOnsibWluIjoyMX19fQ');
    }

    #public function testParseNotAuthorisedUserViaCanvas () {
    #    $signed_request = new Gajus\Puss\SignedRequest($this->app, 'Ojr0tyYd35uaWSFFuM50F1pP2HiOrY-IZAws1Bknybw.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjg5NTgzNCwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjIxfX19');
    #}
}