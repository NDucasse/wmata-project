<?php

namespace App\Service;

class WMATAService
{
    protected APIService $apiService;
    protected string $wmataAPIPath;

    function __construct() {
        $baseURL = $_ENV['BASE_URL'];
        $apiKey = $_ENV['API_KEY'];
        $headers = ['api_key: ' . $apiKey];
        $this->apiService = new APIService($baseURL, $headers);
    }
}