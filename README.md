# Laravel Simple Repository

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)

Simple repository pattern for Laravel's Eloquent models.

### Why Repositories?

You can still use Eloquent queries to fetch models directly. Purpose of Repositories are to offer a standard API (or an interface), so you have a reliable layer between ORM and the controllers. This will reduce duplication and keep code clean on large applications.

Here's an example where why you may need this.

Finding an Eloquent Model by id. Here you limit column names retrived from database.
```
$person = Person::find(1, ['first_name', 'last_name']);
```

You can also do it with a repository, but to fetch relationships (which is a lot more common in large applications).
```
$peopleRepo = new PeopleRepository();
$person = $peopleRepo->find(1, ['profile', 'employee_records']); 
```

Then on the repository, you can add some common checks such as checking authorisation, filtering data based on what you retrieve etc.



## Install

Install via Composer

``` bash
composer require elegantmedia/laravel-simple-repository
```

## Usage

### Repository Generator

Create a new repository with `make:repository` command. By default, they'll be stored in `Models` directory.

``` php
php artisan make:repository Car

// this will create
app/Models/CarsRepository.php
```

The following options are also available.

``` php
// overwrite existing file
php artisan make:repository Car --force

// change the default directory to `Entities`
php artisan make:repository Car --dir Entities
```

By default, the the related model will be **guessed**. For example, if you're creating `CarsRepository` we can assume the associated model will be `Car` in the same directory. But if you want to change it, you can pass that as an argument.

``` php
// change default associated model
php artisan make:repository Car --model Vehicles\\SuperCar
```

To keep folders clean, you can group the Model+Repository to a single folder.

``` php 
php artisan make:repository Car --group
```

When you do this, a new plural directory will be automatically created. For example, `Cars` in this example. The file structure can look like this.

``` 
// command
php artisan make:repository Car --dir Entities --group

// file structure (Car.php is an example and created by this command)
app/Entities/Cars/CarsRepository.php
app/Entities/Cars/Car.php
```

# Search

### Model

To add basic SQL `LIKE` search to a model, add the `SearchableLike` trait to a model.

```
namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use ElegantMedia\SimpleRepository\Search\Eloquent\SearchableLike;

class User Extends Model 
{

	use SearchableLike;
	
	protected $searchable = [
		'name',
		'email',
	];

}
```

Now you can do SQL based keyword searches on Models directly.

```
use App\Models\User;

// get all users where `name` or `email` matches `john`
// This will return a paginator
$users = User::search('john');
```

### Repository

You can call `search` on a repository to build advanced reusable filters.

First, setup the repository
```
<?php

namespace App\Models;

use ElegantMedia\SimpleRepository\SimpleBaseRepository as BaseRepository;

class UsersRepository extends BaseRepository
{

	// bind the model to the Repository
	public function __construct(User $model)
	{
		parent::__construct($model);
	}

}
```

Then call search on your repository. Usually this happens from a controller or a parent repository.

```
use App\Models\UsersRepository;
use App\Models\User;

$repo = app(UsersRepository::class);

$keyword = 'jane';

// Example: Get paginated results of all Users, that has a `name` or `email` LIKE `jane`
$matchedUsers = User::search($keyword)->paginate();

// Same result can be achieved with the repository. 
// If a null value is passed, it will get the `q` parameter from Request as the keyword
$matchedUsers = $repo->search();
```

Because the SearchFilter is a query itself, you can use it to chain conditions.
```
$filter = $repo->newSearchFilter();

// Example: Get results, `with` related models
$filter->with(['roles', 'projects']);

// Example: Only include Users, if `projects` have a status of `completed`
$filter->whereHas('projects', function($q) {
	$q->where('status', 'completed');
});

// Paginated results
$users = $repo->search($filter);

// Change results per page
$filter->setPerPage(100):

// Non-paginated results
$filter->paginate(false);
$users = $repo->search($filter);
```

The default filter will add `q` from query string, and sort results in descending order. If you don't want that, create a filter without the defaults.

```
$filter = $repo->newSearchFilter(false);
```


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.


## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and for details.

## Credits

- [Elegant Media][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/elegantmedia/laravel-simple-repository.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/elegantmedia/laravel-simple-repository/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/elegantmedia/laravel-simple-repository.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/elegantmedia/laravel-simple-repository.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/elegantmedia/laravel-simple-repository.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/elegantmedia/laravel-simple-repository
[link-travis]: https://travis-ci.org/elegantmedia/laravel-simple-repository
[link-scrutinizer]: https://scrutinizer-ci.com/g/elegantmedia/laravel-simple-repository/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/elegantmedia/laravel-simple-repository
[link-downloads]: https://packagist.org/packages/elegantmedia/laravel-simple-repository
[link-author]: https://github.com/elegantmedia
[link-contributors]: ../../contributors
