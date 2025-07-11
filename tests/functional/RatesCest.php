<?php

namespace tests\functional;

use Yii;
use Codeception\Util\HttpCode;

/**
 * @covers \app\handlers\RatesHandler
 */
class RatesCest
{
    /** @var string */
    private string $token;

    public function _before(\FunctionalTester $I): void
    {
        $this->token = Yii::$app->params['apiToken'];

        $I->haveHttpHeader('Authorization', "Bearer {$this->token}");
        $I->haveHttpHeader('Content-Type', 'application/json');
    }

    /** GET /api/v1?method=rates  → полный список */
    public function listAll(\FunctionalTester $I): void
    {
        $I->sendGet('', ['method' => 'rates']);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['status' => 'success']);
        $I->seeResponseMatchesJsonType(['data' => 'array']);
        $I->seeResponseJsonMatchesJsonPath('$.data.USD');
        $I->seeResponseJsonMatchesJsonPath('$.data.BTC');
        $I->seeResponseJsonMatchesJsonPath('$.data.ETH');
    }

    /** GET /api/v1?method=rates&currency=USD,BTC  → фильтр по валютам */
    public function filterCurrencies(\FunctionalTester $I): void
    {
        $I->sendGet('', [
            'method'   => 'rates',
            'currency' => 'USD,BTC',
        ]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseMatchesJsonType([
            'data' => ['USD' => 'string', 'BTC' => 'string'],
        ]);
        $I->dontSeeResponseJsonMatchesJsonPath('$.data.ETH');
    }
}
