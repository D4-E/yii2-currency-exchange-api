<?php

namespace tests\functional;

use Yii;
use Codeception\Util\HttpCode;

/**
 * @covers \app\handlers\ConvertHandler
 */
class ConvertCest
{
    /** @var string */
    private string $token;

    public function _before(\FunctionalTester $I): void
    {
        $this->token = Yii::$app->params['apiToken'];

        $I->haveHttpHeader('Authorization', "Bearer {$this->token}");
        $I->haveHttpHeader('Content-Type', 'application/json');
    }

    /** Успешный обмен USD → BTC */
    public function usdToBtc(\FunctionalTester $I): void
    {
        $I->sendPost('?method=convert', json_encode([
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => '100.00',
        ]));
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['status' => 'success']);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'currency_from' => 'string',
                'currency_to' => 'string',
                'value' => 'string|float',
                'converted_value' => 'string|float',
                'rate' => 'string|float',
            ],
        ]);
    }

    /** Ошибка: value < 0.01 */
    public function minAmountError(\FunctionalTester $I): void
    {
        $I->sendPost('?method=convert', json_encode([
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => '0.001',
        ]));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['status' => 'error']);
    }

    /** Ошибка: неподдерживаемая валюта */
    public function unsupportedCurrency(\FunctionalTester $I): void
    {
        $I->sendPost('?method=convert', json_encode([
            'currency_from' => 'DOG',
            'currency_to' => 'USD',
            'value' => '1.00',
        ]));
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseContainsJson(['status' => 'error']);
    }
}
