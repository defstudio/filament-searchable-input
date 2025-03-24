# A searchable autocomplete input for Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/defstudio/filament-searchable-input.svg?style=flat-square)](https://packagist.org/packages/defstudio/filament-searchable-input)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/defstudio/filament-searchable-input/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/defstudio/filament-searchable-input/actions?query=workflow%3A"fix-php-code-style-issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/defstudio/filament-searchable-input.svg?style=flat-square)](https://packagist.org/packages/defstudio/filament-searchable-input)



[demo.webm](https://github.com/user-attachments/assets/cdc816c4-fa80-46f7-bb7b-43f2f018f61e)


A searchable autocomplete input for Filament

## Installation

You can install the package via composer:

```bash
composer require defstudio/filament-searchable-input
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-searchable-input-views"
```


## Usage

`SearchableInput` is a component input built on top of TextInput, so any TextInput method is available, plus it allows to define a search function that will be executed whenever the user types something.

Here's a basic implementation

```php
use DefStudio\SearchableInput\Forms\Components\SearchableInput;

class ProductResource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            SearchableInput::make('description')
                ->options([
                    'Lorem ipsum dolor' => '[A001] Lorem ipsum dolor.',
                    'Aspernatur labore qui fugiat' => '[A001] Aspernatur labore qui fugiat.',
                    'Dolores tempora libero assumenda' => '[A002] Dolores tempora libero assumenda.',
                    'Qui rem voluptas officiis ut non' => '[A003] Qui rem voluptas officiis ut non.',
                    
                    //..
                    
                ])
        ]);
    }
}
```


## Custom Search Function

Instead (or along with) defining an `->options()` set, the search result set can be customized:

```php

use DefStudio\SearchableInput\Forms\Components\SearchableInput;

class ProductResource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            SearchableInput::make('description')
                ->searchUsing(function(string $search){
                
                    return Product::query()
                        ->where('description', 'like', "%$search%")
                        ->limit(15)
                        ->pluck('description', 'description')
                        ->toArray()
                        
                })
        ]);
    }
}
```

a Value-Label pairs array should be returned by the search function, in order to display a dropdown with the corresponding items

NOTE: if `Value` and `Label` differ, the `Value` will be inserted in the Input field when the user select an item. The `Label` is just used as a display value inside the dropdown.


## Complex Items

`SearchableInput` supports using arrays as dropdown items, this allows to pass metadata to the selected item and consume it in the `->onItemSelected()` method:

```php

use DefStudio\SearchableInput\Forms\Components\SearchableInput;

class ProductResource
{
    public static function form(Form $form): Form
    {
        return $form->schema([
            SearchableInput::make('description')
                ->searchUsing(function(string $search){
               
                    return Product::query()
                        ->where('description', 'like', "%$search%")
                        ->limit(15)
                        ->map(fn(Product $product) => [
                            'value' => $product->description,
                            'label' => $product->code . ' - ' . $product->description,
                            'product_id' => $product->id,
                            'product_code' => $product->code,
                        ])
                        ->toArray()
                        
                })
                ->onItemSelected(function(array $item){
                    /*
                     * $item = [
                     *      'value' => // "product description",
                     *      'label' => //the dropdown label
                     *      'product_id' => eg. 42
                     *      'product_code' => eg. "AB0042"
                     * ]
                     */ 
                }),
        ]);
    }
}
```

## Filament Utility Injection

In each of its methods, `SearchableInput` fully supports Filament utility injection in its methods, like:

```php
 SearchableInput::make('description')
    ->searchUsing(function(string $search, array $options){ //options defined in ->options([...]) 
        //...
    })
    ->searchUsing(function(string $search, Get $get, Set $set){ //$get and $set utilities
        //...
    })
    ->searchUsing(function(string $search, $state){ //current field state
        //...
    })
     ->searchUsing(function(string $search, Component $component){ //current component instance
        //...
    })
     ->searchUsing(function(string $search, ?Model $record){ //current form record
        //...
    })
     ->searchUsing(function(string $search, string $operation){ //current form operation (create/update)
        //...
    });
                

```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Fabio Ivona](https://github.com/fabio-ivona)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
