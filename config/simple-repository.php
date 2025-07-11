<?php

declare(strict_types=1);

return [
	/*
	|--------------------------------------------------------------------------
	| Default Repository Settings
	|--------------------------------------------------------------------------
	|
	| These settings control the default behavior of repositories throughout
	| your application. You can override these on a per-repository basis.
	|
	*/

	'defaults' => [
		/*
		|--------------------------------------------------------------------------
		| Default Pagination Settings
		|--------------------------------------------------------------------------
		|
		| Control the default pagination behavior for repository queries.
		|
		*/
		'pagination' => [
			'per_page' => 50,
			'max_per_page' => 100,
		],

		/*
		|--------------------------------------------------------------------------
		| Default Sorting Settings
		|--------------------------------------------------------------------------
		|
		| Define the default sorting field and direction for queries.
		|
		*/
		'sorting' => [
			'field' => 'created_at',
			'direction' => 'desc', // 'asc' or 'desc'
		],
	],

	/*
	|--------------------------------------------------------------------------
	| Search Settings
	|--------------------------------------------------------------------------
	|
	| Configure the default search behavior and operators.
	|
	*/
	'search' => [
		/*
		|--------------------------------------------------------------------------
		| Search Query Parameter
		|--------------------------------------------------------------------------
		|
		| The query parameter name to look for search keywords in requests.
		|
		*/
		'query_parameter' => 'q',

		/*
		|--------------------------------------------------------------------------
		| Case Sensitivity
		|--------------------------------------------------------------------------
		|
		| Whether searches should be case-sensitive. Note: This depends on your
		| database configuration. MySQL is case-insensitive by default.
		|
		*/
		'case_sensitive' => false,
	],

	/*
	|--------------------------------------------------------------------------
	| Repository Command Settings
	|--------------------------------------------------------------------------
	|
	| Configure the behavior of the make:repository artisan command.
	|
	*/
	'command' => [
		/*
		|--------------------------------------------------------------------------
		| Default Directory
		|--------------------------------------------------------------------------
		|
		| The default directory where repositories will be created.
		|
		*/
		'directory' => 'Models',

		/*
		|--------------------------------------------------------------------------
		| Repository Suffix
		|--------------------------------------------------------------------------
		|
		| The suffix to append to repository class names.
		|
		*/
		'suffix' => 'Repository',
	],
];
