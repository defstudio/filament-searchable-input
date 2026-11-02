<?php

use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentSlot;
use Livewire\Component;

beforeEach(function () {
    view()->share('errors', new ViewErrorBag);
});

it('renders suggestion labels as text unless HTML is enabled', function (?Closure $configure, bool $allowsHtml) {
    $field = SearchableInput::make('unit')->options(['EA' => '<strong>Each</strong>']);

    if ($configure !== null) {
        $configure($field);
    }

    $livewire = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        public array $data = [];
    };

    $schema = Schema::make($livewire)->statePath('data')->components([$field]);
    $schema->getComponents();

    $html = view('searchable-input::components.wrapper', [
        'field' => $field,
        'slot' => new ComponentSlot,
    ])->render();

    expect($html)
        ->toContain($allowsHtml ? 'x-html="`${suggestion.label}`"' : 'x-text="`${suggestion.label}`"')
        ->not->toContain($allowsHtml ? 'x-text="`${suggestion.label}`"' : 'x-html="`${suggestion.label}`"');
})->with([
    'default' => [null, false],
    'enabled' => [fn (SearchableInput $field) => $field->allowHtml(), true],
    'disabled' => [fn (SearchableInput $field) => $field->allowHtml(false), false],
    'closure enabled' => [fn (SearchableInput $field) => $field->allowHtml(fn (): bool => true), true],
    'closure disabled' => [fn (SearchableInput $field) => $field->allowHtml(fn (): bool => false), false],
]);

it('keeps option values separate from HTML labels', function (bool $customSearch) {
    $field = SearchableInput::make('unit')->allowHtml();

    if ($customSearch) {
        $field->searchUsing(fn (string $search): array => [
            SearchResult::make('EA', '<strong>Each</strong>')->withData('unit_id', 1),
        ]);
    } else {
        $field->options(['EA' => '<strong>Each</strong>']);
    }

    $schema = Schema::make()->components([$field]);
    $schema->getComponents();

    $results = $field->getSearchResultsForJs('Each');

    expect($results[0])->toBe([
        'value' => 'EA',
        'label' => '<strong>Each</strong>',
        'data' => $customSearch ? ['unit_id' => 1] : [],
    ]);
})->with([false, true]);
