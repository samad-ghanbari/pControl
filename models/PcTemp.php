<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.temp".
 *
 * @property int $id
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property int $area
 * @property string|null $center
 * @property string|null $address
 * @property string|null $position
 * @property string|null $kv
 * @property string|null $bazdid
 * @property string|null $tarhter
 * @property string|null $ttter
 * @property string|null $nasbter
 * @property string|null $tab
 * @property string|null $ttab
 * @property string|null $mesi
 * @property string|null $tf
 * @property string|null $ttf
 * @property string|null $peymankar
 * @property string|null $mjshahr
 * @property string|null $ejf
 * @property string|null $tahf
 * @property string|null $bah
 * @property string|null $adsl
 * @property string|null $vdsl
 * @property string|null $toz
 */
class PcTemp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.temp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'area'], 'required'],
            [['id', 'area'], 'default', 'value' => null],
            [['id', 'area'], 'integer'],
            [['site_id', 'kv_code', 'center', 'position'], 'string', 'max' => 100],
            [['address', 'toz'], 'string', 'max' => 512],
            [['kv', 'bazdid', 'tarhter', 'ttter', 'nasbter', 'tab', 'ttab', 'mesi', 'tf', 'ttf', 'peymankar', 'mjshahr', 'ejf', 'tahf', 'bah', 'adsl', 'vdsl'], 'string', 'max' => 50],
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
            'site_id' => 'Site ID',
            'kv_code' => 'Kv Code',
            'area' => 'Area',
            'center' => 'Center',
            'address' => 'Address',
            'position' => 'Position',
            'kv' => 'Kv',
            'bazdid' => 'Bazdid',
            'tarhter' => 'Tarhter',
            'ttter' => 'Ttter',
            'nasbter' => 'Nasbter',
            'tab' => 'Tab',
            'ttab' => 'Ttab',
            'mesi' => 'Mesi',
            'tf' => 'Tf',
            'ttf' => 'Ttf',
            'peymankar' => 'Peymankar',
            'mjshahr' => 'Mjshahr',
            'ejf' => 'Ejf',
            'tahf' => 'Tahf',
            'bah' => 'Bah',
            'adsl' => 'Adsl',
            'vdsl' => 'Vdsl',
            'toz' => 'Toz',
        ];
    }
}
