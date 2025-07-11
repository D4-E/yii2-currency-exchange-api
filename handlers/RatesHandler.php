<?php

namespace app\handlers;

use yii\web\Request;
use app\services\RateService;

/**
 * Обрабатывает метод rates.
 *
 * Поддерживает необязательный query-параметр
 * currency=USD,BTC ― тогда возвращаются
 * только перечисленные коды.
 *
 * @package app\handlers
 */
final class RatesHandler implements MethodHandlerInterface
{
    public function __construct(private RateService $service) {}

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request): array
    {
        /** @var string $filter CSV-список кодов валют */
        $filter = $request->get('currency', '');
        $data = $this->service->getRates((string)$filter);

        return [
            'status' => 'success',
            'code' => 200,
            'data' => $data,
        ];
    }
}