<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.lom".
 *
 * @property int $id
 * @property int $project_id
 * @property string $equipment
 * @property int $quantity
 * @property string|null $description
 * @property int $area2
 * @property int $area3
 * @property int $area4
 * @property int $area5
 * @property int $area6
 * @property int $area7
 * @property int $area8
 */
class PcLom extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.lom';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'equipment', 'quantity'], 'required'],
            [['project_id', 'quantity', 'area2', 'area3', 'area4', 'area5', 'area6', 'area7', 'area8'], 'default', 'value' => null],
            [['project_id', 'quantity', 'area2', 'area3', 'area4', 'area5', 'area6', 'area7', 'area8'], 'integer'],
            [['equipment'], 'string', 'max' => 512],
            [['description'], 'string', 'max' => 1024],
            [['project_id', 'equipment'], 'unique', 'targetAttribute' => ['project_id', 'equipment']],
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
            'equipment' => 'تجهیز',
            'quantity' => 'تعداد کل',
            'area2' => 'منطقه ۲',
            'area3' => 'منطقه ۳',
            'area4' => 'منطقه ۴',
            'area5' => 'منطقه ۵',
            'area6' => 'منطقه ۶',
            'area7' => 'منطقه ۷',
            'area8' => 'منطقه ۸',
            'description' => 'توضیحات',
        ];
    }
}
