# Eloquent Helpers

> This package is the part of Awesome Set of Packages for Laravel.
>
> [Read more](https://github.com/LastDragon-ru/lara-asp).

This package contains useful extensions and mixins for [Eloquent](https://laravel.com/docs/8.x/eloquent).


# Installation

```shell
composer require lastdragon-ru/lara-asp-eloquent
```

# Iterators

Iterators are similar to `Builder::chunk()` but uses generators instead of `\Closure` that makes code more readable:

```php
$query = \App\Models\User::query();

$query->chunk(100, function ($users) {
    foreach ($users as $user) {
        // ...
    }
});

foreach ($query->iterator() as $user) {
    // ...
}
```

When you use the default [`ChunkedIterator`](./src/Iterators/ChunkedIterator.php) you should not modify/delete the items while iteration or you will get unexpected results (eg missing items). If you need to modify/delete items while iteration you can use [`ChunkedChangeSafeIterator`](./src/Iterators/ChunkedChangeSafeIterator.php) that specially created for this case and unlike standard `chunkById()` is always safe (please see https://github.com/laravel/framework/issues/35400 for more details). But there are few limitations:

- it is not possible to sort rows, they always will be sorted by `column asc`;
- the `column` should not be changed while iteration or this may lead to repeating row in results;
- the row inserted while iteration may be skipped if it has `column` with the value that lover than the internal pointer;
- queries with UNION is not supported.

To create a change safe instance you can use:

```php
$query = \App\Models\User::query();

foreach ($query->iterator()->safe() as $user) {
    // ...
}
```

# Mixins

## `\Illuminate\Database\Query\Builder`

Name                  | Description
--------------------- | ----
`iterator()`          | Create [`ChunkedIterator`](./src/Iterators/ChunkedIterator.php) instance.

## `\Illuminate\Database\Eloquent\Builder`

Name                                    | Description
--------------------------------------- | ----
`orderByKey(string $direction = 'asc')` | Add an `ORDER BY primary_key` clause to the query.
`orderByKeyDesc()`                      | Alias of `orderByKey('desc')`
