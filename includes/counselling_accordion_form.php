<?php include('includes/dbconnect.php'); ?>
<?php
if(!isset($enquiryIdsCounselling)) $enquiryIdsCounselling = $enquiryIds ?? null;
if(!isset($counsilEqId)) $counsilEqId = 0;
if(!isset($counsil_Query)) $counsil_Query = array();
$counsil_Query = array_merge(array('st_enquiry_id'=>'','counsil_timing'=>'','counsil_end_time'=>'','counsil_type'=>'','counsil_mem_name'=>'','counsil_preferred_intake_date'=>'','counsil_mode_of_study'=>'','counsil_aus_stay_time'=>'','counsil_work_status'=>'','counsil_visa_condition'=>'','counsil_education'=>'','counsil_aus_study_status'=>'','counsil_course'=>'','counsil_university'=>'','counsil_qualification'=>'','counsil_eng_rate'=>'','counsil_migration_test'=>'','counsil_overall_result'=>'','counsil_module_result'=>'','counsil_job_nature'=>'','counsil_vaccine_status'=>'','counsil_remarks'=>'','counsil_notes'=>'','counsil_outcome'=>''), $counsil_Query);
$counsellingUsers = isset($counsellingUsers) ? $counsellingUsers : mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
$loggedInStaffName = isset($_SESSION['user_name']) ? trim((string)$_SESSION['user_name']) : '';
$selectedCounsellor = isset($counsil_Query['counsil_mem_name']) ? trim((string)$counsil_Query['counsil_mem_name']) : '';
if ($selectedCounsellor === '' && $loggedInStaffName !== '') {
    $selectedCounsellor = $loggedInStaffName;
}
$co_raw = isset($counsil_Query['counsil_outcome']) ? trim((string) $counsil_Query['counsil_outcome']) : '';
$co_norm_map = array(
    'counselling done' => 'Counselling Done',
    'counseling done' => 'Counselling Done',
    'rejected' => 'Rejected',
    'rescheduled' => 'Rescheduled',
);
$co_lk = strtolower($co_raw);
$co_sel = ($co_raw !== '' && isset($co_norm_map[$co_lk])) ? $co_norm_map[$co_lk] : $co_raw;
?>
<form class="followup_form" id="counselling_form">
<div class="row">
<div class="col-md-6">
<div class="mb-3">
<label class="form-label">Enquiry ID</label><br>
<?php
$counselling_enquiry_code = isset($counsil_Query['st_enquiry_id']) ? $counsil_Query['st_enquiry_id'] : '';
?>
<?php $counsel_enquiry_placeholder = $counselling_enquiry_code ? $counselling_enquiry_code : 'Save counselling to create or link enquiry (email required above)'; ?>
<input type="text" readonly class="form-control-plaintext fw-semibold<?php echo $counselling_enquiry_code ? '' : ' text-muted'; ?>" value="<?php echo htmlspecialchars($counsel_enquiry_placeholder); ?>">
<input type="hidden" name="enquiry_id" id="counselling_enquiry_id" value="<?php echo htmlspecialchars($counselling_enquiry_code); ?>">
<div class="error-feedback">Please Select the Enquiry ID</div>
</div>
</div>
<?php
$counsil_date_val = '';
$counsil_start_time_val = '';
$counsil_end_time_val = '';
if(!empty($counsil_Query['counsil_timing'])){
    $counsil_date_val = date('Y-m-d', strtotime($counsil_Query['counsil_timing']));
    $counsil_start_time_val = date('H:i', strtotime($counsil_Query['counsil_timing']));
}
if(!empty($counsil_Query['counsil_end_time'])){
    $counsil_end_time_val = date('H:i', strtotime($counsil_Query['counsil_end_time']));
} elseif(!empty($counsil_Query['counsil_timing'])){
    $counsil_end_time_val = date('H:i', strtotime($counsil_Query['counsil_timing']));
}
if($counsil_date_val==''){
    $counsil_date_val = date('Y-m-d');
}
?>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_date">Counselling Date</label>
<input type="date" class="form-control" id="counselling_date" value="<?php echo $counsil_date_val; ?>">
<small class="text-muted">Defaults to today for new counselling</small></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counseling_timing">Counselling Start Time</label>
<input type="time" class="form-control" id="counseling_timing" value="<?php echo $counsil_start_time_val; ?>">
<div class="error-feedback">Please select the start time</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counseling_end_timing">Counselling End Time</label>
<input type="time" class="form-control" id="counseling_end_timing" value="<?php echo $counsil_end_time_val; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Counseling Type</label><br>
<input type="radio" id="counseling_type1" name="counseling_type" class="form-check-input counseling_type" value="1" <?php echo $counsil_Query['counsil_type']==''||$counsil_Query['counsil_type']==1 ? 'checked' : ''; ?>><label for="counseling_type1">Face to Face</label>
<input type="radio" id="counseling_type2" name="counseling_type" class="form-check-input counseling_type" value="2" <?php echo $counsil_Query['counsil_type']==2 ? 'checked' : ''; ?>><label for="counseling_type2">Video</label></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_member_name">Counsellor's Name</label>
<select class="form-select" id="counselling_member_name" disabled>
<option value="">--select--</option>
<?php
if($counsellingUsers){
    mysqli_data_seek($counsellingUsers, 0);
    while($u = mysqli_fetch_array($counsellingUsers)){
        $name = $u['user_name'];
        $selected = ($selectedCounsellor === $name) ? 'selected' : '';
        echo '<option value="'.htmlspecialchars($name).'" '.$selected.'>'.htmlspecialchars($name).'</option>';
    }
}
?>
</select>
<div class="error-feedback">Please select the Counsellor</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_preferred_intake_date">Preferred Intake Date</label>
<input type="date" class="form-control" id="counselling_preferred_intake_date" value="<?php echo !empty($counsil_Query['counsil_preferred_intake_date']) ? date('Y-m-d', strtotime($counsil_Query['counsil_preferred_intake_date'])) : ''; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_mode_of_study">Mode of Study</label>
<select class="form-select" id="counselling_mode_of_study">
<option value="">--select--</option>
<option value="1" <?php echo (isset($counsil_Query['counsil_mode_of_study']) && $counsil_Query['counsil_mode_of_study']==1) ? 'selected' : ''; ?>>Face to Face</option>
<option value="2" <?php echo (isset($counsil_Query['counsil_mode_of_study']) && $counsil_Query['counsil_mode_of_study']==2) ? 'selected' : ''; ?>>Online</option>
<option value="3" <?php echo (isset($counsil_Query['counsil_mode_of_study']) && $counsil_Query['counsil_mode_of_study']==3) ? 'selected' : ''; ?>>Blended</option>
</select></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="aus_duration">How long the student has been to Aus</label>
<input type="text" class="form-control" id="aus_duration" placeholder="Duration" value="<?php echo $counsil_Query['counsil_aus_stay_time']; ?>">
<div class="error-feedback">Please enter the Duration</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Currently Working</label><br>
<input type="radio" id="work_status1" name="work_status" class="form-check-input work_status" value="1" <?php echo $counsil_Query['counsil_work_status']==''||$counsil_Query['counsil_work_status']==1 ? 'checked' : ''; ?>><label>Yes</label>
<input type="radio" id="work_status2" name="work_status" class="form-check-input work_status" value="2" <?php echo $counsil_Query['counsil_work_status']==2 ? 'checked' : ''; ?>><label for="work_status2">No</label></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_visa_condition">Visa Condition</label>
<select name="visa_condition" class="form-select" id="counselling_visa_condition">
<?php $selCounVisa = isset($counsil_Query['counsil_visa_condition']) ? (int)$counsil_Query['counsil_visa_condition'] : 0; ?>
<option value="0">--select--</option>
<option value="1" <?php echo $selCounVisa===1 ? 'selected' : ''; ?>>500 - Main Applicant</option>
<option value="2" <?php echo $selCounVisa===2 ? 'selected' : ''; ?>>500 - Dependent</option>
<option value="11" <?php echo $selCounVisa===11 ? 'selected' : ''; ?>>485 - Main Applicant</option>
<option value="12" <?php echo $selCounVisa===12 ? 'selected' : ''; ?>>485 - Dependent</option>
<option value="3" <?php echo $selCounVisa===3 ? 'selected' : ''; ?>>491 - Main Applicant</option>
<option value="4" <?php echo $selCounVisa===4 ? 'selected' : ''; ?>>491 - Dependent</option>
<option value="5" <?php echo $selCounVisa===5 ? 'selected' : ''; ?>>Visitor Visa</option>
<option value="6" <?php echo $selCounVisa===6 ? 'selected' : ''; ?>>Permanent Resident</option>
<option value="7" <?php echo $selCounVisa===7 ? 'selected' : ''; ?>>Spouse Visa</option>
<option value="8" <?php echo $selCounVisa===8 ? 'selected' : ''; ?>>Working Holiday Visa</option>
<option value="9" <?php echo $selCounVisa===9 ? 'selected' : ''; ?>>AU/NZ Citizen</option>
<option value="10" <?php echo $selCounVisa===10 ? 'selected' : ''; ?>>Other</option>
</select><div class="error-feedback">Please select a visa status</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_education">What is your Educational Background?</label>
<input type="text" class="form-control" id="counselling_education" value="<?php echo $counsil_Query['counsil_education']; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Is the Student Studying in Australia</label><br>
<input type="radio" id="aus_study1" name="aus_study" class="form-check-input aus_study" value="1" <?php echo $counsil_Query['counsil_aus_study_status']==''||$counsil_Query['counsil_aus_study_status']==1 ? 'checked' : ''; ?>><label for="aus_study1">Yes</label>
<input type="radio" id="aus_study2" name="aus_study" class="form-check-input aus_study" value="2" <?php echo $counsil_Query['counsil_aus_study_status']==2 ? 'checked' : ''; ?>><label for="aus_study2">No</label></div></div>
<div class="col-md-6 aus_study_child" style="display:<?php echo ($counsil_Query['counsil_aus_study_status']==1||$counsil_Query['counsil_aus_study_status']=='') ? 'block' : 'none'; ?>"><div class="mb-3"><label class="form-label" for="counselling_course">What course are you currently undertaking in Australia?</label><input type="text" class="form-control" id="counselling_course" value="<?php echo $counsil_Query['counsil_course']; ?>"></div></div>
<div class="col-md-6 aus_study_child" style="display:<?php echo ($counsil_Query['counsil_aus_study_status']==1||$counsil_Query['counsil_aus_study_status']=='') ? 'block' : 'none'; ?>"><div class="mb-3"><label class="form-label" for="counselling_university_name">What is the name of your university?</label><input type="text" class="form-control" id="counselling_university_name" value="<?php echo $counsil_Query['counsil_university']; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_qualification">Any Experience or Qualification related to Aged care/Health care</label><input type="text" class="form-control" id="counselling_qualification" value="<?php echo $counsil_Query['counsil_qualification']; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_eng_rate">Rate your English ( 1 - 10 )</label><input type="tel" class="form-control rating-field" id="counselling_eng_rate" value="<?php echo $counsil_Query['counsil_eng_rate']; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">IELTS or PTE given </label><br>
<input type="radio" id="mig_test1" name="mig_test" class="form-check-input mig_test" value="1" <?php echo $counsil_Query['counsil_migration_test']==''||$counsil_Query['counsil_migration_test']==1 ? 'checked' : ''; ?>><label for="mig_test1">Yes</label>
<input type="radio" id="mig_test2" name="mig_test" class="form-check-input mig_test" value="2" <?php echo $counsil_Query['counsil_migration_test']==2 ? 'checked' : ''; ?>><label for="mig_test2">No</label></div></div>
<div class="col-md-6 mig_test_child" style="display:<?php echo ($counsil_Query['counsil_migration_test']==1||$counsil_Query['counsil_migration_test']=='') ? 'block' : 'none'; ?>"><div class="mb-3"><label class="form-label" for="counselling_overall_result">Overall Result</label><input type="text" class="form-control nummber-field" id="counselling_overall_result" value="<?php echo $counsil_Query['counsil_overall_result']; ?>"></div></div>
<div class="col-md-6 mig_test_child" style="display:<?php echo ($counsil_Query['counsil_migration_test']==1||$counsil_Query['counsil_migration_test']=='') ? 'block' : 'none'; ?>"><div class="mb-3"><label class="form-label" for="counselling_module_result">Each Module Result</label><input type="text" class="form-control" id="counselling_module_result" value="<?php echo $counsil_Query['counsil_module_result']; ?>"></div></div>
<div class="col-md-6 mig_test_child" style="display:<?php echo ($counsil_Query['counsil_migration_test']==1||$counsil_Query['counsil_migration_test']=='') ? 'block' : 'none'; ?>"><div class="mb-3"><label class="form-label" for="counselling_job_nature">Nature of the Job explained</label><input type="text" class="form-control" id="counselling_job_nature" value="<?php echo $counsil_Query['counsil_job_nature']; ?>"></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label">Vaccination Taken</label><br>
<input type="radio" id="vaccine_status1" name="vaccine_status" class="form-check-input vaccine_status" value="1" <?php echo $counsil_Query['counsil_vaccine_status']==''||$counsil_Query['counsil_vaccine_status']==1 ? 'checked' : ''; ?>><label>Yes</label>
<input type="radio" id="vaccine_status2" name="vaccine_status" class="form-check-input vaccine_status" value="2" <?php echo $counsil_Query['counsil_vaccine_status']==2 ? 'checked' : ''; ?>><label>No</label></div></div>
<div class="col-12"><div class="mb-3"><label class="form-label d-block">Remarks</label>
<div class="row">
<?php
$st_remarks=['Seems to be interested to do course and need to contact asap','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Planning to relocate to other state','Wants to get COE for visa purpose'];
$remarksSel=($counsil_Query['counsil_remarks']!='') ? json_decode($counsil_Query['counsil_remarks']) : array();
$total_remarks = count($st_remarks);
$half = (int)ceil(($total_remarks - 1) / 2);
?>
<div class="col-md-6">
<?php for($i=1;$i<=$half;$i++){
$checked=in_array($i,$remarksSel) ? 'checked' : '';
echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input counselling_remarks" id="counsel_remark_'.$i.'" '.$checked.' value="'.$i.'"><label for="counsel_remark_'.$i.'">'.$st_remarks[$i].'</label></div>';
} ?>
</div>
<div class="col-md-6">
<?php for($i=$half+1;$i<$total_remarks;$i++){
$checked=in_array($i,$remarksSel) ? 'checked' : '';
echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input counselling_remarks" id="counsel_remark_'.$i.'" '.$checked.' value="'.$i.'"><label for="counsel_remark_'.$i.'">'.$st_remarks[$i].'</label></div>';
} ?>
</div>
</div>
<div class="error-feedback">Please select atleast one option</div></div></div>
<div class="col-12"><div class="mb-3"><label class="form-label" for="counselling_notes">Notes</label>
<textarea class="form-control" id="counselling_notes" rows="3" placeholder="Counselling notes"><?php echo htmlspecialchars($counsil_Query['counsil_notes'] ?? ''); ?></textarea></div></div>
<div class="col-12 mt-3 pt-3 border-top">
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="counselling_outcome">Counselling outcome</label>
<select class="form-select" id="counselling_outcome" name="counselling_outcome">
<?php
$couns_outcomes = array(''=>'--select--','Counselling Done'=>'Counselling Done','Rejected'=>'Rejected','Rescheduled'=>'Rescheduled');
foreach ($couns_outcomes as $ov => $ol) {
    $os = ($co_sel === $ov) ? 'selected' : '';
    echo '<option value="'.htmlspecialchars($ov, ENT_QUOTES, 'UTF-8').'" '.$os.'>'.htmlspecialchars($ol, ENT_QUOTES, 'UTF-8').'</option>';
}
?>
</select>
<input type="hidden" id="counselling_outcome_actual" value="<?php echo htmlspecialchars((string)$co_sel, ENT_QUOTES, 'UTF-8'); ?>">
<small class="text-muted d-block mt-1">Saving applies the matching enquiry status automatically.</small>
</div></div>
<div class="col-12"><div class="mb-3"><label class="form-label" for="counselling_email_template_status">Email template</label>
<select class="form-select" id="counselling_email_template_status">
<option value="">Select email template</option>
<option value="12">Counselling Done</option>
<option value="13">Rescheduling</option>
<option value="14">Rejected</option>
</select>
<small class="text-muted d-block">Choose a template to preview and send email only (it does not change enquiry status).</small></div></div>
<div class="col-12 mb-2" id="counselling_reschedule_calendar_wrap" style="display:<?php echo ($co_sel === 'Rescheduled') ? 'block' : 'none'; ?>;">
<button type="button" class="btn btn-outline-primary" id="counselling_open_calendar_btn"><i class="ti ti-calendar"></i> Calendar</button>
<small class="text-muted ms-2">Book the rescheduled session; enquiry status is set to Counselling Pending.</small>
</div>
</div>
</div>
</div>
<button class="btn btn-primary" type="button" id="counseling_submit">Submit Counseling</button>
<input type="hidden" value="<?php echo $counsilEqId; ?>" id="counselling_check_update">
<script>
(function(){
    var want = <?php echo json_encode($co_sel, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    function applyCounsellingOutcomePreselect(){
        var el = document.getElementById('counselling_outcome');
        if (!el || !want) return;
        var ok = false;
        for (var i = 0; i < el.options.length; i++) {
            if (el.options[i].value === want) { ok = true; break; }
        }
        if (ok) el.value = want;
    }
    window.__applyCounsellingOutcomePreselect = applyCounsellingOutcomePreselect;
    document.addEventListener('DOMContentLoaded', applyCounsellingOutcomePreselect);
    if (window.jQuery) {
        jQuery(function(){ applyCounsellingOutcomePreselect(); setTimeout(applyCounsellingOutcomePreselect, 0); setTimeout(applyCounsellingOutcomePreselect, 150); });
    }
    setTimeout(applyCounsellingOutcomePreselect, 50);
})();
</script>
</form>
