<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var \yii\web\View $this */

$this->title = 'Currency Exchange API';
?>
<div class="site-index" style="padding:2rem; background: var(--bg-panel); max-width:800px; margin: auto; border-radius:12px; box-shadow:0 4px 12px var(--shadow); backdrop-filter:blur(8px);">
    <h1 style="font-family:'Inter',sans-serif; font-size:2.5rem; color:var(--accent);"><?= \yii\helpers\Html::encode($this->title) ?></h1>
    <p style="color:var(--text-main); margin-bottom:1.5rem; font-family:'Inter',sans-serif; font-size:1rem;">
        Этот API позволяет получать текущие курсы валют с учётом комиссии 2% и выполнять конвертацию между валютами.
    </p>

    <div class="endpoint" style="background:#222; border-radius:8px; padding:1rem; margin-bottom:1rem; box-shadow:0 2px 8px var(--shadow);">
        <h2 style="color:var(--accent); font-size:1.5rem; margin-bottom:0.5rem;">GET /api/v1?method=rates</h2>
        <p style="color:var(--text-secondary); font-family:'Inter',sans-serif; font-size:0.9rem;">
            Возвращает список всех курсов относительно USD с учётом 2% комиссии, отсортированных по возрастанию.
        </p>
        <code style="display:block; background:#333; color:var(--text-secondary); padding:0.5rem; border-radius:4px; margin:0.5rem 0; font-family:monospace; font-size:0.85rem;">
            <?= Html::encode('curl -H "Authorization: Bearer <token>" "' . Url::to(['/api/v1', 'method' => 'rates', 'currency' => 'USD,EUR'], true) . '"') ?>
        </code>
        <ul style="color:var(--text-main); font-family:'Inter',sans-serif; font-size:0.9rem;">
            <li><code>currency</code> — необязательный, через запятую список валют (USD,RUB,EUR).</li>
        </ul>
    </div>

    <div class="endpoint" style="background:#222; border-radius:8px; padding:1rem; margin-bottom:1rem; box-shadow:0 2px 8px var(--shadow);">
        <h2 style="color:var(--accent); font-size:1.5rem; margin-bottom:0.5rem;">POST /api/v1?method=convert</h2>
        <p style="color:var(--text-secondary); font-family:'Inter',sans-serif; font-size:0.9rem;">
            Конвертирует сумму из одной валюты в другую с учётом 2% комиссии.
        </p>
        <code style="display:block; background:#333; color:var(--text-secondary); padding:0.5rem; border-radius:4px; margin:0.5rem 0; font-family:monospace; font-size:0.85rem;">
            <?= Html::encode("curl -X POST -H 'Authorization: Bearer <token>' -H 'Content-Type: application/json' -d '{\"currency_from\":\"USD\",\"currency_to\":\"BTC\",\"value\":100}' '" . Url::to(['/api/v1', 'method'=>'convert'], true) . "'") ?>
        </code>
        <ul style="color:var(--text-main); font-family:'Inter',sans-serif; font-size:0.9rem;">
            <li><code>currency_from</code> — код валюты-источника.</li>
            <li><code>currency_to</code> — код валюты-приёмника.</li>
            <li><code>value</code> — сумма (минимум 0.01).</li>
        </ul>
    </div>
</div>
