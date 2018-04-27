<?php

use App\Controller\Conversion;

Flight::before('start', function (&$params, &$output) {
    $config = Flight::config();
    $accessKey = $_SERVER['HTTP_X_KEY'] ?? null;
    if ($accessKey !== $config['accessKey']) {
        \Flight::json(['error' => 'Access Denied, Please set a valid token in X-Key header'], 403);
        exit;
    }
});

Flight::after('start', function (&$params, &$output) {
    $request = \Flight::request();
    \Flight::Logger()->debug(\json_encode([
        $request->method,
        $request->url,
        [$_SERVER['HTTP_X_KEY'] ?? null, $_SERVER['REMOTE_ADDR'] ?? null],
        file_get_contents("php://input"),
    ]));
});

Flight::map('notFound', function () {
    \Flight::json(['error' => 'Not Found, The route does not exists'], 404);
    exit;
});

Flight::route('POST /api/conversion', [new Conversion(), 'postAction']);