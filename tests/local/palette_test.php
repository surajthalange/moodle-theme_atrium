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

namespace theme_atrium\local;

use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

/**
 * Tests for the command palette search.
 *
 * The course id reaches the palette from the browser, so every one of these asks the same
 * question: does this user get to see inside that course?
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\palette::class)]
final class palette_test extends \advanced_testcase {
    /**
     * The activity names the palette offers for a course, as a flat list.
     *
     * @param int $courseid
     * @return string[]
     */
    private function activity_labels(int $courseid): array {
        $title = get_string('palette_activities', 'theme_atrium');
        foreach (palette::search('', $courseid) as $group) {
            if ($group['title'] === $title) {
                return array_column($group['items'], 'label');
            }
        }
        return [];
    }

    /**
     * A course with one page in it, which is the interesting case: mod/page:view is
     * granted to the authenticated user archetype, so uservisible alone lets it through.
     *
     * @param array $courseoptions passed to the course generator
     * @return stdClass the course
     */
    private function course_with_a_page(array $courseoptions = []): stdClass {
        $course = $this->getDataGenerator()->create_course($courseoptions);
        $this->getDataGenerator()->create_module('page', [
            'course' => $course->id,
            'name' => 'Welcome and housekeeping',
        ]);
        return $course;
    }

    /**
     * Someone enrolled in the course sees its activities. Without this the rest of these
     * tests would pass on a palette that never returned anything.
     */
    public function test_enrolled_user_sees_the_activities(): void {
        $this->resetAfterTest();

        $course = $this->course_with_a_page();
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $this->assertSame(['Welcome and housekeeping'], $this->activity_labels($course->id));
    }

    /**
     * The finding from the Marketplace review of 1.3.0 (MMR-347). A signed-in user who is
     * not in the course can put its id on the request; uservisible does not stop them,
     * because mod/page:view is granted to every authenticated user and many module types
     * define no view capability at all.
     */
    public function test_user_not_in_the_course_sees_nothing(): void {
        $this->resetAfterTest();

        $course = $this->course_with_a_page();
        $this->setUser($this->getDataGenerator()->create_user());

        $this->assertSame([], $this->activity_labels($course->id));
    }

    /**
     * A hidden course is hidden from the people enrolled in it too.
     */
    public function test_hidden_course_is_not_listed_to_a_student(): void {
        $this->resetAfterTest();

        $course = $this->course_with_a_page(['visible' => 0]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id, 'student');
        $this->setUser($user);

        $this->assertSame([], $this->activity_labels($course->id));
    }

    /**
     * Someone who may see hidden courses still sees them.
     */
    public function test_hidden_course_is_listed_to_a_manager(): void {
        $this->resetAfterTest();

        $course = $this->course_with_a_page(['visible' => 0]);
        $this->setUser($this->getDataGenerator()->create_user());
        $this->setAdminUser();

        $this->assertSame(['Welcome and housekeeping'], $this->activity_labels($course->id));
    }

    /**
     * A course id that is not a course is an empty answer, not an exception. The palette
     * replies as JSON to a keystroke, so an uncaught exception would be a broken response
     * and a stack trace in the log on every press of a wrong digit.
     */
    public function test_unknown_course_id_is_an_empty_answer(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $this->assertSame([], $this->activity_labels(99999999));
    }
}
