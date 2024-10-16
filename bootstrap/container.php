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
use Slim\Logger;

return function (string $targetHost, string $targetHostReplace, string $replaceHost): Container {
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
        MainController::class => function (ContainerInterface $container) use ($targetHost, $targetHostReplace, $replaceHost): MainController {
            return new MainController(
                $container->get(ResponseFactoryInterface::class),
                $container->get(StreamFactoryInterface::class),
                new Logger(),
                $targetHost,
                $targetHostReplace,
                $replaceHost
            );
        }
    ]);

    return $containerBuilder->build();
};
