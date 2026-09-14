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

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for the dashboard hero.
 *
 * @package    theme_atrium
 * @copyright  2026 Suraj Thalange
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\theme_atrium\output\dashboard_hero::class)]
final class dashboard_hero_test extends \advanced_testcase {
    /**
     * The hero renders on the dashboard page for a real user, and follows its setting.
     */
    public function test_wanted(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $PAGE->set_pagelayout('mydashboard');
        $PAGE->set_pagetype('my-index');
        $this->assertFalse(dashboard_hero::wanted(), 'Not logged in');

        $this->setUser($user);
        $this->assertTrue(dashboard_hero::wanted());

        set_config('showhero', 0, 'theme_atrium');
        $this->assertFalse(dashboard_hero::wanted());
        set_config('showhero', 1, 'theme_atrium');

        $PAGE->set_pagetype('message-index');
        $this->assertFalse(dashboard_hero::wanted(), 'Messaging borrows the layout but is not the dashboard');

        $PAGE->set_pagetype('my-index');
        $this->setGuestUser();
        $this->assertFalse(dashboard_hero::wanted());
    }

    /**
     * The greeting replaces its placeholders and each tile follows its setting.
     */
    public function test_export(): void {
        global $PAGE;
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user(['firstname' => 'Ada', 'lastname' => 'Lovelace']);
        $this->setUser($user);
        $output = $PAGE->get_renderer('core');

        $data = (new dashboard_hero($user))->export_for_template($output);
        $this->assertSame('Welcome back, Ada', $data['greeting']);
        $this->assertTrue($data['hastiles']);
        $this->assertSame(['inprogress', 'completed', 'due', 'unread'], array_column($data['tiles'], 'key'));
        $this->assertSame([0, 0, 0, 0], array_column($data['tiles'], 'count'));
        $this->assertSame('Unread messages and notifications', $data['tiles'][3]['help']);
        $this->assertFalse($data['hasimage']);
        $this->assertNotEmpty($data['date']);

        set_config('herogreeting', '<b>Hello</b> {fullname} ({firstname})', 'theme_atrium');
        set_config('showstat_due', 0, 'theme_atrium');
        set_config('showstat_unread', 0, 'theme_atrium');
        $data = (new dashboard_hero($user))->export_for_template($output);
        $this->assertSame('Hello Ada Lovelace (Ada)', $data['greeting'], 'Tags are stripped');
        $this->assertSame(['inprogress', 'completed'], array_column($data['tiles'], 'key'));

        set_config('showstat_inprogress', 0, 'theme_atrium');
        set_config('showstat_completed', 0, 'theme_atrium');
        $data = (new dashboard_hero($user))->export_for_template($output);
        $this->assertFalse($data['hastiles']);
    }
}
