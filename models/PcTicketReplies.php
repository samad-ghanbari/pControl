<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.ticket_replies".
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $replier_id
 * @property int $ts
 * @property string $reply
 */
class PcTicketReplies extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.ticket_replies';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['ticket_id', 'replier_id', 'ts', 'reply'], 'required', 'message' => ''],
            [['ticket_id', 'replier_id', 'ts'], 'default', 'value' => null],
            [['ticket_id', 'replier_id', 'ts'], 'integer'],
            [['reply'], 'string'],
            [['ticket_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcTickets::className(), 'targetAttribute' => ['ticket_id' => 'id']],
            [['replier_id'], 'exist', 'skipOnError' => true, 'targetClass' => PcUsers::className(), 'targetAttribute' => ['replier_id' => 'id']],
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
            'ts' => 'زمان ثبت',
            'reply' => 'پاسخ درخواست',
        ];
    }
}
