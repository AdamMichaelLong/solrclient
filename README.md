solrclient
==========

Classes for the busy PHP developer to work with Apache Solr.

[ ![Codeship Status for opendi/solrclient](https://codeship.com/projects/d48f8a90-4e5a-0132-5b16-220cbe717b80/status)](https://codeship.com/projects/47655)

Construction
------------

First, you must construct a Guzzle HTTP client and set the base_url option to
the Solr endpoint you wish to work with. Then use it to create a Solr Client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/"
]);

$client = new Client($guzzle);
```

It's also possible to pass some default request options, such as headers and
timeouts to the Guzzle client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/",
    'defaults' => [
        'timeout' => 10
    ]
]);

$solr = new Client($guzzle);
```

See [Guzzle documentation](http://docs.guzzlephp.org/) for all options.

There is a helper `factory()` method which does the same as above.

```php
use Opendi\Solr\Client\Client;

$url = "http://localhost:8983/solr/";

$defaults = [
    'timeout' => 10
];

$solr = Client::factory($url, $defaults);
```

Working with cores
------------------

A `core` is solar terminology for a collection of records. To select a core, use
the `core($name)` method on the Solr Client.

```php
$core = $client->core('places');

// Perform a select query
$select = Solr::select()->search('name:Franz');
$client->core('places')->select($select);

// Perform an update query
$update = Solr::update()->body('{}');
$client->core('places')->update($update);
```

The Core object also offers some helper methods:

```php
// Returns core status
$client->core('places')->status();

// Returns number of documents in a core
$client->core('places')->count();

// Deletes all records in the core
$client->core('places')->deleteAll();

// Deletes records matching a selector
$client->core('places')->deleteByQuery('name:Opendi');

// Deletes record with the given ID
$client->core('places')->deleteByID('100');
```
