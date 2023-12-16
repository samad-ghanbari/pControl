<?php

namespace app\models;
use yii\base\Model;

/**
 *
 * @property int|null $id
 * @property int $area
 * @property string|null $name
 * @property string|null $abbr
 * @property string|null $type
 * @property string|null $center
 * @property int|null $center_id
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property string|null $address
 * @property string|null $position
 * @property int|null $phase
 */
class XlsModel extends Model
{
    public $id,$area,$name,$abbr,$type,$center,$center_id, $site_id,$kv_code,$address,$position,$phase, $lom, $done;

    public function rules()
    {
        return [
            [['area', 'lom'], 'required'],
            [['id', 'center_id', 'phase'], 'default', 'value' => null],
            [['id', 'area', 'center_id', 'phase', 'type'], 'integer'],
            [['abbr', 'site_id', 'kv_code', 'center', 'position'], 'string', 'max' => 100],
            [['address', 'lom'], 'string', 'max' => 512],
            [['done'], 'boolean', 'default'=>false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'area' => 'منطقه',
            'name' => 'نام سایت/مرکز',
            'abbr' => 'نام اختصار سایت/مرکز',
            'type' => 'نوع سایت/مرکز',
            'center' => 'نام مرکز اصلی',
            'center_id' => 'Center ID',
            'site_id' => 'شناسه سایت',
            'kv-code' => 'کد کافو',
            'address' => 'آدرس',
            'position' => 'موقعیت',
            'phase' => 'فاز',
            'lom'=>'برآورد تجهیزات',
            'done'=>'عملیات ورود'
        ];
    }
}
