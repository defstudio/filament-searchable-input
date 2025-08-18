<?php

namespace DefStudio\SearchableInput\Forms\Components;

use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

class SearchableInput extends TextInput
{
    /** @var ?Closure(string): ?array<int|string, string|SearchResult> */
    protected ?Closure $searchUsing = null;

    /** @var ?Closure(SearchResult): void */
    protected ?Closure $onItemSelected = null;

    /** @var array<array-key, string>|Closure(): ?array<array-key, string>|null */
    protected array | Closure | null $options = null;

    protected function setUp(): void
    {
        $this->fieldWrapperView('searchable-input-wrapper');

        $this->extraInputAttributes(['x-model' => 'value']);

        $this->registerActions([
            Action::make('search')->action(function (SearchableInput $component, array $arguments): array {
                if ($component->isDisabled() || $component->isReadOnly()) {
                    return [];
                }

                $search = $arguments['value'];

                $results = $this->evaluate($this->searchUsing, [
                    'search' => $search,
                    'options' => $this->getOptions(),
                ]);

                $results ??= collect($this->getOptions())
                    ->filter(fn (string $option) => str($option)->contains($search, true))
                    ->toArray();

                if (array_is_list($results)) {
                    $results = collect($results)
                        ->map(fn ($item) => $item instanceof SearchResult ? $item : SearchResult::make($item))
                        ->toArray();
                } else {
                    $results = collect($results)
                        ->map(fn ($item, $key) => $item instanceof SearchResult ? $item : SearchResult::make($key, $item))
                        ->toArray();
                }

                return array_values($results);
            }),

            Action::make('item_selected')->action(function ($arguments, SearchableInput $component) {
                $component->evaluate($this->onItemSelected, [
                    'item' => SearchResult::fromArray($arguments['item']),
                ]);
            }),
        ]);
    }

    /**
     * @return array<array-key, string>
     */
    public function getOptions(): array
    {
        return $this->evaluate($this->options) ?? [];
    }

    /**
     * @param  array<array-key, string>|Closure(): array<array-key, string>|null  $options
     */
    public function options(array | Closure | null $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param  ?Closure(string): ?array<int|string, string|SearchResult>  $searchUsing
     */
    public function searchUsing(?Closure $searchUsing): static
    {
        $this->searchUsing = $searchUsing;

        return $this;
    }

    /**
     * @param  ?Closure(SearchResult $item): void  $callback
     */
    public function onItemSelected(?Closure $callback): static
    {
        $this->onItemSelected = $callback;

        return $this;
    }

    public function isSearchEnabled(): bool
    {
        return $this->searchUsing !== null || $this->getOptions() !== [];
    }
}
