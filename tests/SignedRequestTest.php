<?php
class SignedRequestTest extends PHPUnit_Framework_TestCase {
    const APP_ID = '820202914671347';
    const APP_SECRET = 'a81411f4d1f8a341c8a97cc7d440c7d0';

    private
        /**
         * @var Gajus\Puss\App
         */
        $app;


    public function setUp () {
        $this->app = new Gajus\Puss\App(static::APP_ID, static::APP_SECRET);
    }

    /**
     * @expectedException Gajus\Puss\Exception\SignedRequestException
     * @exceptedExceptionMessage Invalid signature.
     */
    public function testInvalidSignature () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'LAF5VLFt3ZJGY2ka5SaAJ91PET1YHpQDDwqBvanqrxs.123', Gajus\Puss\SignedRequest::SOURCE_INPUT);
    }

    public function testPageGetId () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'LAF5VLFt3ZJGY2ka5SaAJ91PET1YHpQDDwqBvanqrxs.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjk3NjYzMCwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjAsIm1heCI6MTJ9fX0', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertSame(142662942474684, $signed_request->getPageId());

        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'aSX1Jmj9WZ-semd722-yBhihOq_ui3IJfFk-mNRTdv4.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUFDMWtBOTkzbkV4Z0VNQktISTYwRHMtS2F5SC1Dem9nZVVOY0VpbTU4TDY2OWVkR29mSHZCaDdWTG1FUVpFZkJQbXYzcDlVTG9XcERKQUJrYVFsSmpfU3hTX19QbEdjbFRaYk41RXJsRVVlTnVDU3Q1RlVDNWg1MWtoX2NkM2Radlp0QkdZdVd6eDVDdTVUSmhLOWxETlVONTVOUEFfRjhxeXhoZl9Rakw5S3VoMzBRNUpoX0JFX1hBRE1JQklpTmRfSkxQam9GeXRXOGFaUF81MmplNjhxdmRIb0xESW1lQXB4cExoc3JRNmVNNERWZHZCenJibFdDeGRTZk9ZTEhHa1Z0QW4xVnhlTGF2UDU0SWpGUEJVZXVrUVVFY242X1hRRFdsV2ZBZzZOMF9STGp6ZkVhUlVyLUo0cFAzZVpSRHk4NmdCTmhRLVJCU2duNjBoOHQ3RCIsImlzc3VlZF9hdCI6MTQwNjg5OTMzMiwidXNlcl9pZCI6IjE0NzM5NzU4MjYxODMyMDQifQ', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertNull($signed_request->getPageId());
    }

    public function testUserNotLoggedIntoFacebook () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'LAF5VLFt3ZJGY2ka5SaAJ91PET1YHpQDDwqBvanqrxs.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjk3NjYzMCwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjAsIm1heCI6MTJ9fX0', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertNull($signed_request->getUserId(), 'User ID must be null.');
        $this->assertNull($signed_request->getAccessToken(), 'Access Token must be null');
        $this->assertNull($signed_request->getCode(), 'Code must be null');
    }

    public function testUserNotAuthorisedApp () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'Ojr0tyYd35uaWSFFuM50F1pP2HiOrY-IZAws1Bknybw.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImlzc3VlZF9hdCI6MTQwNjg5NTgzNCwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjIxfX19', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertNull($signed_request->getUserId(), 'User ID must be null.');
        $this->assertNull($signed_request->getAccessToken(), 'Access Token must be null');
        $this->assertNull($signed_request->getCode(), 'Code must be null');
    }

    public function testUserAuthorisedWithAccessToken () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'u9I7E42ljSn8erZQo9ZjJMwvvInmoSK5bC4zABKBsr4.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImV4cGlyZXMiOjE0MDY5MDE2MDAsImlzc3VlZF9hdCI6MTQwNjg5NjEyMSwib2F1dGhfdG9rZW4iOiJDQUFMcFpCRjlmYXZNQkFKeTBJRlNhNGJjRzlaQXB2MWZPMVpCYmY5TnVYZmRnZW0wVGlmWXdhVUNJcm9aQnhZRG03aVpCQkVOUjBWRDVDWTRCMEw2NmJ3RjFEWkNNUERTdG1vcHlOclIwM1JmcWsxaDgxQmNjd3BidTFTUEFTRlhUNUE5dHpNSzAzMlMxZ1dPOHVBSHhaQlJaQlJzcFZKMERqZjNWbUc4YVYycnFZQnJiZW5Sc0hOUE9wZFREU1pCeTZsTlNLdVNwNXVmUXFhTEFVcW53cjRRWkJzNmJDMGpoQzc2Z1pEIiwicGFnZSI6eyJpZCI6IjE0MjY2Mjk0MjQ3NDY4NCIsImxpa2VkIjpmYWxzZSwiYWRtaW4iOmZhbHNlfSwidXNlciI6eyJjb3VudHJ5IjoibHQiLCJsb2NhbGUiOiJlbl9VUyIsImFnZSI6eyJtaW4iOjIxfX0sInVzZXJfaWQiOiIzMTUyMjQxMzg2MzgwNDgifQ', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertSame(315224138638048, $signed_request->getUserId(), 'User ID.');
        $this->assertSame('CAALpZBF9favMBAJy0IFSa4bcG9ZApv1fO1ZBbf9NuXfdgem0TifYwaUCIroZBxYDm7iZBBENR0VD5CY4B0L66bwF1DZCMPDStmopyNrR03Rfqk1h81Bccwpbu1SPASFXT5A9tzMK032S1gWO8uAHxZBRZBRspVJ0Djf3VmG8aV2rqYBrbenRsHNPOpdTDSZBy6lNSKuSp5ufQqaLAUqnwr4QZBs6bC0jhC76gZD', $signed_request->getAccessToken(), 'Access token.');
        $this->assertNull($signed_request->getCode(), 'Code must be null');
    }

    public function testUserAuthorisedWithCode () {
        $signed_request = new Gajus\Puss\SignedRequest($this->app, 'aSX1Jmj9WZ-semd722-yBhihOq_ui3IJfFk-mNRTdv4.eyJhbGdvcml0aG0iOiJITUFDLVNIQTI1NiIsImNvZGUiOiJBUUFDMWtBOTkzbkV4Z0VNQktISTYwRHMtS2F5SC1Dem9nZVVOY0VpbTU4TDY2OWVkR29mSHZCaDdWTG1FUVpFZkJQbXYzcDlVTG9XcERKQUJrYVFsSmpfU3hTX19QbEdjbFRaYk41RXJsRVVlTnVDU3Q1RlVDNWg1MWtoX2NkM2Radlp0QkdZdVd6eDVDdTVUSmhLOWxETlVONTVOUEFfRjhxeXhoZl9Rakw5S3VoMzBRNUpoX0JFX1hBRE1JQklpTmRfSkxQam9GeXRXOGFaUF81MmplNjhxdmRIb0xESW1lQXB4cExoc3JRNmVNNERWZHZCenJibFdDeGRTZk9ZTEhHa1Z0QW4xVnhlTGF2UDU0SWpGUEJVZXVrUVVFY242X1hRRFdsV2ZBZzZOMF9STGp6ZkVhUlVyLUo0cFAzZVpSRHk4NmdCTmhRLVJCU2duNjBoOHQ3RCIsImlzc3VlZF9hdCI6MTQwNjg5OTMzMiwidXNlcl9pZCI6IjE0NzM5NzU4MjYxODMyMDQifQ', Gajus\Puss\SignedRequest::SOURCE_INPUT);

        $this->assertSame(1473975826183204, $signed_request->getUserId(), 'User ID.');
        $this->assertNull($signed_request->getAccessToken(), 'Access Token must be null');
        $this->assertSame('AQAC1kA993nExgEMBKHI60Ds-KayH-CzogeUNcEim58L669edGofHvBh7VLmEQZEfBPmv3p9ULoWpDJABkaQlJj_SxS__PlGclTZbN5ErlEUeNuCSt5FUC5h51kh_cd3dZvZtBGYuWzx5Cu5TJhK9lDNUN55NPA_F8qyxhf_QjL9Kuh30Q5Jh_BE_XADMIBIiNd_JLPjoFytW8aZP_52je68qvdHoLDImeApxpLhsrQ6eM4DVdvBzrblWCxdSfOYLHGkVtAn1VxeLavP54IjFPBUeukQUEcn6_XQDWlWfAg6N0_RLjzfEaRUr-J4pP3eZRDy86gBNhQ-RBSgn60h8t7D', $signed_request->getCode(), 'Code.');
    }
}