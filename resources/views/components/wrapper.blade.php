@php use Filament\Support\Facades\FilamentAsset; @endphp

@props([
    'field' => null,
    'hasInlineLabel' => null,
])

@if($field->isSearchEnabled())
    <x-filament-forms::field-wrapper
        :field="$field"
        :has-inline-label="$hasInlineLabel"

        class="def-fo-searchable-input-wrapper relative"

        x-load
        x-load-src="{{FilamentAsset::getAlpineComponentSrc('filament-searchable-input', 'defstudio/filament-searchable-input')}}"
        x-data="searchableInput({
            statePath: '{{$field->getStatePath()}}'
        })"

        x-on:click.away="suggestions=[]"

        x-on:keyup.prevent.enter=""
        x-on:keyup.prevent.esc=""
        x-on:keyup.prevent.up=""
        x-on:keyup.prevent.down=""
        x-on:keyup="$event.key != 'Enter' && $event.key != 'ArrowDown' && $event.key != 'ArrowUp' && $event.key != 'Escape' &&  refresh_suggestions"

        x-on:keydown.prevent.enter="set(suggestions[selected_suggestion])"
        x-on:keydown.prevent.esc="suggestions = []"
        x-on:keydown.prevent.up="previous_suggestion()"
        x-on:keydown.prevent.down="next_suggestion()"

        x-on:keydown.tab="suggestions = []"
    >
        {{$slot}}

        <div x-show="suggestions.length > 0"
             class="def-fo-searchable-input-dropdown
                    z-10 absolute top-full mt-2 left-0 px-1 py-1 overflow-hidden
                    rounded-lg bg-white dark:bg-gray-900
                    shadow-lg  ring-1 ring-gray-950/5 dark:ring-white/10
                    will-change-[visibility]
                    text-sm font-medium text-gray-950 dark:text-white"
        >
            <div class="def-fo-searchable-input-dropdown-wrapper max-h-60 overflow-y-auto">
                <ul class="def-fo-searchable-input-dropdown-list h-full" wire:loading.class.delay="def-fo-searchable-input-dropdown-list-loading animate-pulse">
                    <template x-for="(suggestion, index) in suggestions">
                        <li class="def-fo-searchable-input-dropdown-list-item px-2 py-2 rounded-md hover:bg-gray-50 active:bg-gray-200 cursor-pointer"
                            x-bind:class="{
                            'def-fo-searchable-input-dropdown-list-item-selected bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10': selected_suggestion === index
                        }"
                            x-text="`${suggestion.label}`"
                            x-on:click="set(suggestion)"
                        ></li>
                    </template>
                </ul>
            </div>
        </div>
    </x-filament-forms::field-wrapper>
@else
    <x-filament-forms::field-wrapper
        :field="$field"
        :has-inline-label="$hasInlineLabel"
    >{{$slot}}</x-filament-forms::field-wrapper>
@endif
