<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Tender */

$this->title = Yii::t('app', 'Create Tender');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Tenders'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tender-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
