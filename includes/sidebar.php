<?php session_start(); ?>
<?php 
$CRM_ASSET_BASE = 'crm/html/template/assets';
$is_student_sidebar = (isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 0 || $_SESSION['user_type'] === 'student'));
$sidebar_home_url = $is_student_sidebar ? 'student_docs.php' : 'dashboard.php';

// Determine current page for active highlighting (pretty URLs have no .php; strip extension for comparisons)
$current_page_raw = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '');
$current_page = $current_page_raw !== '' ? preg_replace('/\.php$/i', '', $current_page_raw) : '';
$current_query = $_GET ?? array();

$enquiries_pages    = array('student_enquiry', 'view_enquiries', 'enquiry_reports');
$enrolment_pages    = array('enrolment', 'enrolment_online', 'student_enrolment_form', 'enrolment_form_new', 'enrolment_list', 'enrolment_status');
$appointments_pages = array('appointment_booking', 'appointment_blocks', 'appointment_calendar', 'appointment_reports');
$course_forms_pages = array('course_cancellations_list', 'course_extensions_list');
$invoices_pages         = array('invoices_list', 'invoices_create', 'invoice_track');
$student_invoice_pages  = array('student_invoice', 'student_invoice_view');
$assessment_pages       = array('assessment_list', 'assessment_result', 'assessment_new');

$is_enquiries_active       = in_array($current_page, $enquiries_pages, true);
$is_enrolment_active       = in_array($current_page, $enrolment_pages, true);
$is_appointments_active    = in_array($current_page, $appointments_pages, true);
$is_course_forms_active    = in_array($current_page, $course_forms_pages, true);
$is_invoices_active        = in_array($current_page, $invoices_pages, true);
$is_student_invoice_active = in_array($current_page, $student_invoice_pages, true);
$is_assessment_active      = in_array($current_page, $assessment_pages, true);

