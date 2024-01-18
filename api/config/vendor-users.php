<?php

use api\config\rbac\PermissionVendor;
use api\models\VendorUser;
use api\models\VendorUserRole;

return [
	1 => [
		'id' => 1,
		'username' => 'Goodyear',
		'vendor' => 'goodyear',
		'status' => VendorUser::STATUS_ACTIVE,
		'roles' => [
			VendorUserRole::ROLE_USER,
			PermissionVendor::GOODYEAR,
		],
		'authToken' => 'd945934d-2f54-45ca-aeee-101316291432',
		'orderData' => [
			'vendor' => 'goodyear',
			'vendor-content' => null,
		],
	],
	2 => [
		'id' => 2,
		'username' => 'vianor_shin',
		'password' => '5XjOV6GiWGxD',
		'vendor' => 'vianorru',
		'status' => VendorUser::STATUS_ACTIVE,
		'roles' => [
			VendorUserRole::ROLE_USER,
			PermissionVendor::NOKIAN,
		],
		'authToken' => implode(':', ['vianor_shin', '5XjOV6GiWGxD']),
		'orderData' => [
			'vendor-content' => null,
		],
	],
	3 => [
		'id' => 3,
		'username' => 'Toyo',
		'vendor' => 'toyo',
		'status' => VendorUser::STATUS_ACTIVE,
		'roles' => [
			VendorUserRole::ROLE_USER,
			PermissionVendor::TOYO,
		],
		'authToken' => '1745fb95-6480-408e-818e-165c10240c6f',
		'orderData' => [
			'vendor' => 'toyo',
			'vendor-content' => null,
		],
	],
	4 => [
		'id' => 4,
		'username' => 'Cordiant',
		'vendor' => 'cordiant',
		'status' => VendorUser::STATUS_ACTIVE,
		'roles' => [
			VendorUserRole::ROLE_USER,
			PermissionVendor::CORDIANT,
		],
		'authToken' => 'ea73cdeb-dc92-4707-a23a-952745211234',
		'orderData' => [
			'vendor' => 'cordiant',
			'vendor-content' => null,
		],
	],
	5 => [
		'id' => 5,
		'username' => 'MosAutoShina',
		'vendor' => 'mosautoshina',
		'status' => VendorUser::STATUS_ACTIVE,
		'roles' => [
			VendorUserRole::ROLE_USER,
			PermissionVendor::MOSAUTOSHINA,
		],
		'authToken' => 'df4cb3d9-0c89-4d17-ae12-6dd20f80ab8d',
		'optUserId' => 2808, //3, // 2808,
		'orderData' => [
			'vendor' => 'mosautoshina',
			'vendor-content' => null,
		],
	],
];