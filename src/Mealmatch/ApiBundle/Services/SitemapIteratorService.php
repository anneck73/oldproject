<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace Mealmatch\ApiBundle\Services;

use Exporter\Source\SourceIteratorInterface;

class SitemapIteratorService implements SourceIteratorInterface
{
    protected $key;

    protected $stop;

    protected $current;

    public function __construct($stop = 1000)
    {
        $this->stop = $stop;
    }

    public function current()
    {
        return $this->current;
    }

    public function next()
    {
        ++$this->key;
        $this->current = array(
            'permalink' => '/the/path/to/target',
            'lastmod' => '',
            'changefreq' => 'weekly',
            'priority' => 0.5,
        );
    }

    public function key()
    {
        return $this->key;
    }

    public function valid()
    {
        return $this->key < $this->stop;
    }

    public function rewind()
    {
        $this->key = 0;
    }
}
