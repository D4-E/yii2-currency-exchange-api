<?php

namespace tests\unit\models;

use app\models\ConvertForm;
use Codeception\Test\Unit;
use yii\helpers\Json;

/**
 * @covers \app\models\ConvertForm
 */
class ConvertFormTest extends Unit
{
    /** Валидный кейс: USD → BTC, 100.00 */
    public function testValidDataPasses(): void
    {
        $form = new ConvertForm([
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => 100.00,
        ]);

        $this->assertTrue(
            $form->validate(),
            'Ошибки: ' . Json::encode($form->getErrors())
        );
    }

    /** Ошибка: сумма меньше минимума 0.01 */
    public function testMinAmountFails(): void
    {
        $form = new ConvertForm([
            'currency_from' => 'USD',
            'currency_to' => 'BTC',
            'value' => 0.001,
        ]);

        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('value', $form->getErrors());
    }

    /** Ошибка: одинаковые валюты (правило compare) */
    public function testSameCurrencyFails(): void
    {
        $form = new ConvertForm([
            'currency_from' => 'USD',
            'currency_to' => 'USD',
            'value' => 10.00,
        ]);

        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('currency_to', $form->getErrors());
    }

    /** Ошибка: код валюты не проходит RegExp */
    public function testInvalidCodeFails(): void
    {
        $form = new ConvertForm([
            'currency_from' => 'US',
            'currency_to' => 'BTC',
            'value' => 10.00,
        ]);

        $this->assertFalse($form->validate());
        $this->assertArrayHasKey('currency_from', $form->getErrors());
    }
}
