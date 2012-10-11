<?php
require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once("block_panopto_lib.php");
require_once("PanoptoSoapClient.php");

class panopto_data
{
	var $instancename;
	
	var $moodle_course_id;
	
	var $servername;
	var $applicationkey;
	
	var $soap_client;
	
	var $sessiongroup_id;
		
	function __construct($moodle_course_id)
	{
		global $USER;
		
		// Fetch global settings from DB
		$this->instancename = get_instancename_setting();
		$this->servername = get_servername_setting();
		$this->applicationkey = get_application_key_setting();
		
		if(!empty($this->servername))
		{
			if(isset($USER->username))
			{
				$username = $USER->username;
			}
			else
			{
				$username = "guest";
			}

	        // Compute web service credentials for current user.
			$apiuser_userkey = decorate_username($username);
			$apiuser_authcode = generate_auth_code($apiuser_userkey . "@" . $this->servername);

			// Instantiate our SOAP client.
			$this->soap_client = new PanoptoSoapClient($this->servername, $apiuser_userkey, $apiuser_authcode);
		}

		// Fetch current CC course mapping if we have a Moodle course ID.
		// Course will be null initially for batch-provisioning case.
		if(!empty($moodle_course_id))
		{
			$this->moodle_course_id = $moodle_course_id;
			$this->sessiongroup_id = panopto_data::get_panopto_course_id($moodle_course_id);
		}
	}

	// returns SystemInfo
	function get_system_info()
	{
		return $this->soap_client->GetSystemInfo();
	}
	
	// Create the Panopto course and populate its ACLs.
	function provision_course($provisioning_info)
	{
		$course_info = $this->soap_client->ProvisionCourse($provisioning_info);
		
		if(!empty($course_info) && !empty($course_info->PublicID))
		{
			panopto_data::set_panopto_course_id($this->moodle_course_id, $course_info->PublicID);
		}
		
		return $course_info;
	}
	
	// Fetch course name and membership info from DB in preparation for provisioning operation.
	function get_provisioning_info()
	{
		global $DB;
                $courseshortname =  $DB->get_field('course', 'shortname', array('id'=>$this->moodle_course_id));
                $provisioning_info->ShortName = trim($courseshortname);
                $courselongname = $DB->get_field('course', 'fullname', array('id'=>$this->moodle_course_id));
		$provisioning_info->LongName = trim($courselongname);
		$provisioning_info->ExternalCourseID = $this->instancename . ":" . $this->moodle_course_id;

	    $course_context = get_context_instance(CONTEXT_COURSE, $this->moodle_course_id);

	    // Lookup table to avoid adding instructors as Viewers as well as Creators.
	    $instructor_hash = array();
	    
	    // moodle/course:update capability will include admins along with teachers, course creators, etc.
	    // Could also use moodle/legacy:teacher, moodle/legacy:editingteacher, etc. if those turn out to be more appropriate.
	    $instructors = get_users_by_capability($course_context, 'moodle/course:update');
		// add site admins as instructors as well
                $admins = get_admins();
                $instructors = array_merge($instructors, $admins);
     
		if(!empty($instructors))
		{
			$provisioning_info->Instructors = array();
			foreach($instructors as $instructor)
			{
				$instructor_info = new stdClass;
				$instructor_info->UserKey = $this->decorate_username($instructor->username);
				$instructor_info->FirstName = $instructor->firstname;
			  	$instructor_info->LastName = $instructor->lastname;
			  	$instructor_info->Email = $instructor->email;
			  	$instructor_info->MailLectureNotifications = false;
			  	
				array_push($provisioning_info->Instructors, $instructor_info);

				$instructor_hash[$instructor->username] = true;
			}
		}
		/**
                 * This section of code will add compile array of students as viewers. If new if
                 * will use student just from this course. If existing... checks to see if folder is
                 * being used in any other course(shared folder). If this the case, we need to get every
                 * student from each course using folder... Otherwise students could have their access
                 * removed. Unfortunately this how 3.x api works.
                 */
                $students = array();
                $panoptofolderid = panopto_data::get_panopto_course_id($this->moodle_course_id);
                if (!$panoptofolderid) { // This is new
                    // moodle/course:view capability has different meaning in 2.x
		    $students = get_users_by_capability($course_context, 'block/panopto:view');
                } else {
                    $moodlepapers = $DB->get_records('block_panopto_foldermap', array('panopto_id'=>$panoptofolderid));
                    foreach ($moodlepapers as $moodlepaper) {
                        $papercontext = get_context_instance(CONTEXT_COURSE, $moodlepaper->moodleid);
                        // Block exists in course?
                        $params = array('blockname'=>'panopto', 'parentcontextid'=>$papercontext->id);
                        if (!$DB->record_exists('block_instances', $params)) {
                           continue; // skipping students from this paper as no block exists.
                        }
                        // moodle/course:view capability has different meaning in 2.x
                        $paperstudents = get_users_by_capability($papercontext, 'block/panopto:view', 'u.id, u.username, u.firstname, u.lastname, u.email');
                        $students = array_merge($students, $paperstudents);
                        unset($papercontext);
                        unset($paperstudents);
                    }
                }
		if(!empty($students))
		{
			$provisioning_info->Students = array();
			foreach($students as $student)
			{
				if(array_key_exists($student->username, $instructor_hash)) continue;
				
				$student_info = new stdClass;
				$student_info->UserKey = $this->decorate_username($student->username);
				array_push($provisioning_info->Students, $student_info);
			}
		}

		return $provisioning_info;
	} 
	
