// noinspection JSUnusedGlobalSymbols,JSUnresolvedReference

export default function searchableInput({statePath}) {
    return {
        previous_value: null,
        value: this.$wire.entangle(statePath),
        suggestions: [],
        selected_suggestion: 0,
        refresh_suggestions: function() {
            if (this.value === this.previous_value) {
                return
            }

            if (!this.value) {
                this.suggestions = []
                this.previous_value = null
                return
            }

            this.previous_value = this.value

            // noinspection JSPotentiallyInvalidUsageOfThis
            this.$wire.mountAction('search', { value: this.value })
                .then(response => {
                    this.suggestions = response
                    this.selected_suggestion = 0
                })
        },
        set: function(item) {
            if (item === undefined) {
                return
            }

            this.value = item.value
            this.suggestions = []

            // noinspection JSPotentiallyInvalidUsageOfThis
            this.$wire.mountAction('item_selected', { item: item })
        },
        previous_suggestion() {
            this.selected_suggestion--

            if (this.selected_suggestion < 0) {
                this.selected_suggestion = 0
            }
        },
        next_suggestion() {
            this.selected_suggestion++

            if (this.selected_suggestion > this.suggestions.length - 1) {
                this.selected_suggestion = this.suggestions.length - 1
            }
        },
    }
}
