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

/**
 * Helper factory class for instantiating new Solr components.
 *
 * Allows easy command chaining and a readable interface.
 *
 * Examples:
 * ```
 * $select = Solr::select()->field('name')->rows(10);
 * $facet = Solr::facet()->pivot('foo', 'bar')->prefix('x');
 * ```
 */
class Solr
{
    public static function facet()
    {
        return new Facet();
    }

    public static function filter()
    {
        return new Filter();
    }

    public static function select()
    {
        return new Select();
    }

    public static function update()
    {
        return new Update();
    }

    public static function group()
    {
        return new Group();
    }
}
