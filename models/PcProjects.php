<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.projects".
 *
 * @property int $id
 * @property string $project
 * @property string $office
 * @property int $ts
 * @property int|null $project_weight
 * @property bool $enabled
 * @property string|null $contract_subject
 * @property string|null $contract_company
 * @property string|null $contract_date
 * @property string|null $contract_duration
 */
class PcProjects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.projects';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['project', 'office', 'ts'], 'required', 'message'=>"ورود مقدار فیلد الزامی است"],
            [['ts', 'project_weight'], 'default', 'value' => null],
            [['ts', 'project_weight'], 'integer'],
            [['enabled'], 'boolean'],
            [['project', 'office'], 'string', 'max' => 200],
            [['contract_subject'], 'string', 'max' => 512],
            [['contract_company'], 'string', 'max' => 256],
            [['contract_date', 'contract_duration'], 'string', 'max' => 128],
            [['project', 'office'], 'unique', 'targetAttribute' => ['project', 'office']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project' => 'پروژه',
            'office' => 'اداره کل',
            'ts' => 'تاریخ ثبت پروژه',
            'project_weight' => 'وزن پروژه',
            'enabled' => 'فعال',
            'contract_subject' => 'موضوع قرارداد/پروژه',
            'contract_company' => 'شرکت طرف قرارداد',
            'contract_date' => 'تاریخ قرارداد',
            'contract_duration' => 'مدت زمان اجرای قرارداد',
        ];
    }
}
