<?php

class ApiCest 
{
    private $accessKey;

    public function _before(\ApiTester $I)
    {
        $this->accessKey = Flight::config()['accessKey'];
    }
    public function tryToAccessWithoutAccessKey(ApiTester $I)
    {
        $I->sendGET('/');
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function tryToAccessNonExistingPath(ApiTester $I)
    {
        $I->haveHttpHeader('X-Key', $this->accessKey);
        $I->sendGET('/xxx');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
    }

    public function tryToAccessConversionWithWrongMethod(ApiTester $I)
    {
        $I->haveHttpHeader('X-Key', $this->accessKey);
        $I->sendGET('/conversion');
        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
    }

    public function tryToPostEmptyValues(ApiTester $I)
    {
        $I->haveHttpHeader('X-Key', $this->accessKey);
        $I->sendPOST('/conversion', []);
        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson([
            "Key appId must be present",
            "Key goalId must be present",
            "No items were found for key chain data.headers",
            "No items were found for key chain data.rows"
        ]);
    }

    public function tryToPostValidValues(ApiTester $I)
    {
        $data = [
            'appId'              => '1',
            'goalId'             => '1',
            'data'               =>
                [
                    'headers' =>
                        [
                            'field1',
                            'field2',
                            'field3',
                        ],
                    'rows'    =>
                        [
                            [
                                'data11',
                                'data12',
                                'data13',
                            ],

                            [
                                'data21',
                                'data22',
                                'data23',
                            ],
                        ],
                ],
        ];
        $I->haveHttpHeader('X-Key', $this->accessKey);
        $I->sendPOST('/conversion', $data);
        $I->seeResponseCodeIs(201);
        $I->seeResponseIsJson();
    }
}