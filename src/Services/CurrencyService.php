<?php

namespace App\Service;

use App\Service\OpenExchangeRatesService;

class CurrencyService
{
    private $openExchangeRatesService;

    public function __construct(OpenExchangeRatesService $openExchangeRatesService)
    {
        $this->openExchangeRatesService = $openExchangeRatesService;
    }

    public function getExchangeRate(string $fromCurrency, string $toCurrency): ?float
    {
        return $this->openExchangeRatesService->getExchangeRate($fromCurrency, $toCurrency);
    }

    public static function getPredefinedCurrencyCodes(): array
    {
        
        return ['USD', 'EUR', 'TND'];
    }
}
