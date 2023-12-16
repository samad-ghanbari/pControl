<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.user_projects".
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int|null $area
 * @property int|null $exchange_id
 * @property int $enabled
 * @property int $rw
 * @property int $site_editable
 */
class PcUserProjects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.user_projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id'], 'required', 'message'=>"انتخاب فیلد الزامی است"],
            [['user_id', 'project_id', 'area', 'exchange_id', 'enabled', 'rw', 'site_editable'], 'default', 'value' => null],
            [['user_id', 'project_id', 'area', 'exchange_id', 'enabled', 'rw', 'site_editable'], 'integer'],
            [['user_id', 'project_id'], 'unique', 'targetAttribute' => ['user_id', 'project_id']],
            [['exchange_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcExchanges::className(), 'targetAttribute' => ['exchange_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcProjects::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcUsers::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'کاربر',
            'project_id' => 'پروژه',
            'area' => 'منطقه',
            'exchange_id' => 'مرکز',
            'enabled' => 'فعال',
            'rw' => 'نوع دسترسی',
            'site_editable' => 'ویرایش اطلاعات پایه',
        ];
    }
}
