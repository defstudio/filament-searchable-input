//#region resources/js/components/searchable-input.js
function e({ key: e, statePath: t }) {
	return {
		previous_value: null,
		value: this.$wire.entangle(t),
		suggestions: [],
		selected_suggestion: 0,
		refresh_suggestions: function() {
			if (this.value !== this.previous_value) {
				if (!this.value) {
					this.suggestions = [], this.previous_value = null;
					return;
				}
				this.previous_value = this.value, this.$wire.callSchemaComponentMethod(e, "getSearchResultsForJs", { search: this.value }).then((e) => {
					this.suggestions = Array.isArray(e) ? e : [], this.selected_suggestion = 0;
				});
			}
		},
		set: function(t) {
			t !== void 0 && (this.value = t.value, this.suggestions = [], this.$wire.callSchemaComponentMethod(e, "reactOnItemSelectedFromJs", { item: t }));
		},
		previous_suggestion() {
			if (!Array.isArray(this.suggestions) || this.suggestions.length === 0) {
				this.selected_suggestion = 0;
				return;
			}
			this.selected_suggestion--, this.selected_suggestion < 0 && (this.selected_suggestion = 0);
		},
		next_suggestion() {
			if (!Array.isArray(this.suggestions) || this.suggestions.length === 0) {
				this.selected_suggestion = 0;
				return;
			}
			this.selected_suggestion++, this.selected_suggestion > this.suggestions.length - 1 && (this.selected_suggestion = this.suggestions.length - 1);
		}
	};
}
//#endregion
export { e as default };

//# sourceMappingURL=filament-searchable-input.js.map