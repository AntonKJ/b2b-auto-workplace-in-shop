<?php

return [

	'' => 'site/index',

	// -------------------------------------------------------------------------

	// goods cordiant
	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'cordiant/goods' => 'vendor/cordiant/good',
		],
		'only' => [
			'index',
		],
		'patterns' => [
			'GET,HEAD' => 'index',
		],
	]),

	// shops cordiant
	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'cordiant/shops' => 'vendor/cordiant/shop',
		],
		'only' => [
			'index',
			'view',
			'goods',
		],
		'tokens' => [
			'{shopId}' => '<shopId:[\\d]+>',
			'{goodSku}' => '<sku:[\\da-zA-Z\\_\\-]*>',
		],
		'patterns' => [
			'GET,HEAD {shopId}/goods/{goodSku}' => 'goods',
			'GET,HEAD {shopId}/goods' => 'goods',
			'GET,HEAD {shopId}' => 'view',
			'GET,HEAD' => 'index',
		],
	]),

	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'cordiant/order' => 'vendor/cordiant/order',
		],
		'only' => [
			'create',
		],
		'patterns' => [
			'POST' => 'create',
		],
	]),

	// -------------------------------------------------------------------------

	// goods toyo
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'toyo/goods' => 'vendor/toyo/good',
		],
		'only' => [
			'index',
		],
		'patterns' => [
			'GET,HEAD' => 'index',
		],
	]),

	// shops toyo
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'toyo/shops' => 'vendor/toyo/shop',
		],
		'only' => [
			'index',
			'view',
			'goods',
		],
		'tokens' => [
			'{shopId}' => '<shopId:[\\d]+>',
			'{goodSku}' => '<sku:[\\da-zA-Z\\_\\-]*>',
		],
		'patterns' => [
			'GET,HEAD {shopId}/goods/{goodSku}' => 'goods',
			'GET,HEAD {shopId}/goods' => 'goods',
			'GET,HEAD {shopId}' => 'view',
			'GET,HEAD' => 'index',
		],
	]),

	// -------------------------------------------------------------------------

	// goods goodyear
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'goodyear/good' => 'vendor/goodyear/good',
		],
		'only' => [
			'index',
		],
		'patterns' => [
			'GET,HEAD' => 'index',
		],
	]),

	// shops goodyear
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'goodyear/shops' => 'vendor/goodyear/shop',
		],
		'only' => [
			'index',
			'view',
			'goods',
			'good-by-sku',
		],
		'tokens' => [
			'{shopId}' => '<shopId:[\\d]+>',
			'{goodSku}' => '<sku:[\\da-zA-Z\\_\\-]+>',
		],
		'patterns' => [
			'GET,HEAD {shopId}/goods/{goodSku}' => 'good-by-sku',
			'GET,HEAD {shopId}/goods' => 'goods',
			'GET,HEAD {shopId}' => 'view',
			'GET,HEAD' => 'index',
		],
	]),

	// delivery goodyear
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'goodyear/deliveries' => 'vendor/goodyear/delivery',
		],
		'only' => [
			'index',
			'view',
		],
		'tokens' => [
			'{regionId}' => '<regionId:[\\d]+>',
		],
		'patterns' => [
			'GET,HEAD {regionId}' => 'view',
			'GET,HEAD' => 'index',
		],
	]),

	// -------------------------------------------------------------------------
	// order nokian
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'nokian/order' => 'vendor/nokian/order',
		],
		'only' => [
			'create',
			'status',
		],
		'patterns' => [
			'POST status' => 'status',
			'POST' => 'create',
		],
	]),
	// store nokian
	new \yii\rest\UrlRule([
		'suffix' => '',
		'pluralize' => false,
		'controller' => [
			'nokian/store' => 'vendor/nokian/store',
		],
		'only' => [
			'check',
		],
		'patterns' => [
			'POST check' => 'check',
		],
	]),

	// -------------------------------------------------------------------------

	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'mosautoshina/order' => 'vendor/mosautoshina/order',
		],
		'only' => [
			'create',
			'cancel',
		],
		'tokens' => [
			'{orderId}' => '<orderId:[\\da-z]+>',
		],
		'patterns' => [
			'POST' => 'create',
			'PUT {orderId}/cancel' => 'cancel',
		],
	]),

	// REGULAR API -------------------------------------------------------------------------

	[
		'mode' => \yii\web\UrlRule::PARSING_ONLY,
		'verb' => 'GET',
		'pattern' => 'regular/order/<orderId:[\\da-z]+>/invoice',
		'route' => 'regular/order/invoice',
		'suffix' => '.pdf',
	],

	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'regular/order',
		],
		'only' => [
			'create',
			'cancel',
		],
		'tokens' => [
			'{orderId}' => '<orderId:[\\da-z]+>',
		],
		'patterns' => [
			'POST' => 'create',
			'PUT {orderId}/cancel' => 'cancel',
		],
	]),

	new \yii\rest\UrlRule([
		'suffix' => '.json',
		'pluralize' => false,
		'controller' => [
			'regular/shipment',
		],
		'only' => [
			'index',
		],
		'tokens' => [],
		'patterns' => [
			'POST' => 'index',
		],
	]),

];
