<?php

namespace App\Controller;

use App\Service\ArrivalsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class ArrivalsController
{
    /**
     * Expects comma separated list string of station codes
     * (or single code without commas)
     * @param ArrivalsService $service
     * @param string $stationCodes
     * @return Response
     */
    #[Route('/arrivals/next-arrivals/{stationCodes}')]
    public function getNextArrivals(ArrivalsService $service, string $stationCodes): Response {
        return new Response($service->getNextArrivalsByStationCodes($stationCodes));
    }
}