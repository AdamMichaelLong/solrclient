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

use Opendi\Solr\Client\Parsers\ParserInterface;

/**
 * http://stackoverflow.com/questions/8089947/solr-and-query-over-multiple-fields
 * https://wiki.apache.org/solr/MoreLikeThis
 * Facets
 *
 * TODO http://localhost:8983/solr/entries/select?q=categories%3AFriseur*&wt=json&indent=true&fq={!geofilt%20pt=48.166535,11.5178152%20sfield=location%20d=5}&sfield=location&pt=48.166535,11.5178152&sort=geodist()%20asc
 *
 * Class Select
 * @package opendi\solrclient
 */
class Select extends Expression
{
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_RUBY = 'ruby';
    const FORMAT_PHP = 'php';
    const FORMAT_CSV = 'csv';

    private $filters = [];
    private $components = [];

    private $andExpressions = [];
    private $orExpressions = [];

    private $queryFields = [];

    /** @var Facet */
    private $facet = null;

    /** @var Group */
    private $group = null;

    private $indent = false;
    private $format = null;

    private $start = null;
    private $rows = null;

    private $debug = false;

    private $raw = null;

    /** @var ParserInterface */
    private $parser = null;

    public function andExpression(Expression $expression)
    {
        $this->andExpressions[] = $expression;

        return $this;
    }

    public function orExpression(Expression $expression)
    {
        $this->orExpressions[] = $expression;

        return $this;
    }

    public function queryField($fieldName)
    {
        $this->queryFields[] = $fieldName;

        return $this;
    }

    public function indent()
    {
        $this->indent = true;

        return $this;
    }

    public function debug()
    {
        $this->debug = true;

        return $this;
    }

    public function rows($max)
    {
        $this->rows = $max;

        return $this;
    }

    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function filter(Filter $filter)
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function facet(Facet $facet)
    {
        $this->facet = $facet;

        return $this;
    }

    public function group(Group $group)
    {
        $this->group = $group;

        return $this;
    }

    public function addComponents($component)
    {
        $this->components[] = $component;

        return $this;
    }

    public function raw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    public function start($start = 0)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Use an alternate parser to the lucene one, like dismax
     *
     * @param ParserInterface $parser
     * @return $this
     */
    public function parser(ParserInterface $parser)
    {
        $this->parser = $parser;

        return $this;
    }

    public function render()
    {
        $query = parent::render();

        if (!empty($this->andExpressions)) {
            /** @var Expression $expression */
            foreach ($this->andExpressions as $expression) {
                if (!empty($query)) {
                    $query .= ' AND ';
                }
                $query .= '(' . $expression->render() . ')';
            }
        }

        if (!empty($this->orExpressions)) {
            /** @var Expression $expression */
            foreach ($this->orExpressions as $expression) {
                if (!empty($query)) {
                    $query .= ' OR ';
                }
                $query .= '(' . $expression->render() . ')';
            }
        }

        $query = "q=" . urlencode($query);

        if (!empty($this->queryFields)) {
            $query .= '&qf=' . implode('+', $this->queryFields);
        }

        if ($this->parser != null) {
            $query .= '&' . $this->parser->render();
        }

        if ($this->format != null) {
            $query .= '&wt=' . $this->format;
        }

        if ($this->indent) {
            $query .= '&indent=true';
        }

        if ($this->debug) {
            $query .= '&debug=true';
        }

        if (is_numeric($this->rows)) {
            $query .= '&rows='.$this->rows;
        }

        if (is_numeric($this->start)) {
            $query .= '&start='.$this->start;
        }

        foreach ($this->filters as $filter) {
            $query .= '&' . $filter->render();
        }

        if ($this->facet != null) {
            $query .= '&'. $this->facet->render();
        }

        if ($this->group != null) {
            $query .= '&'. $this->group->render();
        }

        if ($this->raw != null) {
            $query .= $this->raw;
        }

        return $query;
    }
}
