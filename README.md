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

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

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
