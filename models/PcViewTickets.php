<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_tickets".
 *
 * @property int|null $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $office
 * @property int|null $project_id
 * @property string|null $project
 * @property int|null $ts
 * @property bool|null $read
 * @property string|null $title
 * @property string|null $ticket
 * @property bool|null $new_reply
 */
class PcViewTickets extends \yii\db\ActiveRecord
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
        return 'pc.view_tickets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'project_id', 'ts'], 'default', 'value' => null],
            [['id', 'user_id', 'project_id', 'ts'], 'integer'],
            [['read', 'new_reply'], 'boolean'],
            [['ticket'], 'string'],
            [['name', 'lastname'], 'string', 'max' => 100],
            [['office', 'project'], 'string', 'max' => 200],
            [['title'], 'string', 'max' => 512],
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
            'office' => 'اداره کل',
            'project_id' => 'پروژه',
            'project' => 'پروژه',
            'ts' => 'زمان ثبت',
            'read' => 'ملاحظه درخواست',
            'title' => 'عنوان',
            'ticket' => 'درخواست',
            'new_reply' => 'پاسخ جدید',
        ];
    }
}
