<?php
namespace theme_debtheme\output;

defined('MOODLE_INTERNAL') || die;
use core\output\core_renderer;

class renderer extends core_renderer {
    public function get_flight_deck_context() {
        $buttons = [
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'synopsis']), 'text' => get_string('synopsis', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'best_courses']), 'text' => get_string('bestcourses', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'cohort_performance']), 'text' => get_string('cohortperformance', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'best_learning_plan']), 'text' => get_string('bestlearningplan', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => '30_day_synopsis']), 'text' => get_string('synopsis30', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'lowest_courses']), 'text' => get_string('lowestcourses', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'study_frequency']), 'text' => get_string('studyfrequency', 'theme_debtheme')],
            ['url' => new \moodle_url('/local/chartplugin/index.php', ['type' => 'preferred_learning_style']), 'text' => get_string('preferredstyle', 'theme_debtheme')],
        ];

        foreach ($buttons as &$button) {
            if ($button['url']->out_as_local_url() === $this->page->url->out_as_local_url()) {
                $button['active'] = true;
            }
        }
        return ['buttons' => $buttons];
    }
}