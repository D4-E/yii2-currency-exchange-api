<?php

namespace tests\unit\services;

use app\services\RateService;
use Codeception\Test\Unit;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

/**
 * @covers \app\services\RateService
 */
class RateServiceTest extends Unit
{
    /** Проверяем 2 % комиссии и сортировку */
    public function testGetRatesAddsTwoPercentAndSorts(): void
    {
        $mockResponse = $this->makeEmpty(Response::class, [
            'getIsOk' => fn () => true,
            'getData' => fn () => [
                'merchant' => [
                    'USD' => ['USD' => 1],
                    'BTC' => ['USD' => 60000],
                    'ETH' => ['USD' => 3000],
                ],
            ],
        ]);

        $mockRequest = $this->makeEmpty(Request::class, [
            'send' => fn () => $mockResponse,
        ]);

        $mockClient = $this->make(Client::class, [
            'get' => fn (string $url) => $mockRequest,
        ]);

        $rates = (new RateService($mockClient))->getRates();

        $this->assertSame(['USD', 'ETH', 'BTC'], array_keys($rates));
        $this->assertEquals('1.0200000000',    $rates['USD']);
        $this->assertEquals('3060.0000000000', $rates['ETH']);
        $this->assertEquals('61200.0000000000', $rates['BTC']);
    }

    /** При ошибке внешнего API бросается RuntimeException */
    public function testThrowsOnBadApiResponse(): void
    {
        $badResponse = $this->makeEmpty(Response::class, [
            'getIsOk' => fn () => false,
            'getData' => fn () => [],
        ]);

        $badRequest = $this->makeEmpty(Request::class, ['send' => fn () => $badResponse]);
        $mockClient = $this->make(Client::class, ['get' => fn () => $badRequest]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('External API error or unexpected response');

        (new RateService($mockClient))->getRates();
    }

    /** Фильтр currency=… оставляет только запрошенные коды */
    public function testFilterKeepsOnlyRequestedCurrencies(): void
    {
        $response = $this->makeEmpty(Response::class, [
            'getIsOk' => fn () => true,
            'getData' => fn () => [
                'merchant' => [
                    'USD' => ['USD' => 1],
                    'BTC' => ['USD' => 60000],
                    'ETH' => ['USD' => 3000],
                ],
            ],
        ]);
        $request = $this->makeEmpty(Request::class, ['send' => fn () => $response]);
        $client = $this->make(Client::class, ['get' => fn () => $request]);

        $rates = (new RateService($client))->getRates(' usd ,  bTc ');

        $this->assertSame(['USD', 'BTC'], array_keys($rates));
        $this->assertArrayNotHasKey('ETH', $rates);
    }
}
