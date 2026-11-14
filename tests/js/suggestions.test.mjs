import assert from 'node:assert/strict'
import searchableInput from '../../resources/js/components/searchable-input.js'

function boot(response) {
    const wire = {
        entangle() {
            return ''
        },
        callSchemaComponentMethod() {
            return Promise.resolve(response)
        },
    }

    const state = searchableInput.call({ $wire: wire }, {
        key: 'description',
        statePath: 'description',
    })

    state.$wire = wire

    return state
}

async function search(state, value) {
    state.value = value
    state.previous_value = null
    state.refresh_suggestions()
    await new Promise((resolve) => setTimeout(resolve, 0))
}

const nullResponse = boot(null)
await search(nullResponse, 'Lorem')
assert.deepEqual(nullResponse.suggestions, [])
assert.equal(nullResponse.selected_suggestion, 0)

const objectResponse = boot({ label: 'not a list' })
await search(objectResponse, 'Lorem')
assert.deepEqual(objectResponse.suggestions, [])

const results = [
    { value: 'Lorem ipsum dolor', label: '[A001] Lorem ipsum dolor.', data: {} },
    { value: 'Aspernatur labore qui fugiat', label: '[A001] Aspernatur labore qui fugiat.', data: {} },
]
const listResponse = boot(results)
await search(listResponse, 'Lorem')
assert.deepEqual(listResponse.suggestions, results)

const broken = boot([])
broken.suggestions = null
broken.selected_suggestion = 3
broken.next_suggestion()
broken.previous_suggestion()
assert.equal(broken.selected_suggestion, 0)

const navigation = boot(results)
await search(navigation, 'Lorem')
navigation.next_suggestion()
assert.equal(navigation.selected_suggestion, 1)
navigation.next_suggestion()
navigation.next_suggestion()
assert.equal(navigation.selected_suggestion, 1)
navigation.previous_suggestion()
navigation.previous_suggestion()
assert.equal(navigation.selected_suggestion, 0)
