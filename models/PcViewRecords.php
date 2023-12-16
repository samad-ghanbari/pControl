<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_records".
 *
 * @property int|null $id
 * @property int|null $project_id
 * @property int|null $exchange_id
 * @property int|null $area
 * @property string|null $name
 * @property string|null $abbr
 * @property int|null $extype
 * @property int|null $center_id
 * @property string|null $center_abbr
 * @property string|null $center_name
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property string|null $address
 * @property string|null $position
 * @property bool|null $done
 * @property int|null $modifier_id
 * @property string|null $modifier_name
 * @property string|null $modifier_lastname
 * @property string|null $modifier_office
 * @property int|null $modified_ts
 * @property int|null $register_ts
 * @property int|null $phase
 * @property int|null $op_id
 * @property string|null $operation
 * @property int|null $op_weight
 * @property int|null $project_weight
 * @property int|null $weight
 * @property int|null $priority
 * @property int|null $op_type
 * @property string|null $op_value
 */
class PcViewRecords extends \yii\db\ActiveRecord
{
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_records';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'exchange_id', 'area', 'extype', 'center_id', 'modifier_id', 'modified_ts', 'register_ts', 'phase', 'op_id', 'op_weight', 'project_weight', 'weight', 'priority', 'op_type'], 'default', 'value' => null],
            [['id', 'project_id', 'exchange_id', 'area', 'extype', 'center_id', 'modifier_id', 'modified_ts', 'register_ts', 'phase', 'op_id', 'op_weight', 'project_weight', 'weight', 'priority', 'op_type'], 'integer'],
            [['done'], 'boolean'],
            [['name', 'center_name', 'site_id', 'kv_code', 'modifier_office'], 'string', 'max' => 200],
            [['abbr', 'center_abbr'], 'string', 'max' => 10],
            [['address', 'op_value'], 'string', 'max' => 1024],
            [['position', 'modifier_name', 'modifier_lastname'], 'string', 'max' => 100],
            [['operation'], 'string', 'max' => 512],
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
            'exchange_id' => 'شناسه',
            'area' => 'منطقه',
            'name' => 'نام',
            'abbr' => 'اختصار',
            'extype' => 'نوع',
            'center_id' => 'مرکز',
            'center_abbr' => 'اختصار مرکز',
            'center_name' => 'نام مرکز',
            'site_id' => 'شناسه سایت',
            'kv_code' => 'کد کافو',
            'address' => 'آدرس',
            'position' => 'موقعیت',
            'done' => 'اتمام کار',
            'modifier_id' => 'ویراستار',
            'modifier_name' => 'نام ویراستار',
            'modifier_lastname' => 'نام خانوادگی ویراستار',
            'modifier_office' => 'اداره کل ویراستار',
            'modified_ts' => 'زمان ویرایش',
            'register_ts' => 'زمان ثبت',
            'phase' => 'فاز',
            'op_id' => 'شناسه ویژگی',
            'operation' => 'ویژگی',
            'op_weight' => 'وزن ویژگی',
            'project_weight' => 'وزن کل پروژه',
            'weight' => 'وزن',
            'priority' => 'اولویت',
            'op_type' => 'نوغ ویژگی',
            'op_value' => 'مقدار ویژگی'
        ];
    }
}
