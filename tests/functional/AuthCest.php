<?php

namespace tests\functional;

use Codeception\Util\HttpCode;

/**
 * @coversNothing
 *
 * Проверяем Bearer-аутентификатор:
 *  - отсутствие заголовка  → 403
 *  - неправильный токен    → 403
 */
class AuthCest
{
    public function _before(\FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
    }

    /** Доступ без токена — 403 */
    public function unauthorized(\FunctionalTester $I): void
    {
        $I->sendGet('', ['method' => 'rates']);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
        $I->seeResponseContainsJson(['status' => 'error']);
    }

    /** Токен неверный — 403 */
    public function wrongToken(\FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer wrong');
        $I->sendGet('', ['method' => 'rates']);
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);
    }
}
