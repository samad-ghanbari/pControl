<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pc.users".
 *
 * @property int $id
 * @property string $name
 * @property string $lastname
 * @property string $nid
 * @property string $employee_code
 * @property string $office
 * @property string $post
 * @property string $tel
 * @property string $password
 * @property string|null $passwordConfirm
 * @property bool $admin
 * @property bool $reset_password
 * @property int $enabled
 * @property string $action_role
 */
class PcUsers extends \yii\db\ActiveRecord
{

    public $verifyCode;

    public $passwordConfirm;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pc.users';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'lastname', 'nid', 'employee_code', 'office', 'post', 'tel', 'password', 'action_role'], 'required'],
            [['admin', 'reset_password'], 'boolean'],
            [['enabled'], 'default', 'value' => null],
            [['enabled'], 'integer'],
            [['name', 'lastname', 'post'], 'string', 'max' => 100],
            [['nid', 'employee_code'], 'string', 'max' => 20,  'message'=>'ورود فیلد الزامی است'],
            [['office'], 'string', 'max' => 200],
            [['tel'], 'string', 'max' => 15],
            [['password'], 'string', 'max' => 1024,  'message'=>'ورود فیلد الزامی است'],
            [['passwordConfirm'], 'compare', 'compareAttribute' => 'password', 'message'=>'رمز های عبور یکسان نیستتند'],
            [['action_role'], 'string', 'max' => 24],
            [['employee_code'], 'unique'],
            [['nid'], 'unique'],
            ['verifyCode', 'captcha', 'captchaAction' => 'main/captcha',  'message'=>'کد ورودی صحیح نمی‌باشد'] 
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'نام',
            'lastname' => 'نام خانوادگی',
            'nid' => 'کد ملی',
            'employee_code' => 'کد مستخدمی',
            'office' => 'اداره کل',
            'post' => 'سمت',
            'tel' => 'شماره تماس',
            'password' => 'رمز عبور',
            'passwordConfirm' => 'تایید رمز عبور',
            'admin' => 'ادمین سیستم',
            'reset_password' => 'Reset Password',
            'enabled' => 'فعال',
            'action_role' => 'نقش فعالیت',
            'verifyCode' =>"کد تایید"
        ];
    }
}
