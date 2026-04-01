<?php

namespace App\Controller;

use App\Service\StationService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class StationController
{
    #[Route('/station-list')]
    public function getStationList(StationService $service): Response
    {
        return new Response($service->getStationList());
    }

    #[Route('/next-arrivals/{stationName}')]
    public function getNextArrivals(StationService $service, string $stationName): Response {
        return new Response($service->getNextArrivalsByStationName($stationName));
    }
}