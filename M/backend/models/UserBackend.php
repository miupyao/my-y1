<?php

namespace backend\models;

use Yii;
use yii\web\IdentityInterface;

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
 * @property string $allowance
 * @property string $allowance_updated_at
 */
class UserBackend extends \yii\db\ActiveRecord implements IdentityInterface
{
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
            [['status', 'created_at', 'updated_at', 'audit'], 'integer'],
            [['username', 'password_hash', 'password_reset_token', 'email', 'avatar', 'access-token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['intro'], 'string', 'max' => 100],
            [['allowance'], 'string', 'max' => 10],
            [['allowance_updated_at'], 'string', 'max' => 20],
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
            'status' => '状态',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'audit' => '审核状态 -1:禁用 0:未审核 1:通过',
            'intro' => '简介',
            'avatar' => '头像',
            'access-token' => 'Access Token',
            'allowance' => 'Allowance',
            'allowance_updated_at' => 'Allowance Updated At',
        ];
    }

    /**
     * @inheritdoc
     * @return UserQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserQuery(get_called_class());
    }
    // 其他gii生成的代码，因为我们并未对其进行过改动，因此这里省略，下面只补充我们实现的几个抽象方法

    /**
     * @inheritdoc
     * 根据user_backend表的主键（id）获取用户
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @inheritdoc
     * 根据access_token获取用户，我们暂时先不实现，我们在文章 http://www.manks.top/yii2-restful-api.html 有过实现，如果你感兴趣的话可以先看看
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * @inheritdoc
     * 用以标识 Yii::$app->user->id 的返回值
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     * 获取auth_key
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     * 验证auth_key
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
