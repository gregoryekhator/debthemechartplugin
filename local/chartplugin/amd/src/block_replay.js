/**
 * Block replay controller
 *
 * Reads persisted chart state and updates
 * right-hand analytics blocks deterministically.
 */
define(['local_chartplugin/state_manager'], function(StateManager) {

    /**
     * Render block contents from stored state.
     */
    function render() {
        const state = StateManager.load();

        const last = document.querySelector('[data-role="last-chart"]');
        const previous = document.querySelector('[data-role="previous-chart"]');

        if (state.current && last) {
            last.innerHTML =
                '<strong>' + state.current.title + '</strong><br>' +
                '<small>' + new Date(state.current.timestamp).toLocaleString() + '</small>';
        }

        if (state.previous && previous) {
            previous.innerHTML =
                '<strong>' + state.previous.title + '</strong><br>' +
                '<small>' + new Date(state.previous.timestamp).toLocaleString() + '</small>';
        }
    }

    return {
        render
    };
});
