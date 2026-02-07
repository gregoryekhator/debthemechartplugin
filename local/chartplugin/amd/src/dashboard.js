import ChartRegistry from './chart_registry';
import StateManager from './state_manager';

/**
 * Load chart data from server endpoint.
 * @param {string} mode Chart mode identifier
 */
export const loadChart = (mode) => {
    fetch(M.cfg.wwwroot + '/local/chartplugin/api/charts.php?chart=' + encodeURIComponent(mode), {
        credentials: 'same-origin'  
    })
    .then(response => {
        if (!response.ok) throw new Error('Server error loading chart data');
        return response.json();
    })
    .then(data => {
        // This sends the data to your registry to be drawn on the canvas
        ChartRegistry.render(data);
        StateManager.record(mode, data);
    })
    .catch(error => {
        require(['core/log'], function(Log) {
            Log.error(error);
        });
    });
};

/**
 * Initialize listeners for dashboard buttons.
 * This is the function called by columns2.php
 */
export const init = () => {
    // Standard Moodle practice: check if we are on the right page
    // We target the header buttons container specifically
    const dashboardContainer = document.querySelector('.debtheme-header-buttons');
    if (!dashboardContainer) return;

    // Use a single listener for all buttons (Event Delegation)
    dashboardContainer.addEventListener('click', (e) => {
        // Find the button (even if they clicked the text inside it)
        // We look for .header-btn as seen in your Mustache file
        const btn = e.target.closest('.header-btn');
        
        // If it's not one of our buttons or doesn't have an ID, ignore the click
        if (!btn || !btn.id) return;

        // Map button IDs to chart modes
        const chartMap = {
            'synopsis_btn': 'synopsis',
            'best_courses_btn': 'best_courses',
            'cohort_perf_btn': 'cohort_performance',
            'best_learning_btn': 'best_learning_plan',
            '30day_synopsis_btn': '30day_synopsis',
            'lowest_courses_btn': 'lowest_courses',
            'study_freq_btn': 'study_frequency',
            'pref_learning_btn': 'preferred_learning_style'
        };

        if (chartMap[btn.id]) {
            e.preventDefault();
            
            // Visual feedback: briefly dim the container while loading
            const displayArea = document.getElementById('chart-display-area');
            if (displayArea) displayArea.style.opacity = '0.5';

            // Trigger the data fetch
            loadChart(chartMap[btn.id]);
            
            // Restore opacity after a short delay
            setTimeout(() => { if (displayArea) displayArea.style.opacity = '1'; }, 500);
        }
    });
};
