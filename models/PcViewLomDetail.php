<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_lom_detail".
 *
 * @property int|null $id
 * @property int|null $lom_id
 * @property int|null $exchange_id
 * @property int|null $quantity
 * @property int|null $project_id
 * @property string|null $equipment
 * @property int|null $area
 * @property string|null $name
 * @property string|null $abbr
 * @property int|null $type
 */
class PcViewLomDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_lom_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'lom_id', 'exchange_id', 'quantity', 'project_id', 'area', 'type'], 'default', 'value' => null],
            [['id', 'lom_id', 'exchange_id', 'quantity', 'project_id', 'area', 'type'], 'integer'],
            [['equipment'], 'string', 'max' => 512],
            [['name'], 'string', 'max' => 200],
            [['abbr'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lom_id' => 'Lom ID',
            'exchange_id' => 'Exchange ID',
            'quantity' => 'Quantity',
            'project_id' => 'Project ID',
            'equipment' => 'Equipment',
            'area' => 'Area',
            'name' => 'Name',
            'abbr' => 'Abbr',
            'type' => 'Type',
        ];
    }
}
