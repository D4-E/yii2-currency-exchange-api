<?php

namespace app\services;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use RuntimeException;
use yii\httpclient\Client;
use yii\httpclient\Response;

/**
 * Сервис загрузки курсов валют с учётом комиссии 2 %.
 *
 * Источник — API CoinGate.  
 * Возвращаются только пары, где имеется курс к USD.
 *
 * @package app\services
 */
final class RateService
{
    private const API_URL = 'https://api.coingate.com/api/v2/rates';
    private const COMMISSION = '1.02'; // 2 %
    private const SCALE = 10; // знаков после запятой

    public function __construct(
        private Client $client = new Client(),
    ) {}

    /**
     * Загружает и возвращает курсы.
     *
     * @param string $filter CSV-список валют (USD,BTC,ETH). Пустая строка → все.
     *
     * @return array<string,string> Ассоциативный массив «CODE ⇒ rate»,
     *                              отсортированный от меньшего к большему.
     *
     * @throws RuntimeException при проблемах с внешним API.
     */
    public function getRates(string $filter = ''): array
    {
        /** @var Response $response */
        $response = $this->client->get(self::API_URL)->send();

        if (!$response->isOk || !isset($response->data['merchant'])) {
            throw new RuntimeException('External API error or unexpected response');
        }

        $raw = $response->data['merchant'];
        $rates = [];

        foreach ($raw as $symbol => $pairs) {
            if (!isset($pairs['USD'])) {
                continue;
            }

            $rate = BigDecimal::of($pairs['USD'])
                ->multipliedBy(self::COMMISSION)
                ->toScale(self::SCALE, RoundingMode::HALF_UP);
            $rates[$symbol] = (string) $rate;
        }

        asort($rates); // сортировка от меньшего курса
        return $this->applyFilter($rates, $filter);
    }

    /**
     * Возвращает подмассив, если задан фильтр вида «USD,BTC».
     *
     * @param array  $rates   Полный набор курсов.
     * @param string $filter  CSV-список кодов валют.
     *
     * @return array<string,string>
     */
    private function applyFilter(array $rates, string $filter): array
    {
        $filter = trim($filter);
        if ($filter === '') {
            return $rates;
        }

        $keys = array_map('trim', explode(',', strtoupper($filter)));
        return array_intersect_key($rates, array_flip($keys));
    }
}