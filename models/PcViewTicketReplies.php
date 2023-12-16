<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.view_ticket_replies".
 *
 * @property int|null $id
 * @property int|null $ticket_id
 * @property int|null $replier_id
 * @property string|null $name
 * @property string|null $lastname
 * @property string|null $office
 * @property int|null $ts
 * @property string|null $reply
 * @property bool|null $new_reply
 */
class PcViewTicketReplies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.view_ticket_replies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ticket_id', 'replier_id', 'ts'], 'default', 'value' => null],
            [['id', 'ticket_id', 'replier_id', 'ts'], 'integer'],
            [['reply'], 'string'],
            [['new_reply'], 'boolean'],
            [['name', 'lastname'], 'string', 'max' => 100],
            [['office'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ticket_id' => 'درخواست',
            'replier_id' => 'پاسخ دهنده',
            'name' => 'نام',
            'lastname' => 'نام خانوادگی',
            'office' => 'اداره کل',
            'ts' => 'زمان ثبت',
            'reply' => 'پاسخ درخواست',
            'new_reply' => 'پاسخ جدید',
        ];
    }
}
