<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * Инициализатор RBAC выполняется в консоли php yii rbac/init
 */
class RbacController extends Controller {
    
    private $_permissions = [
        ['name' => 'general_settings_edit', 'description' => 'Редактирование общих настроек'],
        ['name' => 'instance_settings_edit', 'description' => 'Редактирование дополнительных настроек'],
        ['name' => 'health_view', 'description' => 'Просмотр здоровья биллеров'],
        ['name' => 'user_list', 'description' => 'Просмотр списка пользователей'],
        ['name' => 'user_create', 'description' => 'Создание пользователя'],
        ['name' => 'user_edit', 'description' => 'Редактирование пользователя'],
        ['name' => 'user_delete', 'description' => 'Удаление пользователя'],
        ['name' => 'acl_list', 'description' => 'Просмотр списка ресурсов доступа'],
        ['name' => 'role_list', 'description' => 'Просмотр списка ролей'],
        ['name' => 'role_create', 'description' => 'Создание роли'],
        ['name' => 'role_edit', 'description' => 'Редактирование роли'],
        ['name' => 'role_delete', 'description' => 'Удаление роли'],
        ['name' => 'trunk_list', 'description' => 'Просмотр списка транков'],
        ['name' => 'trunk_create', 'description' => 'Создание транка'],
        ['name' => 'trunk_edit', 'description' => 'Редактирование транка'],
        ['name' => 'trunk_delete', 'description' => 'Удаление транка'],
        ['name' => 'trunk_group_list', 'description' => 'Просмотр списка групп транков'],
        ['name' => 'trunk_group_create', 'description' => 'Создание группы транков'],
        ['name' => 'trunk_group_edit', 'description' => 'Редактирование группы транков'],
        ['name' => 'trunk_group_delete', 'description' => 'Удаление группы транков'],
        ['name' => 'route_table_list', 'description' => 'Просмотр списка таблиц маршрутизации'],
        ['name' => 'route_table_create', 'description' => 'Создание таблицы маршрутизации'],
        ['name' => 'route_table_edit', 'description' => 'Редактирование таблицы маршрутизации'],
        ['name' => 'route_table_delete', 'description' => 'Удаление таблицы маршрутизации'],
        ['name' => 'route_table_route_lock', 'description' => 'Блокирование роута в таблице маршрутизации'],
        ['name' => 'number_list', 'description' => 'Просмотр списка A/B номеров'],
        ['name' => 'number_create', 'description' => 'Создание A/B номера'],
        ['name' => 'number_edit', 'description' => 'Редактирование A/B номера'],
        ['name' => 'number_delete', 'description' => 'Удаление A/B номера'],
        ['name' => 'outcome_list', 'description' => 'Просмотр списка Outcomes'],
        ['name' => 'outcome_create', 'description' => 'Создание Outcome'],
        ['name' => 'outcome_edit', 'description' => 'Редактирование Outcome'],
        ['name' => 'outcome_delete', 'description' => 'Удаление Outcome'],
        ['name' => 'route_case_list', 'description' => 'Просмотр списка Route cases'],
        ['name' => 'route_case_create', 'description' => 'Создание Route case'],
        ['name' => 'route_case_edit', 'description' => 'Редактирование Route case'],
        ['name' => 'route_case_delete', 'description' => 'Удаление Route case'],
        ['name' => 'blacklist_edit', 'description' => 'Блокировка по А-номеру'],
        ['name' => 'prefixlist_list', 'description' => 'Просмотр списка префикслистов'],
        ['name' => 'prefixlist_create', 'description' => 'Создание префикслиста'],
        ['name' => 'prefixlist_edit', 'description' => 'Редактирование префикслиста'],
        ['name' => 'prefixlist_delete', 'description' => 'Удаление префикслиста'],
        ['name' => 'airp_list', 'description' => 'Просмотр списка AIRP'],
        ['name' => 'airp_create', 'description' => 'Создание AIRP'],
        ['name' => 'airp_edit', 'description' => 'Редактирование AIRP'],
        ['name' => 'airp_delete', 'description' => 'Удаление AIRP'],
        ['name' => 'cpc_list', 'description' => 'Просмотр списка CPC'],
        ['name' => 'cpc_create', 'description' => 'Создание CPC'],
        ['name' => 'cpc_edit', 'description' => 'Редактирование CPC'],
        ['name' => 'cpc_delete', 'description' => 'Удаление CPC'],
        ['name' => 'release_reason_list', 'description' => 'Просмотр списка Release reason'],
        ['name' => 'release_reason_create', 'description' => 'Создание Release reason'],
        ['name' => 'release_reason_edit', 'description' => 'Редактирование Release reason'],
        ['name' => 'release_reason_delete', 'description' => 'Удаление Release reason'],
        ['name' => 'attribute_list', 'description' => 'Просмотр списка атрибутов'],
        ['name' => 'attribute_create', 'description' => 'Создание атрибута'],
        ['name' => 'attribute_edit', 'description' => 'Редактирование атрибута'],
        ['name' => 'attribute_delete', 'description' => 'Удаление атрибута'],
        ['name' => 'attribute_group_list', 'description' => 'Просмотр списка групп атрибутов'],
        ['name' => 'attribute_group_create', 'description' => 'Создание группы атрибутов'],
        ['name' => 'attribute_group_edit', 'description' => 'Редактирование группы атрибутов'],
        ['name' => 'attribute_group_delete', 'description' => 'Удаление группы атрибутов'],
        ['name' => 'test_auth_list', 'description' => 'Просмотр списка тестов маршрутизации'],
        ['name' => 'test_auth_create', 'description' => 'Создание теста маршрутизации'],
        ['name' => 'test_auth_edit', 'description' => 'Редактирование теста маршрутизации'],
        ['name' => 'test_auth_delete', 'description' => 'Удаление теста маршрутизации'],
        ['name' => 'test_call_list', 'description' => 'Просмотр списка тестов звонков'],
        ['name' => 'test_call_create', 'description' => 'Создание теста звонков'],
        ['name' => 'test_call_edit', 'description' => 'Редактирование теста звонков'],
        ['name' => 'test_call_delete', 'description' => 'Удаление теста звонков'],
        ['name' => 'auto_test_management', 'description' => 'Управление автоматическими тестами'],
        ['name' => 'test_group_list', 'description' => 'Просмотр списка групп тестов'],
        ['name' => 'test_group_create', 'description' => 'Создание групп тестов'],
        ['name' => 'test_group_edit', 'description' => 'Редактирование групп тестов'],
        ['name' => 'test_group_delete', 'description' => 'Удаление групп тестов'],
        ['name' => 'trunkhealth_view_list', 'description' => 'Здоровье биллеров'],
        ['name' => 'statistics_tree', 'description' => 'Дерево статистики']
    ];
    
