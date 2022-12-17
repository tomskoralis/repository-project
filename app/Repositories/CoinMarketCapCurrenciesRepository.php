<?php

namespace App\Repositories;

use App\Models\{Currency, Error};
use App\Models\Collections\CurrenciesCollection;
use Dotenv\Dotenv;
use Dotenv\Exception\ValidationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\{BadResponseException, GuzzleException};

class CoinMarketCapCurrenciesRepository implements CurrenciesRepository
{
    private static ?CoinMarketCapCurrenciesRepository $instance = null;
    private static Client $client;
    private static array $headers;
    private static ?Error $error = null;
    private const BASE_URI = 'https://pro-api.coinmarketcap.com';

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__, '../../.env');
        $dotenv->load();
        try {
            $dotenv->required('API_KEY')->notEmpty();
        } catch (ValidationException $e) {
            self::$error = new Error(
                'Dotenv Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
            return;
        }
        self::$headers = [
            'Accepts' => 'application/json',
            'X-CMC_PRO_API_KEY' => $_ENV['API_KEY'],
        ];
        self::$client = new Client();
    }

    public static function getError(): ?Error
    {
        return self::$error;
    }

    public static function fetchCurrencies(array $symbols, string $currencyConvertType): CurrenciesCollection
    {
        try {
            $response = self::getClient()->request(
                'GET',
                self::BASE_URI . '/v2/cryptocurrency/quotes/latest', [
                    'headers' => self::$headers,
                    'query' => [
                        'symbol' => join(',', $symbols),
                        'convert' => $currencyConvertType,
                    ],
                ]
            );
        } catch (BadResponseException $e) {
            $errorContents = json_decode($e->getResponse()->getBody()->getContents());
            self::$error = new Error(
                'API Error: ' . $errorContents->status->error_message . PHP_EOL .
                'code (' . $errorContents->status->error_code . ')',
                'repository',
                'currencies'
            );
            return new CurrenciesCollection();
        } catch (GuzzleException $e) {
            self::$error = new Error(
                'Guzzle Exception: ' . $e->getMessage(),
                'repository',
                'currencies'
            );
            return new CurrenciesCollection();
        }
        $response = json_decode($response->getBody()->getContents());

        if ($response->status->error_code > 0) {
            self::$error = new Error(
                $response->status->error_message,
                'repository',
                'currencies'
            );
            return new CurrenciesCollection();
        }

        if (empty($response->data->{$symbols[0]})) {
            return new CurrenciesCollection();
        }

        $cryptocurrencyPrices = new CurrenciesCollection();
        foreach ($response->data as $currency) {
            $cryptocurrencyPrices->add(new Currency(
                $currency[0]->symbol,
                $currency[0]->name,
                $currency[0]->quote->{$currencyConvertType}->price,
                $currency[0]->quote->{$currencyConvertType}->percent_change_1h,
                $currency[0]->quote->{$currencyConvertType}->percent_change_24h,
                $currency[0]->quote->{$currencyConvertType}->percent_change_7d,
            ));
        }
        return $cryptocurrencyPrices;
    }

    private static function getInstance(): ?CoinMarketCapCurrenciesRepository
    {
        if (!isset(self::$instance)) {
            self::$instance = new CoinMarketCapCurrenciesRepository();
        }
        return self::$instance;
    }

    private static function getClient(): Client
    {
        return self::getInstance()::$client;
    }
}