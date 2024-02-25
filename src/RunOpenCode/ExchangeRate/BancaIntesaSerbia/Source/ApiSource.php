<?php

declare(strict_types=1);

namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Source;

use GuzzleHttp\Client;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Enum\RateType;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\RuntimeException;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Exception\SourceNotAvailableException;
use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Contract\SourceInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\Utils\CurrencyCodeUtil;

/**
 * Fetch rates from Banca Intesa API service.
 */
final class ApiSource implements SourceInterface
{
    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var array<string, array<RateInterface>>
     */
    private $cache;

    const URL = 'https://www.bancaintesa.rs/digitalServicesServlet/';

    const API_RATE_TYPES = [
        RateType::FOREIGN_EXCHANGE_BUYING => 'buyRateCash',
        RateType::FOREIGN_CASH_BUYING => 'buyRate',
        RateType::FOREIGN_EXCHANGE_SELLING => 'sellingRateCash',
        RateType::FOREIGN_CASH_SELLING => 'sellingRate',
        RateType::MEDIAN => 'meanRate',
    ];

    /**
     * @param Client $guzzleClient
     */
    public function __construct(Client $guzzleClient = null)
    {
        $this->guzzleClient = $guzzleClient !== null ? $guzzleClient : new Client();
        $this->cache = [];
    }

    public function fetch($currencyCode, $rateType = RateType::MEDIAN, \DateTime $date = null)
    {
        $currencyCode = CurrencyCodeUtil::clean($currencyCode);

        if (!Api::supports($currencyCode, $rateType)) {
            throw new RuntimeException(sprintf('Banca Intesa Serbia does not supports currency code "%s" for rate type "%s".', $currencyCode, $rateType));
        }

        if ($date === null) {
            $date = new \DateTime('now');
        }

        if (!\array_key_exists($rateType, $this->cache)) {
            try {
                $rates = $this->fetchRates($currencyCode, $date);
                $this->cache[$currencyCode] = $rates;
            } catch (\Exception $e) {
                throw new SourceNotAvailableException(sprintf('Unable to load data from "%s" for "%s" of rate type "%s".', $this->getName(), $currencyCode, $rateType), 0, $e);
            }
        }

        $rate = $this->cache[$currencyCode][$rateType];

        if (false === (bool)$rate->getValue()) {
            throw new RuntimeException(sprintf('Banca Intesa Serbia does not supports currency code "%s" for rate type "%s".', $currencyCode, $rateType));
        }

        return $rate;
    }

    /**
     * @return array<RateInterface>
     */
    private function fetchRates(string $currencyCode, \DateTime $date): array
    {
        $response = $this->guzzleClient->request('GET', self::URL, [
            'query' => $this->getQueryParams($currencyCode, $date)
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \Exception(\sprintf('Invalid response code, received "%s"', $response->getStatusCode()));
        }

        /**
         * @var array{
         *     statusCode: string,
         *     rates: array<array{
         *          buyRateCash: array{rate:string, currencyTo: array{label: string}},
         *          buyRate: array{rate:string, currencyTo: array{label: string}},
         *          sellingRateCash: array{rate:string, currencyTo: array{label: string}},
         *          sellingRate: array{rate:string, currencyTo: array{label: string}},
         *          meanRate: array{rate:string, currencyTo: array{label: string}}
         *      }>
         * } $response
         */
        $response = \json_decode($response->getBody()->getContents(), true);

        if ('200' !== $response['statusCode']) { // For some reason, API returns HTTP status code 200, but sets the actual status code in the response body.. as a string..
            throw new \Exception(\sprintf('Invalid response code, received "%s"', $response['statusCode']));
        }

        if (!\array_key_exists('rates', $response) || 0 === \count($response['rates'])) {
            throw new \Exception('The rates for provided currency not received.');
        }

        $rates = $response['rates'][0]; // We always fetch result for a single day.

        $extractedRates = [];

        foreach (RateType::all() as $rateType) {
            $extractedRates[$rateType] = new Rate(
                Api::NAME,
                (float)$rates[self::API_RATE_TYPES[$rateType]]['rate'],
                $rates[self::API_RATE_TYPES[$rateType]]['currencyTo']['label'],
                $rateType,
                $date,
                'RSD',
                new \DateTime('now'),
                new \DateTime('now')
            );
        }

        return $extractedRates;
    }

    public function getName(): string
    {
        return Api::NAME;
    }

    /**
     * @return array{
     *     operation: string,
     *     httpMethod: string,
     *     endpointName: string,
     *     listType: string,
     *     headers: string,
     *     bankId: string,
     *     bank: string,
     *     locale: string,
     *     fromCurrency: string,
     *     toCurrency: string,
     *     datePickerStart: string,
     *     datePickerStop: string
     * }
     */
    private function getQueryParams(string $currencyCode, \DateTime $date): array
    {
        return [
            'operation' => 'getExchangeRatesArchive',
            'httpMethod' => 'GET',
            'endpointName' => 'getExchangeRatesArchive',
            'listType' => 'BIB_DEV',
            'headers' => 'standardHeader',
            'bankId' => 'BIB',
            'bank' => 'BIB',
            'locale' => 'sr',
            'fromCurrency' => 'RSD',
            'toCurrency' => $currencyCode,
            'datePickerStart' => $date->format('Y-m-d'),
            'datePickerStop' => $date->format('Y-m-d'),
        ];
    }
}