    private $_roles = [
        ['name' => 'admin', 'description' => 'Администратор'],
        ['name' => 'engineer', 'description' => 'Инженер'],
        ['name' => 'manager', 'description' => 'Менеджер'],
    ];
    
    private $_rolePermissions = [
        ['role' => 'admin', 'permissions' => [
                'general_settings_edit', 'instance_settings_edit', 'health_view', 'user_list',
                'user_create', 'user_edit', 'user_delete', 'acl_list', 'role_list', 'role_create',
                'role_edit', 'role_delete', 'trunk_list', 'trunk_create', 'trunk_edit',
                'trunk_delete', 'trunk_group_list', 'trunk_group_create', 'trunk_group_edit',
                'trunk_group_delete', 'route_table_list', 'route_table_create',
                'route_table_edit', 'route_table_delete', 'route_table_route_lock', 'number_list', 'number_create',
                'number_edit', 'number_delete', 'outcome_list', 'outcome_create',
                'outcome_edit', 'outcome_delete', 'route_case_list', 'route_case_create',
                'route_case_edit', 'route_case_delete', 'blacklist_edit', 'prefixlist_list', 'prefixlist_create',
                'prefixlist_edit', 'prefixlist_delete', 'airp_list', 'airp_create', 'airp_edit',
                'airp_delete', 'cpc_list', 'cpc_create', 'cpc_edit',
                'cpc_delete','release_reason_list', 'release_reason_create',
                'release_reason_edit', 'release_reason_delete', 'attribute_list',
                'attribute_create', 'attribute_edit', 'attribute_delete', 'attribute_group_list',
                'attribute_group_create', 'attribute_group_edit', 'attribute_group_delete',
                'test_auth_list', 'test_auth_create', 'test_auth_edit', 'test_auth_delete',
                'test_call_list', 'test_call_create', 'test_call_edit', 'test_call_delete',
                'test_group_list', 'test_group_create', 'test_group_edit', 'test_group_delete',
                'trunkhealth_view_list', 'statistics_tree', 'auto_test_management'
            ]
        ],
        ['role' => 'engineer', 'permissions' => [
            'general_settings_edit', 'instance_settings_edit', 'health_view',
            'trunk_list', 'trunk_create', 'trunk_edit',
            'trunk_delete', 'trunk_group_list', 'trunk_group_create', 'trunk_group_edit',
            'trunk_group_delete', 'route_table_list', 'route_table_create',
            'route_table_edit', 'route_table_delete', 'number_list', 'number_create',
            'number_edit', 'number_delete', 'outcome_list', 'outcome_create',
            'outcome_edit', 'outcome_delete', 'route_case_list', 'route_case_create',
            'route_case_edit', 'route_case_delete', 'prefixlist_list', 'prefixlist_create',
            'prefixlist_edit', 'prefixlist_delete', 'airp_list', 'airp_create', 'airp_edit',
            'airp_delete', 'cpc_list', 'release_reason_list', 'release_reason_create',
            'release_reason_edit', 'release_reason_delete', 'attribute_list',
            'attribute_create', 'attribute_edit', 'attribute_delete', 'attribute_group_list',
            'attribute_group_create', 'attribute_group_edit', 'attribute_group_delete',
            'test_auth_list', 'test_auth_create', 'test_auth_edit', 'test_auth_delete',
            'test_call_list', 'test_call_create', 'test_call_edit', 'test_call_delete',
            'test_group_list', 'test_group_create', 'test_group_edit', 'test_group_delete',
            'trunkhealth_view_list'
        ]],
        ['role' => 'manager', 'permissions' => [
            'trunk_list', 'trunk_group_list',
            'test_auth_list', 'test_auth_create', 'test_auth_edit', 'test_auth_delete',
            'test_call_list', 'test_call_create', 'test_call_edit', 'test_call_delete',
            'test_group_list', 'test_group_create', 'test_group_edit', 'test_group_delete',
            'trunkhealth_view_list'
        ]]
    ];
    
    public function actionInit() {
        $auth = Yii::$app->authManager;
        
        $auth->removeAll(); //На всякий случай удаляем старые данные из БД...
        
        foreach ($this->_roles as $role) {
            $roleObject = $auth->createRole($role['name']);
            $roleObject->description = $role['description'];
            $auth->add($roleObject);
        }

        foreach ($this->_permissions as $permission) {
            $permissionObject = $auth->createPermission($permission['name']);
            $permissionObject->description = $permission['description'];
            $auth->add($permissionObject);
        }
    
        foreach ($this->_rolePermissions as $rolePermission) {
            $role = $auth->getRole($rolePermission['role']);
            
            foreach ($rolePermission['permissions'] as $permissionName) {
                $permission = $auth->getPermission($permissionName);
                $auth->addChild($role, $permission);
            }
        }
        
        $admin = $auth->getRole('admin');
        $engineer = $auth->getRole('engineer');
        $manager = $auth->getRole('manager');
        
        $auth->assign($admin, 102);
        $auth->assign($admin, 116);
        $auth->assign($admin, 123);
        
        $auth->assign($engineer, 103);
        
        $auth->assign($manager, 115);
        $auth->assign($manager, 120);
        $auth->assign($manager, 122);
    }
}

