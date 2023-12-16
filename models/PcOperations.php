<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.operations".
 *
 * @property int $id
 * @property int $project_id
 * @property string $operation
 * @property int $type_id
 * @property int $priority
 * @property int $op_weight
 * @property int $design_role
 * @property int $install_role
 * @property int $test_role
 * @property int $district_role
 * @property int $operation_role
 * @property int $it_role
 * @property int $planning_role
 */
class PcOperations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.operations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'operation', 'type_id'], 'required'],
            [['project_id', 'type_id', 'priority', 'op_weight', 'design_role', 'install_role', 'test_role', 'district_role', 'operation_role', 'it_role', 'planning_role'], 'default', 'value' => null],
            [['project_id', 'type_id', 'priority', 'op_weight', 'design_role', 'install_role', 'test_role', 'district_role', 'operation_role', 'it_role', 'planning_role'], 'integer'],
            [['operation'], 'string', 'max' => 512],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcOpType::className(), 'targetAttribute' => ['type_id' => 'id']],
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
            'operation' => 'ویژگی',
            'type_id' => 'نوع ویژگی',
            'priority' => 'اولویت ویژگی',
            'op_weight' => 'وزن ویژگی',
            'design_role' => 'حیطه عملکرد طراحی',
            'install_role' => 'حیطه عملکرد نصب',
            'test_role' => 'حیطه عملکرد نظارت',
            'district_role' => 'حیطه عملکرد منطقه',
            'operation_role' => 'حیطه عملکرد عملیات شبکه',
            'it_role' => 'حیطه عملکرد فناوری',
            'planning_role' => 'حیطه عملکرد برنامه‌ریزی',
        ];
    }
}
