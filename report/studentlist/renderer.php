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

defined('MOODLE_INTERNAL') || die();

/**/
class report_studentlist_renderer extends plugin_renderer_base {

    public function official_student_picture(stdClass $user, array $options = null) {
        $userpicture = new official_student_picture($user);
        foreach ((array)$options as $key=>$value) {
            if (array_key_exists($key, $userpicture)) {
                $userpicture->$key = $value;
            }
        }
        return $this->render($userpicture);
    }
    
    protected function render_official_student_picture(official_student_picture $userpicture) {
        global $CFG, $DB;

        $user = $userpicture->user;

        $alt = get_string('pictureof', '', fullname($user));
        
        $size = 100;
    
        $class = $userpicture->class;

        $src = $userpicture->get_url($this->page, $this);

        $attributes = array('src'=>$src, 'alt'=>$alt, 'title'=>$alt, 'class'=>$class, 'width'=>$size, 'height'=>$size);

        // get the image html output fisrt
        $output = html_writer::empty_tag('img', $attributes);

        // then wrap it in link if needed
        if (!$userpicture->link) {
            return $output;
        }

        if (empty($userpicture->courseid)) {
            $courseid = $this->page->course->id;
        } else {
            $courseid = $userpicture->courseid;
        }

        if ($courseid == SITEID) {
            $url = new moodle_url('/user/profile.php', array('id' => $user->id));
        } else {
            $url = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $courseid));
        }

        $attributes = array('href'=>$url);

        if ($userpicture->popup) {
            $id = html_writer::random_id('userpicture');
            $attributes['id'] = $id;
            $this->add_action_handler(new popup_action('click', $url), $id);
        }

        return html_writer::tag('a', $output, $attributes);
    }

    public function sort_by_dropdown($options) {
        global $PAGE;

        $html = '';
        
        $strings = array();
        foreach ($options as $option) {
            $strings[$option] = new lang_string($option, 'report_studentlist');
        }

        $pageurl = clone($PAGE->url);

        //$PAGE->url->param('page', 0); // reset pagination

        $sort = $pageurl->get_param('sort');
        if (!in_array($sort, $options)) {
            throw new coding_exception('$PAGE sort param is not in options');
        }

        $html .= html_writer::start_div('dropdown-group pull-right hidden-print'); //
        $html .= html_writer::start_div('js-control btn-group pull-right');

        $html .= html_writer::start_tag('button', array('data-toggle' => 'dropdown',
                                                        'class' =>'btn btn-small dropdown-toggle'));

        $html .= new lang_string('sortedby', 'report_studentlist', $strings[$sort]);
        $html .= html_writer::tag('tag', null, array('class' => 'caret'));
        $html .= html_writer::end_tag('button');
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));

        foreach ($options as $option) {
            $url = clone($PAGE->url);
            $url->param('sort', $option);
            $html .= html_writer::start_tag('li');
            $html .= html_writer::link($url, $strings[$option]);
            $html .= html_writer::end_tag('li');
        }

        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_div(); // end of js-control
        $html .= html_writer::end_div(); // end of container
        return $html;

    }

    public function student_list(array $users) {
        global $PAGE, $USER, $SESSION;

        // get context from global
        $context = $PAGE->context;
        // get course from global
        $course = $PAGE->course;
        // get countries
        $countries = get_string_manager()->get_list_of_countries();

        $stringcache = array();
        $stringcache['emailaddresses'] = new lang_string('emailaddresses', 'report_studentlist');
        $stringcache['phone'] = new lang_string('phone');
        $stringcache['groups'] = new lang_string('groups');
        // font awesome icons
        $envelopeicon       = html_writer::tag('i', ' ', array('class'=>'icon-envelope'));
        $phoneicon          = html_writer::tag('i', ' ', array('class'=>'icon-phone'));
        $phonemobileicon    = html_writer::tag('i', ' ', array('class'=>'icon-mobile-phone'));
        $groupicon          = html_writer::tag('i', ' ', array('class'=>'icon-group'));

        $html = '';
        foreach($users as $user) {
            $user->universityemail = $user->username . '@students.waikato.ac.nz';
            $user->location = ucwords($user->city) .', '. $countries[$user->country];
            // HACK, clean dots, needs to be fixed at source - damn dirty data
            $user->phone1 = ltrim($user->phone1, '.');
            $user->phone2 = ltrim($user->phone2, '.');

            $profileurl = new moodle_url('/user/view.php', array('id'=>$user->id, 'course'=>$course->id));

            $html .= html_writer::start_div('media');

            $userpicture = new official_student_picture($user);
            $userpicture->class .= ' img-rounded media-object';

            //$attributes = array('id'=>$user->id,'class'=>'icon-check icon-large hidden-phone pull-left', 'title'=>get_string('selectstudent', 'report_studentlist', fullname($user)));
            //$iconcheckbox = html_writer::tag('i', '', $attributes); //icon-check-empty

            $attributes = array('class'=>'usercheckbox pull-left hidden-phone hidden-print', 'type'=>'checkbox', 'name'=>'user'.$user->id);
            $html .= html_writer::empty_tag('input', $attributes);

            $attributes = array('href'=>$profileurl, 'class'=>'pull-left');
            $html .= html_writer::tag('a', $this->render($userpicture), $attributes);

            $html .= html_writer::start_div('media-body');
            $fullnamelink = html_writer::link($profileurl, fullname($user));
            $html .= html_writer::tag('h4', $fullnamelink, array('class'=>'media-heading'));
            $idnumber = '';
            if ($user->idnumber) {
                $attributes = array('class'=>'idnumber', 'title'=>get_string('studentidnumber', 'report_studentlist', fullname($user)));
                $idnumber = html_writer::tag('strong', "($user->idnumber)", $attributes);
            }
            $html .= html_writer::tag('div', $idnumber, array('class'=>'pull-right'));
            $html .= html_writer::div($user->location);
            $html .= html_writer::start_div('phones');
            if (!empty($user->phone1)) {
                $html .= html_writer::tag('dt', $stringcache['phone']);
                $phone1url = new moodle_url("tel:$user->phone1");
                $phone1label = $user->phone1;
                $html .= html_writer::tag('dd', $phoneicon . html_writer::link($phone1url, $phone1label));
            }
            if (!empty($user->phone2)) {
                if ($user->phone1 != $user->phone2){
                    $phone2url = new moodle_url("tel:$user->phone2");
                    $phone2label = $user->phone2;
                    $html .= html_writer::tag('dd', $phonemobileicon . html_writer::link($phone2url, $phone2label));
                }
            }
            $html .= html_writer::end_div(); // close panel

            $html .= html_writer::start_div('emails');
            $html .= html_writer::tag('dt', $stringcache['emailaddresses']);
            $html .= html_writer::tag('dd', $envelopeicon . obfuscate_mailto($user->universityemail, ''));
            // Preferred email - can we display?
            if ((($user->id == $USER->id) or
                $user->maildisplay == 1 or
                has_capability('moodle/course:useremail', $context) or
                ($user->maildisplay == 2 and enrol_sharing_course($user, $USER))) and
                ($user->universityemail != $user->email)) {
                // access granted and doesn't match university provided email
                $html .= html_writer::tag('dd', $envelopeicon . obfuscate_mailto($user->email, ''));

            }
            $html .= html_writer::end_div(); // end of email panel

            $groups = report_studentlist_get_user_groups($course->id, $user->id);
            if ($groups) {
                $html .= html_writer::start_div('groups');
                
                $html .= html_writer::tag('dt', $stringcache['groups']);
                foreach ($groups as $group) {
                    $html .= html_writer::tag('dd', $groupicon . $group->name);
                }
               
                $html .= html_writer::end_div();
            }
            
            $html .= html_writer::end_div(); // end media-body
            $html .= html_writer::end_div(); // end media
        }

        return $html;
        
    }    

} // end of renderer
