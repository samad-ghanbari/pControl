<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.users_temp".
 *
 * @property int $id
 * @property string $name
 * @property string $lastname
 * @property string $nid
 * @property string $employee_code
 * @property string $office
 * @property string $post
 * @property string $tel
 * @property int|null $area
 * @property string|null $center
 * @property int|null $exchange_id
 */
class PcUsersTemp extends \yii\db\ActiveRecord
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
        return 'pc.users_temp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'name', 'lastname', 'nid', 'employee_code', 'office', 'post', 'tel'], 'required'],
            [['id', 'area', 'exchange_id'], 'default', 'value' => null],
            [['id', 'area', 'exchange_id'], 'integer'],
            [['name', 'lastname', 'post'], 'string', 'max' => 100],
            [['nid', 'employee_code', 'center'], 'string', 'max' => 20],
            [['office'], 'string', 'max' => 200],
            [['tel'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'lastname' => 'Lastname',
            'nid' => 'Nid',
            'employee_code' => 'Employee Code',
            'office' => 'Office',
            'post' => 'Post',
            'tel' => 'Tel',
            'area' => 'Area',
            'center' => 'Center',
            'exchange_id' => 'Exchange ID',
        ];
    }
}
