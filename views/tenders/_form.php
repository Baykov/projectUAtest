<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Tender */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tender-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'tender_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'date_modified')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
