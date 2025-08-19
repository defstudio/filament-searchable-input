<?php /** @noinspection PhpUnused */

namespace DefStudio\SearchableInput\Forms\Components;

use Closure;
use DefStudio\SearchableInput\DTO\SearchResult;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Support\Components\Attributes\ExposedLivewireMethod;
use Livewire\Attributes\Renderless;

class SearchableInput extends TextInput
{
    /** @var ?Closure(string): ?array<int|string, string|SearchResult> */
    protected ?Closure $searchUsing = null;

    /** @var ?Closure(SearchResult): void */
    protected ?Closure $onItemSelected = null;

    /** @var array<array-key, string>|Closure(): ?array<array-key, string>|null */
    protected array | Closure | null $options = null;

    #[ExposedLivewireMethod]
    #[Renderless]
    public function getSearchResultsForJs(string $search): array
    {
        if ($this->isDisabled() || $this->isReadOnly()) {
            return [];
        }

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
    }

    #[ExposedLivewireMethod]
    #[Renderless]
    public function reactOnItemSelectedFromJs(array $item): void
    {
        $this->evaluate($this->onItemSelected, [
            'item' => SearchResult::fromArray($item),
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
