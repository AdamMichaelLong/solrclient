Opendi Solr Client Changelog
============================

0.3.0 (2014-06-23)
------------------

#### BC breaks

* Renamed classes, removed the "Solr" prefix, so `SolrClient` becomes `Client`,
  etc.
* Separated `Connection` class into `Core` and `Client`. Methods `select()` and
  `update()` methods have been moved to `Core` class. To excecute a select, run
  `$client->core('<core>')->select($select)` where '<core>' is the name of the
  core on which you want to run the query.

#### Features

* Added `Client::coreStatus()`
* Added `Client::getEmitter()`
* Added `Client::ping()`
* Added `Solr` factory class for easier chaining.

0.2.1 (2014-06-11)
------------------

* Reworked facet support, added new options such as pivot
* facet.field is no longer mandatory
* Removed __toString magic methods from SolrSelect and SolrFacet