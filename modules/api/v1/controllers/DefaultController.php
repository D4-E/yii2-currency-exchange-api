<?php

namespace app\modules\api\v1\controllers;

use app\components\ApiBearerAuth;
use app\handlers\ConvertHandler;
use app\handlers\MethodHandlerInterface;
use app\handlers\RatesHandler;
use app\services\RateService;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\Request;

/**
 * Единая точка входа `/api/v1`.
 *
 * Согласно ТЗ, имя метода передаётся query-параметром `method`.
 * Контроллер пробрасывает запрос в соответствующий *handler* и
 * возвращает ассоциативный массив, который Yii автоматически
 * сериализует в JSON.
 *
 * @package app\modules\api\v1\controllers
 */
class DefaultController extends Controller
{
    /**
     * Подключаем Bearer-аутентификацию.
     *
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => ApiBearerAuth::class,
        ];

        return $behaviors;
    }

    /**
     * Обработчик GET|POST /api/v1.
     *
     * @return array JSON-структура, совместимая с форматом API.
     *
     * @throws BadRequestHttpException если метод не указан
     *                                 или неизвестен.
     */
    public function actionIndex()
    {
        $method = Yii::$app->request->get('method');
        if (empty($method)) {
            throw new BadRequestHttpException('Method not specified');
        }

        $handler = $this->resolveHandler($method, Yii::$app->request);
        return $handler->handle(Yii::$app->request);
    }

    /**
     * Определяет обработчик по имени метода.
     *
     * @param string  $method  Значение параметра `method`.
     * @param Request $request Текущий HTTP-запрос
     *
     * @return MethodHandlerInterface
     *
     * @throws BadRequestHttpException если метод не поддерживается
     */
    private function resolveHandler(string $method, Request $request): MethodHandlerInterface
    {
        $rateService = new RateService();

        $handlers = [
            'rates' => new RatesHandler($rateService), // GET
            'convert' => new ConvertHandler($rateService), // POST
        ];

        if (!isset($handlers[$method])) {
            throw new BadRequestHttpException("Unknown method: {$method}");
        }

        // Дополнительная проверка: метод HTTP ↔ имя handler’а
        if ($method === 'rates' && !$request->getIsGet()) {
            throw new BadRequestHttpException('rates method must be requested with GET');
        }
        if ($method === 'convert' && !$request->getIsPost()) {
            throw new BadRequestHttpException('convert method must be requested with POST');
        }

        return $handlers[$method];
    }
}
