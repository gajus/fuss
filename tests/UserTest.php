<?php
class UserTest extends PHPUnit_Framework_TestCase {
    const APP_ID = '820202914671347';
    const APP_SECRET = 'a81411f4d1f8a341c8a97cc7d440c7d0';
    const APP_ACCESS_TOKEN = '820202914671347|a81411f4d1f8a341c8a97cc7d440c7d0';
    const APP_PROOF = 'fd335c4886c9d0a3f7c02e2205c1cbccd4860a48b381c35d5a56f293651d3833';

    public function testGetUserIdFromPostSignedRequest () {
        $_POST['signed_request'] = 'u9I7E42ljSn8erZQo9ZjJMwvvInmoSK5bC4zABKBsr4.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImV4cGlyZXMiOjE0MDY5MDE2MDAsImlzc3VlZF9hdCI6MTQwNjg5NjEyMSwib2F1dGhfdG9rZW4iOiJDQUFMcFpCRjlmYXZNQkFKeTBJRlNhNGJjRzlaQXB2MWZPMVpCYmY5TnVYZmRnZW0wVGlmWXdhVUNJcm9aQnhZRG03aVpCQkVOUjBWRDVDWTRCMEw2NmJ3RjFEWkNNUERTdG1vcHlOclIwM1JmcWsxaDgxQmNjd3BidTFTUEFTRlhUNUE5dHpNSzAzMlMxZ1dPOHVBSHhaQlJaQlJzcFZKMERqZjNWbUc4YVYycnFZQnJiZW5Sc0hOUE9wZFREU1pCeTZsTlNLdVNwNXVmUXFhTEFVcW53cjRRWkJzNmJDMGpoQzc2Z1pEIiwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjIxfX0sInVzZXJfaWQiOiIzMTUyMjQxMzg2MzgwNDgifQ';

        $app = new Gajus\Puss\App(static::APP_ID, static::APP_SECRET);

        $user = new Gajus\Puss\User($app);

        $this->assertSame(315224138638048, $user->getId());
    }

    public function testGetUserSecret () {
        $app = new Gajus\Puss\App(static::APP_ID, static::APP_SECRET);

        $user = new Gajus\Puss\User($app);

        $this->assertSame(static::APP_SECRET, $user->getSecret());
    }
}