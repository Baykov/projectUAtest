<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tenders".
 *
 * @property int $id
 * @property int $tender_id
 * @property string|null $date_modified
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Tender extends \yii\db\ActiveRecord
{
	/**
	 * @var mixed|null
	 */
	public $tenderInfo;

	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tenders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id'], 'required'],
            [['tender_id'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
			['date_modified', 'datetime', 'format' => 'php:Y-m-d H:i:s'],
		];
    }

    public function beforeValidate(): bool
	{
		$dateTimeObject = \DateTime::createFromFormat('Y-m-d\TH:i:s.u+e', $this->date_modified);
		if ($dateTimeObject) {
			$this->date_modified = $dateTimeObject->format('Y-m-d H:i:s');
		}
		return parent::beforeValidate();
	}

	public function afterFind()
	{
		parent::afterFind();
		if ($this->tenderData) {
			$this->tenderInfo = json_decode($this->tenderData->tender_data);
		}
	}

	/**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tender_id' => Yii::t('app', 'Tender ID'),
            'date_modified' => Yii::t('app', 'Date Modified'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTenderData(): \yii\db\ActiveQuery
	{
		return $this->hasOne(TenderData::className(), ['tender_id' => 'tender_id'])->inverseOf('tender');
	}


	/**
     * {@inheritdoc}
     * @return TendersQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TendersQuery(get_called_class());
    }
}
