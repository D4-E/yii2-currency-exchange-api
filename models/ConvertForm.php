<?php

namespace app\models;

use yii\base\Model;

/**
 * Форма для запроса «convert».
 *
 * Принимает JSON-тело вида
 * ```json
 * {
 *   "currency_from": "USD",
 *   "currency_to"  : "BTC",
 *   "value"        : "100.00"
 * }
 * ```
 * и выполняет валидацию входных данных.
 *
 * @property string $currency_from Код валюты-источника (3–5 заглавных A–Z)
 * @property string $currency_to   Код валюты-приёмника  (3–5 заглавных A–Z)
 * @property string $value         Сумма к обмену (строка, ≥ 0.01)
 *
 * @package app\models
 */
class ConvertForm extends Model
{
    public $currency_from;
    public $currency_to;
    public $value;

    public function rules(): array
    {
        return [
            [['currency_from', 'currency_to', 'value'], 'required'],
            ['value', 'number', 'min' => 0.01],
            [['currency_from', 'currency_to'], 'match', 'pattern' => '/^[A-Z]{3,5}$/'],
            [
                'currency_to',
                'compare',
                'compareAttribute' => 'currency_from',
                'operator' => '!=',
                'message' => 'Currencies must differ'
            ],
        ];
    }
}