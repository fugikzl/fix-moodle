<?php

declare(strict_types=1);

namespace App\Controllers;

use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MainController extends BaseController implements RequestHandlerInterface
{
    public function __construct(
        private readonly ResponseFactoryInterface $responseFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $targetHost,
        private readonly string $targetHostReplace,
        private readonly string $replaceHost,
    ) {
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Request $request): Response
    {
        $uri = $request->getServerParams()['REQUEST_URI'];
        $url = (empty($uri) || $uri === '/')
            ? 'https://' . $this->targetHost
            : $this->targetHost . '/' . $uri
        ;

        $headers = array_merge($request->getHeaders(), [
            'Host' => [$this->targetHostReplace],
        ]);

        echo "$url\n";

        $clientResponse = (new GuzzleHttpClient([
            'verify' => false,
            'http_errors' => false
        ]))->request($request->getMethod(), $url, [
            'query' => $request->getQueryParams(),
            'body' => $request->getBody()->getContents(),
            'headers' => $headers
        ]);

        echo "Received response\n";

        $response = $this->responseFactory->createResponse(
            $clientResponse->getStatusCode(),
            $clientResponse->getReasonPhrase()
        );

        /** @var array<string, array<int, string>> */
        $responseHeaders = $clientResponse->getHeaders();
        foreach ($responseHeaders as $header => $values) {
            foreach ($values as $value) {
                $response = $response->withAddedHeader($header, $value);
            }
        }

        return $response->withBody(
            $this->streamFactory->createStream($this->replaceHost((string) $clientResponse->getBody()))
        );
    }

    private function replaceHost(string $body): string
    {
        return str_replace($this->targetHostReplace, $this->replaceHost, $body);
    }
}
