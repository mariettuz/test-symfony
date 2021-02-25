<?php

namespace App\Service;

use App\Entity\Seller;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

class SellersService
{
    private $logger;
    private $entityManager;

    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function getSaveSellers(string $url): array
    {
        set_time_limit(300);

        $url = $url.'/sellers.json';

        $this->logger->info('START - getSaveSellers - '.$url);

        //controllo che l'url sia valida
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $error = 'No valid url into parameters';
            $returnResponse = $this->returnResponse(400, [], 0, 0, 0, $error);
            $this->logger->error('ERROR - '.$error);

            return $returnResponse;
        }

        $this->logger->info('CALL - '.$url);

        $client = HttpClient::create();
        $response = $client->request('GET', $url);

        $statusCode = $response->getStatusCode();

        $this->logger->info('CALL CODE - '.$statusCode);

        //controllo se la chiamata fallisce
        if (200 != $statusCode) {
            if (404 == $statusCode) {
                $error = $url.' not found';
                $returnResponse = $this->returnResponse(404, [], 0, 0, 0, $error);
            } else {
                $error = 'No valid response';
                $returnResponse = $this->returnResponse($statusCode, [], 0, 0, 0, $error);
            }

            $this->logger->error('ERROR - '.$error);

            return $returnResponse;
        }

        $array_sellers = [];
        $write_sellers = 0;
        $no_write_sellers = 0;

        $content = $response->toArray();
        $response_sellers = $this->validationObject('sellers', $content);
        $total_sellers = count($response_sellers);

        //controllo sia un json valido
        if (0 == $total_sellers) {
            $error = $url.' no valid json';
            $returnResponse = $this->returnResponse(400, [], 0, 0, 0, $error);
            $this->logger->error('ERROR - '.$error);

            return $returnResponse;
        }

        $repository = $this->entityManager->getRepository(Seller::class);

        foreach ($content['sellers'] as $item) {
            $name = (string) $this->validationObject('name', $item);
            $seller_type = (string) $this->validationObject('seller_type', $item);
            $domain = (string) $this->validationObject('domain', $item);
            $seller_id = (int) $this->validationObject('seller_id', $item, 'int');

            $seller = $repository->findOneBy(
                [
                    'name' => $name,
                    'type' => $seller_type,
                    'domain' => $domain,
                    'sellerId' => $seller_id,
                    'url' => $url,
                ]
            );

            if ($seller) {
                ++$no_write_sellers;
                $this->logger->error('Seller '.$seller->getName().' already exists with id = '.$seller->getId());
                continue;
            }

            $seller = new Seller($name, $seller_type, $domain, $seller_id, $url);

            $this->entityManager->persist($seller);
            $this->entityManager->flush();

            ++$write_sellers;
        }

        $sellers_from_url = $repository->findBy(
            ['url' => $url]
        );

        foreach ($sellers_from_url as $value) {
            $array_sellers[] = [
                'id' => $value->getId(),
                'name' => $value->getName(),
                'seller_type' => $value->getType(),
                'domain' => $value->getDomain(),
                'seller_id' => $value->getSellerId(),
            ];
        }

        $returnResponse = $this->returnResponse(200, $array_sellers, $total_sellers, $write_sellers, $no_write_sellers, 'No errors');

        $this->logger->info('END - getSaveSellers - total: '.$total_sellers.' done: '.$write_sellers.' no_write: '.$no_write_sellers);

        return $returnResponse;
    }

    private function returnResponse(int $statusCode, array $body, int $total_sellers, int $write_sellers, int $no_write_sellers, string $error): array
    {
        return [
            'response' => [
                'code' => $statusCode,
                'body' => $body,
                'total_sellers' => $total_sellers,
                'write_sellers' => $write_sellers,
                'no_write_sellers' => $no_write_sellers,
                'error' => $error,
            ],
        ];
    }

    private function validationObject(string $index, array $item, string $type = 'string')
    {
        $value = '';

        if ('int' == $type) {
            $value = 0;
        }

        if ('array' == $type) {
            $value = [];
        }

        if (array_key_exists($index, $item)) {
            $value = $item[$index];
        }

        return $value;
    }
}
