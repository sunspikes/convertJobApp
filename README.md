# Convert job app

API to push a app data conversion job to via Jenkins API built with flightphp microframework.

Endpoint: `POST /api/conversion`

Required Header: `X-Key` access key from config to access the API

Input JSON example:

```
{
  "appId": "1",
  "goalId": "1",
  "defaultCountryCode": "DE",
  "defaultTimeOffset": "1",
  "defaultRegionId": "1",
  "data": {
    "headers": [
      "field1",
      "field2",
      "field3"
    ],
    "rows": [
      [
        "data11",
        "data12",
        "data13"
      ],
      [
        "data21",
        "data22",
        "data23"
      ]
    ]
  }
}
```
