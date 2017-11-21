<?php
namespace app\forms;

use app\exceptions\WrongStateException;
use app\models\User;
use Yii;
use yii\base\Model;

class UserForm extends Model
{
    public $id;
    public $login;
    public $name;
    public $password;
    public $role;

    public function rules()
    {
        return [
            [['id', 'login', 'name'], 'required'],
            ['password', 'required', 'on' => 'create'],
            ['id', 'integer'],
            ['password', 'string'],
            ['role', 'string']
        ];
    }

    public function scenarios()
    {
        return [
            'create' => ['login','name','password', 'role'],
            'update' => ['id', 'login','name','password', 'role'],
            'delete' => ['id'],
        ];
    }


    public static function create($scenario = null, array $data = null)
    {
        $item = new self();
        if ($scenario !== null) $item->scenario = $scenario;
        if ($data !== null) $item->load($data, '');
        return $item;
    }

    public function attributeLabels()
    {
        return [
            'login' => 'Логин',
            'name' => 'Имя',
            'password' => 'Пароль',
            'role' => 'Роль',
        ];
    }

    public function createUser()
    {
        if ($this->validate()) {

            $user = new User();
            $user->name = $this->name;
            $user->login = $this->login;
            $user->setPassword($this->password);
            $user->save();

            $this->id = $user->id;

            return true;
        } else {
            return false;
        }
    }

    public function updateUser()
    {
        if ($this->validate()) {

            $user = User::findOne($this->id);
            $user->name = $this->name;
            $user->login = $this->login;
            if ($this->password) {
                $user->setPassword($this->password);
            }
            $user->save();

            $this->id = $user->id;

            return true;
        } else {
            return false;
        }
    }

    public function deleteUser()
    {
        if ($this->validate()) {
            $user = User::findOne($this->id);
            $user->delete();
            return true;
        } else {
            return false;
        }
    }

}