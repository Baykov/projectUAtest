<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[Tender]].
 *
 * @see Tender
 */
class TendersQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Tender[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Tender|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
