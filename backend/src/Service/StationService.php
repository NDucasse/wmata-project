<?php

namespace App\Service;

use Exception;

class StationService extends WMATAService
{
    protected array $stationCodesByName = [];
    protected array $stationNamesDistinctList = [];

    /**
     * @throws Exception
     */
    function __construct() {
        parent::__construct();
        $this->wmataAPIPath = $_ENV['LIST_STATIONS_PATH'];
        try {
            $this->initStationsList();
        } catch (Exception $e) {
            $constructorException = new Exception("Error during StationService construction: {$e->getMessage()}");
            error_log($constructorException->getMessage());
            throw $constructorException;
        }
    }

    // Called only during object construction
    // Retrieves and stores list of stations from WMATA API
    /**
     * @throws Exception
     */
    private function initStationsList(): void {
        try {
            $result = $this->apiService->CallAPI('GET', $this->wmataAPIPath);
            if (!$result) {
                throw new Exception('Bad WMATA API Request: No Data');
            }
            $decodedStationsList = json_decode($result, true);
            if ($decodedStationsList === null) {
                throw new Exception('Bad WMATA API Request: Bad Data');
            }

            $this->formatStationData($decodedStationsList['Stations']);
            $this->formatStationCodeData($decodedStationsList['Stations']);

        } catch (Exception $exception) {
            throw new Exception("Error retrieving Station data: {$exception->getMessage()}");
        }
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
        $this->stationNamesDistinctList = $stationList;
    }

    private function formatStationCodeData(array $stations): void
    {
        foreach ($stations as $station) {
            $stationName = $station['Name'];
            $stationCode = $station['Code'];

            // Push to the association array to translate station name into list of assoc. station codes
            if (!array_key_exists($stationName, $this->stationCodesByName)) {
                $this->stationCodesByName[$stationName] = [$stationCode];
            } else if (!in_array($stationCode, $this->stationCodesByName[$stationName])) {
                $this->stationCodesByName[$stationName][] = $stationCode;
            }
        }
    }

    public function getStationCodesByStationNames(string $stationNames): string|bool {
        $names = explode(',', $stationNames);

        if (!$names) {
            return false;
        }

        $stationCodes = [];
        foreach ($names as $name) {
            foreach ($this->stationCodesByName[$name] as $code) {
                $stationCodes[] = $code;
            }
        }

        return implode(',', $stationCodes);
    }

    // Returns array of distinct station names as json string
    public function getStationNamesList(): string
    {
        return json_encode($this->stationNamesDistinctList);
    }
}
