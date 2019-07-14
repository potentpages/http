<?php
/**
 * Created by PhpStorm.
 * User: Jack Dox
 * Date: 14.07.2019
 * Time: 17:16
 */

namespace PotentHTTP\Tests;

use PHPUnit\Framework\TestCase;
use PotentHTTP\Client;

class ClientTest extends TestCase
{
    public function testClientPageDownload()
    {
        $artifactsDir = realpath(__DIR__.'/../artifacts/');

        if (file_exists($artifactsDir.'/example.html')){
            unlink($artifactsDir.'/example.html');
        }

        $this->assertFileNotExists($artifactsDir.'/example.html');

        $httpObj = new Client();
        $httpObj->add_get("https://potentpages.com/",
            "",
            null,
            $artifactsDir.'/cookies.txt',
            false,
            null,
            null,
            10,
            15,
            $artifactsDir.'/example.html',
            null);

        $response = $httpObj->run();
        $httpObj = null;

        print_r($response);

        $this->assertTrue($response[0]['success']);
    }
}
