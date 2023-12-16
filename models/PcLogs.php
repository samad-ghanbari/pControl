<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.logs".
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $exchange_id
 * @property string $action
 * @property int $ts
 * @property int $project_id
 */
class PcLogs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'action', 'ts', 'project_id'], 'required'],
            [['id', 'user_id', 'exchange_id', 'ts', 'project_id'], 'default', 'value' => null],
            [['id', 'user_id', 'exchange_id', 'ts', 'project_id'], 'integer'],
            [['action'], 'string', 'max' => 256],
            [['id'], 'unique'],
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
            'user_id' => 'User ID',
            'exchange_id' => 'Exchange ID',
            'action' => 'Action',
            'ts' => 'Ts',
            'project_id' => 'Project ID',
        ];
    }
}
