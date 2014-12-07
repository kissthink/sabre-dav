<?php

namespace Sabre\DAV\Auth\Backend;

use Sabre\DAV;
use Sabre\HTTP;

class AbstractDigestTest extends \PHPUnit_Framework_TestCase {

    function testCheckNoHeaders() {

        $request = new HTTP\Request();
        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

    }

    function testCheckBadGetUserInfoResponse() {

        $header = 'username=null, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);
        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

        $backend = new AbstractDigestMock();
        $backend->check($request, $response);

    }

    /**
     * @expectedException Sabre\DAV\Exception
     */
    function testCheckBadGetUserInfoResponse2() {

        $header = 'username=array, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

        $backend = new AbstractDigestMock();
        $backend->check($request, $response);

    }

    function testCheckUnknownUser() {

        $header = 'username=false, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

    }

    function testCheckBadPassword() {

        $header = 'username=user, realm=myRealm, nonce=12345, uri=/, response=HASH, opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray([
            'PHP_AUTH_DIGEST' => $header,
            'REQUEST_METHOD'  => 'PUT',
        ]);

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertNull(
            $backend->check($request, $response)
        );

    }

    function testCheck() {

        $digestHash = md5('HELLO:12345:1:1:auth:' . md5('GET:/'));
        $header = 'username=user, realm=myRealm, nonce=12345, uri=/, response='.$digestHash.', opaque=1, qop=auth, nc=1, cnonce=1';
        $request = HTTP\Sapi::createFromServerArray(array(
            'REQUEST_METHOD'  => 'GET',
            'PHP_AUTH_DIGEST' => $header,
            'REQUEST_URI'     => '/',
        ));

        $response = new HTTP\Response();

        $backend = new AbstractDigestMock();
        $this->assertEquals(
            'principals/user',
            $backend->check($request, $response)
        );

    }

}


class AbstractDigestMock extends AbstractDigest {

    function getDigestHash($realm, $userName) {

        switch($userName) {
            case 'null' : return null;
            case 'false' : return false;
            case 'array' : return array();
            case 'user'  : return 'HELLO';
        }

    }

}
