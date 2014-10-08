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
namespace Opendi\Solr\Client;

use GuzzleHttp\Client as Guzzle;

use InvalidArgumentException;

class Client
{
    private $guzzle;

    private $cores = [];

    private $pingHandler = 'admin/ping';

    public function __construct(Guzzle $guzzle)
    {
        $this->guzzle = $guzzle;

        // Check a base url has been set
        $base = $this->guzzle->getBaseUrl();
        if (empty($base)) {
            throw new SolrException("You need to set a base_url on Guzzle client.");
        }
    }

    /**
     * Returns a Core object for the given core name.
     *
     * @param  String $name
     * @return Core
     */
    public function core($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidArgumentException("Invalid core name.");
        }

        if (!isset($this->cores[$name])) {
            $this->cores[$name] = new Core($this, $name);
        }

        return $this->cores[$name];
    }

    /**
     * Pings the server to check it's there.
     * @return array Solr's reply.
     * @throws SolrException If server does not respond.
     */
    public function ping()
    {
        $query = $this->pingHandler . '?wt=json';

        $response = $this->guzzle->get($query);

        return $response->json();
    }

    /**
     * Sets the path to the Solr ping handler.
     *
     * Use a relative path, such as 'admin/ping', and not absolute like
     * '/admin/ping', otherwise it won't work when solr base is not same as root
     * url.
     *
     * @param string $handler
     * @see https://cwiki.apache.org/confluence/display/solr/Ping
     */
    public function setPingHandler($handler)
    {
        $this->pingHandler = $handler;
    }

    /**
     * Returns the underlying Guzzle client's event emitter.
     *
     * @return GuzzleHttp\Event\EmitterInterface
     *
     * @see  http://guzzle.readthedocs.org/en/latest/events.html
     */
    public function getEmitter()
    {
        return $this->guzzle->getEmitter();
    }

    /**
     * Returns the underlying Guzzle client.
     *
     * @return GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzle;
    }

    /**
     * Performs a GET request.
     *
     * @param  string $path
     * @param  array  $query
     *
     * @return GuzzleHttp\Message\Response
     */
    public function get($path, array $query = [])
    {
        // Exectue the GET request
        try {
            $response = $this->guzzle->get($path, [
                'query' => $query
            ]);
        } catch (RequestException $ex) {
            $this->handleRequestException($ex);
        }

        // Decode and return data
        return $response;
    }

    /**
     * Performs a POST request.
     *
     * @param  string $path
     * @param  array  $query
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return GuzzleHttp\Message\Response
     */
    public function post($path, array $query = [], $body = null, array $headers = [])
    {
        $options = [
            'query' => $query,
            'headers' => $headers,
        ];

        if (isset($body)) {
            $options['body'] = $body;
        }

        // Exectue the POST request
        try {
            $response = $this->guzzle->post($path, $options);
        } catch (RequestException $ex) {
            $this->handleRequestException($ex);
        }

        return $response;
    }

    private function handleRequestException(RequestException $ex)
    {
        if ($ex->hasResponse()) {
            $response = $ex->getResponse();

            $reason = $response->getReasonPhrase();
            $code = $response->getStatusCode();
            $data = $response->json();

            $msg = $data['error']['msg'];

            throw new \Exception("Solr error HTTP $code $reason:  $msg", 0, $ex);
        }

        throw new \Exception("Solr query failed", 0, $ex);
    }
}
