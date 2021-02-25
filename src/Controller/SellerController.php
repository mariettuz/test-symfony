<?php

namespace App\Controller;

use App\Service\SellersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SellerController extends AbstractController
{
    /**
     * @Route("/api/seller", name="seller")
     */
    public function getSellersFromUrl(SellersService $sellersService, Request $request): Response
    {
        $url = (string) $request->query->get('url');

        $returnResponse = $sellersService->getSaveSellers($url);

        return $this->json(
            $returnResponse, $returnResponse['response']['code']
        );
    }
}
