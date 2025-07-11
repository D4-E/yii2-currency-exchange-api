<?php

namespace app\handlers;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use yii\web\Request;
use yii\web\BadRequestHttpException;
use app\services\RateService;
use app\models\ConvertForm;
use yii\helpers\Json;

/**
 * Обрабатывает метод convert.
 *
 * Алгоритм:
 * 1. Валидация входных данных через {@see ConvertForm}.  
 * 2. Получаем актуальные курсы у {@see RateService}.  
 * 3. Выполняем конвертацию, сохраняя точность:
 *    * USD → X — 10 знаков после точки;
 *    * X → USD — 2 знака;
 *    * X → Y   — 10 знаков.
 *
 * @package app\handlers
 */
final class ConvertHandler implements MethodHandlerInterface
{
    private const MIN_AMOUNT = '0.01';

    public function __construct(private RateService $service) {}

    public function handle(Request $request): array
    {
        /* ---------- 1. Валидация входа ---------- */
        $form = new ConvertForm();
        $form->load(Json::decode($request->getRawBody()), '');

        if (!$form->validate()) {
            throw new BadRequestHttpException(Json::encode($form->errors));
        }

        $from = strtoupper($form->currency_from);
        $to = strtoupper($form->currency_to);
        $value = (string) $form->value;

        /* ---------- 2. Минимальная сумма ---------- */
        if (bccomp($value, self::MIN_AMOUNT, 10) < 0) {
            throw new BadRequestHttpException('Minimum exchange amount is 0.01');
        }

        /* ---------- 3. Курсы ---------- */
        $rates = $this->service->getRates();

        if (!isset($rates[$from], $rates[$to])) {
            throw new BadRequestHttpException('Unsupported currency');
        }

        $rateFrom = BigDecimal::of($rates[$from]); // USD за 1 единицу $from
        $rateTo = BigDecimal::of($rates[$to]); // USD за 1 единицу $to
        
        /* ---------- 4. Конвертация ---------- */
        if ($to === 'USD') {// Если конвертируем ИЗ валюты В USD
            $converted = BigDecimal::of($value)
                ->multipliedBy($rateFrom)
                ->toScale(2, RoundingMode::HALF_UP);
            $exchangeRate = $rateFrom;
        } elseif ($from === 'USD') {// Если конвертируем ИЗ USD В валюту
            $converted = BigDecimal::of($value)
                ->dividedBy($rateTo, 10, RoundingMode::HALF_UP)
                ->toScale(10, RoundingMode::HALF_UP);
            $exchangeRate = BigDecimal::of('1')->dividedBy($rateTo, 10, RoundingMode::HALF_UP);
        } else {// Если конвертируем между двумя не-USD валютами
            $usdAmount = BigDecimal::of($value)->multipliedBy($rateFrom);
            $converted = $usdAmount
                ->dividedBy($rateTo, 10, RoundingMode::HALF_UP)
                ->toScale(10, RoundingMode::HALF_UP);
            $exchangeRate = $rateFrom->dividedBy($rateTo, 10, RoundingMode::HALF_UP);
        }
        
        return [
            'status' => 'success',
            'code' => 200,
            'data' => [
                'currency_from' => $from,
                'currency_to' => $to,
                'value' => $value,
                'rate' => (string) $exchangeRate,
                'converted_value' => (string) $converted,
            ],
        ];
    }
}