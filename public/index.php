<?php

declare(strict_types=1);

use App\LegacyBridge\LegacyBridge;
use App\LegacyBridge\RequestOwnershipResolver;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

if (PHP_SAPI === 'cli-server') {
    $documentRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') : null;
    $frontController = $documentRoot !== null ? str_replace('\\', '/', __FILE__) : null;
    $normalizedDocumentRoot = $documentRoot !== null ? str_replace('\\', '/', $documentRoot) : null;

    if ($frontController !== null && $normalizedDocumentRoot !== null && str_starts_with($frontController, $normalizedDocumentRoot . '/')) {
        $frontControllerPath = substr($frontController, strlen($normalizedDocumentRoot));
        $_SERVER['SCRIPT_NAME'] = $frontControllerPath;
        $_SERVER['PHP_SELF'] = $frontControllerPath;
    }
}

/** @var \Illuminate\Container\Container $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$request = Request::capture();
$app->instance('request', $request);
$app->instance(Request::class, $request);

/** @var RequestOwnershipResolver $ownershipResolver */
$ownershipResolver = $app->make(RequestOwnershipResolver::class);
/** @var Router $router */
$router = $app->make(Router::class);
/** @var LegacyBridge $legacyBridge */
$legacyBridge = $app->make(LegacyBridge::class);

try {
    if ($ownershipResolver->isLaravelOwned($request)) {
        try {
            $response = $router->dispatch($request);
        } catch (MethodNotAllowedHttpException $exception) {
            $allowHeader = $exception->getHeaders()['Allow'] ?? '';
            if (is_array($allowHeader)) {
                $allowHeader = implode(', ', $allowHeader);
            }

            $response = new Response($exception->getMessage(), Response::HTTP_METHOD_NOT_ALLOWED, [
                'Allow' => (string) $allowHeader,
                'Content-Type' => 'text/plain; charset=UTF-8',
            ]);
        }

        $response->send();
        exit(0);
    }

    $resolution = $legacyBridge->resolve($request);

    if ($resolution->isDirectAsset()) {
        $legacyBridge->createAssetResponse($resolution)->send();
        exit(0);
    }

    $execution = $legacyBridge->prepare($request, $resolution);
    $GLOBALS['__legacy_bridge_execution'] = $execution;
    $GLOBALS['__legacy_bridge'] = $legacyBridge;

    $response = require dirname(__DIR__) . '/bootstrap/legacy_dispatch.php';
    if ($response instanceof Response) {
        $response->send();
    }
} catch (\Throwable $throwable) {
    $legacyBridge->renderBridgeFailure($request, $throwable, $resolution ?? null)->send();
}
