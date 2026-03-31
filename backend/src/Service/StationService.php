<?php

namespace App\Service;

use App\Service\APIService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

class StationService
{
    protected APIService $apiService;
    protected string $listStationsPath;
    protected array $stations = [];

    function __construct() {
        $apiKey = $_ENV['API_KEY'];
        $baseURL = $_ENV['BASE_URL'];
        $headers = [
            'api_key: ' . $apiKey,
        ];
        $this->apiService = new APIService($baseURL, $headers);
        $this->listStationsPath = $_ENV['LIST_STATIONS_PATH'];
        $this->initStationList();
    }

    private function initStationList(): void {
        $result = $this->apiService->CallAPI('GET', $this->listStationsPath);
        if (!$result) {
            return;
        }
        $decodedStationsList = json_decode($result, true);
        if ($decodedStationsList === null) {
            return;
        }
        $this->stations = $decodedStationsList["Stations"];
    }

    public function getStationList(): bool|string
    {
        $stationList = [];
        foreach ($this->stations as $station) {
            $stationList[] = $station['Name'];
        }
        sort($stationList);
        return json_encode($stationList);
    }
}