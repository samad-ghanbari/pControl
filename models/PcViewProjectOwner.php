<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_project_owner".
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property int|null $project_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $office
 * @property string|null $post
 * @property string|null $tel
 */
class PcViewProjectOwner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_project_owner';
    }

    public static function primaryKey()
    {
        return ['id'];
    }


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'project_id'], 'default', 'value' => null],
            [['id', 'user_id', 'project_id'], 'integer'],
            [['name', 'lastname', 'post'], 'string', 'max' => 100],
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
            'user_id' => 'User ID',
            'project_id' => 'Project ID',
            'name' => 'نام',
            'lastname' => 'نام خانوادگی',
            'office' => 'اداره کل',
            'post' => 'سمت',
            'tel' => 'شماره تماس',
        ];
    }
}
