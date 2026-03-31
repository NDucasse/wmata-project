<?php

namespace App\Controller;

use App\Service\StationListService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

class StationListController
{
    #[Route('/station-list')]
    public function getStationList(StationListService $service)
    {
        return new Response($service->getStationList());
    }
}