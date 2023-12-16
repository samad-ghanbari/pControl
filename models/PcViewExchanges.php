<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_exchanges".
 *
 * @property int|null $id
 * @property int|null $project_id
 * @property int|null $area
 * @property string|null $name
 * @property string|null $abbr
 * @property int|null $type
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
 * @property int|null $project_weight
 * @property int|null $weight
 */
class PcViewExchanges extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_exchanges';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'project_id', 'area', 'type', 'center_id', 'modifier_id', 'modified_ts', 'register_ts', 'phase', 'project_weight', 'weight'], 'default', 'value' => null],
            [['id', 'project_id', 'area', 'type', 'center_id', 'modifier_id', 'modified_ts', 'register_ts', 'phase', 'project_weight', 'weight'], 'integer'],
            [['done'], 'boolean'],
            [['name', 'center_name', 'site_id', 'kv_code', 'modifier_office'], 'string', 'max' => 200],
            [['abbr', 'center_abbr'], 'string', 'max' => 10],
            [['address'], 'string', 'max' => 1024],
            [['position', 'modifier_name', 'modifier_lastname'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'شناسه',
            'project_id' => 'پروژه',
            'area' => 'منطقه',
            'name' => 'نام',
            'abbr' => 'اختصار',
            'type' => 'نوع',
            'center_id' => 'شناسه مرکز',
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
            'project_weight' => 'وزن پروژه',
            'weight' => 'وزن',
        ];
    }
}
