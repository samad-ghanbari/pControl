<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_logs".
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $office
 * @property string|null $post
 * @property int|null $exchange_id
 * @property int|null $area
 * @property string|null $exchange
 * @property string|null $site_id
 * @property string|null $kv_code
 * @property string|null $action
 * @property int|null $ts
 * @property int|null $project_id
 * @property string|null $project
 */
class PcViewLogs extends \yii\db\ActiveRecord
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
        return 'pc.view_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'exchange_id', 'area', 'ts', 'project_id'], 'default', 'value' => null],
            [['id', 'user_id', 'exchange_id', 'area', 'ts', 'project_id'], 'integer'],
            [['name', 'lastname', 'post'], 'string', 'max' => 100],
            [['office', 'exchange', 'site_id', 'kv_code', 'project'], 'string', 'max' => 200],
            [['action'], 'string', 'max' => 256],
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
            'name' => 'نام',
            'lastname' => 'نام خانوادگی',
            'office' => 'اداره کل',
            'post' => 'پست',
            'exchange_id' => 'Exchange ID',
            'area' => 'منطقه',
            'exchange' => 'مرکز',
            'site_id' => 'شناسه سایت',
            'kv_code' => 'کد کافو',
            'action' => 'عمل',
            'ts' => 'زمان',
            'project_id' => 'Project ID',
            'project' => 'پروژه',
        ];
    }
}
