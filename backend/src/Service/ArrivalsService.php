<?php

namespace App\Service;

use DateTime;
use DateTimeZone;
use Exception;

class ArrivalsService extends WMATAService
{
    /**
     * @throws Exception
     */
    function __construct() {
        parent::__construct();
        $this->wmataAPIPath = $_ENV['NEXT_ARRIVALS_PATH'];
    }

    private function formatArrivalData(array $arrivals): array {
        $nextTrains = [];
        foreach ($arrivals['Trains'] as $arrival) {
            $nextTrain = [
                'line' => $arrival['Line'],
                'destination' => $arrival['Destination'],
                'cars' => $arrival['Car'],
            ];
            try {
                $arrivalTime = $this->minutesToTimestampString($arrival['Min']);
            } catch (Exception $e) {
                error_log("Error getting ArrivalTime for train on line {$nextTrain['line']} to destination {$nextTrain['destination']}: {$e->getMessage()}");
                $arrivalTime = 'Unknown';
            }

            $nextTrain['arrivalTime'] = $arrivalTime;
            $nextTrains[] = $nextTrain;
        }
        return $nextTrains;
    }

    /**
     * @throws Exception
     */
    private function minutesToTimestampString(string $minutes): string {
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
            error_log("Error getting datetime: {$e->getMessage()}");
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
    }

    public function getNextArrivalsByStationCodes(string $stationCodes = 'All'): bool | string {
        $fullPath = $this->wmataAPIPath . $stationCodes;
        $result = $this->apiService->CallAPI('GET', $fullPath);

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
