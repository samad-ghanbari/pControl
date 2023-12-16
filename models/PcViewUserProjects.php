<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_user_projects".
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $user_office
 * @property string|null $post
 * @property int|null $project_id
 * @property string|null $project
 * @property string|null $office
 * @property int|null $ts
 * @property int|null $project_weight
 * @property string|null $contract_subject
 * @property string|null $contract_company
 * @property string|null $contract_date
 * @property string|null $contract_duration
 * @property int|null $area
 * @property int|null $exchange_id
 * @property string|null $exchange
 * @property int|null $enabled
 * @property bool|null $project_enabled
 * @property int|null $rw
 * @property int|null $site_editable
 */
class PcViewUserProjects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_user_projects';
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
            [['id', 'user_id', 'project_id', 'ts', 'project_weight', 'area', 'exchange_id', 'enabled', 'rw', 'site_editable'], 'default', 'value' => null],
            [['id', 'user_id', 'project_id', 'ts', 'project_weight', 'area', 'exchange_id', 'enabled', 'rw', 'site_editable'], 'integer'],
            [['project_enabled'], 'boolean'],
            [['name', 'lastname', 'post'], 'string', 'max' => 100],
            [['user_office', 'project', 'office', 'exchange'], 'string', 'max' => 200],
            [['contract_subject'], 'string', 'max' => 512],
            [['contract_company'], 'string', 'max' => 256],
            [['contract_date', 'contract_duration'], 'string', 'max' => 128],
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
            'name' => 'نام',
            'lastname' => 'نام خانوادگی',
            'user_office' => 'اداره کل کاربر',
            'post' => 'سمت',
            'project_id' => 'شناسه پروژه',
            'project' => 'پروژه',
            'office' => 'اداره کل',
            'ts' => 'زمان ثبت',
            'contract_subject' => 'موضوع قرارداد/پروژه',
            'contract_company' => 'شرکت طرف قرارداد',
            'contract_date' => 'تاریخ قرارداد',
            'contract_duration' => 'مدت زمان اجرای قرارداد',
            'project_weight' => 'وزن پروژه',
            'area' => 'منطقه',
            'exchange_id' => 'مرکز',
            'exchange' => 'مرکز',
            'enabled' => 'فعال',
            'project_enabled' => 'فعال بودن پروژه',
            'rw' => 'نوع دسترسی',
            'site_editable' => 'ویرایش اطلاعات پایه',

        ];
    }
}
