<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.choices".
 *
 * @property int $id
 * @property int $op_id
 * @property string $choice
 * @property int $choice_weight
 * @property bool $default
 */
class PcChoices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.choices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['op_id', 'choice'], 'required', 'message' => ''],
            [['op_id', 'choice_weight'], 'default', 'value' => null],
            [['op_id', 'choice_weight'], 'integer'],
            [['default'], 'boolean'],
            [['choice'], 'string', 'max' => 200],
            [['op_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcOperations::className(), 'targetAttribute' => ['op_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'op_id' => 'Op ID',
            'choice' => 'آیتم انتخابی',
            'choice_weight' => 'وزن آیتم انتخاب',
            'default' => 'آیتم پیشفرض',
        ];
    }
}
