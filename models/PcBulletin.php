<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.bulletin".
 *
 * @property int $id
 * @property string $panel_color
 * @property string $title
 * @property string $description
 * @property int $ts
 */
class PcBulletin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.bulletin';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['panel_color', 'title', 'description', 'ts'], 'required', 'message' => ''],
            [['description'], 'string'],
            [['ts'], 'default', 'value' => null],
            [['ts'], 'integer'],
            [['panel_color'], 'string', 'max' => 50],
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
            'panel_color' => 'رنگ پنل',
            'title' => 'عنوان',
            'description' => 'توضیحات',
            'ts' => 'زمان ثبت',
        ];
    }
}
