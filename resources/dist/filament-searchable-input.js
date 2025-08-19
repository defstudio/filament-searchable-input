function t({ statePath: s }) {
  return {
    previous_value: null,
    value: this.$wire.entangle(s),
    suggestions: [],
    selected_suggestion: 0,
    refresh_suggestions: function() {
      if (this.value !== this.previous_value) {
        if (!this.value) {
          this.suggestions = [], this.previous_value = null;
          return;
        }
        this.previous_value = this.value, this.$wire.mountAction(s, "search", { value: this.value }).then((e) => {
          this.suggestions = e, this.selected_suggestion = 0;
        });
      }
    },
    set: function(e) {
      e !== void 0 && (this.value = e.value, this.suggestions = [], this.$wire.mountAction(s, "item_selected", { item: e }));
    },
    previous_suggestion() {
      this.selected_suggestion--, this.selected_suggestion < 0 && (this.selected_suggestion = 0);
    },
    next_suggestion() {
      this.selected_suggestion++, this.selected_suggestion > this.suggestions.length - 1 && (this.selected_suggestion = this.suggestions.length - 1);
    }
  };
}
export {
  t as default
};
//# sourceMappingURL=filament-searchable-input.js.map
