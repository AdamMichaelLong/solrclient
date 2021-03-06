<?php
/*
 *  Copyright 2014 Opendi Software AG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Opendi\Solr\Client\Tests;

use Mockery as m;

use Opendi\Solr\Client\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testFactory()
    {
        $url = "www.google.com";
        $timeout = 666;
        $defaults = [
            'timeout' => $timeout
        ];

        $client = Client::factory($url, $defaults);

        $guzzle = $client->getGuzzleClient();

        $this->assertSame($url, $guzzle->getBaseURL());
        $this->assertSame($timeout, $guzzle->getDefaultOption('timeout'));
    }

    /**
     * @expectedException Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage You need to set a base_url on Guzzle client.
     */
    public function testFailureNoBasUrl()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn(null);

        $select = new Client($guzzle);
    }


    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid core name
     */
    public function testInvalidCore()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $client = new Client($guzzle);
        $client->core([]);
    }

    public function testGetEmitter()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');

        $expected = new \stdClass();

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('getEmitter')
            ->once()
            ->andReturn($expected);

        $client = new Client($guzzle);
        $actual = $client->getEmitter();

        $this->assertSame($expected, $actual);
    }
}
