<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.op_type".
 *
 * @property int $id
 * @property string $type
 * @property string $description
 */
class PcOpType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.op_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'description'], 'required'],
            [['type', 'description'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'description' => 'Description',
        ];
    }
}
