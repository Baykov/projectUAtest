<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\models\search\TenderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Tenders');
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .pagination{justify-content: center;}
    .pagination>li{padding: 10px;}
    .pagination>li.active>a{color: #fff;background-color: darkgray;padding: 5px;}
</style>
<div class="tender-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<?php Pjax::begin(); ?>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'columns' => [
			'id',
			'tender_id',
			[
				'class'=>'yii\grid\DataColumn',
				'attribute'=>'tender_description',
				'value' => function ($model) {
					return $model->tenderInfo->description ?? '-';
				},
				'contentOptions' =>  function($model) {
					return ['style' => "text-align:center; white-space: normal"];
				},
			],
			[
				'class'=>'yii\grid\DataColumn',
				'attribute'=>'tender_value_amount',
				'value' => function ($model) {
					return $model->tenderInfo->value->amount ?? '0';
				},
				'contentOptions' =>  function($model) {
					return ['style' => "text-align:center; white-space: normal"];
				},
			],
			'date_modified',
		],
	]); ?>

	<?php Pjax::end(); ?>

</div>
