<?php
/**
 * Maps Follow-up outcome (student_enquiry follow-up accordion) to st_enquiry_flow_status codes.
 * Labels shown in UI are defined in followup_accordion_form.php / view_enquiries.php.
 */
if (!function_exists('enquiry_flow_status_for_followup_outcome')) {
    function enquiry_flow_status_for_followup_outcome($outcome_raw) {
        $o = trim((string) $outcome_raw);
        if ($o === '') {
            return null;
        }
        static $map = array(
            'No Answer' => 3,
            'Call Back Later' => 3,
            'Booked Counselling' => 2,
            'Requested More Information' => 2,
            'Application Started' => 4,
            'Enrolled' => 6,
            'Not Interested' => 7,
            'Do not Call' => 7,
            'Wrong No' => 7,
            'Enrolled Elsewhere' => 7,
            'Course not Offered' => 7,
            'Funding Enquiry' => 7,
        );
        return isset($map[$o]) ? $map[$o] : null;
    }
}

/**
 * Maps Counselling outcome to st_enquiry_flow_status codes.
 */
if (!function_exists('enquiry_flow_status_for_counselling_outcome')) {
    function enquiry_flow_status_for_counselling_outcome($outcome_raw) {
        $o = trim((string) $outcome_raw);
        if ($o === '') {
            return null;
        }
        static $map = array(
            'Counselling Done' => 5,
            'Rejected' => 7,
            'Rescheduled' => 11,
        );
        return isset($map[$o]) ? $map[$o] : null;
    }
}
