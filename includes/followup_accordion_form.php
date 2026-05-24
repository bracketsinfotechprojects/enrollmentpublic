<?php
/**
 * Follow-up accordion form (Post Enquiry Follow Up or Post Counselling Follow Up).
 * Set $followup_accordion_ctx to 'enquiry' (default) or 'post_counselling' before include.
 */
if (!isset($followup_accordion_ctx)) {
    $followup_accordion_ctx = 'enquiry';
}
$is_post_counselling_followup = ($followup_accordion_ctx === 'post_counselling');
if (!isset($enquiryIdsFollowup)) {
    $enquiryIdsFollowup = $enquiryIds ?? null;
}
if (!isset($followupEqId)) {
    $followupEqId = 0;
}
if (!isset($followup_Query)) {
    $followup_Query = array();
}
$followup_Query = array_merge(array(
    'enquiry_id' => '',
    'flw_name' => '',
    'flw_phone' => '',
    'flw_contacted_person' => '',
    'flw_contacted_time' => '',
    'flw_date' => '',
    'flw_mode_contact' => '',
    'flw_followup_type' => '',
    'flw_follow_up_notes' => '',
    'flw_next_followup_date' => '',
    'flw_follow_up_outcome' => '',
    'flw_comments' => '',
    'flw_progress_state' => '',
    'flw_remarks' => '',
), $followup_Query);
$loggedInStaffName = isset($_SESSION['user_name']) ? trim((string)$_SESSION['user_name']) : '';
$enquiry_flow_statuses = array(
    1 => 'New',
    2 => 'Contacted',
    3 => 'Follow-up Pending',
    4 => 'In Progress',
    5 => 'Enrol Pending',
    6 => 'Converted',
    7 => 'Closed / Lost',
    8 => 'Invalid / Duplicate',
    9 => 'Booked Counselling',
    10 => 'Re-enquired',
    11 => 'Counselling Pending',
);
if (!isset($has_counselling_appointment)) {
    $has_counselling_appointment = false;
}
$follow_up_outcomes = array(
    '' => '--select--',
    'No Answer' => 'No Answer',
    'Call Back Later' => 'Call Back Later',
    'Booked Counselling' => 'Booked Counselling',
    'Application Started' => 'Application Started',
    'Enrolled' => 'Enrolled',
    'Requested More Information' => 'Requested More Information',
    'Not Interested' => 'Not Interested',
    'Do not Call' => 'Do not Call',
    'Wrong No' => 'Wrong No',
    'Enrolled Elsewhere' => 'Enrolled Elsewhere',
    'Course not Offered' => 'Course not Offered',
    'Funding Enquiry' => 'Funding Enquiry',
);

$fid = $is_post_counselling_followup ? 'followup_pc_form_embed' : 'followup_form_embed';
$idp = $is_post_counselling_followup ? 'followup_pc_' : 'followup_';
$remark_id_prefix = $is_post_counselling_followup ? 'flw_remark_pc_' : 'flw_remark_';

$followupUsers = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
?>
<form class="followup_form" id="<?php echo htmlspecialchars($fid, ENT_QUOTES, 'UTF-8'); ?>">
<input type="hidden" class="followup-stage-value" value="<?php echo $is_post_counselling_followup ? 'post_counselling' : 'enquiry'; ?>">
<div class="row">
<div class="col-md-6"><div class="mb-3"><label class="form-label">Enquiry ID</label><br>
<?php
$followup_enquiry_code = isset($followup_Query['enquiry_id']) ? $followup_Query['enquiry_id'] : '';
?>
<?php $followup_enquiry_placeholder = $followup_enquiry_code ? $followup_enquiry_code : 'Save follow-up to create or link enquiry (email required above)'; ?>
<input type="text" readonly class="form-control-plaintext fw-semibold<?php echo $followup_enquiry_code ? '' : ' text-muted'; ?>" value="<?php echo htmlspecialchars($followup_enquiry_placeholder); ?>">
<input type="hidden" name="enquiry_id" id="<?php echo $idp; ?>enquiry_id" value="<?php echo htmlspecialchars($followup_enquiry_code); ?>">
<div class="error-feedback">Please Select the Enquiry ID</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>contacted_time">Follow-up Date &amp; Time</label>
<input type="datetime-local" class="form-control" id="<?php echo $idp; ?>contacted_time" value="<?php echo $followup_Query['flw_contacted_time']=='' ? '' : date('Y-m-d\TH:i',strtotime($followup_Query['flw_contacted_time'])); ?>"><div class="error-feedback">Please select the follow-up date and time</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>contacted_person">Responsible Staff</label>
<select class="form-select" id="<?php echo $idp; ?>contacted_person" disabled>
<option value="">--select--</option>
<?php
if ($followupUsers) {
    mysqli_data_seek($followupUsers, 0);
    while ($u = mysqli_fetch_array($followupUsers)) {
        $name = $u['user_name'];
        $selectedPerson = trim((string)($followup_Query['flw_contacted_person'] ?? ''));
        if ($selectedPerson === '' && $loggedInStaffName !== '') {
            $selectedPerson = $loggedInStaffName;
        }
        $selected = ($selectedPerson === $name) ? 'selected' : '';
        echo '<option value="'.htmlspecialchars($name).'" '.$selected.'>'.htmlspecialchars($name).'</option>';
    }
}
?>
</select>
<div class="error-feedback">Please select the Responsible Staff</div></div></div>
<div class="col-md-6"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>followup_type">Follow-up Type</label>
<select class="form-select" id="<?php echo $idp; ?>followup_type">
<option value="">--select--</option>
<option value="Call" <?php echo (isset($followup_Query['flw_followup_type']) && $followup_Query['flw_followup_type']=='Call') ? 'selected' : ''; ?>>Call</option>
<option value="Email" <?php echo (isset($followup_Query['flw_followup_type']) && $followup_Query['flw_followup_type']=='Email') ? 'selected' : ''; ?>>Email</option>
</select><div class="error-feedback">Please select Follow-up Type</div></div></div>
<div class="col-12"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>follow_up_outcome">Follow Up Outcome</label>
<select class="form-select" id="<?php echo $idp; ?>follow_up_outcome">
<?php foreach ($follow_up_outcomes as $k => $v) {
    echo '<option value="'.htmlspecialchars($k).'" '.((isset($followup_Query['flw_follow_up_outcome']) && $followup_Query['flw_follow_up_outcome']==$k) ? 'selected' : '').'>'.htmlspecialchars($v).'</option>';
} ?>
</select>
<small class="text-muted d-block mt-1">Follow Up Outcome updates Enquiry status automatically. No Answer / Call Back Later / Booked Counselling: use Calendar to book when needed.</small></div></div>
<div class="col-12 mb-2" id="<?php echo $idp; ?>calendar_btn_wrap" style="display:none;">
<button type="button" class="btn btn-outline-primary" id="<?php echo $idp; ?>open_calendar_btn"><i class="ti ti-calendar"></i> Calendar</button>
<small class="text-muted ms-2">Opens New Appointment with this enquiry’s student details pre-filled.</small></div>
<div class="col-12"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>enquiry_flow_status">Email Template</label>
<select class="form-select" id="<?php echo $idp; ?>enquiry_flow_status">
<?php
$stored_enquiry_flow_status = (isset($followup_Query['enquiry_flow_status']) && (string)$followup_Query['enquiry_flow_status'] !== '')
    ? (int)$followup_Query['enquiry_flow_status'] : null;
