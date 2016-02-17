<?php
/*
 * This file is part of the Exchange Rate package, an RunOpenCode project.
 *
 * Implementation of exchange rate crawler for Banca Intesa Serbia, http://www.bancaintesa.rs.
 *
 * (c) 2016 RunOpenCode
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Util;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\StreamInterface;

/**
 * Class BancaIntesaBrowser
 *
 * Browser browses trough web site of Banca Intesa Serbia and fetches documents with rates.
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Util
 */
class BancaIntesaBrowser
{
    const SOURCE = 'http://www.bancaintesa.rs/pocetna.21.html?print=1';
    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var CookieJar
     */
    private $guzzleCookieJar;

    /**
     * Get HTML document with rates.
     *
     * @param \DateTime $date
     * @return StreamInterface
     */
    public function getHtmlDocument(\DateTime $date)
    {
        return $this->request('POST', array(), array(
            'day' => $date->format('d'),
            'month' => $date->format('m'),
            'year' => $date->format('Y')
        ));
    }

    /**
     * Execute HTTP request and get raw body response.
     *
     * @param string $method HTTP Method.
     * @param array $params Params to send with request.
     * @return StreamInterface
     */
    private function request($method, array $query = array(), array $params = array())
    {
        $client = $this->getGuzzleClient();

        $response = $client->request($method, self::SOURCE, array(
            'cookies' => $this->getGuzzleCookieJar(),
            'form_params' => $params,
            'query' => $query
        ));

        return $response->getBody()->getContents();
    }

    /**
     * Get Guzzle Client.
     *
     * @return Client
     */
    private function getGuzzleClient()
    {
        if ($this->guzzleClient === null) {
            $this->guzzleClient = new Client(array('cookies' => true));
        }

        return $this->guzzleClient;
    }

    /**
     * Get Guzzle CookieJar.
     *
     * @return CookieJar
     */
    private function getGuzzleCookieJar()
    {
        if ($this->guzzleCookieJar === null) {
            $this->guzzleCookieJar = new CookieJar();
        }

        return $this->guzzleCookieJar;
    }
}
