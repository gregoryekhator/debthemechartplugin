import ChartRegistry from './chart_registry';
import StateManager from './state_manager';

/**
 * Load chart data from server endpoint.
 *
 * @param {string} mode Chart mode identifier
 */
export const loadChart = (mode) => {
    fetch(M.cfg.wwwroot + '/local/chartplugin/api/charts.php?chart=' + encodeURIComponent(mode), {
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Server error loading chart data');
            }
            return response.json();
        })
        .then(data => {
            ChartRegistry.render(data);
            StateManager.record(mode, data);
        })
        .catch(error => {
            // Moodle-compliant logging (no console.log)
            require(['core/log'], function(Log) {
                Log.error(error);
            });
        });
};
