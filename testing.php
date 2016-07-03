<?php
/**
 * Created by PhpStorm.
 * User: dhiraj
 * Date: 26/6/16
 * Time: 7:13 PM
 */

require __DIR__ . '/vendor/autoload.php';

$client = new \GuzzleHttp\Client([
    'base_url' => 'http://symfony_demo',
    'defaults' => [
        'exception' => false
    ]
]);

$response = $client->post('/api/programmers');

echo $response;

echo '\n\n';