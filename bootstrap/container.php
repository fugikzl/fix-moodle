<?php

declare(strict_types=1);

use App\Controllers\MainController;
use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\{
    ResponseFactoryInterface,
    UploadedFileFactoryInterface,
    StreamFactoryInterface,
    ServerRequestFactoryInterface
};
use Slim\Psr7\Factory\{
    ResponseFactory,
    ServerRequestFactory,
    StreamFactory,
    UploadedFileFactory
};

return (function (): Container {
    $containerBuilder = new ContainerBuilder();

    $responseFactory = new ResponseFactory();
    $serverRequestFactory = new ServerRequestFactory();
    $streamFactory = new StreamFactory();
    $uploadedFileFactory = new UploadedFileFactory();

    $containerBuilder->addDefinitions([
        ResponseFactoryInterface::class => $responseFactory,
        ServerRequestFactoryInterface::class => $serverRequestFactory,
        StreamFactoryInterface::class => $streamFactory,
        UploadedFileFactoryInterface::class => $uploadedFileFactory,
        MainController::class => function (ContainerInterface $container): MainController {
            return new MainController(
                $container->get(ResponseFactoryInterface::class),
                $container->get(StreamFactoryInterface::class),
                '85.159.27.128',
                'moodle.astanait.edu.kz',
                '127.0.0.1:8000'
            );
        }
    ]);

    return $containerBuilder->build();
})();
