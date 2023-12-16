<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.lom_detail".
 *
 * @property int $id
 * @property int $lom_id
 * @property int $exchange_id
 * @property int $quantity
 */
class PcLomDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.lom_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['lom_id', 'exchange_id', 'quantity'], 'required'],
            [['lom_id', 'exchange_id', 'quantity'], 'default', 'value' => null],
            [['lom_id', 'exchange_id', 'quantity'], 'integer'],
            [['lom_id', 'exchange_id'], 'unique', 'targetAttribute' => ['lom_id', 'exchange_id']],
            [['exchange_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcExchanges::className(), 'targetAttribute' => ['exchange_id' => 'id']],
            [['lom_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcLom::className(), 'targetAttribute' => ['lom_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lom_id' => 'تجهیز',
            'exchange_id' => 'سایت/مرکز',
            'quantity' => 'تعداد',
        ];
    }
}
