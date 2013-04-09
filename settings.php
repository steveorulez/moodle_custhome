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

/**
 * custhome block settings
 *
 * @package    block_custhome
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    //$settings->add(new admin_setting_configtext('block_custhome/defaultmaxcourses', new lang_string('defaultmaxcourses', 'block_custhome'),
    //    new lang_string('defaultmaxcoursesdesc', 'block_custhome'), 10, PARAM_INT));
    //$settings->add(new admin_setting_configcheckbox('block_custhome/forcedefaultmaxcourses', new lang_string('forcedefaultmaxcourses', 'block_custhome'),
    //    new lang_string('forcedefaultmaxcoursesdesc', 'block_custhome'), 1, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('block_custhome/showchildren', new lang_string('showchildren', 'block_custhome'),
        new lang_string('showchildrendesc', 'block_custhome'), 1, PARAM_INT));
    $settings->add(new admin_setting_configcheckbox('block_custhome/showwelcomearea', new lang_string('showwelcomearea', 'block_custhome'),
        new lang_string('showwelcomeareadesc', 'block_custhome'), 1, PARAM_INT));
}
