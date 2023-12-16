<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.temp1000".
 *
 * @property int $id
 * @property string $center
 * @property int|null $center_id
 * @property string|null $kv_code
 * @property string|null $site_id
 * @property string|null $address
 * @property string|null $cap
 * @property string|null $fiber_path
 * @property string|null $kv_type
 * @property string|null $ert_fiber
 */
class PcTemp1000 extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.temp1000';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'center'], 'required'],
            [['id', 'center_id'], 'default', 'value' => null],
            [['id', 'center_id'], 'integer'],
            [['center', 'kv_code', 'site_id', 'cap', 'fiber_path', 'kv_type', 'ert_fiber'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 1024],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'center' => 'Center',
            'center_id' => 'Center ID',
            'kv_code' => 'Kv Code',
            'site_id' => 'Site ID',
            'address' => 'Address',
            'cap' => 'Cap',
            'fiber_path' => 'Fiber Path',
            'kv_type' => 'Kv Type',
            'ert_fiber' => 'Ert Fiber',
        ];
    }
}
