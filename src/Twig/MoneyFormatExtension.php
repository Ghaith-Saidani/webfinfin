<?php

namespace App\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Money\Money;

class MoneyFormatExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('money_format', [$this, 'formatMoney']),
        ];
    }

    public function formatMoney(Money $money): string
    {
        // Convert the amount to dollars with two decimal places
        $formattedAmount = number_format($money->getAmount() / 100, 2);

        // Get the currency code
        $currencySymbol = $this->getCurrencySymbol($money->getCurrency()->getCode());
      

        return $currencySymbol . ' ' . $formattedAmount;
    }

    private function getCurrencySymbol($currency): string
{
    // Define currency symbols for supported currencies
    $currencySymbols = [
        'USD' => '$',
        'EUR' => '€', // Euro symbol
        'TND' => 'TND', 
        // Add more currency symbols as needed
    ];

    // Return the currency symbol based on the provided currency code,
    // or default to '$' if the currency code is not found
    return $currencySymbols[$currency] ?? '$';
}
}