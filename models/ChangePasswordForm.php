<?php
namespace app\models;

use Yii;
use yii\base\Model;
use app\models\User;

class ChangePasswordForm extends Model
{
    public $currentPassword;
    public $newPassword;
    public $newPasswordRepeat;

    public function rules()
    {
        return [
            [['currentPassword', 'newPassword', 'newPasswordRepeat'], 'required'],
            [['currentPassword'], 'validateCurrentPassword'],
            [['newPassword'], 'string', 'min' => 6],
            [['newPasswordRepeat'], 'compare', 'compareAttribute' => 'newPassword', 'message' => 'Пароли не совпадают'],
        ];
    }

    public function validateCurrentPassword($attribute, $params)
    {
        if (!Yii::$app->security->validatePassword($this->currentPassword, Yii::$app->user->identity->password_hash)) {
            $this->addError($attribute, 'Текущий пароль неверен.');
        }
    }

    public function changePassword()
    {
        if ($this->validate()) {
            $user = User::findOne(Yii::$app->user->id);
            $user->setPassword($this->newPassword);
            return $user->save();
        }

        return false;
    }
}