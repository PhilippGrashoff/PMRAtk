<?php declare(strict_types=1);

namespace PMRAtk\tests\phpunit\Data\Connector;


use Curl\Curl;
use PMRAtk\Data\Connector\CurlException;
use PMRAtk\Data\Connector\CurlTrait;
use PMRAtk\tests\phpunit\TestCase;

/**
 *
 */
class SomeConnector {

    use CurlTrait;

    public $curl;

    public function __construct() {
        $this->curl = new Curl();
    }
}


/**
 *
 */
class CurlTraitTest extends TestCase {


    /*
     *
     */
    public function testNormalResponse() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $this->assertEquals('LALA', $sc->returnCurlResponse());
    }


    /*
     *
     */
    public function testExceptionOnNetworkError() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $sc->curl->curlError = 'SomeNetworkFail';
        $this->expectException(CurlException::class);
        $sc->returnCurlResponse();
    }


    /*
     *
     */
    public function testExceptionOnErrorResponse() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $sc->curl->error = 'SomeNot200Response';
        $this->expectException(CurlException::class);
        $sc->returnCurlResponse();
    }

    /**
     *
     */
    public function testReturnCurlResponseWithDataSent() {
        $sc = new SomeConnector();
        $sc->curl->response = 'LALA';
        $sc->curl->error = 'SomeNot200Response';
        $data = ['fsdfs' => 'dsfsf'];
        try {
            $sc->returnCurlResponse($data);
        }
        catch (CurlException $e) {
            self::assertArrayHasKey("data sent in body", $e->params);
        }
    }
}
