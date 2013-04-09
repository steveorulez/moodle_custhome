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
 * Helper functions for custhome block
 *
 * @package    block_custhome
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Display overview for courses
 *
 * @param array $courses courses for which overview needs to be shown
 * @return array html overview
 */
function block_custhome_get_overviews($courses) {
    global $USER,$DB,$CFG;
    $htmlarray = array();
    if ($modules = get_plugin_list_with_function('mod', 'print_overview')) {
        foreach ($modules as $fname) {
            $fname($courses,$htmlarray);
        }
    }
    
    //Get course new resources    
    foreach($courses as $course){
        
        $timestart = round(time() - COURSE_MAX_RECENT_PERIOD, -2); // better db caching for guests - 100 seconds

        if (!isguestuser()) {
            if (!empty($USER->lastcourseaccess[$course->id])) {
                if ($USER->lastcourseaccess[$course->id] > $timestart) {
                    $timestart = $USER->lastcourseaccess[$course->id];
                }
            }
        }
        if($logs = $DB->get_records_select('log', "time > ? AND course = ? AND module = 'resource' AND action = 'add'",
                                    array($timestart, $course->id), "id ASC")){
        
            $files = html_writer::start_tag('div', array('class' => 'resource overview'));
            foreach($logs as $resource){
                if($cm = get_coursemodule_from_id('resource',$resource->cmid)){//If not deleted
                    $files .= html_writer::link(
                            $CFG->wwwroot."/mod/resource/view.php?id=".$resource->cmid,
                            $cm->name,
                            array('title' => $cm->name))."<br />";
                }
            }
            $files.= html_writer::end_tag('div');
            $htmlarray[$course->id]['resource'] = $files;
        }
    }
    
    //Get course new URL    
    foreach($courses as $course){
        
        $timestart = round(time() - COURSE_MAX_RECENT_PERIOD, -2); // better db caching for guests - 100 seconds

        if (!isguestuser()) {
            if (!empty($USER->lastcourseaccess[$course->id])) {
                if ($USER->lastcourseaccess[$course->id] > $timestart) {
                    $timestart = $USER->lastcourseaccess[$course->id];
                }
            }
        }
        if($logs = $DB->get_records_select('log', "time > ? AND course = ? AND module = 'url' AND action = 'add'",
                                    array($timestart, $course->id), "id ASC")){
        
            $files = html_writer::start_tag('div', array('class' => 'url overview'));
            foreach($logs as $resource){
                if($cm = get_coursemodule_from_id('url',$resource->cmid)){//If not deleted
                    $files .= html_writer::link($CFG->wwwroot."/mod/url/view.php?id=".$resource->cmid,$cm->name,array('title' => $cm->name))."<br />";
                }
            }
            $files.= html_writer::end_tag('div');
            $htmlarray[$course->id]['url'] = $files;
        }
    }
    
    //Get course new Folder    
    foreach($courses as $course){
        
        $timestart = round(time() - COURSE_MAX_RECENT_PERIOD, -2); // better db caching for guests - 100 seconds

        if (!isguestuser()) {
            if (!empty($USER->lastcourseaccess[$course->id])) {
                if ($USER->lastcourseaccess[$course->id] > $timestart) {
                    $timestart = $USER->lastcourseaccess[$course->id];
                }
            }
        }
        if($logs = $DB->get_records_select('log', "time > ? AND course = ? AND module = 'folder' AND action = 'add'",
                                    array($timestart, $course->id), "id ASC")){
        
            $files = html_writer::start_tag('div', array('class' => 'folder overview'));
            foreach($logs as $resource){
                if($cm = get_coursemodule_from_id('folder',$resource->cmid)){//If not deleted
                    $files .= html_writer::link($CFG->wwwroot."/mod/folder/view.php?id=".$resource->cmid,$cm->name,array('title' => $cm->name))."<br />";
                }
            }
            $files.= html_writer::end_tag('div');
            $htmlarray[$course->id]['folder'] = $files;
        }
    }

    return $htmlarray;
}

/**
 * Sets user preference for maximum courses to be displayed in custhome block
 *
 * @param int $number maximum courses which should be visible
 */
function block_custhome_update_mynumber($number) {
    set_user_preference('custhome_number_of_courses', $number);
}

/**
 * Sets user course sorting preference in custhome block
 *
 * @param array $sortorder sort order of course
 */
function block_custhome_update_myorder($sortorder) {
    set_user_preference('custhome_course_order', serialize($sortorder));
}

