/**
 * Chart state manager
 *
 * Responsible for persisting chart selections
 * in deterministic order for replay & ML use.
 */
define([], function() {

    const STORAGE_KEY = 'local_chartplugin.dashboard.state';

    /**
     * Load persisted state.
     *
     * @returns {Object}
     */
    function load() {
        try {
            return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
        } catch (e) {
            return {};
        }
    }

    /**
     * Save state.
     *
     * @param {Object} state
     */
    function save(state) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
    }

    /**
     * Record a new chart selection.
     *
     * @param {string} chart
     * @param {string} title
     */
    function record(chart, title) {
        const current = load();

        const next = {
            previous: current.current || null,
            current: {
                chart: chart,
                title: title,
                timestamp: Date.now()
            }
        };

        save(next);
    }

    return {
        load,
        record
    };
});
