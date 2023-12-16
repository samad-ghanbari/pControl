<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.records".
 *
 * @property int $id
 * @property int $project_id
 * @property int $exchange_id
 * @property int $op_id
 * @property string|null $op_value
 */
class PcRecords extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.records';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'exchange_id', 'op_id'], 'required', 'message'=>''],
            [['project_id', 'exchange_id', 'op_id'], 'default', 'value' => null],
            [['project_id', 'exchange_id', 'op_id'], 'integer'],
            [['op_value'], 'string', 'max' => 1024],
            [['exchange_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcExchanges::className(), 'targetAttribute' => ['exchange_id' => 'id']],
            [['op_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcOperations::className(), 'targetAttribute' => ['op_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'پروژه',
            'exchange_id' => 'مرکز / سایت',
            'op_id' => 'ویژگی',
            'op_value' => 'مقدار ویژگی',
        ];
    }
}
