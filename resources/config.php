<?php

Flight::map('config', function () {
    return [
        'accessKey' => 'some-accesskey',
        'jenkins' => [
            'url' => 'jenkins-url',
            'credentials' => [
                'username' => 'jenkins-username',
                'password' => 'jenkins-password',
            ],
            'headers' => [
                'Jenkins-Crumb' => 'jenkins-crumb',
            ],
        ],
    ];
});

Flight::register('Logger', \Monolog\Logger::class, ['appLog'], function (\Monolog\Logger $logger) {
    $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ .'/../log/app.log'));
});
