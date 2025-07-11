<?php

namespace app\handlers;

use yii\web\Request;

/**
 * Interface для классов-обработчиков методов API.
 *
 * Каждый handler получает экзем­пляр {@see Request} и
 * обязан вернуть ассоциативный массив, пригодный для
 * сериализации в JSON-ответ.
 *
 * @package app\handlers
 */
interface MethodHandlerInterface
{
    /**
     * Выполняет бизнес-логику метода и формирует JSON-данные.
     *
     * @param Request $request HTTP-запрос, поступивший в ендпойнт.
     *
     * @return array Структура, которую глобальный фильтр переведёт в JSON.
     */
    public function handle(Request $request): array;
}
