<?php

use Silex\Application;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Silex\Provider\TwigServiceProvider;

use GuzzleHttp\Client;

$app = new Application();

$app['logger'] = new Logger('log');
$app['logger']->pushHandler(new ErrorLogHandler());

$app->register(new TwigServiceProvider(), [
    'twig.path' => __DIR__.'/views',
]);

/**
 * show a list of repositories
 */
$app->get('/', function(Request $request) use ($app){

    // get the host from config
    $client = new Client([
        'base_uri' => 'http://repoman'
    ]);

    $response = $client->request('GET', '/repositories');

    $data = json_decode($response->getBody(), true);

    return $app['twig']->render('index.html', [
        'repositories' => $data,
    ]);
});

/**
 * add a repository
 * validate here or on repo-man service?
 */
$app->post('/', function(Request $request) use ($app){

    // get the host from config
    $client = new Client([
        'base_uri' => 'http://repoman'
    ]);

    $client->request('POST', '/repositories',
        ['form_params' => [
            'url' => $request->get('repository')
        ]]);

    // redirect to GET /
    return $app->redirect('/');
});

/**
 */
$app->error(function (Exception $e, $code) use($app) {
    $app['logger']->addError($e->getMessage());
    return new Response($e->getMessage());
});

return $app;