	// Get courses visible to the current user.
	function get_courses()
	{
		$courses_result = $this->soap_client->GetCourses();
		$courses = array();
		if(!empty($courses_result->CourseInfo))
		{
			$courses = $courses_result->CourseInfo;
			// Single-element return set comes back as scalar, not array (?)
			if(!is_array($courses))
			{
				$courses = array($courses);
			}
		}
		return $courses;
	}
	
	// Get info about the currently mapped course.
	function get_course()
	{
		return $this->soap_client->GetCourse($this->sessiongroup_id);
	}
	
	// Get ongoing Panopto sessions for the currently mapped course.
	function get_live_sessions()
	{
		$live_sessions_result = $this->soap_client->GetLiveSessions($this->sessiongroup_id);
		
		$live_sessions = array();
		if(!empty($live_sessions_result->SessionInfo))
		{
			$live_sessions = $live_sessions_result->SessionInfo;
			// Single-element return set comes back as scalar, not array (?)
			if(!is_array($live_sessions))
	        {
	        	$live_sessions = array($live_sessions);
	        }
		}

		return $live_sessions;
	}
	
	// Get recordings available to view for the currently mapped course.
	function get_completed_deliveries()
	{
		$completed_deliveries_result = $this->soap_client->GetCompletedDeliveries($this->sessiongroup_id);
		
		$completed_deliveries = array();
		if(!empty($completed_deliveries_result->DeliveryInfo))
		{
			$completed_deliveries = $completed_deliveries_result->DeliveryInfo;
			// Single-element return set comes back as scalar, not array (?)
			if(!is_array($completed_deliveries))
        	{
        		$completed_deliveries = array($completed_deliveries);
        	}
		}
		
		return $completed_deliveries; 
	}
	
	// Instance method caches Moodle instance name from DB (vs. block_panopto_lib version). 
	function decorate_username($moodle_username)
	{
		return ($this->instancename . "\\" . $moodle_username);
	}
	// Prepend the instance name to the Moodle course ID to create an external ID for Panopto CourseCast.
        function decorate_course_id($moodle_course_id)
        {
                return ($this->instancename . ":" . $moodle_course_id);
        }
	// We need to retrieve the current course mapping in the constructor, so this must be static.
	static function get_panopto_course_id($moodle_course_id)
	{
                global $DB;
                return $DB->get_field('block_panopto_foldermap', 'panopto_id', array('moodleid'=>$moodle_course_id));
                
	}
	
