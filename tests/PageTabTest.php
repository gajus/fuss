<?php
/**
 * @link https://github.com/gajus/fuss for the canonical source repository
 * @license https://github.com/gajus/fuss/blob/master/LICENSE BSD 3-Clause
 */
class PageTabTest extends PHPUnit_Framework_TestCase {
    /**
     * @expectedException Gajus\Fuss\Exception\PageTabException
     * @expectedExceptionMessage Signed request does not describe page tab.
     */
    public function testSignedRequestDoesNotDescribePageTab () {
        $signed_request = make_signed_request([]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);
    }

    public function testGetId () {
        $signed_request = make_signed_request([
            'page' => [
                'id' => '123'
            ]
        ]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);

        $this->assertSame(123, $page_tab->getId());
    }

    public function testPageTabIsLiked () {
        $signed_request = make_signed_request([
            'page' => [
                'id' => '123',
                'liked' => true
            ]
        ]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);

        $this->assertTrue($page_tab->isLiked());
    }

    public function testPageTabIsNotLiked () {
        $signed_request = make_signed_request([
            'page' => [
                'id' => '123',
                'liked' => false
            ]
        ]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);
        $this->assertFalse($page_tab->isLiked());
    }

    public function testPageTabIsAdmin () {
        $signed_request = make_signed_request([
            'page' => [
                'id' => '123',
                'admin' => true
            ]
        ]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);
        $this->assertTrue($page_tab->isAdmin());
    }

    public function testPageTabIsNotAdmin () {
        $signed_request = make_signed_request([
            'page' => [
                'id' => '123',
                'admin' => false
            ]
        ]);

        $page_tab = new Gajus\Fuss\PageTab($signed_request);
        $this->assertFalse($page_tab->isAdmin());
    }
}