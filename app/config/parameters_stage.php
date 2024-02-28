<?php
/**
 * Copyright 2016 Andre Anneck <andreanneck73@gmail.com>
 * Created: 22.11.16 11:03
 */
// on fortrabbit: construct credentials from App secrets
$secrets = json_decode(file_get_contents($_SERVER['APP_SECRETS']), true);
if (!$secrets) {
    return;
}
$container->setParameter('database_driver', 'pdo_mysql');
$container->setParameter('database_host', $secrets['MYSQL']['HOST']);
$container->setParameter('database_name', $secrets['MYSQL']['DATABASE']);
$container->setParameter('database_user', $secrets['MYSQL']['USER']);
$container->setParameter('database_password', $secrets['MYSQL']['PASSWORD']);
$container->setParameter('database_port', $secrets['MYSQL']['PORT']);

// check if Object-Storage is present ...
if (isset($secrets['OBJECT_STORAGE'])) {
    $container->setParameter('aws3_bucket', $secrets['OBJECT_STORAGE']['BUCKET']);
    $container->setParameter('aws3_host', $secrets['OBJECT_STORAGE']['HOST']);
    $container->setParameter('aws3_key', $secrets['OBJECT_STORAGE']['KEY']);
    $container->setParameter('aws3_region', $secrets['OBJECT_STORAGE']['REGION']);
    $container->setParameter('aws3_secret', $secrets['OBJECT_STORAGE']['SECRET']);
    $container->setParameter('aws3_server', $secrets['OBJECT_STORAGE']['SERVER']);
}

// check if the Memcache component is present
if (isset($secrets['MEMCACHE'])) {
    $memcache = $secrets['MEMCACHE'];
    $handlers = array();

    foreach (range(1, $memcache['COUNT']) as $num) {
        $handlers[] = $memcache['HOST'.$num].':'.$memcache['PORT'.$num];
    }

    $container->setParameter('session_memcache_host_1', $secrets['MEMCACHE']['HOST1']);
    $container->setParameter('session_memcache_port_1', $secrets['MEMCACHE']['PORT1']);

    // ON STAGE|LIVE fortrabbit
    ini_set('session.save_handler', 'memcached');
    ini_set('session.save_path', implode(',', $handlers));

    if ("2" === $memcache['COUNT']) {
        ini_set('memcached.sess_number_of_replicas', 1);
        ini_set('memcached.sess_consistent_hash', 1);
        ini_set('memcached.sess_binary', 1);
        $container->setParameter('session_memcache_host_2', $secrets['MEMCACHE']['HOST2']);
        $container->setParameter('session_memcache_port_2', $secrets['MEMCACHE']['PORT2']);

    }
}

// check if CUSTOM settings are set ...
if (isset($secrets['CUSTOM'])) {
    $mm = $secrets['CUSTOM'];
    /*
        if(isset($secrets['CUSTOM']['PAYPAL'])) {
            $container->setParameter('paypal_appid', $secrets['CUSTOM']['PAYPAL_APPID']);
            $container->setParameter('paypal_username', $secrets['CUSTOM']['PAYPAL_USERNAME']);
            $container->setParameter('paypal_password', $secrets['CUSTOM']['PAYPAL_PASSWORD']);
            $container->setParameter('paypal_signature', $secrets['CUSTOM']['PAYPAL_SIGNATURE']);
            $container->setParameter('paypal_email', $secrets['CUSTOM']['PAYPAL_EMAIL']);
        }
    */
    /*
        $container->setParameter('mailer_transport', $mm['MAILER_TRANSPORT']);
        $container->setParameter('mailer_host', $mm['MAILER_HOST']);
        $container->setParameter('mailer_user', $mm['MAILER_USER']);
        $container->setParameter('mailer_password', $mm['MAILER_PASSWORD']);
    */
}
