<?php

use hipanel\assets\CheckboxStyleAsset;
use hiqdev\yii2\cart\widgets\QuantityCell;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = Yii::t('cart', 'Cart');
$this->params['breadcrumbs'][] = $this->title;

/** @var \yii\web\View $this */
/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var \hiqdev\yii2\cart\ShoppingCart $cart */
?>

<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa fa-shopping-cart"></i> &nbsp;
                <?= Yii::t('cart', 'Your order') ?>:
                &nbsp; <?= Yii::t('cart', '{0, plural, one{# position} other{# positions}}', $cart->count) ?>
                <small class="pull-right"><?= Yii::t('cart', 'Date') ?>
                    : <?= Yii::$app->formatter->asDate(new DateTime()) ?></small>
            </h2>
        </div>
    </div>

    <!-- Table row -->
    <div class="row">
        <div class="col-xs-12 table-responsive">
            <?php
            echo GridView::widget([
                'dataProvider' => $dataProvider,
                'layout' => '{items}',
                'rowOptions' => function ($model, $key, $index, $grid) {
                    return $model->getRowOptions($key, $index, $grid);
                },
                'columns' => [
                    [
                        'attribute' => 'no',
                        'label' => '#',
                        'value' => function ($model) {
                            static $no;

                            return ++$no;
                        },
                        'headerOptions' => ['width' => '4%', 'style' => 'text-align: center'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                    ],
                    [
                        'attribute' => 'name',
                        'format' => 'raw',
                        'label' => Yii::t('cart', 'Description'),
                        'contentOptions' => ['style' => 'vertical-align: middle'],
                        'value' => function ($model) {
                            /** @var \hiqdev\yii2\cart\CartPositionTrait $model */
                            return $model->renderDescription();
                        },
                    ],
                    [
                        'attribute' => 'quantity',
                        'label' => Yii::t('cart', 'Quantity'),
                        'value' => function ($model, $key, $index, $column) {
                            return QuantityCell::widget(['model' => $model]); //, 'type' => 'number'
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'price',
                        'label' => Yii::t('cart', 'Price'),
                        'contentOptions' => ['style' => 'vertical-align: middle;white-space: nowrap;'],
                        'value' => function ($model) use ($cart) {
                            return $cart->formatCurrency($model->cost, $model->currency);
                        },
                    ],
                    'actions' => [
                        'class' => ActionColumn::class,
                        'template' => '{remove}',
                        'headerOptions' => ['width' => '4%'],
                        'contentOptions' => ['style' => 'text-align: center; vertical-align: middle;'],
                        'buttons' => [
                            'remove' => function ($url, $model, $key) {
                                return Html::a('<i class="fa fa-times text-danger"></i>', ['remove', 'id' => $model->id]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>

    <div class="row">
        <!-- accepted payments column -->
        <div class="col-xs-6">
            <?= $module->paymentMethods ?>
        </div>
        <!-- /.col -->
        <div class="col-xs-6">
            <p class="lead"><?= Yii::t('cart', 'Amount due') ?>:</p>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                    <tr>
                        <th style="width:30%"><?= Yii::t('cart', 'Subtotal') ?>:</th>
                        <td style="width:30%" align="right"><?= $cart->formatCurrency($cart->subtotal) ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th><?= Yii::t('cart', 'Discount') ?>:</th>
                        <td align="right"><?= $cart->formatCurrency($cart->discount) ?></td>
                        <td></td>
                    </tr>
                    <tr style="font-size:130%;font-weight:bold">
                        <th><?= Yii::t('cart', 'Total') ?>:</th>
                        <td align="right"><?= $cart->formatCurrency($cart->total) ?></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- this row will not appear when printing -->
    <div class="row no-print">
        <div class="col-xs-4">
            <?= Html::a('<i class="fa fa-trash"></i> ' . Yii::t('cart', 'Clear cart'), ['clear'], [
                'class' => 'btn btn-default',
                'data-ga-clear' => true,
            ]); ?>
        </div>
        <?php if (!empty($cart->positions)) : ?>
            <div class="col-xs-8">
                <span class="pull-right">
                    <?php if (class_exists('CheckboxStyleAsset')) : ?>
                        <?php CheckboxStyleAsset::register($this) ?>
                    <?php endif ?>
                    <?php $this->registerJs("
                            jQuery('#term-of-use').attr('checked', false);
                            jQuery('#make-order-button').addClass('disabled');
                            jQuery('#term-of-use').on('click', function() {
                                jQuery('#make-order-button').toggleClass('disabled');
                            });"); ?>
                    <label>
                        <input class="option-input" type="checkbox" id="term-of-use">
                        <?php if ($module->termsPage != '') : ?>
                            &nbsp;<?= Yii::t('cart', 'I have read and agree to the {termsLink}', ['termsLink' => Html::a(Yii::t('cart', 'terms of use'), $module->termsPage)]) ?>
                        <?php else: ?>
                            &nbsp;<?= Yii::t('cart', 'I have read and agree to the {termsLink}', ['termsLink' => Yii::t('cart', 'terms of use')]) ?>
                        <?php endif; ?>
                    </label> &nbsp; &nbsp;
                    <?php if ($module->orderButton) : ?>
                        <?= $module->orderButton ?>
                    <?php else : ?>
                        <?= Html::a('<i class="fa fa-credit-card"></i> ' . Yii::t('cart', 'Make order'), $module->orderPage, [
                            'id' => 'make-order-button',
                            'class' => 'btn btn-success',
                            'data-ga-confirm' => true,
                        ]); ?>
                    <?php endif ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
    $this->registerJS(<<<JS
    hipanel.googleAnalytics($('[data-ga-confirm]'), {
        'category': 'cart',
        'action': 'confirm'
    });
    hipanel.googleAnalytics($('[data-ga-clear]'), {
        'category': 'cart',
        'action': 'clear'
    });
JS
);
