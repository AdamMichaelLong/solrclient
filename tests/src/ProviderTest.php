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

use Opendi\Solr\Client\Client;
use Opendi\Solr\Client\Providers\SolrClientServiceProvider;

class ProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $url = 'http://localhost:8983/solr/';

        $container = new \Pimple\Container();

        $provider = new SolrClientServiceProvider([
            'base_url' => $url
        ]);

        $container->register($provider);

        $client = $container['solr'];

        $this->assertInstanceOf(Client::class, $client);
        $this->assertSame($url, $client->getGuzzleClient()->getBaseUrl());
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must give a base_url for the solr provider
     */
    public function testNoBaseUrl()
    {
        new SolrClientServiceProvider([]);
    }
}
