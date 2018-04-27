<?php

namespace App\Controller;

use flight\net\Request;
use GuzzleHttp\Client;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator;

class Conversion
{
    /**
     * @route POST /api/conversion
     */
    public function postAction()
    {
        $request = \Flight::request();

        if ($errors = $this->validate($request)) {
            return \Flight::json($errors, 403);
        }

        $csvFile = $this->createTsvFile(
            $request->data['data']['headers'],
            $request->data['data']['rows']
        );

        if (file_exists($csvFile)) {
            try {
                $statusCode = $this->triggerJenkinsJob($request, $csvFile);
            } catch (\Exception $e) {
                return \Flight::json(['error' => 'Cannot create the conversion job, '. $e->getMessage()], 500);
            }

            return \Flight::json(['success' => 'Job request sent to jenkins'], $statusCode);
        }

        return \Flight::json(['error' => 'Internal error'], 500);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function validate(Request $request)
    {
        $errors = [];

        $validator = Validator::key('appId', Validator::stringType()->length(1, 255))
            ->key('goalId', Validator::stringType()->length(1, 255))
            ->keyNested('data.headers', Validator::arrayType()->length(1))
            ->keyNested('data.rows', Validator::arrayType()->length(1));

        try {
            $validator->assert($request->data->getData());
        } catch (NestedValidationException $e) {
            $errors = $e->getMessages();
        }

        return $errors;
    }

    /**
     * @param array $headers
     * @param array $rows
     * @return bool|string
     */
    private function createTsvFile(array $headers, array $rows)
    {
        $delimiter = "\t";
        $csv = tempnam(sys_get_temp_dir(), 'csv');
        $file = fopen($csv, 'w+');

        fputcsv($file, $headers, $delimiter);

        foreach ($rows as $row) {
            fputcsv($file, $row, $delimiter);
        }

        fclose($file);

        return $csv;
    }

    /**
     * @param Request $request
     * @param string  $file
     * @return int
     */
    private function triggerJenkinsJob(Request $request, string $file)
    {
        $config = \Flight::config()['jenkins'];
        $guzzle = new Client();

        $response = $guzzle->post($config['url'], [
            'auth' => array_values($config['credentials']),
            'headers' => $config['headers'],
            'multipart' => [
                [
                    'name' => 'appId',
                    'contents' => $request->data['appId'],
                ],
                [
                    'name' => 'goalId',
                    'contents' => $request->data['goalId'],
                ],
                [
                    'name' => 'defaultCountryCode',
                    'contents' => $request->data['defaultCountryCode'],
                ],
                [
                    'name' => 'defaultTimeOffset',
                    'contents' => $request->data['defaultTimeOffset'],
                ],
                [
                    'name' => 'defaultRegionId',
                    'contents' => $request->data['defaultRegionId'],
                ],
                [
                    'name' => 'conversions_file',
                    'contents' => fopen($file, 'r+'),
                ],
            ]
        ]);

        return $response->getStatusCode();
    }
}