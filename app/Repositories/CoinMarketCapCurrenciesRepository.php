<?php

namespace App\Repositories;

use App\Models\Currency;
use App\Models\Collections\CurrenciesCollection;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\{BadResponseException, GuzzleException};

class CoinMarketCapCurrenciesRepository implements CurrenciesRepository
{
    private const BASE_URI = 'https://pro-api.coinmarketcap.com';
    private static Client $client;
    private static array $headers;
    private ?string $errorMessage = null;

    public function __construct()
    {
        if (!isset(self::$client)) {
            $this->createApiClient();
        }
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function fetchCurrencies(array $symbols, string $currencyType): CurrenciesCollection
    {
        if (!isset(self::$client)) {
            return new CurrenciesCollection();
        }
        try {
            $response = self::$client->request(
                'GET',
                self::BASE_URI . '/v2/cryptocurrency/quotes/latest', [
                    'headers' => self::$headers,
                    'query' => [
                        'symbol' => join(',', $symbols),
                        'convert' => $currencyType,
                    ],
                ]
            );
        } catch (BadResponseException $e) {
            $errorContents = json_decode($e->getResponse()->getBody()->getContents());
            $this->errorMessage = 'API Error: ' . $errorContents->status->error_message . PHP_EOL .
                'Error code: ' . $errorContents->status->error_code;
            return new CurrenciesCollection();
        } catch (GuzzleException $e) {
            $this->errorMessage = 'Guzzle Exception: ' . $e->getMessage();
            return new CurrenciesCollection();
        }
        $response = json_decode($response->getBody()->getContents());
        $firstKey = $symbols[0];
        if (empty($response->data->$firstKey)) {
            $this->errorMessage = "Error: No market data found!";
            return new CurrenciesCollection();
        }

        if ($response->status->error_code > 0) {
            $this->errorMessage = $response->status->error_message;
            return new CurrenciesCollection();
        }

        $cryptocurrencyPrices = new CurrenciesCollection();
        foreach ($response->data as $currency) {
            $cryptocurrencyPrices->addCurrency(new Currency(
                $currency[0]->symbol,
                $currency[0]->name,
                $currency[0]->quote->$currencyType->price,
                $currency[0]->quote->$currencyType->percent_change_1h,
                $currency[0]->quote->$currencyType->percent_change_24h,
                $currency[0]->quote->$currencyType->percent_change_7d,
            ));
        }
        return $cryptocurrencyPrices;
    }

    private function createApiClient(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
        $dotenv->load();
        try {
            $dotenv->required('API_KEY')->notEmpty();
        } catch (ValidationException $e) {
            $this->errorMessage = 'Dotenv Exception: ' . $e->getMessage();
            return;
        }
        self::$headers = [
            'Accepts' => 'application/json',
            'X-CMC_PRO_API_KEY' => $_ENV['API_KEY'],
        ];
        self::$client = new Client();
    }
}