<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
    
    // Get all dropdown data
    $purposes = mysqli_query($connection, "SELECT * FROM appointment_purposes WHERE purpose_status != 1 ORDER BY purpose_name");
    $attendeeTypes = mysqli_query($connection, "SELECT * FROM appointment_attendee_types WHERE type_status != 1 ORDER BY type_name");
    $locations = mysqli_query($connection, "SELECT * FROM appointment_locations WHERE location_status != 1 ORDER BY location_name");
    $platforms = mysqli_query($connection, "SELECT * FROM appointment_platforms WHERE platform_status != 1 ORDER BY platform_name");
    $users = mysqli_query($connection, "SELECT * FROM users WHERE user_status != 1 ORDER BY user_name");
    $usersForShare = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
    $enquiries = mysqli_query($connection, "SELECT st_enquiry_id, st_name, st_surname, st_phno, st_email FROM student_enquiry WHERE st_enquiry_status != 1 ORDER BY st_enquiry_id DESC");
    $enrolments = mysqli_query($connection, "SELECT st_unique_id, st_given_name, st_surname, st_email, st_mobile FROM student_enrolments WHERE st_status != 1 ORDER BY st_unique_id DESC");
    $counsellings = mysqli_query($connection, "SELECT counsil_id, st_enquiry_id FROM counseling_details WHERE counsil_enquiry_status != 1 ORDER BY counsil_id DESC");

    // Check if editing
    $editMode = false;
    $appointmentData = null;
    if(isset($_GET['id'])){
        $appointmentId = base64_decode($_GET['id']);
        $appointmentQuery = mysqli_query($connection, "SELECT * FROM appointments WHERE appointment_id = $appointmentId AND delete_status != 1");
        if(mysqli_num_rows($appointmentQuery) > 0){
            $appointmentData = mysqli_fetch_array($appointmentQuery);
            $editMode = true;
        }
    }

    // From Follow-up: prefill Student attendee with enquiry details and hide Link to Enquiry/Enrolment/Counselling
    $fromFollowupEnquiry = null;
    if(!$editMode && !empty($_GET['from_followup']) && !empty($_GET['enquiry_id'])){
        $enq_id_esc = mysqli_real_escape_string($connection, $_GET['enquiry_id']);
        $enqRow = @mysqli_fetch_array(mysqli_query($connection, "SELECT st_enquiry_id, st_name, st_surname, st_phno, st_email FROM student_enquiry WHERE st_enquiry_id = '$enq_id_esc' AND st_enquiry_status != 1 LIMIT 1"));
        if($enqRow){
            $fromFollowupEnquiry = array(
                'enquiry_id' => $enqRow['st_enquiry_id'],
                'student_name' => trim($enqRow['st_name'] . ' ' . $enqRow['st_surname']),
                'student_phone' => $enqRow['st_phno'] ?? '',
                'student_email' => $enqRow['st_email'] ?? ''
            );
        }
    }

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Appointment Booking</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        
        <!-- FullCalendar CSS -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet" />
        
        <style>
            .asterisk {
                color: red;
            }
            .timezone-display {
                background: #f8f9fa;
                padding: 10px;
                border-radius: 5px;
                margin-top: 10px;
            }
            .timezone-display .timezone-item {
                margin: 5px 0;
                font-size: 14px;
            }
            .color-preview {
                width: 30px;
                height: 30px;
                border-radius: 4px;
                display: inline-block;
                margin-right: 10px;
                vertical-align: middle;
            }
        </style>
    </head>

    <body>

        <div id="loader-container" style="display:none;">
            <div class="loader"></div>
        </div>

        <!-- Begin page -->
        <div class="main-wrapper">

            
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0"><?php echo $editMode ? 'Edit Appointment' : 'Book Appointment'; ?></h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item"><a href="appointment_calendar.php">Appointments</a></li>
                                            <li class="breadcrumb-item active"><?php echo $editMode ? 'Edit' : 'Book'; ?></li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form id="appointment_form" novalidate>
                                            <input type="hidden" name="formName" value="appointment_booking">
                                            <input type="hidden" name="appointment_id" id="appointment_id" value="<?php echo $editMode ? $appointmentData['appointment_id'] : '0'; ?>">
                                            <input type="hidden" name="created_by" value="<?php echo $_SESSION['user_id']; ?>">
                                            
                                            <!-- Basic Information -->
                                            <h5 class="mb-3">Basic Information</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Appointment Date <span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="appointment_date" name="appointment_date" 
                                                               value="<?php echo $editMode ? date('Y-m-d', strtotime($appointmentData['appointment_date'])) : ''; ?>" 
                                                               <?php if(!$editMode) echo ' min="'.date('Y-m-d').'"'; ?> required>
                                                        <div class="error-feedback">Please select appointment date</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <!-- <label class="form-label">Time Slot <span class="asterisk">*</span></label> -->
                                                        <div class="row g-2 align-items-end">
                                                            <div class="col">
                                                                <label class="form-label small text-muted mb-0">From</label>
                                                                <input type="time" class="form-control" id="appointment_time" name="appointment_time"
                                                                       value="<?php echo $editMode ? date('H:i', strtotime($appointmentData['appointment_time'])) : ''; ?>" required>
                                                            </div>
                                                            <div class="col">
                                                                <label class="form-label small text-muted mb-0">To</label>
                                                                <input type="time" class="form-control" id="appointment_time_to" name="appointment_time_to"
                                                                       value="<?php echo $editMode && !empty($appointmentData['appointment_end_time']) ? date('H:i', strtotime($appointmentData['appointment_end_time'])) : ''; ?>">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">To is set to From + 1 minute when you pick From. You can change To to a later time.</small>
                                                        <div class="error-feedback" style="display:none;">Please select appointment time</div>
                                                        <div class="error-feedback" id="time_slot_range_error" style="display:none;">To must be at least 1 minute after From.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Time Zone (State) <span class="asterisk">*</span></label>
                                                        <select class="form-select" id="timezone_state" name="timezone_state" required>
                                                            <option value="">-- Select State --</option>
                                                            <option value="Adelaide" <?php echo $editMode && $appointmentData['timezone_state'] == 'Adelaide' ? 'selected' : ''; ?>>Adelaide (ACST)</option>
                                                            <option value="Melbourne" <?php echo $editMode && $appointmentData['timezone_state'] == 'Melbourne' ? 'selected' : ''; ?>>Melbourne (AEST)</option>
                                                            <option value="Sydney" <?php echo $editMode && $appointmentData['timezone_state'] == 'Sydney' ? 'selected' : ''; ?>>Sydney (AEST)</option>
                                                            <option value="Perth" <?php echo $editMode && $appointmentData['timezone_state'] == 'Perth' ? 'selected' : ''; ?>>Perth (AWST)</option>
                                                            <option value="Darwin" <?php echo $editMode && $appointmentData['timezone_state'] == 'Darwin' ? 'selected' : ''; ?>>Darwin (ACST)</option>
                                                            <option value="Brisbane" <?php echo $editMode && $appointmentData['timezone_state'] == 'Brisbane' ? 'selected' : ''; ?>>Brisbane (AEST)</option>
                                                        </select>
                                                        <div class="error-feedback">Please select timezone</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Booked By <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="booked_by_name" name="booked_by_name" 
                                                               value="<?php echo $editMode ? $appointmentData['booked_by_name'] : $_SESSION['user_name']; ?>" required>
                                                        <div class="error-feedback">Please enter who booked this appointment</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Booking Comments</label>
                                                        <textarea class="form-control" id="booking_comments" name="booking_comments" rows="2"><?php echo $editMode ? $appointmentData['booking_comments'] : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Timezone Display -->
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="timezone-display" id="timezone_display" style="display:none;">
                                                        <strong>Time in different timezones:</strong>
                                                        <div class="timezone-item"><strong>State Time:</strong> <span id="display_state_time"></span></div>
                                                        <div class="timezone-item"><strong>Adelaide Time:</strong> <span id="display_adelaide_time"></span></div>
                                                        <div class="timezone-item"><strong>India Time (IST):</strong> <span id="display_india_time"></span></div>
                                                        <div class="timezone-item"><strong>Philippines Time (PHT):</strong> <span id="display_philippines_time"></span></div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Hidden fields for timezone times -->
                                            <input type="hidden" id="appointment_time_state" name="appointment_time_state">
                                            <input type="hidden" id="appointment_time_adelaide" name="appointment_time_adelaide">
                                            <input type="hidden" id="appointment_time_india" name="appointment_time_india">
                                            <input type="hidden" id="appointment_time_philippines" name="appointment_time_philippines">

                                            <hr>

                                            <!-- Purpose and Staff -->
                                            <h5 class="mb-3">Appointment Details</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Purpose of Appointment <span class="asterisk">*</span></label>
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-select" id="purpose_id" name="purpose_id" required>
                                                                <option value="">-- Select Purpose --</option>
                                                                <?php 
                                                                while($purpose = mysqli_fetch_array($purposes)){
                                                                    $selected = $editMode && $appointmentData['purpose_id'] == $purpose['purpose_id'] ? 'selected' : '';
                                                                    echo "<option value='{$purpose['purpose_id']}' data-color='{$purpose['purpose_color']}' {$selected}>{$purpose['purpose_name']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#managePurposesModal">
                                                                <i class="mdi mdi-cog"></i> Manage
                                                            </button>
                                                        </div>
                                                        <div class="error-feedback">Please select purpose</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Appointment To See <span class="asterisk">*</span></label>
                                                        <select class="form-select" id="appointment_to_see" name="appointment_to_see" required>
                                                            <option value="">-- Select Staff --</option>
                                                            <?php 
                                                            while($user = mysqli_fetch_array($users)){
                                                                $selected = $editMode && $appointmentData['appointment_to_see'] == $user['user_id'] ? 'selected' : '';
                                                                echo "<option value='{$user['user_id']}' {$selected}>{$user['user_name']} ({$user['user_email']})</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="error-feedback">Please select staff member</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Staff Member Type <span class="asterisk">*</span></label>
                                                        <select class="form-select" id="staff_member_type" name="staff_member_type" required>
                                                            <option value="">-- Select Type --</option>
                                                            <option value="Admin" <?php echo $editMode && $appointmentData['staff_member_type'] == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                                            <option value="Trainers" <?php echo $editMode && $appointmentData['staff_member_type'] == 'Trainers' ? 'selected' : ''; ?>>Trainers</option>
                                                            <option value="Management" <?php echo $editMode && $appointmentData['staff_member_type'] == 'Management' ? 'selected' : ''; ?>>Management</option>
                                                        </select>
                                                        <div class="error-feedback">Please select staff member type</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- Share With (visibility control) -->
                                            <h5 class="mb-3">Share With</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <?php
                                                        $selectedShare = array();
                                                        $shareAll = false;
                                                        if($editMode && !empty($appointmentData['appointment_shared_with'])){
                                                            if(trim($appointmentData['appointment_shared_with']) === 'ALL'){
                                                                $shareAll = true;
                                                            } else {
                                                                $selectedShare = array_map('intval', explode(',', $appointmentData['appointment_shared_with']));
                                                            }
                                                        }
                                                        ?>
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input" type="checkbox" id="share_all" <?php echo $shareAll ? 'checked' : ''; ?>>
                                                            <label class="form-check-label" for="share_all">
                                                                All (share with all employees)
                                                            </label>
                                                        </div>
                                                        <div class="row">
                                                            <?php
                                                            mysqli_data_seek($usersForShare, 0);
                                                            while($u = mysqli_fetch_array($usersForShare)){
                                                                $uid = (int)$u['user_id'];
                                                                $checked = $shareAll || in_array($uid, $selectedShare) ? 'checked' : '';
                                                                echo '<div class="col-md-4"><div class="form-check">';
                                                                echo '<input class="form-check-input share-with-item" type="checkbox" name="share_with[]" id="share_with_'.$uid.'" value="'.$uid.'" '.$checked.'>';
                                                                echo '<label class="form-check-label" for="share_with_'.$uid.'">'.htmlspecialchars($u['user_name']).'</label>';
                                                                echo '</div></div>';
                                                            }
                                                            ?>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">If no one is selected, only admins will see this appointment.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Attendee Information -->
                                            <h5 class="mb-3">Attendee Information</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Who wants to come for meeting? <span class="asterisk">*</span></label>
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-select" id="attendee_type_id" name="attendee_type_id" required>
                                                                <option value="">-- Select Type --</option>
                                                                <?php 
                                                                mysqli_data_seek($attendeeTypes, 0);
                                                                while($type = mysqli_fetch_array($attendeeTypes)){
                                                                    $selected = ($editMode && $appointmentData['attendee_type_id'] == $type['type_id']) || ($fromFollowupEnquiry && $type['type_id'] == 1) ? 'selected' : '';
                                                                    echo "<option value='{$type['type_id']}' {$selected}>{$type['type_name']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#manageAttendeeTypesModal">
                                                                <i class="mdi mdi-cog"></i> Manage
                                                            </button>
                                                        </div>
                                                        <div class="error-feedback">Please select attendee type</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Student Information (shown when Student is selected) -->
                                            <div class="row" id="student_info_section" style="display:none;">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Student Name <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="student_name" name="student_name" 
                                                               value="<?php echo $editMode ? $appointmentData['student_name'] : ($fromFollowupEnquiry ? htmlspecialchars($fromFollowupEnquiry['student_name']) : ''); ?>">
                                                        <div class="error-feedback">Please enter student name</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Student Phone <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="student_phone" name="student_phone" 
                                                               value="<?php echo $editMode ? $appointmentData['student_phone'] : ($fromFollowupEnquiry ? htmlspecialchars($fromFollowupEnquiry['student_phone']) : ''); ?>">
                                                        <div class="error-feedback">Please enter student phone</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Student Email <span class="asterisk">*</span></label>
                                                        <input type="email" class="form-control" id="student_email" name="student_email" 
                                                               value="<?php echo $editMode ? $appointmentData['student_email'] : ($fromFollowupEnquiry ? htmlspecialchars($fromFollowupEnquiry['student_email']) : ''); ?>">
                                                        <div class="error-feedback">Please enter student email</div>
                                                    </div>
                                                </div>
                                                <div id="attendee_link_section" class="row"<?php echo $fromFollowupEnquiry ? ' style="display:none;"' : ''; ?>>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Link to Enquiry</label>
                                                        <select class="selectpicker" data-live-search="true" id="connected_enquiry_id" name="connected_enquiry_id" title="-- Select Enquiry --">
                                                            <option value="">-- None --</option>
                                                            <?php 
                                                            mysqli_data_seek($enquiries, 0);
                                                            while($enq = mysqli_fetch_array($enquiries)){
                                                                $selected = $editMode && $appointmentData['connected_enquiry_id'] == $enq['st_enquiry_id'] ? 'selected' : '';
                                                                echo "<option value='{$enq['st_enquiry_id']}' {$selected}>{$enq['st_enquiry_id']} - {$enq['st_name']} {$enq['st_surname']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Link to Enrolment</label>
                                                        <select class="selectpicker" data-live-search="true" id="connected_enrolment_id" name="connected_enrolment_id" title="-- Select Enrolment --">
                                                            <option value="">-- None --</option>
                                                            <?php 
                                                            mysqli_data_seek($enrolments, 0);
                                                            while($enrol = mysqli_fetch_array($enrolments)){
                                                                $selected = $editMode && $appointmentData['connected_enrolment_id'] == $enrol['st_unique_id'] ? 'selected' : '';
                                                                echo "<option value='{$enrol['st_unique_id']}' {$selected}>{$enrol['st_unique_id']} - {$enrol['st_given_name']} {$enrol['st_surname']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Link to Counselling</label>
                                                        <select class="selectpicker" data-live-search="true" id="connected_counselling_id" name="connected_counselling_id" title="-- Select Counselling --">
                                                            <option value="">-- None --</option>
                                                            <?php 
                                                            mysqli_data_seek($counsellings, 0);
                                                            while($couns = mysqli_fetch_array($counsellings)){
                                                                $selected = $editMode && $appointmentData['connected_counselling_id'] == $couns['counsil_id'] ? 'selected' : '';
                                                                echo "<option value='{$couns['counsil_id']}' {$selected}>Counselling #{$couns['counsil_id']} - {$couns['st_enquiry_id']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>

                                            <!-- Business Information (shown when Business Purpose is selected) -->
                                            <div class="row" id="business_info_section" style="display:none;">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Business Name <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="business_name" name="business_name" 
                                                               value="<?php echo $editMode ? $appointmentData['business_name'] : ''; ?>">
                                                        <div class="error-feedback">Please enter business name</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Business Contact <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="business_contact" name="business_contact" 
                                                               value="<?php echo $editMode ? $appointmentData['business_contact'] : ''; ?>">
                                                        <div class="error-feedback">Please enter business contact</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- Meeting Details -->
                                            <h5 class="mb-3">Meeting Details</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Meeting Type <span class="asterisk">*</span></label>
                                                        <select class="form-select" id="meeting_type" name="meeting_type" required>
                                                            <option value="">-- Select Type --</option>
                                                            <option value="Online" <?php echo $editMode && $appointmentData['meeting_type'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                                                            <option value="Face to Face" <?php echo $editMode && $appointmentData['meeting_type'] == 'Face to Face' ? 'selected' : ''; ?>>Face to Face</option>
                                                            <option value="Phone" <?php echo $editMode && $appointmentData['meeting_type'] == 'Phone' ? 'selected' : ''; ?>>Phone</option>
                                                        </select>
                                                        <div class="error-feedback">Please select meeting type</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="location_section" style="display:none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Location <span class="asterisk">*</span></label>
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-select" id="location_id" name="location_id">
                                                                <option value="">-- Select Location --</option>
                                                                <?php 
                                                                mysqli_data_seek($locations, 0);
                                                                while($loc = mysqli_fetch_array($locations)){
                                                                    $selected = $editMode && $appointmentData['location_id'] == $loc['location_id'] ? 'selected' : '';
                                                                    echo "<option value='{$loc['location_id']}' {$selected}>{$loc['location_name']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#manageLocationsModal">
                                                                <i class="mdi mdi-cog"></i> Manage
                                                            </button>
                                                        </div>
                                                        <div class="error-feedback">Please select location</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="platform_section" style="display:none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Online Platform <span class="asterisk">*</span></label>
                                                        <div class="d-flex align-items-center">
                                                            <select class="form-select" id="platform_id" name="platform_id">
                                                                <option value="">-- Select Platform --</option>
                                                                <?php 
                                                                mysqli_data_seek($platforms, 0);
                                                                while($plat = mysqli_fetch_array($platforms)){
                                                                    $selected = $editMode && $appointmentData['platform_id'] == $plat['platform_id'] ? 'selected' : '';
                                                                    echo "<option value='{$plat['platform_id']}' {$selected}>{$plat['platform_name']}</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-primary ms-2" data-bs-toggle="modal" data-bs-target="#managePlatformsModal">
                                                                <i class="mdi mdi-cog"></i> Manage
                                                            </button>
                                                        </div>
                                                        <div class="error-feedback">Please select platform</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="meeting_link_section" style="display:none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Meeting Link/Details</label>
                                                        <input type="text" class="form-control" id="online_meeting_link" name="online_meeting_link" 
                                                               value="<?php echo $editMode ? $appointmentData['online_meeting_link'] : ''; ?>" 
                                                               placeholder="Enter Zoom/Google Meet/Outlook link">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Send Email Invitation</label><br>
                                                        <input type="checkbox" id="send_email" name="send_email" value="1" 
                                                               <?php echo $editMode && $appointmentData['send_email'] == 1 ? 'checked' : 'checked'; ?>>
                                                        <label for="send_email">Enable automatic email invitation</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Appointment Notes</label>
                                                        <textarea class="form-control" id="appointment_notes" name="appointment_notes" rows="3"><?php echo $editMode ? $appointmentData['appointment_notes'] : ''; ?></textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-12 text-center">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="submit_btn">
                                                        <?php echo $editMode ? 'Update Appointment' : 'Book Appointment'; ?>
                                                    </button>
                                                    <a href="appointment_calendar.php" class="btn btn-secondary waves-effect waves-light">Cancel</a>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end row -->
                    </div> <!-- container-fluid -->
                </div>
            </div>

        </div>

        <!-- Manage Purposes Modal -->
        <div class="modal fade" id="managePurposesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Appointment Purposes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="purposes_list"></div>
                        <hr>
                        <h6>Add New Purpose</h6>
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" class="form-control" id="new_purpose_name" placeholder="Purpose Name">
                            </div>
                            <div class="col-md-4">
                                <input type="color" class="form-control" id="new_purpose_color" value="#0bb197">
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary mt-2" onclick="addPurpose()">Add Purpose</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Attendee Types Modal -->
        <div class="modal fade" id="manageAttendeeTypesModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Attendee Types</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="attendee_types_list"></div>
                        <hr>
                        <h6>Add New Type</h6>
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="new_attendee_type_name" placeholder="Type Name">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" onclick="addAttendeeType()">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Locations Modal -->
        <div class="modal fade" id="manageLocationsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Locations</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="locations_list"></div>
                        <hr>
                        <h6>Add New Location</h6>
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="new_location_name" placeholder="Location Name">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" onclick="addLocation()">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Platforms Modal -->
        <div class="modal fade" id="managePlatformsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Manage Platforms</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="platforms_list"></div>
                        <hr>
                        <h6>Add New Platform</h6>
                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" class="form-control" id="new_platform_name" placeholder="Platform Name">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" onclick="addPlatform()">Add</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer_includes.php'); ?>
        
        <!-- FullCalendar JS -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
        
        <script>
            $(document).ready(function() {
                // Initialize selectpicker
                $('.selectpicker').selectpicker();
                
                // Load management lists
                loadPurposes();
                loadAttendeeTypes();
                loadLocations();
                loadPlatforms();

                // -------- Time Slot Control --------

function timeToMinutes(time){
    if(!time) return null;
    var p = time.split(':');
    return parseInt(p[0])*60 + parseInt(p[1]);
}

function minutesToTime(mins){
    var h = Math.floor(mins/60);
    var m = mins%60;
    return (h<10?'0':'')+h+':' + (m<10?'0':'')+m;
}

function addMinute(time){
    var mins = timeToMinutes(time);
    return minutesToTime(mins+1);
}

// When FROM changes
$('#appointment_time').on('change input', function(){

    var from = $(this).val();
    if(!from) return;

    var to = $('#appointment_time_to').val();

    var fromM = timeToMinutes(from);
    var toM   = timeToMinutes(to);

    var newTo = addMinute(from);

    // auto set if empty or invalid
    if(!to || toM <= fromM){
        $('#appointment_time_to').val(newTo);
    }

    // enforce minimum
    $('#appointment_time_to').attr('min', newTo);
});


// When TO changes
$('#appointment_time_to').on('change input', function(){

    var to   = $(this).val();
    var from = $('#appointment_time').val();

    if(!to || !from) return;

    var fromM = timeToMinutes(from);
    var toM   = timeToMinutes(to);

    if(toM <= fromM){
        var fixed = addMinute(from);
        $('#appointment_time_to').val(fixed);
    }

});
                
                // Show/hide sections based on selections
                $('#attendee_type_id').on('change', function() {
                    var typeId = $(this).val();
                    if(typeId == 1) { // Student
                        $('#student_info_section').show();
                        $('#business_info_section').hide();
                        $('#student_name, #student_phone, #student_email').prop('required', true);
                        $('#business_name, #business_contact').prop('required', false);
                    } else if(typeId == 2) { // Business Purpose
                        $('#student_info_section').hide();
                        $('#business_info_section').show();
                        $('#student_name, #student_phone, #student_email').prop('required', false);
                        $('#business_name, #business_contact').prop('required', true);
                    } else {
                        $('#student_info_section').hide();
                        $('#business_info_section').hide();
                        $('#student_name, #student_phone, #student_email, #business_name, #business_contact').prop('required', false);
                    }
                });
                
                // Trigger on page load if editing or opened from follow-up (Student pre-selected)
                <?php if($editMode || $fromFollowupEnquiry): ?>
                setTimeout(function() {
                    $('#attendee_type_id').trigger('change');
                    $('#meeting_type').trigger('change');
                }, 100);
                <?php endif; ?>
                
                // Show/hide meeting type sections
                $('#meeting_type').on('change', function() {
                    var meetingType = $(this).val();
                    if(meetingType == 'Online') {
                        $('#platform_section').show();
                        $('#meeting_link_section').show();
                        $('#location_section').hide();
                        $('#platform_id').prop('required', true);
                        $('#location_id').prop('required', false);
                    } else if(meetingType == 'Face to Face') {
                        $('#location_section').show();
                        $('#platform_section').hide();
                        $('#meeting_link_section').hide();
                        $('#location_id').prop('required', true);
                        $('#platform_id').prop('required', false);
                    } else {
                        $('#location_section').hide();
                        $('#platform_section').hide();
                        $('#meeting_link_section').hide();
                        $('#location_id, #platform_id').prop('required', false);
                    }
                });
                
                // Trigger on page load if editing
                <?php if($editMode): ?>
                $('#meeting_type').trigger('change');
                <?php endif; ?>
                
                // Disable previous dates (min = today) for new bookings; when date is today, disable past times
                function applyAppointmentDateMin() {
                    var today = new Date();
                    var todayStr = today.toISOString().slice(0, 10);
                    var selectedDate = ($('#appointment_date').val() || '').toString().trim();
                    var nowTimeStr = today.toTimeString().slice(0, 5); // HH:MM
                    if (selectedDate === todayStr) {
                        $('#appointment_time, #appointment_time_to').attr('min', nowTimeStr);
                    } else {
                        $('#appointment_time, #appointment_time_to').removeAttr('min');
                    }
                }
                $('#appointment_date').on('change', applyAppointmentDateMin);
                applyAppointmentDateMin(); // run on load (e.g. when today is pre-selected or in edit mode)

                // Calculate timezone conversions
                $('#appointment_date, #appointment_time, #timezone_state').on('change', function() {
                    calculateTimezones();
                });
                
                // Initial calculation if editing
                <?php if($editMode): ?>
                calculateTimezones();
                <?php endif; ?>
                
                // Form submission
                $('#appointment_form').on('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate required fields
                    var isValid = true;
                    $(this).find('input[required], select[required]').each(function() {
                        if(!$(this).val()) {
                            isValid = false;
                            $(this).closest('.mb-3').find('.error-feedback').show();
                        } else {
                            $(this).closest('.mb-3').find('.error-feedback').hide();
                        }
                    });
                    
                    if(!isValid) {
                        $('.toast-text2').html('Please fill all required fields.');
                        $('#borderedToast2Btn').trigger('click');
                        return;
                    }
                    
                    // Calculate timezones before submit
                    calculateTimezones();
                    
                    // Ensure timezone fields are set
                    if(!$('#appointment_time_state').val()) {
                        var date = $('#appointment_date').val();
                        var time = $('#appointment_time').val();
                        var state = $('#timezone_state').val();
                        if(date && time && state) {
                            var stateDateTime = date + ' ' + time;
                            $('#appointment_time_state').val(stateDateTime);
                            $('#appointment_time_adelaide').val(stateDateTime);
                            $('#appointment_time_india').val(stateDateTime);
                            $('#appointment_time_philippines').val(stateDateTime);
                        }
                    }
                    
                    var formData = new FormData(this);
                    
                    // Show loader similar to Student Enquiry while saving (Google Calendar integration may take time)
                    $('#loader-container').css('display','flex');
                    $('#appointment_form').css('opacity','0.1');

                    $.ajax({
                        type: 'POST',
                        url: 'includes/datacontrol',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            // Backend echoes:
                            // 1 = success
                            // 2 = double-booking conflict for this attendee
                            // 3 = falls inside a blocked slot for this staff/all staff
                            // 0 or others = failure
                            var res = (response || '').toString().trim();
                            if(res === '1') {
                                $('#toast-text').html('Appointment saved successfully!');
                                $('#borderedToast1Btn').trigger('click');
                                setTimeout(function(){
                                    window.location.href = 'appointment_calendar.php';
                                }, 600);
                            } else if(res === '2') {
                                $('.toast-text2').html('Time Slot Already Booked. This person is already booked for the selected time. Please choose a different time slot.');
                                $('#borderedToast2Btn').trigger('click');
                            } else if(res === '3') {
                                $('.toast-text2').html('This time slot is blocked for the selected staff member. Please choose a different time or staff.');
                                $('#borderedToast2Btn').trigger('click');
                            } else {
                                $('.toast-text2').html('Cannot save appointment. Please try again.');
                                $('#borderedToast2Btn').trigger('click');
                            }
                            $('#loader-container').hide();
                            $('#appointment_form').css('opacity','');
                        },
                        error: function() {
                            $('.toast-text2').html('An error occurred. Please try again.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#loader-container').hide();
                            $('#appointment_form').css('opacity','');
                        }
                    });
                });

                // Share With: select all toggles
                $('#share_all').on('change', function(){
                    var checked = $(this).is(':checked');
                    $('.share-with-item').prop('checked', checked);
                });

                $('.share-with-item').on('change', function(){
                    if(!$(this).is(':checked')){
                        $('#share_all').prop('checked', false);
                    }
                });
            });
            
            function calculateTimezones() {
                var date = $('#appointment_date').val();
                var time = $('#appointment_time').val();
                var state = $('#timezone_state').val();
                
                if(date && time && state) {
                    var stateDateTime = date + ' ' + time;
                    var stateMoment = moment.tz(stateDateTime, getTimezone(state));
                    
                    $('#display_state_time').text(stateMoment.format('YYYY-MM-DD HH:mm:ss') + ' (' + state + ')');
                    $('#display_adelaide_time').text(stateMoment.clone().tz('Australia/Adelaide').format('YYYY-MM-DD HH:mm:ss') + ' (Adelaide)');
                    $('#display_india_time').text(stateMoment.clone().tz('Asia/Kolkata').format('YYYY-MM-DD HH:mm:ss') + ' (IST)');
                    $('#display_philippines_time').text(stateMoment.clone().tz('Asia/Manila').format('YYYY-MM-DD HH:mm:ss') + ' (PHT)');
                    
                    $('#timezone_display').show();
                    
                    // Store in hidden fields for submission
                    $('#appointment_time_state').val(stateMoment.format('YYYY-MM-DD HH:mm:ss'));
                    $('#appointment_time_adelaide').val(stateMoment.clone().tz('Australia/Adelaide').format('YYYY-MM-DD HH:mm:ss'));
                    $('#appointment_time_india').val(stateMoment.clone().tz('Asia/Kolkata').format('YYYY-MM-DD HH:mm:ss'));
                    $('#appointment_time_philippines').val(stateMoment.clone().tz('Asia/Manila').format('YYYY-MM-DD HH:mm:ss'));
                } else {
                    $('#timezone_display').hide();
                }
            }
            
            function getTimezone(state) {
                var timezones = {
                    'Adelaide': 'Australia/Adelaide',
                    'Melbourne': 'Australia/Melbourne',
                    'Sydney': 'Australia/Sydney',
                    'Perth': 'Australia/Perth',
                    'Darwin': 'Australia/Darwin',
                    'Brisbane': 'Australia/Brisbane'
                };
                return timezones[state] || 'Australia/Adelaide';
            }
            
            function loadPurposes() {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {formName: 'get_purposes'},
                    success: function(response) {
                        $('#purposes_list').html(response);
                    }
                });
            }
            
            function loadAttendeeTypes() {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {formName: 'get_attendee_types'},
                    success: function(response) {
                        $('#attendee_types_list').html(response);
                    }
                });
            }
            
            function loadLocations() {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {formName: 'get_locations'},
                    success: function(response) {
                        $('#locations_list').html(response);
                    }
                });
            }
            
            function loadPlatforms() {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {formName: 'get_platforms'},
                    success: function(response) {
                        $('#platforms_list').html(response);
                    }
                });
            }
            
            function addPurpose() {
                var name = $('#new_purpose_name').val();
                var color = $('#new_purpose_color').val();
                if(name) {
                    $.ajax({
                        url: 'includes/datacontrol',
                        type: 'POST',
                        data: {
                            formName: 'add_purpose',
                            purpose_name: name,
                            purpose_color: color
                        },
                        success: function(response) {
                            if(response == '1') {
                                $('#toast-text').html('Purpose added successfully');
                                $('#borderedToast1Btn').trigger('click');
                                $('#new_purpose_name').val('');
                                loadPurposes();
                                location.reload();
                            } else {
                                $('.toast-text2').html('Cannot add purpose');
                                $('#borderedToast2Btn').trigger('click');
                            }
                        }
                    });
                }
            }
            
            function addAttendeeType() {
                var name = $('#new_attendee_type_name').val();
                if(name) {
                    $.ajax({
                        url: 'includes/datacontrol',
                        type: 'POST',
                        data: {
                            formName: 'add_attendee_type',
                            type_name: name
                        },
                        success: function(response) {
                            if(response == '1') {
                                $('#toast-text').html('Attendee type added successfully');
                                $('#borderedToast1Btn').trigger('click');
                                $('#new_attendee_type_name').val('');
                                loadAttendeeTypes();
                                location.reload();
                            } else {
                                $('.toast-text2').html('Cannot add attendee type');
                                $('#borderedToast2Btn').trigger('click');
                            }
                        }
                    });
                }
            }
            
            function addLocation() {
                var name = $('#new_location_name').val();
                if(name) {
                    $.ajax({
                        url: 'includes/datacontrol',
                        type: 'POST',
                        data: {
                            formName: 'add_location',
                            location_name: name
                        },
                        success: function(response) {
                            if(response == '1') {
                                $('#toast-text').html('Location added successfully');
                                $('#borderedToast1Btn').trigger('click');
                                $('#new_location_name').val('');
                                loadLocations();
                                location.reload();
                            } else {
                                $('.toast-text2').html('Cannot add location');
                                $('#borderedToast2Btn').trigger('click');
                            }
                        }
                    });
                }
            }
            
            function addPlatform() {
                var name = $('#new_platform_name').val();
                if(name) {
                    $.ajax({
                        url: 'includes/datacontrol',
                        type: 'POST',
                        data: {
                            formName: 'add_platform',
                            platform_name: name
                        },
                        success: function(response) {
                            if(response == '1') {
                                $('#toast-text').html('Platform added successfully');
                                $('#borderedToast1Btn').trigger('click');
                                $('#new_platform_name').val('');
                                loadPlatforms();
                                location.reload();
                            } else {
                                $('.toast-text2').html('Cannot add platform');
                                $('#borderedToast2Btn').trigger('click');
                            }
                        }
                    });
                }
            }
        </script>
        
        <!-- Moment.js with timezone support -->
        <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/moment-timezone@0.5.43/moment-timezone.min.js"></script>
    </body>
</html>
<?php }else{ 
    header("Location: index.php");
}
?>