// Check enrolment_status for student menu items
$student_enrolment_unlocked = false;
$student_enrolment_complete = false;
if ($is_student_sidebar && isset($_SESSION['user_id']) && $_SESSION['user_id'] !== '') {
    $sid = intval($_SESSION['user_id']);
    if (isset($connection)) {
        $enr_check = mysqli_query($connection,
            "SELECT id FROM assessment_assignments
             WHERE student_enrol_id = $sid AND enrolment_status = 1 LIMIT 1"
        );
       if ($enr_check && mysqli_num_rows($enr_check) > 0) {
            $student_enrolment_unlocked = true;
        }

        // Check if enrolment form is marked complete
        $complete_check = mysqli_query($connection,
            "SELECT efn.id FROM enrolment_form_new efn
             INNER JOIN student_users su ON su.email = efn.email_address
             WHERE su.id = $sid AND efn.status = 'complete' LIMIT 1"
        );
        if ($complete_check && mysqli_num_rows($complete_check) > 0) {
            $student_enrolment_complete = true;
        }
    }
}
?>
        <div class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div>
                    <a href="<?php echo $sidebar_home_url; ?>" class="logo logo-normal text-center">
                        <img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo.png" alt="Logo" height="50">
                    </a>
                    <a href="<?php echo $sidebar_home_url; ?>" class="logo-small">
                        <img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo-small.png" alt="Logo" width="50">
                    </a>
                    <a href="<?php echo $sidebar_home_url; ?>" class="dark-logo text-center">
                        <img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo-white.png" alt="Logo" height="50">
                    </a>
                </div>
                <button class="sidenav-toggle-btn btn border-0 p-0 active" id="toggle_btn">
                    <i class="ti ti-arrow-bar-to-left"></i>
                </button>
                <button class="sidebar-close">
                    <i class="ti ti-x align-middle"></i>
                </button>
            </div>

            <div class="sidebar-inner" data-simplebar>
                <div id="sidebar-menu" class="sidebar-menu">
                    <ul>
                        <li class="menu-title"><span>Main Menu</span></li>
                        <li>
                            <ul>
                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li>
                                    <a href="dashboard.php" class="<?php echo $current_page === 'dashboard' ? 'active' : ''; ?>"><i class="ti ti-dashboard"></i><span>Dashboard</span></a>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="submenu">
                                    <a href="javascript:void(0);" class="<?php echo $is_enquiries_active ? 'active' : ''; ?>"><i class="ti ti-file-text"></i><span>Enquiries</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <li><a href="student_enquiry.php" class="<?php echo ($current_page === 'student_enquiry' && (!isset($current_query['view']) || $current_query['view'] !== 'list')) ? 'active' : ''; ?>">Create Enquiry</a></li>
                                        <li><a href="view_enquiries.php" class="<?php echo $current_page === 'view_enquiries' ? 'active' : ''; ?>">View Enquiries</a></li>
                                        <li><a href="enquiry_reports.php" class="<?php echo $current_page === 'enquiry_reports' ? 'active' : ''; ?>">Enquiry Reports</a></li>
                                    </ul>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="submenu">
                                    <a href="javascript:void(0);" class="<?php echo $is_enrolment_active ? 'active' : ''; ?>"><i class="ti ti-user-plus"></i><span>Enrolment</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <li><a href="enrolment.php" class="<?php echo $current_page === 'enrolment' ? 'active' : ''; ?>">Enrolment (Legacy)</a></li>
                                        <li><a href="enrolment_online.php" class="<?php echo $current_page === 'enrolment_online' ? 'active' : ''; ?>">Enrolment Form (Online)</a></li>
                                        <li><a href="enrolment_form_new.php" class="<?php echo $current_page === 'enrolment_form_new' ? 'active' : ''; ?>">Enrolment Form New</a></li>
                                        <li><a href="enrolment_list.php" class="<?php echo $current_page === 'enrolment_list' ? 'active' : ''; ?>">Enrolment List</a></li>
                                    </ul>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="submenu">
                                    <a href="javascript:void(0);" class="<?php echo $is_appointments_active ? 'active' : ''; ?>"><i class="ti ti-calendar"></i><span>Appointments</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <li><a href="appointment_booking.php" class="<?php echo $current_page === 'appointment_booking' ? 'active' : ''; ?>">Book Appointment</a></li>
                                        <li><a href="appointment_blocks.php" class="<?php echo $current_page === 'appointment_blocks' ? 'active' : ''; ?>">Block Slots</a></li>
                                        <li><a href="appointment_calendar.php" class="<?php echo $current_page === 'appointment_calendar' ? 'active' : ''; ?>">Calendar View</a></li>
                                        <li><a href="appointment_reports.php" class="<?php echo $current_page === 'appointment_reports' ? 'active' : ''; ?>">List View</a></li>
                                    </ul>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==0 || @$_SESSION['user_type']==='student'){ ?>
                                <li>
                                    <a href="student_docs.php" class="<?php echo $current_page === 'student_docs' ? 'active' : ''; ?>"><i class="ti ti-file-upload"></i><span>Documents</span></a>
                                </li>
                                <li>
                                    <a href="student_enquiry_form.php" class="<?php echo $current_page === 'student_enquiry_form' ? 'active' : ''; ?>"><i class="ti ti-file-text"></i><span>My Enquiry</span></a>
                                </li>
                                <li>
                                    <a href="student_assessment.php" class="<?php echo $current_page === 'student_assessment' ? 'active' : ''; ?>"><i class="ti ti-file-text"></i><span>My Assessment</span></a>
                                </li>
<?php if ($student_enrolment_unlocked): ?>
                                <li>
                                    <a href="enrolment_status.php" class="<?php echo $current_page === 'enrolment_status' ? 'active' : ''; ?>"><i class="ti ti-activity"></i><span>Enrolment Status</span></a>
                                </li>
                                <li>
                                    <a href="student_enrolment_form.php" class="<?php echo $current_page === 'student_enrolment_form' ? 'active' : ''; ?>"><i class="ti ti-forms"></i><span>Enrolment Form</span></a>
                                </li>
                                <li>
                                    <a href="student_queries.php" class="<?php echo $current_page === 'student_queries' ? 'active' : ''; ?>"><i class="ti ti-message-report"></i><span>My Queries</span></a>
                                </li>
                                <?php endif; ?>
                                <?php if ($student_enrolment_complete): ?>
                                <li>
                                    <a href="student_invoice.php" class="<?php echo $is_student_invoice_active ? 'active' : ''; ?>"><i class="ti ti-file-invoice"></i><span>My Invoice</span></a>
                                </li>
                                <?php endif; ?>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="d-none">
                                    <a href="attendance_record.php"><i class="ti ti-file-spreadsheet"></i><span>Attendance Records</span></a>
                                </li>
                                <li class="d-none">
                                    <a href="attendance.php"><i class="ti ti-file-spreadsheet"></i><span>Add Attendance</span></a>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li>
                                    <a href="email_logs.php" class="<?php echo $current_page === 'email_logs' ? 'active' : ''; ?>"><i class="ti ti-mail-forward"></i><span>Email Logs</span></a>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="submenu">
                                    <a href="javascript:void(0);" class="<?php echo $is_invoices_active ? 'active' : ''; ?>"><i class="ti ti-file-invoice"></i><span>Invoices</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <li><a href="invoices_list.php" class="<?php echo $current_page === 'invoices_list' ? 'active' : ''; ?>">Invoice List</a></li>
                                        <li><a href="invoices_create.php" class="<?php echo $current_page === 'invoices_create' ? 'active' : ''; ?>">Create Invoice</a></li>
                                        <li><a href="invoice_track.php" class="<?php echo $current_page === 'invoice_track' ? 'active' : ''; ?>">Track &amp; Follow-up</a></li>
                                    </ul>
                                </li>
                                <?php } ?>

                                <?php if(@$_SESSION['user_type']==1 || @$_SESSION['user_type']==2){ ?>
                                <li class="submenu">
                                    <a href="javascript:void(0);" class="<?php echo $is_course_forms_active ? 'active' : ''; ?>"><i class="ti ti-file-off"></i><span>Course Forms</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <li><a href="course_cancellations_list.php" class="<?php echo $current_page === 'course_cancellations_list' ? 'active' : ''; ?>">Course Cancellations</a></li>
                                        <li><a href="course_extensions_list.php" class="<?php echo $current_page === 'course_extensions_list' ? 'active' : ''; ?>">Course Extensions</a></li>
                                    </ul>
                                </li>
                                <?php } ?>

                               

                                <?php if(@$_SESSION['user_type']==1){ ?>
                                <li>
                                    <a href="create_user.php" class="<?php echo $current_page === 'create_user' ? 'active' : ''; ?>"><i class="ti ti-users"></i><span>Staff Management</span></a>
                                </li>
                                <li class="submenu">
                                    <a href="javascript:void(0);"><i class="ti ti-user-plus"></i><span>Assessment</span><span class="menu-arrow"></span></a>
                                    <ul>
                                        <!-- <li><a href="create_assessment.php" class="<?php echo $current_page === 'create_assessment.php' ? 'active' : ''; ?>">Create Assessment</a></li> -->
                                        <!-- <li><a href="assessment_list.php" class="<?php echo $current_page === 'assessment_list.php' ? 'active' : ''; ?>">Assessment List</a></li> -->
                                       <li><a href="assessment_list.php" class="<?php echo $current_page === 'assessment_list.php' ? 'active' : ''; ?>">Assessment List</a></li>
                                        <li><a href="assessment_result.php" class="<?php echo $current_page === 'assessment_result.php' ? 'active' : ''; ?>">Assessment Results</a></li>
                                    </ul>
                                </li>
                                <!-- <li>
                                    <a href="assessment_new.php" class="<?//php echo $current_page === 'assessment_new' ? 'active' : ''; ?>"><i class="ti ti-clipboard-list"></i><span>Assessment New</span></a>
                                </li> -->
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>