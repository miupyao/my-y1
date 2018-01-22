<?php

namespace api\modules\v1\controllers;

use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\auth\QueryParamAuth;
class UserController extends ActiveController
{
    public $modelClass = 'api\models\User';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['rateLimiter']['enableRateLimitHeaders'] = true;
        return ArrayHelper::merge (parent::behaviors(), [
            'authenticator' => [
                'class' => QueryParamAuth::className()
            ]
        ] );
    }
}
