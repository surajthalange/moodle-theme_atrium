<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace theme_atrium\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;
use moodle_url;
use theme_atrium\local\courses;
use theme_atrium\local\frontpage_settings as settings;

/**
 * The designed front page: every enabled section, in order, ready for its template.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class frontpage implements renderable, templatable {
    /**
     * Whether the designed front page should render on the current page.
     *
     * @return bool
     */
    public static function wanted(): bool {
        global $PAGE;
        return $PAGE->pagelayout === 'frontpage' && settings::enabled();
    }

    /**
     * Export for template.
     *
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(renderer_base $output): array {
        $counts = courses::site_counts();
        $data = ['sections' => []];
        foreach (settings::SECTIONS as $section) {
            if (!settings::section_enabled($section)) {
                continue;
            }
            $method = 'export_' . $section;
            $exported = $this->$method($output, $counts);
            if ($exported === null) {
                continue;
            }
            $data[$section] = $exported;
            $data['sections'][] = $section;
        }
        $data['hassections'] = !empty($data['sections']);
        $data['transparentnavbar'] = isset($data['hero']) && $data['hero']['transparentnavbar'];
        return $data;
    }

    /**
     * Hero.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array
     */
    private function export_hero(renderer_base $output, array $counts): array {
        $hero = settings::hero();
        $theme = \theme_config::load('atrium');
        $imageurl = $theme->setting_file_url('fp_heroimage', 'fp_heroimage');
        return [
            'heading' => $this->format(settings::resolve($hero['heading'], $counts)),
            'subheading' => $this->format(settings::resolve($hero['subheading'], $counts)),
            'button1' => $this->button($hero['button1text'], $hero['button1url']),
            'button2' => $this->button($hero['button2text'], $hero['button2url']),
            'imageurl' => $imageurl ?: '',
            'hasimage' => !empty($imageurl),
            'align' => $hero['align'],
            'height' => $hero['height'],
            'transparentnavbar' => $hero['transparentnavbar'],
        ];
    }

    /**
     * Feature blocks.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array|null
     */
    private function export_features(renderer_base $output, array $counts): ?array {
        $features = settings::features();
        if (!$features['items']) {
            return null;
        }
        $items = [];
        foreach ($features['items'] as $item) {
            $items[] = [
                'icon' => $item['icon'],
                'title' => $this->format($item['title']),
                'text' => $this->format($item['text']),
                'url' => $item['url'] !== '' ? (new moodle_url($item['url']))->out(false) : '',
                'haslink' => $item['url'] !== '',
            ];
        }
        return [
            'heading' => $this->format($features['heading']),
            'items' => $items,
            'columns' => count($items) > 3 ? 3 : count($items),
        ];
    }

    /**
     * Course showcase.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array|null
     */
    private function export_showcase(renderer_base $output, array $counts): ?array {
        $showcase = settings::showcase();
        switch ($showcase['source']) {
            case 'category':
                $list = courses::in_category($showcase['category'], $showcase['count']);
                break;
            case 'ids':
                $list = array_slice(courses::by_ids($showcase['ids']), 0, $showcase['count']);
                break;
            default:
                $list = courses::latest($showcase['count']);
        }
        if (!$list) {
            return null;
        }
        $cards = [];
        foreach ($list as $course) {
            $cards[] = (new course_card($course))->export_for_template($output);
        }
        $browseurl = new moodle_url('/course/index.php');
        if ($showcase['source'] === 'category' && $showcase['category']) {
            $browseurl->param('categoryid', $showcase['category']);
        }
        return [
            'heading' => $this->format($showcase['heading']),
            'cards' => $cards,
            'browseurl' => $browseurl->out(false),
        ];
    }

    /**
     * Stats strip.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array|null
     */
    private function export_stats(renderer_base $output, array $counts): ?array {
        $items = [];
        foreach (settings::stats() as $stat) {
            $items[] = [
                'label' => $this->format($stat['label']),
                'value' => $this->format(settings::resolve($stat['value'], $counts)),
            ];
        }
        return $items ? ['items' => $items] : null;
    }

    /**
     * Testimonials.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array|null
     */
    private function export_testimonials(renderer_base $output, array $counts): ?array {
        $testimonials = settings::testimonials();
        if (!$testimonials['items']) {
            return null;
        }
        $theme = \theme_config::load('atrium');
        $items = [];
        foreach ($testimonials['items'] as $item) {
            $area = 'fp_testimonial' . $item['index'] . '_photo';
            $photo = $theme->setting_file_url($area, $area);
            $items[] = [
                'quote' => $this->format($item['quote']),
                'name' => $this->format($item['name']),
                'role' => $this->format($item['role']),
                'photourl' => $photo ?: '',
                'hasphoto' => !empty($photo),
                'initial' => \core_text::strtoupper(\core_text::substr(trim($item['name']) ?: '?', 0, 1)),
            ];
        }
        return ['heading' => $this->format($testimonials['heading']), 'items' => $items];
    }

    /**
     * About band.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array|null
     */
    private function export_about(renderer_base $output, array $counts): ?array {
        $about = settings::about();
        if (trim($about['heading']) === '' && trim(strip_tags($about['text'])) === '') {
            return null;
        }
        $theme = \theme_config::load('atrium');
        $imageurl = $theme->setting_file_url('fp_aboutimage', 'fp_aboutimage');
        return [
            'heading' => $this->format($about['heading']),
            'text' => format_text($about['text'], FORMAT_HTML, ['context' => \context_system::instance(), 'noclean' => true]),
            'imageurl' => $imageurl ?: '',
            'hasimage' => !empty($imageurl),
            'imageleft' => $about['imageside'] === 'left',
            'button' => $this->button($about['buttontext'], $about['buttonurl']),
        ];
    }

    /**
     * Call-to-action band.
     *
     * @param renderer_base $output
     * @param array $counts
     * @return array
     */
    private function export_cta(renderer_base $output, array $counts): array {
        $cta = settings::cta();
        $theme = \theme_config::load('atrium');
        $imageurl = $cta['background'] === 'image' ? $theme->setting_file_url('fp_ctaimage', 'fp_ctaimage') : '';
        return [
            'heading' => $this->format(settings::resolve($cta['heading'], $counts)),
            'text' => $this->format(settings::resolve($cta['text'], $counts)),
            'button' => $this->button($cta['buttontext'], $cta['buttonurl']),
            'background' => $imageurl ? 'image' : ($cta['background'] === 'image' ? 'accent' : $cta['background']),
            'imageurl' => $imageurl ?: '',
        ];
    }

    /**
     * A button, or false when it has no text.
     *
     * @param string $text
     * @param string $url
     * @return array|false
     */
    private function button(string $text, string $url) {
        if (trim($text) === '') {
            return false;
        }
        $url = trim($url) === '' ? '/' : $url;
        return ['text' => $this->format($text), 'url' => (new moodle_url($url))->out(false)];
    }

    /**
     * Format a short administrator-entered string.
     *
     * @param string $text
     * @return string
     */
    private function format(string $text): string {
        return format_string($text, true, ['context' => \context_system::instance()]);
    }
}
