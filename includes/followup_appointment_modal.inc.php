<?php
// Full New Appointment form for Follow-up Calendar popup. Uses same field names as appointment_booking.php for datacontrol.php.
// Expects: $fp_purposes, $fp_users, $fp_attendeeTypes, $fp_locations, $fp_platforms, $fp_usersForShare (mysqli_result)
// IDs prefixed with fp_ for JS; name attributes match appointment_booking for backend.
if(!isset($fp_purposes)) $fp_purposes = null;
if(!isset($fp_users)) $fp_users = null;
if(!isset($fp_attendeeTypes)) $fp_attendeeTypes = null;
if(!isset($fp_locations)) $fp_locations = null;
if(!isset($fp_platforms)) $fp_platforms = null;
if(!isset($fp_usersForShare)) $fp_usersForShare = null;
?>
<div class="modal fade" id="followupAppointmentModal" tabindex="-1" aria-labelledby="followupAppointmentModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="followupAppointmentModalLabel">New Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="fp_appointment_form">
                    <input type="hidden" name="formName" value="appointment_booking">
                    <input type="hidden" name="appointment_id" value="0">
                    <input type="hidden" name="created_by" value="<?php echo (int)($_SESSION['user_id'] ?? 0); ?>">
                    <input type="hidden" name="connected_enquiry_id" id="fp_connected_enquiry_id" value="">

                    <h5 class="mb-3">Basic Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Appointment Date </label>
                                <input type="date" class="form-control" id="fp_appointment_date" name="appointment_date">
                                <div class="error-feedback">Please select appointment date</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <!-- <label class="form-label">Time Slot </label> -->
                                <div class="row g-2 align-items-end">
                                    <div class="col">
                                        <label class="form-label small text-muted mb-0">From</label>
                                        <input type="time" class="form-control" id="fp_appointment_time" name="appointment_time">
                                    </div>
                                    <div class="col">
                                        <label class="form-label small text-muted mb-0">To</label>
                                        <input type="time" class="form-control" id="fp_appointment_time_to" name="appointment_time_to">
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">To is set to From + 1 minute when you pick From. You can change To to a later time.</small>
                                <div class="error-feedback" style="display:none;">Please select appointment time</div>
                                <div class="error-feedback" id="fp_time_slot_range_error" style="display:none;">To must be at least 1 minute after From.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Time Zone (State) </label>
                                <select class="form-select" id="fp_timezone_state" name="timezone_state">
                                    <option value="">-- Select State --</option>
                                    <option value="Adelaide">Adelaide (ACST)</option>
                                    <option value="Melbourne">Melbourne (AEST)</option>
                                    <option value="Sydney">Sydney (AEST)</option>
                                    <option value="Perth">Perth (AWST)</option>
                                    <option value="Darwin">Darwin (ACST)</option>
                                    <option value="Brisbane">Brisbane (AEST)</option>
                                </select>
                                <div class="error-feedback">Please select timezone</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Booked By </label>
                                <input type="text" class="form-control" id="fp_booked_by_name" name="booked_by_name" value="<?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?>">
                                <div class="error-feedback">Please enter who booked this appointment</div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Booking Comments</label>
                                <textarea class="form-control" id="fp_booking_comments" name="booking_comments" rows="2"></textarea>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="fp_appointment_time_state" name="appointment_time_state">
                    <input type="hidden" id="fp_appointment_time_adelaide" name="appointment_time_adelaide">
                    <input type="hidden" id="fp_appointment_time_india" name="appointment_time_india">
                    <input type="hidden" id="fp_appointment_time_philippines" name="appointment_time_philippines">

                    <hr>
                    <h5 class="mb-3">Appointment Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Purpose of Appointment </label>
                                <select class="form-select" id="fp_purpose_id" name="purpose_id">
                                    <option value="">-- Select Purpose --</option>
                                    <?php if($fp_purposes){ mysqli_data_seek($fp_purposes,0); while($p = mysqli_fetch_array($fp_purposes)){ echo "<option value='".(int)$p['purpose_id']."'>".htmlspecialchars($p['purpose_name'])."</option>"; } } ?>
                                </select>
                                <div class="error-feedback">Please select purpose</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Appointment To See </label>
                                <select class="form-select" id="fp_appointment_to_see" name="appointment_to_see">
                                    <option value="">-- Select Staff --</option>
                                    <?php if($fp_users){ mysqli_data_seek($fp_users,0); while($u = mysqli_fetch_array($fp_users)){ echo "<option value='".(int)$u['user_id']."'>".htmlspecialchars($u['user_name'])." (".htmlspecialchars($u['user_email'] ?? '').")</option>"; } } ?>
                                </select>
                                <div class="error-feedback">Please select staff member</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Staff Member Type </label>
                                <select class="form-select" id="fp_staff_member_type" name="staff_member_type">
                                    <option value="">-- Select Type --</option>
                                    <option value="Admin">Admin</option>
                                    <option value="Trainers">Trainers</option>
                                    <option value="Management">Management</option>
                                </select>
                                <div class="error-feedback">Please select staff member type</div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Share With</h5>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="fp_share_all">
                                    <label class="form-check-label" for="fp_share_all">All (share with all employees)</label>
                                </div>
                                <div class="row">
                                    <?php if($fp_usersForShare){ mysqli_data_seek($fp_usersForShare,0); while($u = mysqli_fetch_array($fp_usersForShare)){ $uid=(int)$u['user_id']; echo '<div class="col-md-4"><div class="form-check"><input class="form-check-input fp-share-with-item" type="checkbox" name="share_with[]" id="fp_share_'.$uid.'" value="'.$uid.'"><label class="form-check-label" for="fp_share_'.$uid.'">'.htmlspecialchars($u['user_name']).'</label></div></div>'; } } ?>
                                </div>
                                <small class="text-muted d-block mt-1">If no one is selected, only admins will see this appointment.</small>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Attendee Information</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Who wants to come for meeting? </label>
                                <select class="form-select" id="fp_attendee_type_id" name="attendee_type_id">
                                    <option value="">-- Select Type --</option>
                                    <?php if($fp_attendeeTypes){ mysqli_data_seek($fp_attendeeTypes,0); while($t = mysqli_fetch_array($fp_attendeeTypes)){ $sel = ($t['type_id']==1) ? 'selected' : ''; echo "<option value='".(int)$t['type_id']."' {$sel}>".htmlspecialchars($t['type_name'])."</option>"; } } ?>
                                </select>
                                <div class="error-feedback">Please select attendee type</div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="fp_student_info_section">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Student Name </label>
                                <input type="text" class="form-control" id="fp_student_name" name="student_name">
                                <div class="error-feedback">Please enter student name</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Student Phone </label>
                                <input type="text" class="form-control" id="fp_student_phone" name="student_phone">
                                <div class="error-feedback">Please enter student phone</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Student Email </label>
                                <input type="email" class="form-control" id="fp_student_email" name="student_email">
                                <div class="error-feedback">Please enter student email</div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="fp_business_info_section" style="display:none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business Name </label>
                                <input type="text" class="form-control" id="fp_business_name" name="business_name">
                                <div class="error-feedback">Please enter business name</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business Contact </label>
                                <input type="text" class="form-control" id="fp_business_contact" name="business_contact">
                                <div class="error-feedback">Please enter business contact</div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h5 class="mb-3">Meeting Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meeting Type </label>
                                <select class="form-select" id="fp_meeting_type" name="meeting_type">
                                    <option value="">-- Select Type --</option>
                                    <option value="Online">Online</option>
                                    <option value="Face to Face">Face to Face</option>
                                    <option value="Phone">Phone</option>
                                </select>
                                <div class="error-feedback">Please select meeting type</div>
                            </div>
                        </div>
                        <div class="col-md-6" id="fp_location_section" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Location </label>
                                <select class="form-select" id="fp_location_id" name="location_id">
                                    <option value="">-- Select Location --</option>
                                    <?php if($fp_locations){ mysqli_data_seek($fp_locations,0); while($loc = mysqli_fetch_array($fp_locations)){ echo "<option value='".(int)$loc['location_id']."'>".htmlspecialchars($loc['location_name'])."</option>"; } } ?>
                                </select>
                                <div class="error-feedback">Please select location</div>
                            </div>
                        </div>
                        <div class="col-md-6" id="fp_platform_section" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Online Platform </label>
                                <select class="form-select" id="fp_platform_id" name="platform_id">
                                    <option value="">-- Select Platform --</option>
                                    <?php if($fp_platforms){ mysqli_data_seek($fp_platforms,0); while($plat = mysqli_fetch_array($fp_platforms)){ echo "<option value='".(int)$plat['platform_id']."'>".htmlspecialchars($plat['platform_name'])."</option>"; } } ?>
                                </select>
                                <div class="error-feedback">Please select platform</div>
                            </div>
                        </div>
                        <div class="col-md-6" id="fp_meeting_link_section" style="display:none;">
                            <div class="mb-3">
                                <label class="form-label">Meeting Link/Details</label>
                                <input type="text" class="form-control" id="fp_online_meeting_link" name="online_meeting_link" placeholder="Enter Zoom/Google Meet/Outlook link">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Send Email Invitation</label><br>
                                <input type="checkbox" id="fp_send_email" name="send_email" value="1" checked>
                                <label for="fp_send_email">Enable automatic email invitation</label>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Appointment Notes</label>
                                <textarea class="form-control" id="fp_appointment_notes" name="appointment_notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary" id="fp_appointment_submit_btn">Book Appointment</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
