<?php

namespace api\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\filters\RateLimitInterface;
/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property int $status 状态
 * @property int $created_at
 * @property int $updated_at
 * @property int $audit 审核状态 -1:禁用 0:未审核 1:通过
 * @property string $intro 简介
 * @property string $avatar 头像
 * @property string $access-token
 * @property string allowance
 * @property string allowance_updated_at
 */
class User extends ActiveRecord implements IdentityInterface, RateLimitInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'], 'required'],
            [['id','status', 'created_at', 'updated_at', 'audit'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'avatar', 'access-token','allowance','allowance_updated_at'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['intro'], 'string', 'max' => 100],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'email' => 'Email',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'audit' => 'Audit',
            'intro' => 'Intro',
            'avatar' => 'Avatar',
            'access-token' => 'Access Token',
            'allowance'=>'allowance',
            'allowance_updated_at'=>'allowance_updated_at'
        ];
    }
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return new static(User::findOne(['access-token' => $token, 'status' => self::STATUS_ACTIVE]));
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
    // 返回某一时间允许请求的最大数量，比如设置10秒内最多5次请求（小数量方便我们模拟测试）
    public function getRateLimit($request, $action){
        return [1,300];
    }
    // 回剩余的允许的请求和相应的UNIX时间戳数 当最后一次速率限制检查时
    public function loadAllowance($request, $action){
        return [$this->allowance, $this->allowance_updated_at];
    }
    // 保存允许剩余的请求数和当前的UNIX时间戳
    public function saveAllowance($request, $action, $allowance, $timestamp){
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->updateAll([
            'allowance'=>$this->allowance,
            'allowance_updated_at'=>$this->allowance_updated_at,
        ],['id'=>$this->id]);
    }
}
