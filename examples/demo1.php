<?php

    use Coco\session\SessionManager;

    require '../vendor/autoload.php';

    $storage = new \Coco\session\storages\Redis();

    SessionManager::InitStorage($storage);
    SessionManager::setExpire(200);
    SessionManager::setBase64Factor('_-.ACDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 'B');

//    $token = SessionManager::generateToken();
    $token = 'KhGwKxS0LRC1LxawKRa2Kh_uLAGuLxez';

    echo $token;
    echo PHP_EOL;

    $container = SessionManager::getSessionContainer('forentend', $token);

    $container->set('a', 'teststsetest');
//    echo $container->get('a');
    echo PHP_EOL;

    print_r($container->getAllData());
    echo PHP_EOL;

    print_r($container->getAnalysis());
    echo PHP_EOL;

//    ($container->disable());

    var_export($container->isSessionAvailable());
    echo PHP_EOL;

    var_export($container->isSessionExpired());
    echo PHP_EOL;


    //    ($container->flushCurrentSession());

    /*

    KhGwKxS0LRC1LxawKRa2Kh_uLAGuLxez
    teststsetest
    Array
    (
        [a] => teststsetest
    )

    Array
    (
        [init_time] => 1712028008
        [is_session_available] => 1
        [last_write_time] => 1712028023
        [last_read_time] => 1712028023
    )

    true
    true
    false


     */