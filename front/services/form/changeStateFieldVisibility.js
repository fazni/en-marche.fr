export function changeStateFieldVisibility(country, state) {
    if ('FR' === country.value) {
        state.classList.add('hidden');
    } else {
        state.classList.remove('hidden');
    }
}