/**
 * Returns shortname of activities in course
 *
 * @param int $courseid id of course for which activity shortname is needed
 * @return string|bool list of child shortname
 */
function block_custhome_get_child_shortnames($courseid) {
    global $DB;
    $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
    $sql = "SELECT c.id, c.shortname, $ctxselect
            FROM {enrol} e
            JOIN {course} c ON (c.id = e.customint1)
            JOIN {context} ctx ON (ctx.instanceid = e.customint1)
            WHERE e.courseid = :courseid AND e.enrol = :method AND ctx.contextlevel = :contextlevel ORDER BY e.sortorder";
    $params = array('method' => 'meta', 'courseid' => $courseid, 'contextlevel' => CONTEXT_COURSE);

    if ($results = $DB->get_records_sql($sql, $params)) {
        $shortnames = array();
        // Preload the context we will need it to format the category name shortly.
        foreach ($results as $res) {
            context_helper::preload_from_record($res);
            $context = context_course::instance($res->id);
            $shortnames[] = format_string($res->shortname, true, $context);
        }
        $total = count($shortnames);
        $suffix = '';
        if ($total > 10) {
            $shortnames = array_slice($shortnames, 0, 10);
            $diff = $total - count($shortnames);
            if ($diff > 1) {
                $suffix = get_string('shortnamesufixprural', 'block_custhome', $diff);
            } else {
                $suffix = get_string('shortnamesufixsingular', 'block_custhome', $diff);
            }
        }
        $shortnames = get_string('shortnameprefix', 'block_custhome', implode('; ', $shortnames));
        $shortnames .= $suffix;
    }

    return isset($shortnames) ? $shortnames : false;
}

/**
 * Returns maximum number of courses which will be displayed in custhome block
 *
 * @return int maximum number of courses
 */
/*function block_custhome_get_max_user_courses() {
    // Get block configuration
    $config = get_config('block_custhome');
    $limit = $config->defaultmaxcourses;

    // If max course is not set then try get user preference
    if (empty($config->forcedefaultmaxcourses)) {
        $limit = get_user_preferences('custhome_number_of_courses', $limit);
    }
    return $limit;
}
*/
/**
 * Return sorted list of user courses
 *
 * @return array list of sorted courses and count of courses.
 */
function block_custhome_get_sorted_courses() {
    global $USER;

    //$limit = block_custhome_get_max_user_courses();

    $courses = enrol_get_my_courses('id, shortname, fullname, modinfo, sectioncache');
    $site = get_site();

    if (array_key_exists($site->id,$courses)) {
        unset($courses[$site->id]);
    }

    foreach ($courses as $c) {
        if (isset($USER->lastcourseaccess[$c->id])) {
            $courses[$c->id]->lastaccess = $USER->lastcourseaccess[$c->id];
        } else {
            $courses[$c->id]->lastaccess = 0;
        }
    }

    // Get remote courses.
    $remotecourses = array();
    if (is_enabled_auth('mnet')) {
        $remotecourses = get_my_remotecourses();
    }
    // Remote courses will have -ve remoteid as key, so it can be differentiated from normal courses
    foreach ($remotecourses as $id => $val) {
        $remoteid = $val->remoteid * -1;
        $val->id = $remoteid;
        $courses[$remoteid] = $val;
    }

    $order = array();
    if (!is_null($usersortorder = get_user_preferences('custhome_course_order'))) {
        $order = unserialize($usersortorder);
    }

    $sortedcourses = array();
    $counter = 0;
    // Get courses in sort order into list.
    foreach ($order as $key => $cid) {        
        
        if(is_numeric($cid)){
            // Make sure user is still enroled.
            if (isset($courses[$cid])) {
                $sortedcourses[$cid] = $courses[$cid];
                $counter++;
            }        
        }else{ //Category
            $sortedcourses[$counter++."cat"]=$cid;
            
        }
    }
    // Append unsorted courses if limit allows
    foreach ($courses as $c) {        
        if (!in_array($c->id, $order)) {
            $sortedcourses[$c->id] = $c;
            $counter++;
        }
    }

    // From list extract site courses for overview
    $sitecourses = array();
    foreach ($sortedcourses as $key => $course) {
        //print_r($course);
        if(!is_string($course)){
            if ($course->id > 0) {
                $sitecourses[$key] = $course;
            }
        }
    }
    
   
    return array($sortedcourses, $sitecourses, count($courses));
}
