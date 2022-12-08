<?php

namespace App\Repositories;

use App\Models\Collections\CurrenciesCollection;
use App\Models\Currency;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;

class MarketCapCurrenciesRepository implements CurrenciesRepository
{
    private static array $headers;
    private ?string $errorMessage = null;

    public function __construct(Dotenv $dotenv)
    {
        if (!isset(self::$headers)) {
            try {
                $dotenv->required("API_KEY")->notEmpty();
                self::$headers = [
                    'Accepts: application/json',
                    'X-CMC_PRO_API_KEY: ' . $_ENV["API_KEY"],
                ];
            } catch (ValidationException $e) {
                $this->errorMessage = "Dotenv Validation Exception: {$e->getMessage()}";
            } catch (\Exception $e) {
                $this->errorMessage = "Exception: {$e->getMessage()}";
            }
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function fetchCurrencies(array $symbols, string $currencyType): CurrenciesCollection
    {
        $url = 'https://pro-api.coinmarketcap.com/v2/cryptocurrency/quotes/latest';
        $parameters = [
            'symbol' => join(",", $symbols),
            'convert' => $currencyType
        ];

        $qs = http_build_query($parameters);
        $request = "{$url}?{$qs}";

        $curl = curl_init();
        curl_setopt_array($curl,
            [
                CURLOPT_URL => $request,
                CURLOPT_HTTPHEADER => self::$headers,
                CURLOPT_RETURNTRANSFER => 1
            ]
        );

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if ($response->status->error_code > 0) {
            $this->errorMessage = $response->status->error_message;
            return new CurrenciesCollection();
        }

        $cryptocurrencyPrices = new CurrenciesCollection();
        foreach ($response->data as $currency) {
            $cryptocurrencyPrices->addCurrency(new Currency(
                $currency[0]->name,
                $currency[0]->quote->$currencyType->price,
                $currency[0]->quote->$currencyType->percent_change_24h,
            ));
        }
        return $cryptocurrencyPrices;
    }
}