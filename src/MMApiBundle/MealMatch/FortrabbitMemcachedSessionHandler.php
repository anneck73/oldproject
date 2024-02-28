<?php

/*
 * Copyright (c) 2016-2018. Mealmatch GmbH
 * (c) André Anneck <andre.anneck@mealmatch.de>
 * Mealmatch WebApp v0.2
 */

namespace MMApiBundle\MealMatch;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\MemcachedSessionHandler;

/**
 * The class FortrabbitMemcachedSessionHandler is a simple Wrapper or Memcached used at fortrabbit.
 * Use this service configuration:.
 *
 * session.memcached:
 *   class: Memcached
 *
 * session.handler.memcached:
 *   class: MMApiBundle\MealMatch\FortrabbitMemcachedSessionHandler
 *   arguments: ["@session.memcached", { prefix: "%session_memcache_prefix%", expiretime: "%session_memcache_expire%" }]
 *   calls:
 *       - [ addServer, [ "%session_memcache_host_1%", "%session_memcache_port_1%" ]]
 *
 * Motivation was ... to much confusion about "Memcache(d)" configs ... this makes it clear, and it seems to work
 * on fortrabbit like a charm.
 */
class FortrabbitMemcachedSessionHandler extends MemcachedSessionHandler
{
    public function addServer(string $pHost, string $pPort)
    {
        $this->getMemcached()->addServer($pHost, $pPort, 15);
    }
}
