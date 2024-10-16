<?php

declare(strict_types=1);

use App\Controllers\MainController;
use Slim\Factory\AppFactory;
use Slim\Logger;
use Swoole\Http\Server;
use Chubbyphp\SwooleRequestHandler\OnRequest;
use Chubbyphp\SwooleRequestHandler\PsrRequestFactory;
use Chubbyphp\SwooleRequestHandler\SwooleResponseEmitter;

require_once __DIR__ . "/vendor/autoload.php";

[$file, $targetHost, $targetHostReplace, $replaceHost, $port] = $argv;

/**@var \Psr\Container\ContainerInterface */
$container = (require __DIR__ . "/bootstrap/container.php")($targetHost, $targetHostReplace, $replaceHost);

/**@var callable */
$routes = require __DIR__ . "/routes/api.php";

$app = AppFactory::createFromContainer($container);
$routes($app);

$app->addErrorMiddleware(true, true, true);

$server = new Server('0.0.0.0', (int)$port);
$server->set([
    // The number of worker processes to start, in our case all workers will handle http requests
    'worker_num' => 12,
]);
Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
$server->on('request', new OnRequest(
    $app->getContainer()->get(PsrRequestFactory::class),
    new SwooleResponseEmitter(),
    $container->get(MainController::class)
));

(new Logger())->debug('Starting server on ' . $port);
$server->start();