?>
<option value=""<?php echo $stored_enquiry_flow_status === null ? ' selected' : ''; ?>>Select email template</option>
<?php foreach ($enquiry_flow_statuses as $k => $v) {
    $sel = ($stored_enquiry_flow_status !== null && $stored_enquiry_flow_status === (int)$k) ? 'selected' : '';
    $dis = ($k==9 && !$has_counselling_appointment) ? ' disabled' : '';
    echo '<option value="'.$k.'" '.$sel.$dis.'>'.$v.'</option>';
} ?>
</select>
<?php
$followup_status_actual = $stored_enquiry_flow_status !== null ? (int)$stored_enquiry_flow_status : '';
?>
<input type="hidden" id="<?php echo $idp; ?>enquiry_status_actual" value="<?php echo $followup_status_actual !== '' ? (int)$followup_status_actual : ''; ?>">
<small class="text-muted d-block">Follow Up Outcome sets enquiry status automatically. Changing <strong>Email Template</strong> only opens the email popup and does not change enquiry status.</small></div></div>
<div class="col-12"><div class="mb-3"><label class="form-label" for="<?php echo $idp; ?>follow_up_notes">Follow-Up Notes</label>
<textarea class="form-control" id="<?php echo $idp; ?>follow_up_notes" rows="3" placeholder="Free text notes"><?php echo htmlspecialchars(isset($followup_Query['flw_follow_up_notes']) ? $followup_Query['flw_follow_up_notes'] : ''); ?></textarea></div></div>
<?php if (!$is_post_counselling_followup) { ?>
<div class="col-12"><div class="mb-3"><label class="form-label d-block">Remarks</label>
<div class="row">
<?php
$st_remarks = array('Seems to be interested to do course and need to contact asap','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Planning to relocate to other state','Wants to get COE for visa purpose');
$remarksSel = ($followup_Query['flw_remarks']!='') ? json_decode($followup_Query['flw_remarks']) : array();
$total_remarks = count($st_remarks);
$half = (int)ceil(($total_remarks - 1) / 2);
?>
<div class="col-md-6">
<?php
for ($i = 1; $i <= $half; $i++) {
    $checked = in_array($i, $remarksSel) ? 'checked' : '';
    echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input followup_remarks" id="'.$remark_id_prefix.$i.'" value="'.$i.'" '.$checked.'><label for="'.$remark_id_prefix.$i.'">'.$st_remarks[$i].'</label></div>';
}
?>
</div>
<div class="col-md-6">
<?php
for ($i = $half + 1; $i < $total_remarks; $i++) {
    $checked = in_array($i, $remarksSel) ? 'checked' : '';
    echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input followup_remarks" id="'.$remark_id_prefix.$i.'" value="'.$i.'" '.$checked.'><label for="'.$remark_id_prefix.$i.'">'.$st_remarks[$i].'</label></div>';
}
?>
</div>
</div>
<div class="error-feedback">Please select atleast one option</div></div></div>
<?php } ?>
</div>
<p class="text-muted small mb-2">Each submit adds a new entry in <strong>this</strong> section&rsquo;s history (Post Enquiry and Post Counselling are stored separately). Use <strong>Follow-up history</strong> on this accordion header to review or resend status emails for this section only.</p>
<button class="btn btn-primary" type="button" id="<?php echo $idp; ?>check">Submit Follow Up</button>
<input type="hidden" value="<?php echo $followupEqId; ?>" id="<?php echo $idp; ?>check_update">
</form>
