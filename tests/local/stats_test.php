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

use completion_completion;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the dashboard counts.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\local\stats::class)]
final class stats_test extends \advanced_testcase {
    /**
     * In progress counts active enrolments in visible, completion-tracked, incomplete courses;
     * completed counts completion records; the two are disjoint.
     */
    public function test_course_counts(): void {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();

        $inprogress = $generator->create_course(['enablecompletion' => 1]);
        $completed = $generator->create_course(['enablecompletion' => 1]);
        $untracked = $generator->create_course(['enablecompletion' => 0]);
        $hidden = $generator->create_course(['enablecompletion' => 1, 'visible' => 0]);
        foreach ([$inprogress, $completed, $untracked, $hidden] as $course) {
            $generator->enrol_user($user->id, $course->id, 'student');
        }
        $completion = new completion_completion(['userid' => $user->id, 'course' => $completed->id]);
        $completion->mark_complete(time() - DAYSECS);

        $counts = stats::for_user((int) $user->id);
        $this->assertSame(1, $counts['inprogress']);
        $this->assertSame(1, $counts['completed']);
        $this->assertSame(0, $counts['due']);
        // Completing a course sends the learner a notification, which unread counts.
        $this->assertSame(1, $counts['unread']);
    }

    /**
     * A completion event drops the cached counts at once; otherwise they are held.
     */
    public function test_cache_and_invalidation(): void {
        global $CFG;
        require_once($CFG->libdir . '/completionlib.php');
        $this->resetAfterTest();
        set_config('enablecompletion', 1);
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course(['enablecompletion' => 1]);
        $generator->enrol_user($user->id, $course->id, 'student');

        $this->assertSame(['inprogress' => 1, 'completed' => 0, 'due' => 0, 'unread' => 0], stats::for_user((int) $user->id));

        // A second enrolment without an event is not seen until the cache expires.
        $other = $generator->create_course(['enablecompletion' => 1]);
        $generator->enrol_user($user->id, $other->id, 'student');
        $this->assertSame(1, stats::for_user((int) $user->id)['inprogress']);
        stats::invalidate((int) $user->id);
        $this->assertSame(2, stats::for_user((int) $user->id)['inprogress']);

        // Completing a course fires core's event, which the observer turns into an invalidation.
        $completion = new completion_completion(['userid' => $user->id, 'course' => $course->id]);
        $completion->mark_complete();
        $counts = stats::for_user((int) $user->id);
        $this->assertSame(1, $counts['inprogress']);
        $this->assertSame(1, $counts['completed']);
    }

    /**
     * Due counts action events in the next seven days and nothing beyond.
     */
    public function test_due(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $course = $generator->create_course();
        $generator->enrol_user($user->id, $course->id, 'student');
        $assign = $generator->get_plugin_generator('mod_assign');
        $assign->create_instance(['course' => $course->id, 'duedate' => time() + 3 * DAYSECS]);
        $assign->create_instance(['course' => $course->id, 'duedate' => time() + 30 * DAYSECS]);
        $assign->create_instance(['course' => $course->id, 'duedate' => time() - DAYSECS]);

        $this->assertSame(1, stats::for_user((int) $user->id)['due']);
    }

    /**
     * Unread counts conversations with unread messages.
     */
    public function test_unread(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $user = $generator->create_user();
        $sender = $generator->create_user();
        $this->assertSame(0, stats::for_user((int) $user->id)['unread']);

        \core_message\tests\helper::send_fake_message($sender, $user, 'Hello');
        \core_message\tests\helper::send_fake_message($sender, $user, 'Again');
        stats::invalidate((int) $user->id);
        $this->assertSame(1, stats::for_user((int) $user->id)['unread'], 'One conversation, however many messages');
    }
}
