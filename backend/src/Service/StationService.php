<?php

namespace App\Service;

use Exception;

class StationService extends WMATAService
{
    protected array $stationCodesByName = [];
    protected array $stationNamesDistinctList = [];

    /**
     * Constructor function
     * @throws Exception
     * @returns void
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

    /**
     * Called only during object construction.
     * Retrieves and stores list of stations from WMATA API
     * @throws Exception
     * @return void
     */
    private function initStationsList(): void {
        try {
            $result = $this->apiService->CallAPI($this->wmataAPIPath);
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

    /**
     * Extracts list of station names from raw data.
     * Stations with multiple platforms are listed multiple times
     * in raw data. Resulting array removes duplicates.
     * @param array $stations
     * @return void
     */
    private function formatStationData(array $stations): void {
        $stationList = [];
        foreach ($stations as $station) {
            $stationName = $station['Name'];

            // No duplicates (stations with multiple platforms listed
            // multiple times in raw data)
            if (!in_array($stationName, $stationList)) {
                $stationList[] = $stationName;
            }
        }
        // Station names should be alphabetical for easy user lookup
        sort($stationList);
        $this->stationNamesDistinctList = $stationList;
    }

    /**
     * Gets the station codes (separate code for each platform
     * on a station) and maps them by station name.
     * @param array $stations
     * @return void
     */
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

    /**
     * Returns a comma separated list string of station codes of the
     * stations passed in as a comma separated list. Returns false if
     * an error occurs.
     * @param string $stationNames
     * @return string|bool
     */
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

    /**
     * Returns the array of distinct station names
     * created at object construction as a json string.
     * @return string
     */
    public function getStationNamesList(): string
    {
        return json_encode($this->stationNamesDistinctList);
    }
}
