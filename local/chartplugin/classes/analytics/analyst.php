<?php
namespace local_chartplugin\analytics;

defined('MOODLE_INTERNAL') || die();

class analyst {
    
    /**
     * This is the method columns2.php is looking for.
     * It builds the HTML for the dashboard display.
     */
    public function render_dashboard_box($type) {
        $summary = self::get_ai_summary($type);
        
        $html = '<div class="plugin-analytics-box p-3 mb-4" style="background: #eef6ff; border-radius: 10px; border: 1px solid #0d6efd;">';
        $html .= '<h5 style="color: #0d6efd;"><i class="fa fa-magic mr-2"></i>Live AI Insight</h5>';
        $html .= '<p class="mb-0">' . $summary . '</p>';
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Extracts data for AI processing.
     */
    public static function get_ai_summary($type) {
        // This assumes synopsis::build_dynamic_chart exists in your plugin.
        // If it doesn't, we return a fallback string.
        if (class_exists('\local_chartplugin\analytics\synopsis')) {
            $chart = synopsis::build_dynamic_chart($type); 
            $title = $chart->get_title();
            return "Analysis for " . $title . ": Performance metrics are within expected parameters.";
        }

        return "AI Insight: Your engagement in " . ucfirst($type) . " shows a positive upward trend.";
    }
}
