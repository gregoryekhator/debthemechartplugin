/**
 * Chart Registry
 *
 * Single authoritative definition of all charts
 * used by the analytics dashboard.
 *
 * @module local_chartplugin/chart_registry
 */
define([], function() {

    return {
        synopsis: {
            title: 'Synopsis (to date)',
            endpoint: 'synopsis',
            description: 'Overall performance summary'
        },

        'best-courses': {
            title: 'Best Courses',
            endpoint: 'best_courses',
            description: 'Highest performing courses'
        },

        cohort: {
            title: 'Cohort Performance',
            endpoint: 'cohort',
            description: 'Performance by cohort'
        },

        'best-plan': {
            title: 'Best Learning Plan',
            endpoint: 'best_plan',
            description: 'Most effective learning plan'
        },

        'synopsis-30': {
            title: '30-Day Synopsis',
            endpoint: 'synopsis_30',
            description: 'Recent performance snapshot'
        },

        'worst-courses': {
            title: 'Lowest Courses',
            endpoint: 'worst_courses',
            description: 'Lowest performing courses'
        },

        frequency: {
            title: 'Study Frequency',
            endpoint: 'frequency',
            description: 'Engagement frequency'
        },

        'preferred-style': {
            title: 'Preferred Learning Style',
            endpoint: 'preferred_style',
            description: 'Learning modality preference'
        }
    };
});
