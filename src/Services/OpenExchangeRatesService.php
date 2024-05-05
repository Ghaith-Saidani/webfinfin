<?php


namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenExchangeRatesService
{
    private $httpClient;
    private $apiKey;

    public function __construct(HttpClientInterface $httpClient, string $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        $apiUrl = sprintf('https://open.er-api.com/v6/latest/%s?apikey=%s', $fromCurrency, $this->apiKey);

        try {
            $response = $this->httpClient->request('GET', $apiUrl);
            $data = $response->toArray();

            return $data['rates'][$toCurrency] ?? null;
        } catch (\Exception $e) {
            // Handle exceptions
            return null;
        }
    }
}
