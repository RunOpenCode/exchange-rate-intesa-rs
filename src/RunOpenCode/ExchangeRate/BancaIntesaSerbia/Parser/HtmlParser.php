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
namespace RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser;

use RunOpenCode\ExchangeRate\Contract\RateInterface;
use RunOpenCode\ExchangeRate\Model\Rate;
use RunOpenCode\ExchangeRate\BancaIntesaSerbia\Api;
use Symfony\Component\DomCrawler\Crawler;


/**
 * Class HtmlParser
 *
 * Parse HTML document with daily rates from Banca Intesa Serbia.
 *
 * @package RunOpenCode\ExchangeRate\BancaIntesaSerbia\Parser
 */
class HtmlParser
{
    /**
     * @var RateInterface[]
     */
    private $rates;

    /**
     * @var array
     */
    private $currentRate;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $html;

    /**
     * HtmlParser constructor.
     * @param $node string
     * @param $date \DateTime
     */
    public function __construct($node, \DateTime $date)
    {
        $this->html = $node;
        $this->date = $date;
    }

    public function getRates()
    {
        if (!$this->rates) {
            $this->extractRates();
        }

        return $this->rates;
    }

    private function extractRates()
    {
        $crawler = new Crawler($this->html);
        $crawler = $crawler->filter('table');

        $buildRate = function($value, $currencyCode, $rateType, $date) {

            return new Rate(
                Api::NAME,
                $value,
                $currencyCode,
                $rateType,
                $date,
                'RSD',
                new \DateTime('now'),
                new \DateTime('now')
            );
        };

        $nodesArray = $crawler->filter('tr')->each(function (Crawler $node, $i) {
            return $node->html();
        });

        foreach ($nodesArray as $node) {

            $crawler->clear();

            $crawler->add($node);

            $crawler->filter('td')->each(function (Crawler $node, $i) {

                if($i == 1) {
                    $this->currentRate['currencyCode'] = trim($node->text());
                }

                if($i == 2) {
                    $this->currentRate['unit'] = (int) trim($node->text());
                }

                if($i == 3) {
                    $this->currentRate['foreign_exchange_buying'] = (float) trim($node->text());
                }

                if($i == 4) {
                    $this->currentRate['default'] = (float) trim($node->text());
                }

                if($i == 5) {
                    $this->currentRate['foreign_exchange_selling'] = (float) trim($node->text());
                }

                if($i == 6) {
                    $this->currentRate['foreign_cash_buying'] = (float) trim($node->text());
                }

                if($i == 7) {
                    $this->currentRate['foreign_cash_selling'] = (float) trim($node->text());
                }

            });

            //make rates for currency
            if(strlen($this->currentRate['currencyCode']) === 3) {
                $this->rates[] = $buildRate(
                    $this->currentRate['default'] / $this->currentRate['unit'],
                    $this->currentRate['currencyCode'],
                    'default',
                    $this->date
                );

                if ($this->currentRate['foreign_exchange_buying'] > 0) {
                    $this->rates[] = $buildRate(
                        $this->currentRate['foreign_exchange_buying'] / $this->currentRate['unit'],
                        $this->currentRate['currencyCode'],
                        'foreign_exchange_buying',
                        $this->date
                    );
                }

                if ($this->currentRate['foreign_exchange_selling'] > 0) {
                    $this->rates[] = $buildRate(
                        $this->currentRate['foreign_exchange_selling'] / $this->currentRate['unit'],
                        $this->currentRate['currencyCode'],
                        'foreign_exchange_selling',
                        $this->date
                    );
                }

                if ($this->currentRate['foreign_cash_buying'] > 0) {
                    $this->rates[] = $buildRate(
                        $this->currentRate['foreign_cash_buying'] / $this->currentRate['unit'],
                        $this->currentRate['currencyCode'],
                        'foreign_cash_buying',
                        $this->date
                    );
                }

                if ($this->currentRate['foreign_cash_selling'] > 0) {
                    $this->rates[] = $buildRate(
                        $this->currentRate['foreign_cash_selling'] / $this->currentRate['unit'],
                        $this->currentRate['currencyCode'],
                        'foreign_cash_selling',
                        $this->date
                    );
                }
            }
        }
    }
}
