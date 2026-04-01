<?php

namespace App\Service;

class StationService
{
    protected APIService $apiService;
    protected string $listStationsPath;
    protected string $nextArrivalsPath;
    protected array $stationNamesList = [];
    protected array $stationNameToCodes = [];

    function __construct() {
        $this->listStationsPath = $_ENV['LIST_STATIONS_PATH'];
        $this->nextArrivalsPath = $_ENV['NEXT_ARRIVALS_PATH'];

        $apiKey = $_ENV['API_KEY'];
        $baseURL = $_ENV['BASE_URL'];

        $headers = ['api_key: ' . $apiKey];

        $this->apiService = new APIService($baseURL, $headers);

        $this->initStationsList();
    }

    // Called only during object construction
    // Retrieves and stores list of stations from WMATA API
    private function initStationsList(): void {
        $result = $this->apiService->CallAPI('GET', $this->listStationsPath);
        if (!$result) {
            return;
        }
        $decodedStationsList = json_decode($result, true);
        if ($decodedStationsList === null) {
            return;
        }
        $this->formatStationData($decodedStationsList['Stations']);
        $this->formatStationAccessData($decodedStationsList['Stations']);
    }

    private function formatStationData(array $stations): void {
        $stationList = [];
        foreach ($stations as $station) {
            $stationName = $station['Name'];

            // Push to list of station names.
            // No duplicates (same station different platforms listed
            // as separate stations w same name in raw data)
            if (!in_array($stationName, $stationList)) {
                $stationList[] = $stationName;
            }
        }
        // Station names should be alphabetical for easy user lookup
        sort($stationList);
        $this->stationNamesList = $stationList;
    }

    private function formatStationAccessData(array $stations): void
    {
        foreach ($stations as $station) {
            $stationName = $station['Name'];
            $stationCode = $station['Code'];

            // Push to the association array to translate station name into list of assoc. station codes
            if (!array_key_exists($stationName, $this->stationNameToCodes)) {
                $this->stationNameToCodes[$stationName] = [$stationCode];
            } else if (!in_array($stationCode, $this->stationNameToCodes[$stationName])) {
                $this->stationNameToCodes[$stationName][] = $stationCode;
            }
        }
    }

    private function getNextArrivalsForStationCode(array $stationCodes): array {
        $stationCodesString = implode(',', $stationCodes);
        $fullPath = $this->nextArrivalsPath. $stationCodesString;

        $result = $this->apiService->CallAPI('GET', $fullPath);
        if (!$result) {
            return [];
        }

        $decodedArrivals = json_decode($result, true);
        $nextTrains = [];
        foreach ($decodedArrivals['Trains'] as $arrival) {
           $nextTrain = [
               'line' => $arrival['Line'],
               'destination' => $arrival['Destination'],
               'minToArrival' => $arrival['Min'],
               'cars' => $arrival['Car'],
           ];

           $nextTrains[] = $nextTrain;
        }

        return $nextTrains;
    }

    // Returns array of station names as json string
    public function getStationList(): bool|string
    {
        return json_encode($this->stationNamesList);
    }

    // Returns the next train arrivals for the station (all platforms)
    public function getNextArrivalsByStationName(string $stationName): bool|string {
        $codes = $this->stationNameToCodes[$stationName];
        $nextArrivals = $this->getNextArrivalsForStationCode($codes);
        return json_encode($nextArrivals);
    }
}
