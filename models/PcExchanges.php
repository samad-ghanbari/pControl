<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.exchanges".
 *
 * @property int $id
 * @property int $project_id
 * @property int $area
 * @property string $name
 * @property string|null $abbr
 * @property int $type
 * @property int|null $center_id
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property string|null $address
 * @property string|null $position
 * @property bool $done
 * @property int|null $modifier_id
 * @property int|null $register_ts
 * @property int|null $modified_ts
 * @property int|null $phase
 * @property int|null $weight
 */
class PcExchanges extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.exchanges';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project_id', 'area', 'name', 'type'], 'required'],
            [['project_id', 'area', 'type', 'center_id', 'modifier_id', 'register_ts', 'modified_ts', 'phase', 'weight'], 'default', 'value' => null],
            [['project_id', 'area', 'type', 'center_id', 'modifier_id', 'register_ts', 'modified_ts', 'phase', 'weight'], 'integer'],
            [['done'], 'boolean'],
            [['name', 'site_id', 'kv_code'], 'string', 'max' => 200],
            [['abbr'], 'string', 'max' => 10],
            [['address'], 'string', 'max' => 1024],
            [['position'], 'string', 'max' => 100],
            [['project_id', 'area', 'name'], 'unique', 'targetAttribute' => ['project_id', 'area', 'name']],
            [['area', 'center_id', 'name', 'kv_code'], 'unique', 'targetAttribute' => ['area', 'center_id', 'name', 'kv_code']],
            [['center_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcExchanges::className(), 'targetAttribute' => ['center_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['modifier_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcUsers::className(), 'targetAttribute' => ['modifier_id' => 'id']],
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
            'center_id' => 'مرکز',
            'site_id' => 'شناسه سایت',
            'kv_code' => 'کد کافو',
            'address' => 'آدرس',
            'position' => 'موقعیت',
            'done' => 'اتمام کار',
            'modifier_id' => 'ویراستار',
            'register_ts' => 'زمان ثبت',
            'modified_ts' => 'زمان ویرایش',
            'phase' => 'فاز',
            'weight' => 'وزن',
        ];
    }
}
