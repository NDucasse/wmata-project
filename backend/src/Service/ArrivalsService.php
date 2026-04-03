<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Exception;

class ArrivalsService extends WMATAService
{
    /**
     * Constructor function
     * @throws Exception
     * @returns void
     */
    function __construct() {
        parent::__construct();
        $this->wmataAPIPath = $_ENV['NEXT_ARRIVALS_PATH'];
    }

    /**
     * Formats the raw arrival data to mutate "minutes until next train arrives"
     * into a timestamp of form "hh:mm XM" (12 hour time) in East Coast time.
     * @param array $arrivals
     * @return array
     */
    private function formatArrivalData(array $arrivals): array {
        $nextTrains = [];
        foreach ($arrivals['Trains'] as $arrival) {
            $nextTrain = [
                'line' => $arrival['Line'],
                'destination' => $arrival['Destination'],
                'cars' => $arrival['Car'],
            ];
            $arrivalTime = $this->minutesToTimestampString($arrival['Min']);

            $nextTrain['arrivalTime'] = $arrivalTime;
            $nextTrains[] = $nextTrain;
        }
        return $nextTrains;
    }

    /**
     * Takes raw "minutes until..." data and adds it to current time in
     * East Coast timezone to get a future time. Other values for the $minutes
     * string include "BRD" -> "Boarding", "ARR" -> "Arriving Soon",
     * and "---" or "" -> "Unknown". All other values must be an integer indicating
     * number of minutes ahead. Defaults to "EDT" timezone if an error occurs
     * getting the proper timezone. Defaults to returning "Unknown" if any
     * other errors occur.
     * @param string $minutes
     * @return string
     */
    private function minutesToTimestampString(string $minutes): string {
        try {
            // returns tz offset in seconds
            $tz = timezone_offset_get(new DateTimeZone('America/New_York'), new DateTime('now'));
            if (!$tz) {
                // If there's a failure, default to EDT offset (UTC-4)
                $tz = '-4';
            } else {
                // convert seconds to hours
                $tz = $tz / 60 / 60;
            }
            try {
                $now = new DateTime("+$tz hours");
            } catch (Exception $e) {
                error_log("Error getting datetime for minutes {$minutes}: {$e->getMessage()}");
                throw $e;
            }

            switch ($minutes) {
                case 'BRD':
                    $arrivalTimestampString = 'Boarding';
                    break;
                case 'ARR':
                    $arrivalTimestampString = 'Arriving Soon';
                    break;
                case '---': // '---' and '' both should result in 'Unknown'
                case '':
                    $arrivalTimestampString = 'Unknown';
                    break;
                default: // Any other expected value will be numerical (integer string):
                    $now->modify("+$minutes minutes");
                    $arrivalTimestampString = date('g:i A', $now->getTimestamp());
            }
            return $arrivalTimestampString;
        } catch (Exception $e) {
            error_log("Error formatting timestamp: {$e->getMessage()}");
            return 'Unknown';
        }
    }

    /**
     * Takes in comma separated string list of station codes.
     * Returns a json string list of upcoming train
     * arrivals at those stations.
     * Future update: separate out each set of arrivals by station
     * in return json. Right now they're all combined in one list.
     * @param string $stationCodes
     * @return bool|string
     */
    public function getNextArrivalsByStationCodes(string $stationCodes = 'All'): bool | string {
        $fullPath = $this->wmataAPIPath . $stationCodes;
        $result = $this->apiService->CallAPI($fullPath);

        if (!$result) {
            error_log('Bad WMATA API Request: No Data');
            return false;
        }

        $decodedArrivals = json_decode($result, true);

        if ($decodedArrivals != Null) {
            $formattedArrivals = $this->formatArrivalData($decodedArrivals);
        } else {
            error_log("Error decoding Arrival data");
            return false;
        }
        return json_encode($formattedArrivals);
    }
}
