<?php

namespace app\components;

use yii\filters\auth\HttpBearerAuth;
use yii\web\ForbiddenHttpException;
use Yii;

/**
 * Аутентификатор API-методов по схеме Bearer.
 *
 * > **Формат токена** — 64 символа Base62 + «-»/«_».
 * > Валидным считается только точное совпадение с `Yii::$app->params['apiToken']`.
 *
 * @package app\components
 */
final class ApiBearerAuth extends HttpBearerAuth
{
    /**
     * Переопределяем проверку заголовка «Authorization».
     *
     * @param User      $user      компонент пользователя (не используется, но сохраняем сигнатуру)
     * @param Request   $request   HTTP-запрос
     * @param Response  $response  HTTP-ответ
     *
     * @return bool `true` — аутентификация успешна
     *
     * @throws ForbiddenHttpException если токен отсутствует или некорректен
     */
    public function authenticate($user, $request, $response)
    {
        $header = $request->getHeaders()->get('Authorization');

        if (
            !$header
            || !preg_match('/^Bearer\s+([\w\-_]{64})$/', $header, $m)
            || $m[1] !== Yii::$app->params['apiToken']
        ) {
            throw new ForbiddenHttpException('Invalid token');
        }

        return true;
    }
    
}
