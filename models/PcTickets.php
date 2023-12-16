<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.tickets".
 *
 * @property int $id
 * @property int $user_id
 * @property int $project_id
 * @property int $ts
 * @property bool $read
 * @property string $title
 * @property string $ticket
 * @property bool $new_reply
 */
class PcTickets extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.tickets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'project_id', 'ts', 'title', 'ticket'], 'required', 'message' => ''],
            [['user_id', 'project_id', 'ts'], 'default', 'value' => null],
            [['user_id', 'project_id', 'ts'], 'integer'],
            [['read', 'new_reply'], 'boolean'],
            [['ticket'], 'string'],
            [['title'], 'string', 'max' => 512],
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
            'ts' => 'زمان ثبت درخواست',
            'read' => 'ملاحظه شده',
            'title' => 'عنوان درخواست',
            'ticket' => 'درخواست',
            'new_reply' => 'پاسخ جدید',
        ];
    }
}
