<?php

use api\config\rbac\PermissionVendor;
use api\models\VendorUserRole;
use yii\rbac\Item;

return [

	PermissionVendor::REGULAR => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Стандартный доступ к API',
	],

	// ---------------------------------------------

	PermissionVendor::GOODYEAR => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Доступ для Goodyear',
	],

	PermissionVendor::NOKIAN => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Доступ для Vianor.ru',
	],

	PermissionVendor::TOYO => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Доступ для Toyo',
	],

	PermissionVendor::CORDIANT => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Доступ для Cordiant',
	],

	PermissionVendor::MOSAUTOSHINA => [
		'type' => Item::TYPE_PERMISSION,
		'description' => 'Доступ для МосАвтоШина',
	],

	// ROLES -----------------------------------------------

	VendorUserRole::ROLE_GUEST => [
		'type' => Item::TYPE_ROLE,
		'description' => 'Гость',
	],

	VendorUserRole::ROLE_USER => [
		'type' => Item::TYPE_ROLE,
		'description' => 'Зарегистрированный пользователь',
		'children' => [
			VendorUserRole::ROLE_GUEST,
		],
	],

	VendorUserRole::ROLE_ADMIN => [
		'type' => Item::TYPE_ROLE,
		'description' => 'Администратор',
		'children' => [
			VendorUserRole::ROLE_USER,
		],
	],

];