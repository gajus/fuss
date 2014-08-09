<?php
class AccessTokenTest extends PHPUnit_Framework_TestCase {
    const APP_ID = '820202914671347';
    const APP_SECRET = 'a81411f4d1f8a341c8a97cc7d440c7d0';
    
    public function setUp () {
        $this->app = new Gajus\Puss\App(static::APP_ID, static::APP_SECRET);
    }

    public function testUser () {
        $access_token = new Gajus\Puss\AccessToken($this->app, 'CAALpZBF9favMBAKyKgYzDaZCC6RUABqZCeQa07Mke3QZClpDoo8D4n7mP8r173YXnT0md1cbL1x1rbd1TwUJL1gJcpECkKY9dF19Y4blcntHZAUUZBLTBgM37bgqDvt0DhjMrAvoX93q9MXO94RgIC7toz8ZC89XkXADLBzqzZA4uZACFjpYNcO8CoACCM5ufVeimLli5NnxRfrVulGLYeC27deG7vjlWQYwZD');

        die(var_dump( $access_token ));
    }
}