<?php

namespace App\Controller;

use App\Service\StationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class StationController
{
    #[Route('/stations/station-list')]
    public function getStationList(StationService $service): Response {
        return new Response($service->getStationNamesList());
    }

    #[Route('/stations/station-codes/{stationNames}')]
    public function getStationCodes(StationService $service, string $stationNames): Response {
        return new Response($service->getStationCodesByStationNames($stationNames));
    }
}