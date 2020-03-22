Repository package in development...

What is this?
-
This package is powerful and fully customizable DB model repository for storing a queries.

How to intall package?
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

But if you want to use another model, you can require that.
```
php artisan make:repository ExampleRepository --model NewModel
```

How to access methods from `ServiceContainer`?
--

It's simple, just call your `Repository` facade.
```php
ExampleRepository::simpleMethod();
```

Now, let's create a simple method in `ExampleServiceContainer`.
```php
public function simpleMethod()
{
    // use buildQuery() to prepare your query for execution
    // $builder is instance of \Illuminate\Database\Eloquent\Builder so you can 
    // regularly use builder's methods whatever you want.
    $this->buildQuery(function (Builder $builder) {
        $builder->where('destination', 'San Diego')
                ->take(20);
    });
    
    // this actually execute your query
    // in this place you can use any 'end point' method from Laravel builder
    // like count(), max(), findOrFail(), etc...
    return $this->get();
}
```

Of cource, there's the old-school query.
```php
public function simpleMethod()
{
    return $this->executeRaw("SELECT * FROM examples LIMIT 20");
}
```

`Repository` package has built-in query caching system so you can easily use it.
Let's try it (you do not need all these methods at once, use only the ones you need).
```php
// remember result for a globally defined time or set your custom time
$this->remember(?duration:);

// remember results forever
$this->rememberForever();

// if you have changeable caching state, this is ideally for you
$this->useCache(cache_state:, ?remember_type:);

// is you not satisfied with global settings, you can change it any time
// keep it mind this only affect locally
$this->store('cache_engine');
```

Well that's nice but where is all that 'customizable'?
---

All of these methods above you can use in fly, and more. Look.
```php
ExampleRepository::remember()->simpleMethod();

// change cache engine in fly
ExampleRepository::store('redis')->remember(3600)->simpleMethod();
```

Hm, `simpleMethod` use `get()` to retrieve results but I need take max salary, is that possible? Of course it is possible. Look.
```php
ExampleRepository::requireBuilder()->simpleMethod()->max('salary');
```
> <i>Note: keep in mind that `requireBuilder()` exclude all repository benefits (like caching).</i>

That's all?
---
Of cource it is not. Repository gives you a possibility to modify queries on fly.
```php
ExampleRepository::orderBy('hire_date', 'desc')->simpleMethod();
```
> <i>Note: this not overriding query in `simpleMethod()`. If you want to override, then require a builder.</i>

Repository have a possibility to dynamic handle all Laravel query builder methods. You can even write the entire query in fly and cache the result.
```php
ExampleRepository::rememberForever()->where('destination', 'San Diego')->get();
```
Only sky is the limit. Enjoy.