# Mantenimiento APP

Mantenimiento App  based in Laravel 5

###Install

Clone repository

```
git clone https://github.com/ArrozAlba/mantenimiento mantenimiento


```

Change directory

```
cd mantenimiento
```

Remove .git directory (optional)

```
rm -rf .git
```

Composer update (first download composer from <a href="https://getcomposer.org/download/" target="_blank">here</a>)

```
php composer.phar update
```
or

```
composer update
```

Copy file .env.example to .env

```
cp .env.example .env
```

Edit file .env and change conection database and email data.

```
APP_ENV=local
APP_DEBUG=true
APP_KEY=TNP6X0eATkfsgHzgrqlByxcPL7Hnfldc

DB_HOST=127.0.0.1
DB_DATABASE=mantenimiento
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file

MAIL_DRIVER=sendmail
MAIL_ADDRESS=demo@demo.com
MAIL_NAME=mantenimiento
```

Regenerate APP_KEY
```
php artisan key:generate
```

Migrate tables

```
php artisan migrate
```

Install demo user (optional)

```
php artisan db:seed
```

Run server

```
php artisan serve
```

Go to <a href="http://localhost:8000/" target="_blank">Mantenimiento Local</a> and login with user demo:


```
email: demo@demo.com password: demo
```

###Change custom vars


Edit file config/custom.php


```
return [
	
	'name' => 'Mantenimiento APP',

	'htmlname' => '<b>Mantenimiento </b>App',

	'url' => ' ',

	'paginate' => '20'

];
```

###Adding new entity (Article) very fast

Create controller, repository, model, migrate, request and form classes, all in one.

```
php artisan mantenimiento:all articles
```

or for steps
```
//Create controller
php artisan mantenimiento:controller ArticlesController

//Create repository
php artisan mantenimiento:repository ArticleRepository

//Create Model
php artisan mantenimiento:model Article

//Create request
php artisan mantenimiento:request ArticleRequest

//Create form
php artisan mantenimiento:form ArticleForm

//Create views
php artisan mantenimiento:views articles

//Create migrate
php artisan make:migration:schema create_articles_table --schema="name:string"
```

Add routes in app/Http/routes.php into admin section

```
Route::resource('articles', 'Admin\ArticlesController');
Route::post('articles/delete', array('as' => 'admin.articles.delete', 'uses' => 'Admin\ArticlesController@delete'));
```

Add menu in array menus in app/Composers/MenusComposer.php

```
'articles' => [
	'visible' => true,
	'icon' => 'fa-file-o',
	'edit' => true,
	'name' => trans('messages.articles.index')
]
```

Add custom messages in resources/lang/es/messages.php 

```
'article' => 'Artículo',
'articles.index' => 'Artículos',
'articles.create' => 'Nuevo artículo',
'articles.edit' => 'Editar artículo',
'articles.show' => 'Ver artículo',
'articles.delete.title' => 'Eliminar artículo',
'articles.delete.message' => '¿Está seguro de que quiere continuar?',
```

Migrate article

```
php artisan migrate
```

###Adding Api controller (Article)

Create a controller only method index and show function

```
php artisan mantenimiento:apicontroller ArticlesController
```

Add routes in app/Http/routes.php into api section
```
Route::resource('articles', 'Api\ArticlesController', ['only' => ['index', 'show']]);
```

###Based on:
https://github.com/raulduran/backendl5/
```
