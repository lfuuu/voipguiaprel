<?php
namespace app\forms;

use app\models\Role;
use yii\base\Model;

class RoleForm extends Model
{
    public $name;
    public $description;

    public function rules()
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'string'],
            ['description', 'string'],
        ];
    }

    public function scenarios()
    {
        return [
            'create' => ['name','description'],
            'update' => ['name','description'],
            'delete' => ['name'],
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
            'name' => 'Название',
            'description' => 'Примечание',
        ];
    }

    public function createRole()
    {
        if ($this->validate()) {

            $role = new Role();
            $role->name = $this->name;
            $role->description = $this->description;
            $role->save();

            $this->name = $role->name;

            return true;
        } else {
            return false;
        }
    }

    public function updateRole()
    {
        if ($this->validate()) {

            $role = Role::findOne($this->name);
            $role->description = $this->description;

            $role->save();

            $this->name = $role->name;

            return true;
        } else {
            return false;
        }
    }

    public function deleteRole()
    {
        if ($this->validate()) {
            $role = Role::findOne($this->name);
            $role->delete();
            return true;
        } else {
            return false;
        }
    }

}