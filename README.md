Repository module in development...

How to intall package
--
```
composer require repository/repository
```

Make new repository with artisan command
--
```
php artisan make:repository ExampleRepository
```
This will automaticaly create `ExampleRepository` facade and `ExampleServiceContainer` where you write your queries. This means that service container will call `Example` model. If `Example` model don't exist, don't wory, artisan will create it instead of you.

But if you want to use other model, you can require that.
```
php artisan make:repository ExampleRepository --model NewModel
```

How to access methods from `ServiceContainer`?
--
It's simple, just call you `Repository` facade.
```php
ExampleRepository::simpleMethod();
```