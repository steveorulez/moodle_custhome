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
 * custhome block rendrer
 *
 * @package    block_custhome
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * custhome block rendrer
 *
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_custhome_renderer extends plugin_renderer_base {

    /**
     * Construct contents of custhome block
     *
     * @param array $courses list of courses in sorted order
     * @param array $overviews list of course overviews
     * @return string html to be displayed in custhome block
     */
    public function custhome($courses, $overviews) {
        $html = '';
        $config = get_config('block_custhome');

        $html .= html_writer::start_tag('div', array('id' => 'course_list'));
        $courseordernumber = 0;
        $categoryordernumber = 0;
        $maxcourses = count($courses);
        // Intialize string/icon etc if user is editing.
        $url = null;
        $moveicon = null;
        $moveup[] = null;
        $movedown[] = null;
        if ($this->page->user_is_editing()) {
            if (ajaxenabled()) {
                $moveicon = html_writer::tag('div', html_writer::empty_tag('img', array('src' => $this->pix_url('i/move_2d')->out(false),
                                    'alt' => get_string('move'), 'class' => 'cursor',
                                    'title' => get_string('move'))
                                ), array('class' => 'move')
                );
            } else {
                $url = new moodle_url('/blocks/custhome/move.php', array('sesskey' => sesskey()));
                $moveup['str'] = get_string('moveup');
                $moveup['icon'] = $this->pix_url('t/up');
                $movedown['str'] = get_string('movedown');
                $movedown['icon'] = $this->pix_url('t/down');
            }
        }

        foreach ($courses as $key => $course) {
            if (is_object($course)) {
                
                $html .= $this->output->box_start('coursebox', "course-{$course->id}");
         
                $html .= html_writer::start_tag('div', array('class' => 'course_category'));//open course category
                $coursecategory = get_course_category($course->category);
                $html .= "Category: ";
                $html .= html_writer::link(new moodle_url('/course/category.php', array('sesskey' => sesskey(),'id'=>$coursecategory->id)), $coursecategory->name,array('title'=>'Category'));
                $html .= html_writer::end_tag('div');//close course category
                $html .= html_writer::start_tag('div', array('class' => 'course_title'));
                // Ajax enabled then add moveicon html
                if (!is_null($moveicon)) {
                    $html .= $moveicon;
                } else if (!is_null($url)) {
                    // Add course id to move link
                    $url->param('source', $course->id);
                    $html .= html_writer::start_tag('div', array('class' => 'moveicons'));
                    // Add an arrow to move course up.
                    if ($courseordernumber > 0) {
                        $url->param('move', -1);
                        $html .= html_writer::link($url, html_writer::empty_tag('img', array('src' => $moveup['icon'],
                                            'class' => 'up', 'alt' => $moveup['str'])), array('title' => $moveup['str'], 'class' => 'moveup'));
                    } else {
                        // Add a spacer to keep keep down arrow icons at right position.
                        $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('spacer'),
                                    'class' => 'movedownspacer'));
                    }
                    // Add an arrow to move course down.
                    if ($courseordernumber <= $maxcourses - 2) {
                        $url->param('move', 1);
                        $html .= html_writer::link($url, html_writer::empty_tag('img', array('src' => $movedown['icon'], 'class' => 'down', 'alt' => $movedown['str'])), array('title' => $movedown['str'], 'class' => 'movedown'));
                    } else {
                        // Add a spacer to keep keep up arrow icons at right position.
                        $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('spacer'),
                                    'class' => 'moveupspacer'));
                    }
                    $html .= html_writer::end_tag('div');
                }


                $attributes = array('title' => s($course->fullname));
                if ($course->id > 0) {
                    $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
                    $coursefullname = format_string($course->fullname, true, $course->id);
                    $link = html_writer::link($courseurl, $coursefullname, $attributes);
                    $html .= $this->output->heading($link, 2, 'title');
                } else {
                    $html .= $this->output->heading(html_writer::link(
                                    new moodle_url('/auth/mnet/jump.php', array('hostid' => $course->hostid, 'wantsurl' => '/course/view.php?id=' . $course->remoteid)), format_string($course->shortname, true), $attributes) . ' (' . format_string($course->hostname) . ')', 2, 'title');
                }
               
                $html .= $this->output->box('', 'flush');                
                $html .= html_writer::end_tag('div');
               
                if (!empty($config->showchildren) && ($course->id > 0)) {
                    // List children here.
                    if ($children = block_custhome_get_child_shortnames($course->id)) {
                        $html .= html_writer::tag('span', $children, array('class' => 'coursechildren'));
                    }
                }

                if (isset($overviews[$course->id])) {
                    $html .= $this->activity_display($course->id, $overviews[$course->id]);
                }

                $html .= $this->output->box('', 'flush');
                $html .= $this->output->box_end();
                $courseordernumber++;
            } else { //Category
                if (ajaxenabled()) {
                    $html .= $this->output->box_start('coursebox', "category-" . ++$categoryordernumber);
                    $html .= html_writer::start_tag('div', array('class' => 'category_title','id'=>'category-title-'.$categoryordernumber));

                    if (!is_null($moveicon)) {
                        $html .= $moveicon;
                    } else if (!is_null($url)) {
                        // Add course id to move link
                        $url->param('source', $course->id);
                        $html .= html_writer::start_tag('div', array('class' => 'moveicons'));
                        // Add an arrow to move course up.
                        if ($courseordernumber > 0) {
                            $url->param('move', -1);
                            $html .= html_writer::link($url, html_writer::empty_tag('img', array('src' => $moveup['icon'],
                                                'class' => 'up', 'alt' => $moveup['str'])), array('title' => $moveup['str'], 'class' => 'moveup'));
                        } else {
                            // Add a spacer to keep keep down arrow icons at right position.
                            $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('spacer'),
                                        'class' => 'movedownspacer'));
                        }
                        // Add an arrow to move course down.
                        if ($courseordernumber <= $maxcourses - 2) {
                            $url->param('move', 1);
                            $html .= html_writer::link($url, html_writer::empty_tag('img', array('src' => $movedown['icon'], 'class' => 'down', 'alt' => $movedown['str'])), array('title' => $movedown['str'], 'class' => 'movedown'));
                        } else {
                            // Add a spacer to keep keep up arrow icons at right position.
                            $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('spacer'),
                                        'class' => 'moveupspacer'));
                        }
                        $html .= html_writer::end_tag('div');
                    }

                    $html .= $this->output->heading($course, 2, 'title');

                    if ($this->page->user_is_editing()) {
                        if (ajaxenabled()) {
                            $html .= html_writer::start_tag('div', array('class' => 'category_buttons','id'=>'category-buttons-'.$categoryordernumber));
                            $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('t/editstring')->out(false),
                                        'alt' => "Edit title", 'class' => 'edittitle_category',
                                        'title' => "Edit title",
                                        'id' => "edittitle__category-" . $categoryordernumber)
                            );
                            $html .= html_writer::empty_tag('img', array('src' => $this->pix_url('t/delete')->out(false),
                                        'alt' => "Delete label", 'class' => 'delete_category',
                                        'title' => "Delete label",
                                        'id' => "delete__category-" . $categoryordernumber)
                            );
                            $html .= $this->output->box('', 'flush');
                            $html .= html_writer::end_tag('div'); //end Category buttons
                        }
                    }
                    $html .= html_writer::end_tag('div');


                    $html .= $this->output->box('', 'flush');
                    $html .= $this->output->box_end();
                    $courseordernumber++;
                    
                }
            }
        }
        $html .= html_writer::end_tag('div');

        if (ajaxenabled()) {
            $jsmodule = array(
                    'name' => 'block_custhome',
                    'fullpath' => new moodle_url('/blocks/custhome/module.js'),
                    'requires' => array('node', 'event'), // YUI3 modules to include
                );
                $this->page->requires->js_init_call('M.block_custhome.deleteCategoryEvent', array(), true, $jsmodule);
                $this->page->requires->js_init_call('M.block_custhome.edittitleCategoryEvent', array(), true, $jsmodule);
         }
        
        return $html;
    }

    /**
     * Coustuct activities overview for a course
     *
     * @param int $cid course id
     * @param array $overview overview of activities in course
     * @return string html of activities overview
     */
    protected function activity_display($cid, $overview) {
        $output = html_writer::start_tag('div', array('class' => 'activity_info'));
        foreach (array_keys($overview) as $module) {
            $output .= html_writer::start_tag('div', array('class' => 'activity_overview'));
            $url = new moodle_url("/mod/$module/index.php", array('id' => $cid));
            $modulename = get_string('modulename', $module);
            $icontext = html_writer::link($url, $this->output->pix_icon('icon', $modulename, 'mod_' . $module, array('class' => 'iconlarge')));
            if (get_string_manager()->string_exists("activityoverview", $module)) {
                $icontext .= get_string("activityoverview", $module);
            } else {
                $icontext .= get_string("activityoverview", 'block_custhome', $modulename);
            }

            // Add collapsible region with overview text in it.
            $output .= $this->collapsible_region($overview[$module], '', 'region_' . $cid . '_' . $module, $icontext, '', true);

            $output .= html_writer::end_tag('div');
        }
        $output .= html_writer::end_tag('div');
        return $output;
    }

    /**
     * Constructs header in editing mode
     *
     * @param int $max maximum number of courses
     * @return string html of header bar.
     */
    public function editing_bar_head($max = 0) {

        $output = $this->output->box_start('notice');
        if (ajaxenabled()) {
            $output .= html_writer::start_tag('div', array('id' => 'addCategoryDiv'));
            $output .= get_string('addnewlabel', 'block_custhome')." ";
            $output .= html_writer::empty_tag('img', array('src' => $this->pix_url('t/add')->out(false),
                        'alt' => get_string('addnewlabelalt', 'block_custhome'), 'class' => '',
                        'title' => get_string('addnewlabeltitle', 'block_custhome'),
                        'id' => "addCategory")
            );
            $output .= html_writer::end_tag('div');            

            $jsmodule = array(
                'name' => 'block_custhome',
                'fullpath' => new moodle_url('/blocks/custhome/module.js'),
                'requires' => array('node', 'event', 'dd-constrain', 'dd-proxy', 'dd-drop', 'dd-plugin', 'dd-delegate'), // YUI3 modules to include
            );

            $opts = array('name' => 'value'); // Params to pass to the init function

            $this->page->requires->js_init_call('M.block_custhome.addCategoryEvent', array(), true, $jsmodule);
            
        }
        $output .= $this->output->box_end();

        return $output;
    }

    /**
     * Show hidden courses count
     *
     * @param int $total count of hidden courses
     * @return string html
     */
    public function hidden_courses($total) {
        if ($total <= 0) {
            return;
        }
        $output = $this->output->box_start('notice');
        $plural = $total > 1 ? 'plural' : '';
        $output .= get_string('hiddencoursecount' . $plural, 'block_custhome', $total);
        $output .= $this->output->box_end();
        return $output;
    }

    /**
     * Creates collapsable region
     *
     * @param string $contents existing contents
     * @param string $classes class names added to the div that is output.
     * @param string $id id added to the div that is output. Must not be blank.
     * @param string $caption text displayed at the top. Clicking on this will cause the region to expand or contract.
     * @param string $userpref the name of the user preference that stores the user's preferred default state.
     *      (May be blank if you do not wish the state to be persisted.
     * @param bool $default Initial collapsed state to use if the user_preference it not set.
     * @return bool if true, return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region($contents, $classes, $id, $caption, $userpref = '', $default = false) {
        $output = $this->collapsible_region_start($classes, $id, $caption, $userpref, $default);
        $output .= $contents;
        $output .= $this->collapsible_region_end();

        return $output;
    }

    /**
     * Print (or return) the start of a collapsible region, that has a caption that can
     * be clicked to expand or collapse the region. If JavaScript is off, then the region
     * will always be expanded.
     *
     * @param string $classes class names added to the div that is output.
     * @param string $id id added to the div that is output. Must not be blank.
     * @param string $caption text displayed at the top. Clicking on this will cause the region to expand or contract.
     * @param string $userpref the name of the user preference that stores the user's preferred default state.
     *      (May be blank if you do not wish the state to be persisted.
     * @param bool $default Initial collapsed state to use if the user_preference it not set.
     * @return bool if true, return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region_start($classes, $id, $caption, $userpref = '', $default = false) {
        // Work out the initial state.
        if (!empty($userpref) and is_string($userpref)) {
            user_preference_allow_ajax_update($userpref, PARAM_BOOL);
            $collapsed = get_user_preferences($userpref, $default);
        } else {
            $collapsed = $default;
            $userpref = false;
        }

        if ($collapsed) {
            $classes .= ' collapsed';
        }

        $output = '';
        $output .= '<div id="' . $id . '" class="collapsibleregion ' . $classes . '">';
        $output .= '<div id="' . $id . '_sizer">';
        $output .= '<div id="' . $id . '_caption" class="collapsibleregioncaption">';
        $output .= $caption . ' ';
        $output .= '</div><div id="' . $id . '_inner" class="collapsibleregioninner">';
        $this->page->requires->js_init_call('M.block_custhome.collapsible', array($id, $userpref, get_string('clicktohideshow')));

        return $output;
    }

    /**
     * Close a region started with print_collapsible_region_start.
     *
     * @return string return the HTML as a string, rather than printing it.
     */
    protected function collapsible_region_end() {
        $output = '</div></div></div>';
        return $output;
    }

    /**
     * Cretes html for welcome area
     *
     * @param int $msgcount number of messages
     * @return string html string for welcome area.
     */
    public function welcome_area($msgcount) {
        global $USER;
        $output = $this->output->box_start('welcome_area');

        $picture = $this->output->user_picture($USER, array('size' => 75, 'class' => 'welcome_userpicture'));
        $output .= html_writer::tag('div', $picture, array('class' => 'profilepicture'));

        $output .= $this->output->box_start('welcome_message');
        $output .= $this->output->heading(get_string('welcome', 'block_custhome', $USER->firstname));

        /*$plural = 's';
        if ($msgcount > 0) {
            $output .= get_string('youhavemessages', 'block_custhome', $msgcount);
        } else {
            $output .= get_string('youhavenomessages', 'block_custhome');
            if ($msgcount == 1) {
                $plural = '';
            }
        }
        $output .= html_writer::link(new moodle_url('/message/index.php'), get_string('message' . $plural, 'block_custhome'));
        */$output .= $this->output->box_end();
        $output .= $this->output->box('', 'flush');
        $output .= $this->output->box_end();

        return $output;
    }

}