	function get_course_options_html($provision_url)
	{
		$is_provisioned = false;
		$has_selection = false;
		$can_sync = false;
		
		$courses_by_access_level = array("Creator" => array(), "Viewer" => array(), "Public" => array());
		
		$panopto_courses = $this->get_courses();
		if(!empty($panopto_courses))
		{
			foreach($panopto_courses as $course_info)
			{
				array_push($courses_by_access_level[$course_info->Access], $course_info);
			}
			
			$options = "";
			foreach(array_keys($courses_by_access_level) as $access_level)
			{
				$courses = $courses_by_access_level[$access_level];
				$options .= "<optgroup label='$access_level'>\n";
				foreach($courses as $course_info)
				{
					$selectedText = ($course_info->PublicID == $this->sessiongroup_id) ? " SELECTED" : "";
					
					if($selectedText)
					{
						$has_selection = true;
					}
					$display_name = s($course_info->DisplayName);
					$options .= "<option value='$course_info->PublicID' $selectedText>$display_name</option>\n";
					
					if($course_info->ExternalCourseID == decorate_course_id($this->moodle_course_id))
					{
			 			// Don't display provision link.
						$is_provisioned = true;
						
			 			// If provisioned course is currently selected, display "sync users" link.
						if($course_info->PublicID == $this->sessiongroup_id)
						{
							$can_sync = true;
					}
				}
				}
				$options .= "</optgroup>\n";
			}
					
			if(!$has_selection)
			{
				$options = "<option value=''>-- Select an Existing Course --</option>" . $options;
			}
		}
		else if(isset($panopto_courses))
		{
			$options = "<option value=''>-- No Courses Available --</option>";
		}
		else
		{
			$options = "<option value=''>!! Unable to retrieve course list !!</option>";
		}
		
		$disabled = (($has_selection || empty($panopto_courses)) ? "disabled='true'" : "");
		$result = "<select id='sessionGroupSelect' name='panopto_course_publicid' $disabled>$options</select>";
		
		if(!empty($panopto_courses))
		{
			if($has_selection)
			{
				$result .= "<input id='editButton' type='button' value='Edit' onclick='return editCourse()' style='font-size: 0.6em'/>\n";
			}
			
			if(!$is_provisioned)
			{
				$result .= "<br><br>- OR -<br><br><a href='$provision_url'>Add Course to Panopto CourseCast</a>";
			}
			else if($can_sync)
			{
				$result .= "<div id='syncSection'>";
				$result .= "<br>";
				$result .= "Adding a course to CourseCast copies the list of instructors and students.<br>";
				$result .= "To update CourseCast after a change in course enrollment, click the link below:<br><br>";
				$result .= "<a href='$provision_url'><b>Sync User List</b></a>";
				$result .= "</div>";
			}
		}
		
		return $result;
	}
	
        function get_available_folders(){
            $coursesbyaccesslevels = array();
            $panoptocourses = $this->get_courses();
            if (!empty($panoptocourses)) {
                foreach($panoptocourses as $courseinfo) {
                    //$displayname = s($courseinfo->DisplayName);
                    //$coursesbyaccesslevels[$courseinfo->Access][$courseinfo->PublicID]=$displayname;
                    $coursesbyaccesslevels[$courseinfo->Access][$courseinfo->PublicID]=$courseinfo;
		}
                return $coursesbyaccesslevels;
            }
            return $coursesbyaccesslevels;
        }


	// Called by Moodle block instance config save method, so must be static.
	static function set_panopto_course_id($moodle_course_id, $sessiongroup_id)
	{
		global $DB;

                $mapping = $DB->get_record('block_panopto_foldermap', array('moodleid'=>$moodle_course_id));
                if ($mapping) {
                    $mapping->panopto_id = $sessiongroup_id;
                    $mapping->syncuserlist = 1;
                    $DB->update_record('block_panopto_foldermap', $mapping);
                } else {
                    $mapping = new stdClass();
                    $mapping->moodleid = $moodle_course_id;
                    $mapping->panopto_id = $sessiongroup_id;
                    $mapping->syncuserlist = 1;
                    $DB->insert_record('block_panopto_foldermap', $mapping);
                }
                return true;
	}
	
	// Gets a list of Moodle courses from the DB and generates drop-down options HTML.
	static function get_moodle_course_options_html()
	{
	        global $DB;

                $output = "";
		
		$courses = $DB->get_records('course',null,'shortname');
		if($courses)
		{
			foreach($courses as $course)
			{
				// HTML-escape course display name
				$display_name = s("$course->shortname: $course->fullname");
				$output .= "<option value='$course->id'>$display_name</option>\n";
			}
		}
		
		return $output;
	}
}
?>