<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Главная страница.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('index');
    }
}
