<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tender_data".
 *
 * @property int $id
 * @property string $tender_id
 * @property string|null $tender_data
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class TenderData extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tender_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tender_id'], 'required'],
            [['tender_data'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['tender_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tender_id' => Yii::t('app', 'Tender ID'),
            'tender_data' => Yii::t('app', 'Tender Data'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTender(): \yii\db\ActiveQuery
	{
		return $this->hasOne(Tender::className(), ['tender_id' => 'tender_id'])->inverseOf('tenderData');
	}

    /**
     * {@inheritdoc}
     * @return TenderDataQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new TenderDataQuery(get_called_class());
    }
}
