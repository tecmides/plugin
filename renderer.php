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

defined('MOODLE_INTERNAL') || die;

class report_tecmides_renderer extends plugin_renderer_base
{

    /**
     * Defer to template
     *
     * @param dashboard $dashboard
     * @return string html for the page
     */
    public function render_dashboard( \tecmides\output\dashboard $dashboard )
    {
        $data = $dashboard->export_for_template($this);
        $template = $dashboard->get_template_name();

        return parent::render_from_template("report_tecmides/{$template}", $data);

    }

}
