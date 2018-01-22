<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[UserBackend]].
 *
 * @see UserBackend
 */
class UserQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return UserBackend[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return UserBackend|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
