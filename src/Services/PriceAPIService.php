<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PriceAPIService
{
    private const API_TOKEN = 'DOJYDUKUHWOLZMRRTYAINSIYHYOUCGZYSYIQKBSTXDWPQPTKWKLQZFBJPXDKTTKZ';
    private const API_BASE_URL = 'https://api.priceapi.com/v2';

    public function postJob(string $term): ?string
    {
        $client = new Client();

        try {
            $response = $client->request('POST', self::API_BASE_URL.'/jobs?token='.self::API_TOKEN, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'source' => 'amazon',
                    'country' => 'fr',
                    'topic' => 'product_and_offers',
                    'key' => 'term',
                    'values' => $term,
                    'max_age' => '1440',
                    'timeout' => '5',
                    'webhook_method' => 'POST',
                    'webhook_download_format' => 'json',
                    'webhook_url' => 'https://app.priceapi.com/v2/jobs',
                ]
            ]);

            return $response->getBody()->getContents();
        } catch (GuzzleException $e) {
            return null;
        }
    }

    public function fetchPriceAndAvailability(string $jobId): ?string
    {
        sleep(6); // Wait for 6 seconds
    
        $client = new Client();
    
        try {
            $response = $client->request('GET', self::API_BASE_URL.'/jobs/'.$jobId.'/download?token='.self::API_TOKEN, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
    
            $jsonResponse = $response->getBody()->getContents();
            $result = '';
    
            $jsonData = json_decode($jsonResponse, true);
            $resultsArray = $jsonData['results'];
    
            if (!empty($resultsArray)) {
                $resultElement = $resultsArray[0];
                $contentObject = $resultElement['content'];
                $name = $contentObject['name'];
                $url = $contentObject['url'];
                $imageUrl = $contentObject['image_url'];
    
                $buyboxObject = $contentObject['buybox'];
                $availabilityText = $buyboxObject['availability_text'];
    
                $offersArray = $contentObject['offers'];
                if (!empty($offersArray)) {
                    $offerElement = $offersArray[0];
                    $price = $offerElement['price'];
                    $currency = $offerElement['currency'];
    
                    $result .= "Name: $name\n";
                    $result .= "URL: $url\n";
                    $result .= "Image URL: $imageUrl\n";
                    $result .= "Price: $price $currency\n";
                    $result .= "Availability: $availabilityText\n";
                }
            }
    
            return $result;
    
        } catch (GuzzleException $e) {
            return null;
        }
    }
    
    


}
