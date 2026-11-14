<?php

use DefStudio\SearchableInput\DTO\SearchResult;
use DefStudio\SearchableInput\Forms\Components\SearchableInput;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentSlot;
use Livewire\Component;
use Livewire\Livewire;

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

it('null guards the suggestion list when the field uses key()', function () {
    config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);

    $field = SearchableInput::make('description')
        ->key('description')
        ->options([
            'Lorem ipsum dolor' => '[A001] Lorem ipsum dolor.',
        ]);

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
        ->toContain('x-show="(suggestions ?? []).length > 0"')
        ->toContain('x-for="(suggestion, index) in (suggestions ?? [])"')
        ->toContain('set((suggestions ?? [])[selected_suggestion])');
});

it('returns null from an unresolved field key and results from the absolute key', function () {
    config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);

    $component = new class extends Component implements HasSchemas
    {
        use InteractsWithSchemas;

        public array $data = [];

        public function form(Schema $schema): Schema
        {
            return $schema->components([
                SearchableInput::make('description')
                    ->key('description')
                    ->options([
                        'Lorem ipsum dolor' => '[A001] Lorem ipsum dolor.',
                        'Aspernatur labore qui fugiat' => '[A001] Aspernatur labore qui fugiat.',
                    ]),
            ]);
        }

        public function render(): string
        {
            return '<div>{{ $this->form }}</div>';
        }
    };

    $test = Livewire::test($component::class);
    $field = $test->instance()->getSchema('form')->getComponent('description', isAbsoluteKey: false);

    expect($field)->not->toBeNull()
        ->and($field->getKey())->toBe('form.description')
        ->and($test->instance()->callSchemaComponentMethod('description', 'getSearchResultsForJs', ['search' => 'Lorem']))->toBeNull()
        ->and($test->instance()->callSchemaComponentMethod($field->getKey(), 'getSearchResultsForJs', ['search' => 'Lorem']))->toBe([
            [
                'value' => 'Lorem ipsum dolor',
                'label' => '[A001] Lorem ipsum dolor.',
                'data' => [],
            ],
        ]);
});

it('coerces a null search response into an empty suggestion list', function () {
    $node = shell_exec('node -v 2>&1');

    if (! is_string($node) || ! preg_match('/^v\d+/', trim($node))) {
        test()->markTestSkipped('Node is not available');
    }

    $result = Process::path(dirname(__DIR__))->run(['node', 'tests/js/suggestions.test.mjs']);

    expect($result->successful())->toBeTrue($result->errorOutput() . $result->output());
});
