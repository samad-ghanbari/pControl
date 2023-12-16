<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_choices".
 *
 * @property int|null $id
 * @property int|null $op_id
 * @property int|null $project_id
 * @property string|null $operation
 * @property int|null $op_weight
 * @property int|null $type_id
 * @property string|null $choice
 * @property int|null $choice_weight
 * @property bool|null $default
 */
class PcViewChoices extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_choices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'op_id', 'project_id', 'op_weight', 'type_id', 'choice_weight'], 'default', 'value' => null],
            [['id', 'op_id', 'project_id', 'op_weight', 'type_id', 'choice_weight'], 'integer'],
            [['default'], 'boolean'],
            [['operation'], 'string', 'max' => 512],
            [['choice'], 'string', 'max' => 200],
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
            'project_id' => 'پروژه',
            'operation' => 'ویژگی',
            'op_weight' => 'وزن ویژگی',
            'type_id' => 'نوع ویژگی',
            'choice' => 'آیتم انتخاب',
            'choice_weight' => 'وزن آیتم انتخاب',
            'default' => 'آیتم پیشفرض',
        ];
    }
}
