<?php

namespace App\Service;

use App\Service\APIService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;


class StationListService
{
    protected APIService $apiService;
    protected string $listStationsPath;
    function __construct() {
        $apiKey = getenv('API_KEY');
        $baseURL = getenv('BASE_URL');
        $this->apiService = new APIService($baseURL, $apiKey);
        $this->listStationsPath = getenv('LIST_STATIONS_PATH');
    }
    public function getStationList(): bool|string
    {
        $result = $this->apiService->CallAPI('GET', $this->listStationsPath);
        error_log($result);
        if (!$result)
            return false;
        else
            $station_list = json_decode($result, true);
        return $station_list;
    }
}