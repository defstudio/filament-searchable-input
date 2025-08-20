# Filament Searchable Input

[![Latest Version on Packagist](https://img.shields.io/packagist/v/defstudio/filament-searchable-input.svg?style=flat-square)](https://packagist.org/packages/defstudio/filament-searchable-input)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/defstudio/filament-searchable-input/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/defstudio/filament-searchable-input/actions?query=workflow%3A"fix-php-code-style-issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/defstudio/filament-searchable-input.svg?style=flat-square)](https://packagist.org/packages/defstudio/filament-searchable-input)

![image](https://github.com/user-attachments/assets/6e85aa0e-13b5-4776-ae96-b3abc23d9f5f)

A searchable autocomplete input form field


## Filament Compatibility

| Package Version | Filament Version |
|:---------------:|:----------------:|
|      1.x       |       3.x       |
|      4.x       |       4.x        |

## Installation

You can install the package via composer:

```bash
composer require defstudio/filament-searchable-input
```


> [!IMPORTANT]
> If you have not set up a custom theme and are using Filament Panels follow the instructions in the [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) first.

After setting up a custom theme add the plugin's views to your theme css file or your app's css file if using the standalone packages.

```css
@source '../../../../vendor/defstudio/filament-searchable-input/resources/**/*.blade.php';
```


## Views customization


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
                    'Lorem ipsum dolor',
                    'Aspernatur labore qui fugiat',
                    'Dolores tempora libero assumenda',
                    'Qui rem voluptas officiis ut non',
                    
                    //..
                    
                ])
        ]);
    }
}
```

### Value-Label pairs options

Options can be defined also as an array of Value and Label pairs.

The `Value` will be inserted in the Input field when the user select an item. The `Label` is just used as a display value inside the search dropdown.


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
                        ->orWhere('code', 'like', "%$search%")
                        ->limit(15)
                        ->pluck('description')
                        ->values()
                        ->toArray();
                        
                    // Or, an associative array as well...
                    
                    return Product::query()
                        ->where('description', 'like', "%$search%")
                        ->orWhere('code', 'like', "%$search%")
                        ->limit(15)
                        ->mapWithKeys(fn(Product $product) => [
                            $product->description => "[$product->code] $product->description"
                        ])
                        ->toArray();            
                        
                        
                    // Or, also, an array of complex items (see below)
                })
        ]);
    }
}
```


## Complex Items

`SearchableInput` supports using arrays as search results, this allows to pass metadata to the selected item and consume it in the `->onItemSelected()` method:

```php

use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use DefStudio\SearchableInput\DTO\SearchResult;

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
                        ->map(fn(Product $product) => SearchResult::make($product->description, "[$product->code] $product->description")
                            ->withData('product_id', $product->id)
                            ->withData('product_code', $product->code) 
                        )
                        ->toArray()
                        
                })
                ->onItemSelected(function(SearchResult $item){
                    $item->value();
                    $item->label();
                    $item->get('product_id');
                    $item->get('product_code');
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



## Upgrading

### From v1.x (Filament v3) to v4.x (Filament v4)

With Filament v4 it has been recommended for plugin authors to have the users include their plugins views in a custom theme, rather than include the built css from the plugin. So for upgrades from Filament v3 to v4 it is recommended to follow these instructions from [Filament Docs](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) to set up a custom theme (if not already done) and add this to your theme/app css file

```css
@source '../../../../vendor/defstudio/filament-searchable-input/resources/**/*.blade.php';
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Fabio Ivona](https://github.com/fabio-ivona)
- [Mario Gattolla](https://github.com/MarioGattolla)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
