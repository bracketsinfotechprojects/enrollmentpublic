<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin/staff only. Students use student_enquiry_form.php (separate page).
$ut = @$_SESSION['user_type'];
if (in_array($ut, [0, 'student'], true)) {
    header('Location: student_enquiry_form.php');
    exit;
}
// Auth: no login -> index (admin login)
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: index.php');
    exit;
}
$is_student_portal = false; // this page is admin-only now

if(isset($_GET['view']) && $_GET['view']=='list'){
        ?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>View Enquiry</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include('includes/app_includes.php'); ?>
    <style>
        #viewEnquiryAccordion .accordion-button { font-weight: 600; }
        #viewEnquiryAccordion .table-responsive { overflow-x: auto; overflow-y: visible; min-height: 0; }
        #viewEnquiryAccordion .dataTables_wrapper .dataTables_filter input { margin-left: 0.5em; border-radius: 4px; padding: 4px 8px; }
        #viewEnquiryAccordion .dataTables_wrapper .dataTables_length select { padding: 4px 8px; margin: 0 4px; border-radius: 4px; }
        #viewEnquiryAccordion .dataTables_wrapper .dataTables_paginate .pagination { margin: 0; }
        #viewEnquiryAccordion .dataTables_wrapper .dataTables_paginate .page-link { display: inline-flex; align-items: center; justify-content: center; min-width: 2em; }
        #viewEnquiryAccordion .dataTables_wrapper .dataTables_paginate .page-link i.ti { font-size: 1.1em; }
        /* Ensure all enquiry table columns show - strip imp-none so Edit stays in Action column */
        #datatable_enquiries tbody td.imp-none { display: table-cell !important; }
        #datatable_enquiries { min-width: 1200px; }
        #viewEnquiryAccordion .dataTables_scrollBody { overflow-x: auto !important; }
        .course-cell-first { display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px; vertical-align: middle; }
        .course-view-more { color: #0d6efd; cursor: pointer; white-space: nowrap; font-size: 0.9em; vertical-align: middle; }
        .course-view-more:hover { color: #0a58ca; text-decoration: underline; }
        .course-view-more .mdi { font-size: 1.1em; vertical-align: middle; }
        .course-view-more-text { margin-left: 1px; }
        .popover-body { max-height: 280px; overflow-y: auto; }
        #viewEnquiryAccordion .dataTables_scrollBody { cursor: grab; user-select: none; }
        #viewEnquiryAccordion .dataTables_scrollBody.drag-scrolling { cursor: grabbing; }
    </style>
</head>
<body class="page-student-enquiry">
<div class="main-wrapper">
<?php include('includes/header.php'); ?>
<?php include('includes/sidebar.php'); ?>
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">View Enquiry</h4>
                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="student_enquiry.php">Create Enquiry</a></li>
                                            <li class="breadcrumb-item active">View Enquiry</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion" id="viewEnquiryAccordion">
                            <!-- Accordion 1: Student Enquiries -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headEnquiries">
                                    <button class="accordion-button fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapseEnquiries" aria-expanded="true" aria-controls="collapseEnquiries">Student Enquiries</button>
                                </h2>
                                <div id="collapseEnquiries" class="accordion-collapse collapse show" aria-labelledby="headEnquiries">
                                    <div class="accordion-body">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="datatable_enquiries" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th>Enquiry ID</th><th>Student Name</th><th>Contact Number</th><th>Email</th><th>Course Type</th><th>Date</th><th>States</th><th>Course</th><th>Visa Condition</th><th>Visa Status</th><th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="student_filter_body"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Accordion 2: Counseling -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headCounseling">
                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCounseling" aria-expanded="false" aria-controls="collapseCounseling">Counseling</button>
                                </h2>
                                <div id="collapseCounseling" class="accordion-collapse collapse" aria-labelledby="headCounseling">
                                    <div class="accordion-body">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="datatable_counseling" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th>Student Name</th><th>Enquiry ID</th><th>Phone</th><th>Email</th><th>Type</th><th>Team Member</th><th>Start Date</th><th>End Date</th><th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="counsel_filter_body"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Accordion 3: Post Enquiry Follow Up (list) -->
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headFollowup">
                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowupList" aria-expanded="false" aria-controls="collapseFollowupList">Post Enquiry Follow Up</button>
                                </h2>
                                <div id="collapseFollowupList" class="accordion-collapse collapse" aria-labelledby="headFollowup">
                                    <div class="accordion-body">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table id="datatable_followup" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th>Enquiry ID</th><th>Name</th><th>Phone</th><th>Contacted Person</th><th>Contacted Time</th><th>Date</th><th>Mode of Contact</th><th>Staff Notes</th><th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="followup_filter_body"></tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <?php include('includes/footer_includes.php'); ?>
        <script>
        function initCoursePopovers(){
            $('.course-view-more').each(function(){
                var el = $(this);
                if (el.data('bs.popover')) el.popover('dispose');
                var raw = el.attr('data-courses');
                if (!raw) return;
                try {
                    var courses = JSON.parse(raw);
                    if (courses && courses.length) {
                        var content = courses.map(function(c){ return $('<div/>').text(c).html(); }).join('<br>');
                        el.popover({ html: true, content: content, trigger: 'hover focus', title: 'All courses', container: 'body', sanitize: false });
                    }
                } catch(e) {}
            });
        }
        function initDataTableEnquiries(){
            if($.fn.DataTable.isDataTable('#datatable_enquiries')) return;
            $('#datatable_enquiries').DataTable({
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                language: { paginate: { previous: "<i class=\"ti ti-chevron-left\"></i>", next: "<i class=\"ti ti-chevron-right\"></i>" } },
                drawCallback: function(){
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    initCoursePopovers();
                },
                scrollX: true,
                autoWidth: false,
                columnDefs: [ { width: '90px', targets: 0 }, { width: '120px', targets: 1 }, { width: '110px', targets: 2 }, { width: '160px', targets: 3 }, { width: '90px', targets: 10 } ],
                order: [[0, 'desc']]
            });
        }
        function initDataTableCounseling(){
            if($.fn.DataTable.isDataTable('#datatable_counseling')) return;
            $('#datatable_counseling').DataTable({
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                language: { paginate: { previous: "<i class=\"ti ti-chevron-left\"></i>", next: "<i class=\"ti ti-chevron-right\"></i>" } },
                drawCallback: function(){ $(".dataTables_paginate > .pagination").addClass("pagination-rounded"); },
                scrollX: true,
                order: [[6, 'desc']]
            });
        }
        function initDataTableFollowup(){
            if($.fn.DataTable.isDataTable('#datatable_followup')) return;
            $('#datatable_followup').DataTable({
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                pageLength: 10,
                language: { paginate: { previous: "<i class=\"ti ti-chevron-left\"></i>", next: "<i class=\"ti ti-chevron-right\"></i>" } },
                drawCallback: function(){ $(".dataTables_paginate > .pagination").addClass("pagination-rounded"); },
                scrollX: true,
                order: [[5, 'desc']]
            });
        }
        var dragScroll = null;
        $(document).on('mousedown', '#viewEnquiryAccordion .dataTables_scrollBody', function(e){
            var el = $(this);
            dragScroll = { el: this, startX: e.pageX, startScroll: this.scrollLeft };
            el.addClass('drag-scrolling');
        });
        $(document).on('mousemove', function(e){
            if (!dragScroll) return;
            e.preventDefault();
            dragScroll.el.scrollLeft = dragScroll.startScroll + (dragScroll.startX - e.pageX);
        });
        $(document).on('mouseup mouseleave', function(e){
            if (dragScroll) {
                $(dragScroll.el).removeClass('drag-scrolling');
                dragScroll = null;
            }
        });
        $(document).on('touchstart', '#viewEnquiryAccordion .dataTables_scrollBody', function(e){
            if (e.originalEvent.touches.length !== 1) return;
            var el = $(this);
            dragScroll = { el: this, startX: e.originalEvent.touches[0].pageX, startScroll: this.scrollLeft };
            el.addClass('drag-scrolling');
        });
        $(document).on('touchmove', function(e){
            if (!dragScroll || e.originalEvent.touches.length !== 1) return;
            e.preventDefault();
            var x = e.originalEvent.touches[0].pageX;
            dragScroll.el.scrollLeft += (dragScroll.startX - x);
            dragScroll.startX = x;
            dragScroll.startScroll = dragScroll.el.scrollLeft;
        });
        $(document).on('touchend touchcancel', function(){
            if (dragScroll) {
                $(dragScroll.el).removeClass('drag-scrolling');
                dragScroll = null;
            }
        });
        $(function(){
            $.ajax({ type:'post', url:'includes/datacontrol', data:{ formName:'fetchEnquiries' }, success:function(data){
                var html = (data && data.trim()) ? data : '<tr><td colspan="11">No records</td></tr>';
                $('#student_filter_body').html(html);
                $('#student_filter_body td.imp-none').removeClass('imp-none');
                initDataTableEnquiries();
                initCoursePopovers();
            }});
            $.ajax({ type:'post', url:'includes/datacontrol', data:{ formName:'fetchCounsel' }, success:function(data){
                $('#counsel_filter_body').html(data && data.trim() ? data : '<tr><td colspan="9">No records</td></tr>');
                initDataTableCounseling();
            }});
            $.ajax({ type:'post', url:'includes/datacontrol', data:{ formName:'fetchFollowupList' }, success:function(data){
                $('#followup_filter_body').html(data && data.trim() ? data : '<tr><td colspan="9">No records</td></tr>');
                initDataTableFollowup();
            }});
        });
        </script>
        </body></html>
        <?php
        exit;
    }

    $script_web_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if ($script_web_dir === '/' || $script_web_dir === '.') {
        $script_web_dir = '';
    } else {
        $script_web_dir = rtrim($script_web_dir, '/');
    }
    $student_login_page_url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $script_web_dir . '/student_login.php';
    
            $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");
        $visaStatus=mysqli_query($connection,"SELECT * from visa_statuses where visa_state_status!=1");
        // Appointment popup (Follow-up Calendar): dropdown data
        $fp_purposes = mysqli_query($connection, "SELECT * FROM appointment_purposes WHERE purpose_status != 1 ORDER BY purpose_name");
        $fp_users = mysqli_query($connection, "SELECT * FROM users WHERE user_status != 1 ORDER BY user_name");
        $fp_attendeeTypes = mysqli_query($connection, "SELECT * FROM appointment_attendee_types WHERE type_status != 1 ORDER BY type_name");
        $fp_locations = mysqli_query($connection, "SELECT * FROM appointment_locations WHERE location_status != 1 ORDER BY location_name");
        $fp_platforms = mysqli_query($connection, "SELECT * FROM appointment_platforms WHERE platform_status != 1 ORDER BY platform_name");
        $fp_usersForShare = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");

    if(isset($_GET['eq'])){
        $Updatestatus=1;
        $eqId=base64_decode($_GET['eq']);
        $eqId=(int)$eqId;
        $queryRes=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enquiry where st_enquiry_status!=1 and st_id=$eqId"));
        if(!$queryRes){
            $queryRes=array();
            $form_id=0;
        } else {
            $form_id=$queryRes['st_id'];
        }


        $rpl_exp=$exp_in=$exp_docs=$exp_prev=$exp_name=$exp_years=$exp_prev_name='';
        $short_grp_org_name=$short_grp_org_type=$short_grp_campus=$short_grp_date=$short_grp_num_std=$short_grp_ind_exp=$short_grp_con_type=$short_grp_con_num=$short_grp_con_name=$short_grp_con_email=$short_grp_before='';
        $slot_book_time=$slot_book_purpose=$slot_book_date=$slot_book_by=$slot_book_link='';


        // RPL Enquiries
        $queryRes_rpl=mysqli_query($connection,"SELECT * from rpl_enquries where enq_form_id=$form_id");
        if(mysqli_num_rows($queryRes_rpl)!=0){
            $queryRes_rpls=mysqli_fetch_array($queryRes_rpl);
            $rpl_status=1;
            $rpl_array=["rpl_exp" => $queryRes_rpls['rpl_exp'] , "exp_in"=>$queryRes_rpls['rpl_exp_in'] , "exp_docs"=>$queryRes_rpls['rpl_exp_docs'] , "exp_prev"=>$queryRes_rpls['rpl_exp_prev_qual'] , "exp_name"=>$queryRes_rpls['rpl_exp_role']  , "exp_years"=>$queryRes_rpls['rpl_exp_years']  , "exp_prev_name"=>$queryRes_rpls['rpl_exp_qual_name']];        
        }else{
            $rpl_status=0;
            $rpl_array=["rpl_exp" => '' , "exp_in"=>'' , "exp_docs"=>'' , "exp_prev"=>'' , "exp_name"=>''  , "exp_years"=>''  , "exp_prev_name"=>'']; 
        }

        // Short Groups 
        $queryRes_regGrp=mysqli_query($connection,"SELECT * from regular_group_form where enq_form_id=$form_id");
        if(mysqli_num_rows($queryRes_regGrp)!=0){
            $queryRes_regGrps=mysqli_fetch_array($queryRes_regGrp);
            $reg_grp_status=1;
            $reg_grp=$queryRes_regGrps['reg_grp_names'];
        }else{
            $reg_grp_status=0;
            $reg_grp='';    
        }

        $queryRes_shortGrp=mysqli_query($connection,"SELECT * from short_group_form where enq_form_id=$form_id");
        if(mysqli_num_rows($queryRes_shortGrp)!=0){
            $queryRes_shortGrps=mysqli_fetch_array($queryRes_shortGrp);
            $short_grp_status=1;
            $short_grp=["short_grp_org_name" => $queryRes_shortGrps['sh_org_name'] , "short_grp_org_type"=>$queryRes_shortGrps['sh_grp_org_type'] , "short_grp_campus"=>$queryRes_shortGrps['sh_grp_campus'],"short_grp_date"=>$queryRes_shortGrps['sh_grp_date'] , "short_grp_num_std"=> $queryRes_shortGrps['sh_grp_num_stds'],"short_grp_ind_exp"=>$queryRes_shortGrps['sh_grp_ind_exp'],"short_grp_con_type"=>$queryRes_shortGrps['sh_grp_con_us'] , "short_grp_con_num"=>$queryRes_shortGrps['sh_grp_phone'],"short_grp_con_name"=>$queryRes_shortGrps['sh_grp_name'],"short_grp_con_email"=>$queryRes_shortGrps['sh_grp_email'],"short_grp_before"=>$queryRes_shortGrps['sh_grp_train_bef']];        
        }else{
            $short_grp_status=0;
            $short_grp=["short_grp_org_name" => '' , "short_grp_org_type"=>'' , "short_grp_campus"=>'',"short_grp_date"=>'', "short_grp_num_std"=> '',"short_grp_ind_exp"=>'',"short_grp_con_type"=>'' , "short_grp_con_num"=>'',"short_grp_con_name"=>'',"short_grp_con_email"=>'',"short_grp_before"=>'' ];    
        }

        // booked appointement
        $queryRes_slotBook=mysqli_query($connection,"SELECT * from slot_book where enq_form_id=$form_id");
        if(mysqli_num_rows($queryRes_slotBook)!=0){
            $slot_book_status=1;
            $queryRes_slotBooks=mysqli_fetch_array($queryRes_slotBook);
            $slot_book=["slot_book_time"=>$queryRes_slotBooks['slot_bk_datetime'],"slot_book_purpose"=>$queryRes_slotBooks['slot_bk_purpose'],"slot_book_date"=>$queryRes_slotBooks['slot_bk_on'],"slot_book_by"=>$queryRes_slotBooks['slot_book_by'],"slot_book_link"=>$queryRes_slotBooks['slot_book_email_link']];                  
        }else{
            $slot_book_status=0;
            $slot_book=["slot_book_time"=>'',"slot_book_purpose"=>'',"slot_book_date"=>'',"slot_book_by"=>'',"slot_book_link"=>''];  
        }

        $rpl_arrays=json_encode($rpl_array);
        $short_grps=json_encode($short_grp);
        $slot_books=json_encode($slot_book);


    }else{
        $Updatestatus=0;
        $eqId=0;
        $queryRes=array();
        $rpl_array=["rpl_exp" => '' , "exp_in"=>'' , "exp_docs"=>'' , "exp_prev"=>'' , "exp_name"=>''  , "exp_years"=>''  , "exp_prev_name"=>'']; 
        $slot_book=["slot_book_time"=>'',"slot_book_purpose"=>'',"slot_book_date"=>'',"slot_book_by"=>'',"slot_book_link"=>''];  
        $short_grp=["short_grp_org_name" => '' , "short_grp_org_type"=>'' , "short_grp_campus"=>'',"short_grp_date"=>'', "short_grp_num_std"=> '',"short_grp_ind_exp"=>'',"short_grp_con_type"=>'' , "short_grp_con_num"=>'',"short_grp_con_name"=>'',"short_grp_con_email"=>'',"short_grp_before"=>'' ];  
        $rpl_arrays=json_encode($rpl_array);
        $short_grps=json_encode($short_grp);
        $slot_books=json_encode($slot_book);
        $form_id=0;
        $reg_grp='';
        $rpl_status=0;
        $short_grp_status=0;
        $slot_book_status=0;
        $reg_grp_status=0;
    }

    // Safe defaults so the form always renders (new enquiry, sparse DB rows, missing st_enquiry_id)
    $student_enquiry_row_defaults = array(
        'st_id' => 0,
        'st_enquiry_id' => '',
        'st_enquiry_date' => '',
        'st_name' => '',
        'st_member_name' => '',
        'st_surname' => '',
        'st_email' => '',
        'st_phno' => '',
        'st_course' => '',
        'st_enquiry_for' => 1,
        'st_enquiry_source' => 0,
        'st_location' => '',
        'st_enquiry_college' => 0,
        'st_course_type' => 0,
        'st_street_details' => '',
        'st_suburb' => '',
        'st_state' => '0',
        'st_post_code' => '',
        'st_visited' => 0,
        'st_startplan_date' => '',
        'st_refered' => 0,
        'st_refer_name' => '',
        'st_refer_alumni' => 0,
        'st_visa_status' => 0,
        'st_visa_note' => '',
        'st_shore' => 0,
        'st_visa_condition' => '',
        'st_ethnicity' => '',
        'st_comments' => '',
        'st_pref_comments' => '',
        'st_fee' => '',
        'st_appoint_book' => 0,
        'st_remarks' => '',
        'st_hearedby' => '',
        'st_enquiry_flow_status' => '',
        'st_contact_notes' => '',
    );
    $queryRes = array_merge($student_enquiry_row_defaults, is_array($queryRes) ? $queryRes : array());
    if ((int)($queryRes['st_id'] ?? 0) === 0 && !empty($_SESSION['user_name']) && trim((string)($queryRes['st_hearedby'] ?? '')) === '') {
        $queryRes['st_hearedby'] = trim((string)$_SESSION['user_name']);
    }
    $sel_source = isset($queryRes['st_enquiry_source']) ? (int)$queryRes['st_enquiry_source'] : 0;
    $show_responsible_staff_row = in_array($sel_source, array(2, 4, 5, 6), true)
        || (!$is_student_portal && isset($eqId) && (int)$eqId === 0);

    // From View Enquiries list (?view=1): open in read-only until user turns "Allow editing" on
    $enquiry_locked_start = (isset($_GET['view']) && (string)$_GET['view'] === '1' && isset($eqId) && (int)$eqId > 0);

    /** Safe Y-m-d for date inputs (avoids warnings on empty/invalid DB values) */
    if (!function_exists('student_enquiry_safe_date_ymd')) {
        function student_enquiry_safe_date_ymd($val) {
            if ($val === null || $val === '') return '';
            $t = strtotime((string)$val);
            return $t ? date('Y-m-d', $t) : '';
        }
    }

    $enquiryIds=mysqli_query($connection,"SELECT st_enquiry_id,st_name,st_phno from student_enquiry where st_enquiry_status!=1");
    $enquiryIdsCounselling=mysqli_query($connection,"SELECT st_enquiry_id,st_name,st_phno from student_enquiry where st_enquiry_status!=1");
    $enquiryIdsFollowup=mysqli_query($connection,"SELECT st_enquiry_id,st_name,st_phno from student_enquiry where st_enquiry_status!=1");
    $enquirySourceStaffUsers = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
    $counsilEqId=0;
    $followupEqId=0;
    $counsil_Query=array('st_enquiry_id'=>'','counsil_timing'=>'','counsil_end_time'=>'','counsil_type'=>'','counsil_mem_name'=>'','counsil_aus_stay_time'=>'','counsil_work_status'=>'','counsil_visa_condition'=>'','counsil_education'=>'','counsil_aus_study_status'=>'','counsil_course'=>'','counsil_university'=>'','counsil_qualification'=>'','counsil_eng_rate'=>'','counsil_migration_test'=>'','counsil_overall_result'=>'','counsil_module_result'=>'','counsil_job_nature'=>'','counsil_vaccine_status'=>'','counsil_pref_comments'=>'','counsil_remarks'=>'','counsil_outcome'=>'');
    $followup_Query=array('enquiry_id'=>'','flw_name'=>'','flw_phone'=>'','flw_contacted_person'=>'','flw_contacted_time'=>'','flw_date'=>'','flw_mode_contact'=>'','flw_followup_type'=>'','flw_follow_up_notes'=>'','flw_next_followup_date'=>'','flw_follow_up_outcome'=>'','flw_comments'=>'','flw_progress_state'=>'','flw_remarks'=>'');
    $followup_pc_Query=array('enquiry_id'=>'','flw_name'=>'','flw_phone'=>'','flw_contacted_person'=>'','flw_contacted_time'=>'','flw_date'=>'','flw_mode_contact'=>'','flw_followup_type'=>'','flw_follow_up_notes'=>'','flw_next_followup_date'=>'','flw_follow_up_outcome'=>'','flw_comments'=>'','flw_progress_state'=>'','flw_remarks'=>'');

    // When editing a specific enquiry, pre-fill counselling & follow-up context from that enquiry
    if(!empty($queryRes['st_id'])){
        $current_enquiry_code = !empty($queryRes['st_enquiry_id']) ? $queryRes['st_enquiry_id'] : sprintf('EQ%05d', (int)$queryRes['st_id']);
        // Always INSERT a new follow-up_calls row on each save (full history). Form is still prefilled from the latest row.
        $followupEqId = 0;
        $counsil_Query['st_enquiry_id'] = $current_enquiry_code;
        $followup_Query['enquiry_id'] = $current_enquiry_code;
        $followup_Query['flw_name'] = $queryRes['st_name'] ?: $queryRes['st_member_name'];
        $followup_Query['flw_phone'] = $queryRes['st_phno'];
        // Load latest counselling record for this enquiry so we UPDATE it if it exists, else INSERT
        $eid = mysqli_real_escape_string($connection, $current_enquiry_code);
        $counsel_q = mysqli_query($connection, "SELECT * FROM counseling_details WHERE st_enquiry_id = '$eid' AND counsil_enquiry_status = 0 ORDER BY counsil_id DESC LIMIT 1");
        if($counsel_q && mysqli_num_rows($counsel_q) > 0){
            $crow = mysqli_fetch_assoc($counsel_q);
            $counsilEqId = (int)$crow['counsil_id'];
            $counsil_Query = array_merge($counsil_Query, array(
                'counsil_timing' => $crow['counsil_timing'] ?? '',
                'counsil_end_time' => $crow['counsil_end_time'] ?? '',
                'counsil_type' => $crow['counsil_type'] ?? '',
                'counsil_mem_name' => $crow['counsil_mem_name'] ?? '',
                'counsil_preferred_intake_date' => $crow['counsil_preferred_intake_date'] ?? '',
                'counsil_mode_of_study' => $crow['counsil_mode_of_study'] ?? '',
                'counsil_aus_stay_time' => $crow['counsil_aus_stay_time'] ?? '',
                'counsil_work_status' => $crow['counsil_work_status'] ?? '',
                'counsil_visa_condition' => $crow['counsil_visa_condition'] ?? '',
                'counsil_education' => $crow['counsil_education'] ?? '',
                'counsil_aus_study_status' => $crow['counsil_aus_study_status'] ?? '',
                'counsil_course' => $crow['counsil_course'] ?? '',
                'counsil_university' => $crow['counsil_university'] ?? '',
                'counsil_qualification' => $crow['counsil_qualification'] ?? '',
                'counsil_eng_rate' => $crow['counsil_eng_rate'] ?? '',
                'counsil_migration_test' => $crow['counsil_migration_test'] ?? '',
                'counsil_overall_result' => $crow['counsil_overall_result'] ?? '',
                'counsil_module_result' => $crow['counsil_module_result'] ?? '',
                'counsil_job_nature' => $crow['counsil_job_nature'] ?? '',
                'counsil_vaccine_status' => $crow['counsil_vaccine_status'] ?? '',
                'counsil_remarks' => $crow['counsil_remarks'] ?? '',
                'counsil_notes' => $crow['counsil_notes'] ?? '',
                'counsil_outcome' => isset($crow['counsil_outcome']) ? trim((string) $crow['counsil_outcome']) : ''
            ));
            // Re-read outcome by primary key so it always pre-fills (SELECT * can omit new columns on some setups)
            if ($counsilEqId > 0) {
                $coq = @mysqli_query($connection, 'SELECT counsil_outcome FROM counseling_details WHERE counsil_id=' . (int) $counsilEqId . ' LIMIT 1');
                if ($coq && ($cor = mysqli_fetch_assoc($coq)) && array_key_exists('counsil_outcome', $cor)) {
                    $vco = trim((string) $cor['counsil_outcome']);
                    if ($vco !== '') {
                        $counsil_Query['counsil_outcome'] = $vco;
                    }
                }
            }
        } else {
            $counsilEqId = 0; // no existing counselling -> INSERT on submit
            $counsil_Query['counsil_visa_condition'] = $queryRes['st_visa_status'] ?? '';
        }
        // Load latest post-enquiry follow-up (each save still INSERTs a new row for history)
        if (!mysqli_fetch_assoc(@mysqli_query($connection, "SHOW COLUMNS FROM followup_calls LIKE 'flw_followup_stage'"))) {
            @mysqli_query($connection, "ALTER TABLE followup_calls ADD COLUMN `flw_followup_stage` VARCHAR(32) NOT NULL DEFAULT 'enquiry'");
        }
        $flw_q = mysqli_query($connection, "SELECT * FROM followup_calls WHERE enquiry_id = '" . mysqli_real_escape_string($connection, $current_enquiry_code) . "' AND flw_enquiry_status = 0 AND flw_followup_stage = 'enquiry' ORDER BY flw_id DESC LIMIT 1");
        if ($flw_q && mysqli_num_rows($flw_q) > 0) {
            $frow = mysqli_fetch_assoc($flw_q);
            $followup_Query = array_merge($followup_Query, array(
                'enquiry_id' => $frow['enquiry_id'] ?? $current_enquiry_code,
                'flw_name' => $frow['flw_name'] ?? $followup_Query['flw_name'],
                'flw_phone' => $frow['flw_phone'] ?? $followup_Query['flw_phone'],
                'flw_contacted_person' => $frow['flw_contacted_person'] ?? '',
                'flw_contacted_time' => $frow['flw_contacted_time'] ?? '',
                'flw_date' => $frow['flw_date'] ?? '',
                'flw_mode_contact' => $frow['flw_mode_contact'] ?? '',
                'flw_followup_type' => $frow['flw_followup_type'] ?? '',
                'flw_follow_up_notes' => $frow['flw_follow_up_notes'] ?? '',
                'flw_next_followup_date' => isset($frow['flw_next_followup_date']) && $frow['flw_next_followup_date'] !== null && $frow['flw_next_followup_date'] !== '' ? $frow['flw_next_followup_date'] : '',
                'flw_follow_up_outcome' => $frow['flw_follow_up_outcome'] ?? '',
                'flw_comments' => $frow['flw_comments'] ?? '',
                'flw_progress_state' => $frow['flw_progress_state'] ?? '',
                'flw_remarks' => $frow['flw_remarks'] ?? '',
                'enquiry_flow_status' => isset($frow['flw_progress_state']) && $frow['flw_progress_state'] !== '' ? $frow['flw_progress_state'] : (isset($queryRes['st_enquiry_flow_status']) ? $queryRes['st_enquiry_flow_status'] : '')
            ));
        } else {
            // Keep enquiry_id, flw_name, flw_phone from main enquiry; use enquiry's flow status for dropdown if available
            if (isset($queryRes['st_enquiry_flow_status']) && $queryRes['st_enquiry_flow_status'] !== '') {
                $followup_Query['enquiry_flow_status'] = $queryRes['st_enquiry_flow_status'];
            }
        }
        // Post-counselling follow-up: latest row for this stage only
        $followup_pc_Query['enquiry_id'] = $current_enquiry_code;
        $followup_pc_Query['flw_name'] = $queryRes['st_name'] ?: $queryRes['st_member_name'];
        $followup_pc_Query['flw_phone'] = $queryRes['st_phno'];
        $flw_pc_q = mysqli_query($connection, "SELECT * FROM followup_calls WHERE enquiry_id = '" . mysqli_real_escape_string($connection, $current_enquiry_code) . "' AND flw_enquiry_status = 0 AND flw_followup_stage = 'post_counselling' ORDER BY flw_id DESC LIMIT 1");
        if ($flw_pc_q && mysqli_num_rows($flw_pc_q) > 0) {
            $fpc = mysqli_fetch_assoc($flw_pc_q);
            $followup_pc_Query = array_merge($followup_pc_Query, array(
                'enquiry_id' => $fpc['enquiry_id'] ?? $current_enquiry_code,
                'flw_name' => $fpc['flw_name'] ?? $followup_pc_Query['flw_name'],
                'flw_phone' => $fpc['flw_phone'] ?? $followup_pc_Query['flw_phone'],
                'flw_contacted_person' => $fpc['flw_contacted_person'] ?? '',
                'flw_contacted_time' => $fpc['flw_contacted_time'] ?? '',
                'flw_date' => $fpc['flw_date'] ?? '',
                'flw_mode_contact' => $fpc['flw_mode_contact'] ?? '',
                'flw_followup_type' => $fpc['flw_followup_type'] ?? '',
                'flw_follow_up_notes' => $fpc['flw_follow_up_notes'] ?? '',
                'flw_next_followup_date' => isset($fpc['flw_next_followup_date']) && $fpc['flw_next_followup_date'] !== null && $fpc['flw_next_followup_date'] !== '' ? $fpc['flw_next_followup_date'] : '',
                'flw_follow_up_outcome' => $fpc['flw_follow_up_outcome'] ?? '',
                'flw_comments' => $fpc['flw_comments'] ?? '',
                'flw_progress_state' => $fpc['flw_progress_state'] ?? '',
                'flw_remarks' => $fpc['flw_remarks'] ?? '',
                'enquiry_flow_status' => isset($fpc['flw_progress_state']) && $fpc['flw_progress_state'] !== '' ? $fpc['flw_progress_state'] : (isset($queryRes['st_enquiry_flow_status']) ? $queryRes['st_enquiry_flow_status'] : '')
            ));
        } else {
            if (isset($queryRes['st_enquiry_flow_status']) && $queryRes['st_enquiry_flow_status'] !== '') {
                $followup_pc_Query['enquiry_flow_status'] = $queryRes['st_enquiry_flow_status'];
            }
        }
    }

    $followup_history_enquiry_code = '';
    if (!empty($queryRes['st_id'])) {
        $followup_history_enquiry_code = !empty($queryRes['st_enquiry_id']) ? $queryRes['st_enquiry_id'] : sprintf('EQ%05d', (int)$queryRes['st_id']);
    }

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Student Enquiry</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php 
        include('includes/app_includes.php'); 
        ?>
        <style>
            /* Follow-up history modal: first three columns (outcome, status, notes) stay pinned; others scroll horizontally */
            #followup_history_scrollwrap {
                max-height: 70vh;
                overflow: auto;
                -webkit-overflow-scrolling: touch;
            }
            #followup_history_table thead th.fh-pin,
            #followup_history_table tbody td.fh-pin {
                position: sticky;
                z-index: 15;
                vertical-align: top;
                box-sizing: border-box;
                background-color: #fff;
                overflow-wrap: anywhere;
                word-break: break-word;
            }
            #followup_history_table thead th.fh-pin {
                z-index: 25;
                background-color: #fff;
            }
            #followup_history_table .fh-pin-1 {
                left: 0;
                width: 6.5rem;
                min-width: 6.5rem;
                max-width: 6.5rem;
            }
            #followup_history_table .fh-pin-2 {
                left: 6.5rem;
                width: 10rem;
                min-width: 7.75rem;
                max-width: 10rem;
            }
            #followup_history_table .fh-pin-3 {
                left: 14.25rem;
                width: 9.5rem;
                min-width: 9.5rem;
                max-width: 9.5rem;
                /* box-shadow: 6px 0 8px -4px rgba(0, 0, 0, 0.12); */
            }
            /* Post Enquiry / Post Counselling follow-up: history control sits just left of the chevron (::after) */
            #headingFollowup.has-followup-history-btn .accordion-button,
            #headingFollowupPostCounselling.has-followup-history-btn .accordion-button {
                padding-right: 10.5rem;
            }
            @media (max-width: 576px) {
                #headingFollowup.has-followup-history-btn .accordion-button,
                #headingFollowupPostCounselling.has-followup-history-btn .accordion-button {
                    padding-right: 9rem;
                }
            }
            #headingFollowup .btn-followup-history-accordion,
            #headingFollowupPostCounselling .btn-followup-history-accordion {
                right: 2.85rem;
                z-index: 5;
                pointer-events: auto;
            }
            /* View-only (Allow editing off): disabled buttons use Theme Customizer primary — not template generic gray */
            body.student-enquiry-controls-disabled .btn-primary:disabled,
            body.student-enquiry-controls-disabled .btn-primary.disabled,
            body.student-enquiry-controls-disabled fieldset:disabled .btn-primary {
                color: #fff !important;
                background-color: rgba(var(--primary-rgb), 0.5) !important;
                border-color: rgba(var(--primary-rgb), 0.42) !important;
                opacity: 1 !important;
            }
            body.student-enquiry-controls-disabled .btn-outline-primary:disabled,
            body.student-enquiry-controls-disabled .btn-outline-primary.disabled {
                color: rgba(var(--primary-rgb), 0.95) !important;
                background-color: rgba(var(--primary-rgb), 0.12) !important;
                border-color: rgba(var(--primary-rgb), 0.4) !important;
                opacity: 1 !important;
            }
            body.student-enquiry-controls-disabled .btn-info:disabled,
            body.student-enquiry-controls-disabled .btn-info.disabled,
            body.student-enquiry-controls-disabled fieldset:disabled .btn-info {
                color: #fff !important;
                background-color: rgba(var(--primary-rgb), 0.5) !important;
                border-color: rgba(var(--primary-rgb), 0.42) !important;
                opacity: 1 !important;
            }
            body.student-enquiry-controls-disabled .btn-success:disabled,
            body.student-enquiry-controls-disabled .btn-success.disabled,
            body.student-enquiry-controls-disabled fieldset:disabled .btn-success {
                color: #fff !important;
                background-color: rgba(var(--primary-rgb), 0.5) !important;
                border-color: rgba(var(--primary-rgb), 0.42) !important;
                opacity: 1 !important;
            }
            body.student-enquiry-controls-disabled .btn-outline-secondary:disabled,
            body.student-enquiry-controls-disabled .btn-outline-secondary.disabled {
                color: rgba(var(--primary-rgb), 0.75) !important;
                background-color: rgba(var(--primary-rgb), 0.08) !important;
                border-color: rgba(var(--primary-rgb), 0.35) !important;
                opacity: 1 !important;
            }
        </style>
    </head>

    <body<?php
        $body_classes = array('page-student-enquiry');
        if (isset($eqId) && (int)$eqId > 0 && !empty($enquiry_locked_start)) {
            $body_classes[] = 'student-enquiry-controls-disabled';
        }
        echo ' class="' . htmlspecialchars(implode(' ', $body_classes), ENT_QUOTES, 'UTF-8') . '"';
    ?>>

    <div id="loader-container">
        <div class="loader"></div>
    </div>

        <!-- Begin page (same structure as dashboard for correct sidebar width) -->
        <div class="main-wrapper">
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0"><?php echo $is_student_portal ? 'Student – My Enquiry' : "Student's Enquiry"; ?></h4>
                                    <div class="page-title-right d-flex align-items-center flex-wrap gap-2 justify-content-end">
                                        <ol class="breadcrumb m-0 align-items-baseline">
                                        <?php if (!$is_student_portal): ?>
                                        <!-- <li class="breadcrumb-item">
                                            <button type="button" id="generate_qr" onclick="genQR()" class="btn btn-info waves-effect waves-light">Create QR Code <i class="mdi mdi-qrcode-edit"></i> </button>
                                            <div class="d-none" id="qrcode"></div>
                                            <a id="downloadLink" download="enquiry_QR.png" class="d-none">Download QR Code</a>
                                        </li> -->
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                        <?php endif; ?>
                                        <li class="breadcrumb-item active"><?php echo $is_student_portal ? 'My Enquiry' : "Student's Enquiry"; ?></li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php if(isset($eqId) && (int)$eqId > 0): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-light border py-2 mb-3 small d-flex flex-wrap align-items-center justify-content-between gap-3">
                                    <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                                        <span class="text-muted fw-semibold"><i class="ti ti-cloud-upload me-1"></i> Auto-save</span>
                                        <span id="autosave_badge_enquiry" class="badge rounded-pill bg-secondary">Enquiry: idle</span>
                                        <span id="autosave_badge_counsel" class="badge rounded-pill bg-secondary">Counseling: idle</span>
                                        <span id="autosave_badge_followup" class="badge rounded-pill bg-secondary">Follow-up: idle</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" id="enquiry_edit_mode_toggle" role="switch" <?php echo !empty($enquiry_locked_start) ? '' : 'checked'; ?> aria-label="Allow editing">
                                            <label class="form-check-label fw-semibold" for="enquiry_edit_mode_toggle">Allow editing</label>
                                        </div>
                                        <span id="enquiry_edit_mode_hint" class="text-muted"><?php echo !empty($enquiry_locked_start) ? 'View only — turn on to edit.' : 'Turn off to view without editing.'; ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <!-- end page title -->
        <div id="enquiryAccordionGroup">
                        <form class="student_enquiry_form" id="student_enquiry_form">
                        <div class="card mb-3" id="student_enquiry_contact_bar">
                            <div class="card-body">
                                <h6 class="card-title mb-3">Student contact</h6>
                                <p class="text-muted small mb-3">Used for every section below (enquiry, follow-up, and counselling). Email is required to save any of them. If you save follow-up or counselling first, an enquiry is created automatically from these details.</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="enquiry_date">Enquiry Date</label>
                                            <input type="date" class="form-control" id="enquiry_date" value="<?php echo htmlspecialchars(student_enquiry_safe_date_ymd($queryRes['st_enquiry_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            <div class="error-feedback">Please select the Date</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="surname">Surname</label>
                                            <input type="text" class="form-control" id="surname" placeholder="Surname" value="<?php echo  $queryRes['st_surname']; ?>" >
                                            <div class="error-feedback">Please enter the Surname</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="contact_num">Mobile</label>
                                            <input type="text" class="form-control number-field" maxlength="10" id="contact_num" placeholder="Contact Number" value="<?php echo $queryRes['st_phno']; ?>" >
                                            <div class="error-feedback">Please enter the Contact Number</div>
                                            <div class="phone_error">
                                                Entered Number Already exist with Enquiry ID: <span id="phone_err_id"></span>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="courses">Course Interested In</label>
                                            <?php
                                            $counts = 1;
                                            mysqli_data_seek($courses, 0);
                                            while ($coursesRes = mysqli_fetch_array($courses)) {
                                                if ($queryRes['st_course'] != '') {
                                                    $coursesSel = json_decode($queryRes['st_course']);
                                                } else {
                                                    $coursesSel = array();
                                                }
                                                if (in_array($counts, $coursesSel)) {
                                                    $checked = 'checked';
                                                } else {
                                                    $checked = '';
                                                }
                                                echo '<div class="form-check"><input type="checkbox" class="courses_check form-check-input" id="course_check_' . $counts . '" ' . $checked . ' value="' . $counts . '">';
                                                echo '<label for="course_check_' . $counts . '">' . $coursesRes['course_sname'] . '-' . $coursesRes['course_name'] . '</label></div>';
                                                $counts++;
                                            }
                                            ?>
                                            <div class="courses_error">Please select the Courses</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="member_name">Name</label>
                                            <input type="text" class="form-control" id="member_name" placeholder="Name" value="<?php echo $queryRes['st_enquiry_for']==1 ? $queryRes['st_name'] : $queryRes['st_member_name']; ?>">
                                            <div class="error-feedback">Please enter the Name</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="email_address">Email</label>
                                            <?php
                                            $is_edit_enquiry = isset($eqId) && (int)$eqId > 0;
                                            $linked_student_user_id = isset($queryRes['student_user_id']) ? (int)$queryRes['student_user_id'] : 0;
                                            $email_linked_to_student = $is_edit_enquiry && $linked_student_user_id > 0;
                                            ?>
                                            <div class="d-flex flex-wrap align-items-stretch gap-2">
                                                <input type="email" class="form-control flex-grow-1 min-w-0" id="email_address" name="email_address" placeholder="Email Address (required)" required autocomplete="email" value="<?php echo htmlspecialchars((string)($queryRes['st_email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" >
                                                <?php if ($is_edit_enquiry && !$is_student_portal): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary align-self-start" id="send_student_login_link_btn" title="Send student portal login link">Send login link</button>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($is_edit_enquiry): ?>
                                            <div class="form-text text-muted small"><?php echo $email_linked_to_student ? 'Linked to a registered student — updating the email will stay in sync with the student portal account. Turn on Allow editing to change it.' : 'Used for notifications and enquiry identity. Turn on Allow editing to change it (same as other fields).'; ?></div>
                                            <?php else: ?>
                                            <div class="error-feedback">Please enter the Email Address</div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label" for="enquiry_for">Enquiring For</label>
                                            <select name="enquiry_for" class="form-select" id="enquiry_for">
                                            <?php
                                            $st_enquiry = array('--select--', 'Self', 'Family Member');
                                            for ($i = 0; $i < count($st_enquiry); $i++) {
                                                $checked = $i == $queryRes['st_enquiry_for'] ? 'selected' : '';
                                                echo '<option value="' . $i . '" ' . $checked . '>' . $st_enquiry[$i] . '</option>';
                                            }
                                            ?>
                                            </select>
                                            <div class="error-feedback">Please select atleast one option</div>
                                        </div>
                                        <div class="mb-3" id="student_name_wrap" style="display:<?php echo $queryRes['st_enquiry_for'] == 2 ? 'block' : 'none'; ?>">
                                            <label class="form-label" for="student_name">Student name / Family member name</label>
                                            <input type="text" class="form-control" id="student_name" placeholder="Student name" value="<?php echo $queryRes['st_name']; ?>">
                                            <div class="error-feedback">Please enter the Student name</div>
                                        </div>
                                        <?php if (!$is_student_portal): ?>
                                        <div class="mb-3">
                                            <label class="form-label" for="enquiry_college">Received Enquiry for Which college</label>
                                            <select name="enquiry_college" class="form-select" id="enquiry_college">
                                            <?php
                                            $st_enquiry_college = array('--select--', 'Apt Training College', 'Milton College', 'NCA', 'Power Education', 'Auz Training');
                                            $sel_college = isset($queryRes['st_enquiry_college']) ? (int) $queryRes['st_enquiry_college'] : 0;
                                            for ($i = 0; $i < count($st_enquiry_college); $i++) {
                                                $ch = $i === $sel_college ? 'selected' : '';
                                                echo '<option value="' . $i . '" ' . $ch . '>' . $st_enquiry_college[$i] . '</option>';
                                            }
                                            ?>
                                            </select>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="enquiry_source">Enquiry source</label>
                                            <select name="enquiry_source" class="form-select" id="enquiry_source">
                                            <?php
                                            $st_enquiry_source = ['--select--','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads'];
                                            for($i=0;$i<count($st_enquiry_source);$i++){
                                                $ch = $i === $sel_source ? 'selected' : '';
                                                echo '<option value="'.$i.'" '.$ch.'>'.$st_enquiry_source[$i].'</option>';
                                            }
                                            if ($sel_source === 7) {
                                                echo '<option value="7" selected>Agent / referral (legacy)</option>';
                                            }
                                            ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3" id="enquiry_source_staff_wrap" style="display:<?php echo $show_responsible_staff_row ? 'block' : 'none'; ?>;">
                                            <label class="form-label" for="enquiry_source_responsible_staff">Responsible staff</label>
                                            <select class="form-select" id="enquiry_source_responsible_staff" name="enquiry_source_responsible_staff" disabled>
                                                <option value="">--select--</option>
                                                <?php
                                                $staff_sel = isset($queryRes['st_hearedby']) ? trim((string)$queryRes['st_hearedby']) : '';
                                                if ($staff_sel === '' && isset($_SESSION['user_name'])) {
                                                    $staff_sel = trim((string)$_SESSION['user_name']);
                                                }
                                                if ($enquirySourceStaffUsers && mysqli_num_rows($enquirySourceStaffUsers) > 0) {
                                                    mysqli_data_seek($enquirySourceStaffUsers, 0);
                                                    while ($su = mysqli_fetch_array($enquirySourceStaffUsers)) {
                                                        $nm = $su['user_name'];
                                                        $os = ($staff_sel === $nm) ? 'selected' : '';
                                                        echo '<option value="'.htmlspecialchars($nm, ENT_QUOTES, 'UTF-8').'" '.$os.'>'.htmlspecialchars($nm, ENT_QUOTES, 'UTF-8').'</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <div class="error-feedback" id="enquiry_source_staff_error" style="display:none;">Please select the responsible staff for this enquiry source.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="location">Location</label>
                                            <input type="text" class="form-control" id="location" name="location" placeholder="Location" value="<?php echo isset($queryRes['st_location']) ? htmlspecialchars($queryRes['st_location']) : ''; ?>">
                                            <div class="error-feedback">Please enter the Location</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="enquiry_source_phone_calendar_wrap" style="display:<?php echo ($sel_source === 2) ? 'block' : 'none'; ?>;">
                                    <div class="col-12 mb-2">
                                        <button type="button" class="btn btn-outline-primary" id="contact_bar_open_calendar_btn"><i class="ti ti-calendar"></i> Calendar</button>
                                        <small class="text-muted ms-2">Book a counselling appointment (same as Follow-up). With no enquiry yet, saving the appointment creates the enquiry and sets status to Booked Counselling.</small>
                                    </div>
                                </div>
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col-12">
                                        <div class="mb-0">
                                            <label class="form-label" for="st_contact_notes">Notes</label>
                                            <textarea class="form-control" id="st_contact_notes" name="st_contact_notes" rows="3" placeholder="Notes about this contact (internal)"><?php echo htmlspecialchars((string)($queryRes['st_contact_notes'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
        <div class="accordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingStudentEnquiry">
                    <button class="accordion-button fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudentEnquiry" aria-expanded="true" aria-controls="collapseStudentEnquiry">
                        Student Enquiry
                    </button>
                </h2>
                <div id="collapseStudentEnquiry" class="accordion-collapse collapse show" aria-labelledby="headingStudentEnquiry" data-bs-parent="#enquiryAccordionGroup">
                    <div class="accordion-body p-0">

                          <!-- Short Course - group Form -->

                <div class="row" id="short_grp_form" style="display:<?php echo $queryRes['st_course_type']==5 || $queryRes['st_course_type']==4 ? 'block' : 'none' ?>">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <b><p class="card-title">Short Course Group Form</p></b>
                                <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_org_name">Organisation Name</label>
                                    <input type="text" name="short_grp_org_name" class="form-control" id="short_grp_org_name" placeholder="Organisation Name"  value="<?php echo $short_grp['short_grp_org_name']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_org_type">Type of Organisation</label>
                                    <select name="short_grp_org_type" class="form-control" id="short_grp_org_type">
                                    <?php 
                                        $short_grp_org_type=['--select--','Job Agency','Employer','College'];
                                        for($i=0;$i<count($short_grp_org_type);$i++){
                                            $selected=$i==$short_grp['short_grp_org_type'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_org_type[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_campus">Training to be given</label>
                                    <select name="short_grp_campus" class="form-control" id="short_grp_campus">
                                    <?php 
                                        $short_grp_campus=['--select--','Off Campus','On Campus'];
                                        for($i=0;$i<count($short_grp_campus);$i++){
                                            $selected=$i==$short_grp['short_grp_campus'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_campus[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_date">Date Required</label>
                                    <input type="date" name="short_grp_date" class="form-control" id="short_grp_date" value="<?php echo $short_grp['short_grp_date']!='' ? date('Y-m-d',strtotime($short_grp['short_grp_date'])) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_num_std">Number of Students</label>
                                    <input type="tel" name="short_grp_num_std" class="form-control number-field" id="short_grp_num_std" value="<?php echo $short_grp['short_grp_num_std']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_ind_exp">Have they got Industry Experience ?</label>
                                    <select name="short_grp_ind_exp" class="form-control" id="short_grp_ind_exp">
                                    <?php 
                                        $short_grps_ind_exp=['--select--','Yes','No'];
                                        for($i=0;$i<count($short_grps_ind_exp);$i++){
                                            $selected=$i==$short_grp['short_grp_ind_exp'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grps_ind_exp[$i]."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_before">Have they done this Training Before ?</label>
                                    <select name="short_grp_before" class="form-control" id="short_grp_before">
                                    <?php 
                                        $short_grp_train_bef=['--select--','Yes','No'];
                                        for($i=0;$i<count($short_grp_train_bef);$i++){
                                            $selected=$i==$short_grp['short_grp_before'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_train_bef[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_type">How did they Contact us</label>
                                    <input type="text" name="short_grp_con_type" class="form-control" id="short_grp_con_type" value="<?php echo $short_grp['short_grp_con_type']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_num">Contact Number</label>
                                    <input type="tel" name="short_grp_con_num" class="form-control number-field" id="short_grp_con_num" placeholder="Contact Number" value="<?php echo $short_grp['short_grp_con_num']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_name">Contact Person Name</label>
                                    <input type="text" name="short_grp_con_name" class="form-control" id="short_grp_con_name" placeholder="Name" value="<?php echo $short_grp['short_grp_con_name']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_email">Contact Person Email</label>
                                    <input type="email" name="short_grp_con_email" class="form-control" id="short_grp_con_email" placeholder="Email" value="<?php echo $short_grp['short_grp_con_email']; ?>">
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                                <div class="row" id="rpl_form" style="display:<?php echo (isset($queryRes['st_course_type']) && $queryRes['st_course_type']==1) ? 'block' : 'none' ?>">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Need exemption Form</p></b>
                                                <div class="row">

                                                <div class="col-md-6">
                                                    <label class="form-label" for="rpl_exp">Do they have Experience ?</label>
                                                    <select name="rpl_exp" class="form-control rpl_parent" id="rpl_exp">
                                                        <?php 
                                                        $rpl_exps=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps);$i++){
                                                            $selected=$i==$rpl_array['rpl_exp'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_in">Experienced In</label>
                                                    <select name="exp_in" class="form-control" id="exp_in">
                                                    <?php 
                                                        $rpl_exps_in=['--select--','Aged Care','Disability Care','Mental Health','Home Care and Hospitals'];
                                                        for($i=0;$i<count($rpl_exps_in);$i++){
                                                            $selected=$i==$rpl_array['exp_in'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_in[$i]."</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_name">Role/Designation</label>
                                                    <input type="text" name="exp_name" class="form-control" id="exp_name" placeholder="Role" value="<?php echo $rpl_array['exp_name']; ?>">
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_years">How Many Years & Months</label>
                                                    <input type="text" name="exp_years" class="form-control" id="exp_years" placeholder="Years" value="<?php echo $rpl_array['exp_years']; ?>">
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_docs">Do they have any Documents(payslips and job description / Contract Letter)</label>
                                                    <select name="exp_docs" class="form-control" id="exp_docs">
                                                    <?php 
                                                        $rpl_exps_doc=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps_doc);$i++){
                                                            $selected=$i==$rpl_array['exp_docs'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_doc[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_prev">Any previous Qualification done ?</label>
                                                    <select name="exp_prev" class="form-control rpl_prev_parent" id="exp_prev">
                                                    <?php 
                                                        $rpl_exps_prev=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps_prev);$i++){
                                                            $selected=$i==$rpl_array['exp_prev'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_prev[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_prev_child">
                                                    <label class="form-label" for="exp_prev_name">Previous Qualification Name</label>
                                                    <input type="text" name="exp_prev_name" class="form-control" id="exp_prev_name" placeholder="Name" value="<?php echo $rpl_array['exp_prev_name']; ?>">
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="regular_grp_form" style="display:<?php echo $queryRes['st_course_type']==3 ? '' : 'none' ?>">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Regular Group Form</p></b>
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <label class="form-label" for="reg_grp_names">Enter the People Names</label>
                                                        <input type="text" id="reg_grp_names" class="form-control" name="reg_grp_names" value="<?php echo $reg_grp; ?>">
                                                        <div class="alert alert-primary d-flex align-items-center mt-2" role="alert">
                                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                                                        <div>
                                                        Multiple Names can be written with a Comma(,) in Between
                                                        </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">
                                            <div class="accordion" id="accordionAddressDetails">
                                                <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwo">
                                                                <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                                Address Details
                                                                </button>
                                                            </h2>                                                                
                                                    <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionAddressDetails">
                                                        <div class="accordion-body">
                                                            <div class="row">
                                                                <!-- Course Type: single line, 50% width only -->
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="course_type">Course Type</label>
                                                                        <select name="course_type" class="form-select" id="course_type">
                                                                        <?php  
                                                                        $st_course_type=['--select--','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
                                                                        $selectedCourseType=$queryRes['st_course_type']!='' ? $queryRes['st_course_type'] : 0;
                                                                        for($i=0;$i<count($st_course_type);$i++){
                                                                            $checked= $i==$queryRes['st_course_type'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" data="'.$st_course_type[$i].'" '.$checked.'>'.$st_course_type[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>  
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6"></div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="street_no">Street No / Name</label>
                                                                        <input type="text" class="form-control street_no" id="street_no" placeholder="Street No / Name" value="<?php echo $queryRes['st_street_details']; ?>" >
                                                                        <div class="error-feedback">
                                                                            Please enter the Street Details
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="suburb">Suburb</label>
                                                                        <input type="text" class="form-control suburb" id="suburb" placeholder="Suburb" value="<?php echo $queryRes['st_suburb']; ?>" >
                                                                        <div class="error-feedback">
                                                                            Please enter the Suburb
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="stu_state">State</label>
                                                                        <select name="stu_state" id="stu_state" class="form-control">
                                                                        <?php  
                                                                        $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
                                                                        for($i=0;$i<count($st_states);$i++){
                                                                            $checked= $i==$queryRes['st_state'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_states[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>
                                                                        <div class="error-feedback">
                                                                            Please enter the State
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="post_code">Post Code</label>
                                                                        <input type="tel" class="form-control number-field" maxlength="6" id="post_code" placeholder="Post Code" value="<?php echo $queryRes['st_post_code']; ?>" >
                                                                        <div class="error-feedback">
                                                                            Please enter the Post Code
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">      
                                                <div class="accordion" id="accordionVisaStatus">
                                                    <div class="accordion-item">
                                                                <h2 class="accordion-header" id="headingThree">
                                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_counsel" aria-expanded="true" aria-controls="collapse_counsel">
                                                                    Applicant Details & Visa Status
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse_counsel" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionVisaStatus">
                                                                    <div class="accordion-body">
                                                                    <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="visit_before">Have you visited us before?</label>
                                                                        <select name="visit_before" class="form-select" id="visit_before">
                                                                        <?php  
                                                                        $st_visited=['--select--','Yes','No'];
                                                                        for($i=0;$i<count($st_visited);$i++){
                                                                            $checked= $i==$queryRes['st_visited'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_visited[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>  
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="plan_to_start_date">When do you plan to start?</label>
                                                                        <input type="date" class="form-control" id="plan_to_start_date" value="<?php echo htmlspecialchars(student_enquiry_safe_date_ymd($queryRes['st_startplan_date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" >
                                                                        <div class="error-feedback">
                                                                            Please select the Plan to Start Date
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="refer_select">Have you been referred by someone?</label>
                                                                        <select name="refer_select" class="form-select refered" id="refer_select">
                                                                        <?php  
                                                                        $st_refered=['--select--','Yes','No'];
                                                                        for($i=0;$i<count($st_refered);$i++){
                                                                            $checked= $i==$queryRes['st_refered'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_refered[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="visa_condition">Visa Condition</label>
                                                                        <select name="visa_condition" class="form-select" id="visa_condition">
                                                                            <?php $selVisa = isset($queryRes['st_visa_status']) ? (int)$queryRes['st_visa_status'] : 0; ?>
                                                                            <option value="0">--select--</option>
                                                                            <option value="1" <?php echo $selVisa===1 ? 'selected' : ''; ?>>500 - Main Applicant</option>
                                                                            <option value="2" <?php echo $selVisa===2 ? 'selected' : ''; ?>>500 - Dependent</option>
                                                                            <option value="11" <?php echo $selVisa===11 ? 'selected' : ''; ?>>485 - Main Applicant</option>
                                                                            <option value="12" <?php echo $selVisa===12 ? 'selected' : ''; ?>>485 - Dependent</option>
                                                                            <option value="3" <?php echo $selVisa===3 ? 'selected' : ''; ?>>491 - Main Applicant</option>
                                                                            <option value="4" <?php echo $selVisa===4 ? 'selected' : ''; ?>>491 - Dependent</option>
                                                                            <option value="5" <?php echo $selVisa===5 ? 'selected' : ''; ?>>Visitor Visa</option>
                                                                            <option value="6" <?php echo $selVisa===6 ? 'selected' : ''; ?>>Permanent Resident</option>
                                                                            <option value="7" <?php echo $selVisa===7 ? 'selected' : ''; ?>>Spouse Visa</option>
                                                                            <option value="8" <?php echo $selVisa===8 ? 'selected' : ''; ?>>Working Holiday Visa</option>
                                                                            <option value="9" <?php echo $selVisa===9 ? 'selected' : ''; ?>>AU/NZ Citizen</option>
                                                                            <option value="10" <?php echo $selVisa===10 ? 'selected' : ''; ?>>Other</option>
                                                                        </select>
                                                                        <div class="error-feedback">
                                                                            Please select a visa status
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 visa_note" style="display:<?php echo (isset($queryRes['st_visa_status']) && (int)$queryRes['st_visa_status']===10) ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="visa_note">Specify Visa Status</label>
                                                                        <input type="text" class="form-control" id="visa_note" value="<?php echo $queryRes['st_visa_note']; ?>" placeholder="Visa Note">
                                                                        <div class="error-feedback">
                                                                            Please Specify the Visa Condition
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="shore">Are you Offshore or Onshore</label>
                                                                        <select name="shore" class="form-select" id="shore">
                                                                        <?php  
                                                                        $st_shore=['--select--','OffShore','OnShore'];
                                                                        $shore_sel = isset($queryRes['st_shore']) ? (int)$queryRes['st_shore'] : 0;
                                                                        for($i=0;$i<count($st_shore);$i++){
                                                                            $checked= $i==$shore_sel ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_shore[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <div><label for="visa_status_label">Visa Status</label></div>
                                                                        <div>
                                                                            <input class="form-check-input visa_status" type="radio" value="1" name="visa_status" id="visa_status1" <?php echo $queryRes['st_visa_condition']=='' ? 'checked' :  ( $queryRes['st_visa_condition']==1 ? 'checked' : '' ) ; ?>>
                                                                            <label class="form-check-label" for="visa_status1">Approved</label>
                                                                            <input class="form-check-input visa_status" type="radio" value="2" name="visa_status" id="visa_status2" <?php echo $queryRes['st_visa_condition']==2 ? 'checked' : ''; ?>>
                                                                            <label class="form-check-label" for="visa_status2">Not Approved</label>
                                                                            <div class="error-feedback">Please select a visa status</div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="ethnicity">Ethnicity</label>
                                                                        <input type="text" class="form-control" id="ethnicity" placeholder="Ethnicity" value="<?php echo $queryRes['st_ethnicity']; ?>">
                                                                        <div class="error-feedback">Please enter the Ethnicity</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 refered_field" style="display:<?php echo $queryRes['st_refered']==1 ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="referer_name">Please specify his / her name</label>
                                                                        <input type="text" class="form-control" id="referer_name" value="<?php echo $queryRes['st_refer_name']; ?>" placeholder="name1,name2,name3">
                                                                        <div class="alert alert-primary d-flex align-items-center mt-2" role="alert">
                                                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                                                                        <div>Multiple Names can be written with a Comma(,) in Between</div>
                                                                        </div>
                                                                        <div class="error-feedback">Please Enter his / her name</div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 refered_field" style="display:<?php echo $queryRes['st_refered']==1 ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="refer_alumni">Is he / she an alumni</label>
                                                                        <select name="refer_alumni" class="form-select" id="refer_alumni">
                                                                        <?php  
                                                                        $st_refer_alumni=['--select--','Yes','No'];
                                                                        for($i=0;$i<count($st_refer_alumni);$i++){
                                                                            $checked= $i==$queryRes['st_refer_alumni'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_refer_alumni[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">Please select atleast one option</div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body" id="student_enquiry_form_parent">
                                            <div class="accordion" id="accordionCourseConsult">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingfour">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsefour" aria-expanded="true" aria-controls="collapsefour">
                                                    Course & Consultation Details
                                                    </button>
                                                </h2>
                                                <div id="collapsefour" class="accordion-collapse collapse show" aria-labelledby="headingfour" data-bs-parent="#accordionCourseConsult">
                                                    <div class="accordion-body">
                                                    <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="payment_fee">Fees mentioned</label>
                                                        <input type="text" class="form-control" maxlength="255" id="payment_fee" placeholder="0.00" value="<?php echo $queryRes['st_fee']; ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Mentioned Fee
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="appointment_booked">Appointment booked for counseling or not?</label>
                                                        <select name="appointment_booked" class="form-select" id="appointment_booked">
                                                        <?php  
                                                        $st_appoint_book=['--select--','Yes','No'];
                                                        $selectedAppoint=$queryRes['st_appoint_book']=='' ? 0 : $queryRes['st_appoint_book'];
                                                        for($i=0;$i<count($st_appoint_book);$i++){
                                                            $checked= $i==$queryRes['st_appoint_book'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_appoint_book[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>                                                          
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="pref_comment">Any preferences or requirements or expectations regarding this course</label>
                                                        <input type="text" class="form-control" id="pref_comment" placeholder="Requirements" value="<?php echo $queryRes['st_pref_comments']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6 d-none">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="remarks">Remarks</label>
                                                        <?php  
                                                        $st_remarks=['Seems to be interested to do course and need to contact asap','contacted and followed','Selected - Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose','Rejected - "Reasons mentioned in comments" or " ReCounseliing needed"'];
                                                        if(($queryRes['st_remarks'] ?? '')!=''){
                                                            $remarksSel=json_decode($queryRes['st_remarks']);
                                                        }else{
                                                            $remarksSel=[];   
                                                        }
                                                        for($i=1;$i<count($st_remarks);$i++){                                            
                                                            if(in_array($i,$remarksSel)){
                                                                $checked='checked';
                                                            }else{
                                                                $checked='';
                                                            }                                            
                                                            echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input" id="remark_check_"'.$i.' '.$checked.' value="'.$i.'">';
                                                            echo '<label for="remark_check_"'.$i.'>'.$st_remarks[$i].'</label></div>';
                                                        }
                                                        ?>
                                                        <div class="error-feedback">Please select atleast one option</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                                <!-- appointment form  -->

                                <div class="row" id="appointment_form" style="display:<?php echo $queryRes['st_appoint_book']==1 ? 'block' : 'none' ?>"> 
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Appointment Form</p></b>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_time">Appointment Time</label>
                                                        <input type="datetime-local" name="slot_book_time" class="form-control" id="slot_book_time" value="<?php echo $slot_book['slot_book_time']!='' ? date('Y-m-d H:i',strtotime($slot_book['slot_book_time'])) : '' ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_purpose">Purpose of Appointment</label>
                                                        <input type="text" name="slot_book_purpose" class="form-control" id="slot_book_purpose" placeholder="Purpose" value="<?php echo $slot_book['slot_book_purpose']; ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_date">Booked On</label>
                                                        <input type="date" name="slot_book_date" class="form-control" id="slot_book_date" value="<?php echo $slot_book['slot_book_date']!='' ? date('Y-m-d',strtotime($slot_book['slot_book_date'])) : '' ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_by">Booking Made By</label>
                                                        <input type="text" name="slot_book_by" class="form-control" id="slot_book_by" placeholder="Booked By" value="<?php echo $slot_book['slot_book_by']; ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_link">Sent the Email for the Link ?</label>
                                                        <select name="slot_book_link" class="form-control" id="slot_book_link">
                                                        <?php 
                                                            $slot_booking_link=['--select--','Yes','No'];
                                                            for($i=0;$i<count($slot_booking_link);$i++){
                                                                $selected=$i==$slot_book['slot_book_link'] ? 'selected' : '';
                                                                echo "<option value='".$i."' ".$selected.">".$slot_booking_link[$i]."</option>";
                                                            }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <?php if($eqId==0){ ?>
                                                        <button class="btn btn-primary" type="button" id="enquiry_form">Submit Enquiry</button>
                                                        <?php }else{ ?>
                                                        <button class="btn btn-primary" type="button" id="enquiry_form">Update Enquiry</button>
                                                        <?php } ?>
                                                        <input type="hidden" value="<?php echo $eqId; ?>" id="check_update">
                                                        <input type="hidden" id="current_enquiry_code" value="<?php echo isset($current_enquiry_code) ? htmlspecialchars($current_enquiry_code, ENT_QUOTES, 'UTF-8') : ''; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    </div></div></div>
        </div>
                            </form>

        <?php if (!$is_student_portal): ?>
        <!-- Accordion 2: Post Enquiry Follow Up (admin only) -->
        <div class="accordion" id="followupMainAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header position-relative<?php echo (isset($eqId) && (int)$eqId > 0 && $followup_history_enquiry_code !== '') ? ' has-followup-history-btn' : ''; ?>" id="headingFollowup">
                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowup" aria-expanded="false" aria-controls="collapseFollowup">Post Enquiry Follow Up</button>
                    <?php if (isset($eqId) && (int)$eqId > 0 && $followup_history_enquiry_code !== ''): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm position-absolute top-50 translate-middle-y btn-followup-history-accordion" id="followup_history_open_btn" data-enquiry-id="<?php echo htmlspecialchars($followup_history_enquiry_code, ENT_QUOTES, 'UTF-8'); ?>" data-followup-stage="enquiry">
                        <i class="ti ti-history me-1"></i> Follow-up history
                    </button>
                    <?php endif; ?>
                </h2>
                <div id="collapseFollowup" class="accordion-collapse collapse" aria-labelledby="headingFollowup" data-bs-parent="#enquiryAccordionGroup">
                    <div class="accordion-body">
                        <?php
                        $has_counselling_appointment = false;
                        if(!empty($queryRes['st_id'])){
                            $eid = mysqli_real_escape_string($connection, !empty($queryRes['st_enquiry_id']) ? $queryRes['st_enquiry_id'] : sprintf('EQ%05d', (int)$queryRes['st_id']));
                            $chk = @mysqli_fetch_row(mysqli_query($connection, "SELECT 1 FROM appointments WHERE connected_enquiry_id='$eid' AND delete_status!=1 LIMIT 1"));
                            $has_counselling_appointment = (bool)$chk;
                        }
                        include('includes/followup_accordion_form.php');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion 3: Counseling (admin only) -->
        <div class="accordion" id="counsellingMainAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingCounseling">
                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCounseling" aria-expanded="false" aria-controls="collapseCounseling">Counseling</button>
                </h2>
                <div id="collapseCounseling" class="accordion-collapse collapse" aria-labelledby="headingCounseling" data-bs-parent="#enquiryAccordionGroup">
                    <div class="accordion-body">
                        <?php include('includes/counselling_accordion_form.php'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accordion 4: Post Counselling Follow Up (admin only) -->
        <div class="accordion" id="followupPostCounsellingAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header position-relative<?php echo (isset($eqId) && (int)$eqId > 0 && $followup_history_enquiry_code !== '') ? ' has-followup-history-btn' : ''; ?>" id="headingFollowupPostCounselling">
                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFollowupPostCounselling" aria-expanded="false" aria-controls="collapseFollowupPostCounselling">Post Counselling Follow Up</button>
                    <?php if (isset($eqId) && (int)$eqId > 0 && $followup_history_enquiry_code !== ''): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm position-absolute top-50 translate-middle-y btn-followup-history-accordion" id="followup_pc_history_open_btn" data-enquiry-id="<?php echo htmlspecialchars($followup_history_enquiry_code, ENT_QUOTES, 'UTF-8'); ?>" data-followup-stage="post_counselling">
                        <i class="ti ti-history me-1"></i> Follow-up history
                    </button>
                    <?php endif; ?>
                </h2>
                <div id="collapseFollowupPostCounselling" class="accordion-collapse collapse" aria-labelledby="headingFollowupPostCounselling" data-bs-parent="#enquiryAccordionGroup">
                    <div class="accordion-body">
                        <?php
                        $_followup_query_saved = $followup_Query;
                        $followup_Query = $followup_pc_Query;
                        $followup_accordion_ctx = 'post_counselling';
                        include 'includes/followup_accordion_form.php';
                        $followup_Query = $_followup_query_saved;
                        unset($_followup_query_saved, $followup_accordion_ctx);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        </div>
                    </div> <!-- container-fluid -->
                </div>
            </div>
        </div>
        <!-- END main-wrapper -->
        <div class="rightbar-overlay"></div>

        <?php include('includes/followup_appointment_modal.inc.php'); ?>

        <?php if (!$is_student_portal): ?>
        <div class="modal fade" id="followupStatusEmailModal" tabindex="-1" aria-labelledby="followupStatusEmailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupStatusEmailModalLabel">Send status email to student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Template is loaded for the selected enquiry status. Edit if needed, then send.</p>
                        <div class="mb-2">
                            <label class="form-label" for="followup_email_subject">Subject</label>
                            <input type="text" class="form-control" id="followup_email_subject" placeholder="Email subject" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" for="followup_email_body">Message</label>
                            <textarea class="form-control followup-email-body-autoheight" id="followup_email_body" rows="6" placeholder="Email body" style="min-height:7.5rem;max-height:28rem;line-height:1.5;resize:vertical;box-sizing:border-box;"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="followup_save_template_default" value="1">
                            <label class="form-check-label" for="followup_save_template_default">Save as default template for this status</label>
                        </div>
                        <button type="button" class="btn btn-success" id="followup_send_status_email">Send email</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="counsellingStatusEmailModal" tabindex="-1" aria-labelledby="counsellingStatusEmailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="counsellingStatusEmailModalLabel">Send counselling email to student</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small">Template is loaded for the selected type. Edit if needed, then send.</p>
                        <div class="mb-2">
                            <label class="form-label" for="counselling_email_subject">Subject</label>
                            <input type="text" class="form-control" id="counselling_email_subject" placeholder="Email subject" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" for="counselling_email_body">Message</label>
                            <textarea class="form-control counselling-email-body-autoheight" id="counselling_email_body" rows="6" placeholder="Email body" style="min-height:7.5rem;max-height:28rem;line-height:1.5;resize:vertical;box-sizing:border-box;"></textarea>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="counselling_save_template_default" value="1">
                            <label class="form-check-label" for="counselling_save_template_default">Save as default template for this type</label>
                        </div>
                        <button type="button" class="btn btn-success" id="counselling_send_status_email">Send email</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="studentLoginLinkEmailModal" tabindex="-1" aria-labelledby="studentLoginLinkEmailModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentLoginLinkEmailModalLabel">Send student login link</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted small mb-2">Login page: <code class="user-select-all" id="student_login_link_url_display"></code></p>
                        <div class="mb-2">
                            <label class="form-label" for="login_link_email_subject">Subject</label>
                            <input type="text" class="form-control" id="login_link_email_subject" placeholder="Email subject" autocomplete="off">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" for="login_link_email_body">Message</label>
                            <textarea class="form-control" id="login_link_email_body" rows="8" placeholder="Email body" style="min-height:7.5rem;max-height:28rem;line-height:1.5;resize:vertical;box-sizing:border-box;"></textarea>
                        </div>
                        <button type="button" class="btn btn-success" id="login_link_send_email_btn">Send email</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!$is_student_portal && isset($eqId) && (int)$eqId > 0 && $followup_history_enquiry_code !== ''): ?>
        <div class="modal fade" id="followupHistoryModal" tabindex="-1" aria-labelledby="followupHistoryModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="followupHistoryModalLabel">Follow-up history</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="fh_pane_list">
                            <p class="text-muted small mb-2" id="followup_history_intro">Each row shows saved fields for <strong>this section only</strong> (Post Enquiry Follow Up or Post Counselling Follow Up — not mixed). Newest first. <strong>Follow-up outcome</strong>, <strong>Enquiry status</strong>, and <strong>Follow-up notes</strong> stay fixed on the left; scroll sideways for the rest. Use <strong>Resend email</strong> to send the status template for that row&rsquo;s enquiry status again.</p>
                            <div id="followup_history_loading" class="text-muted py-3 d-none">Loading…</div>
                            <div id="followup_history_empty" class="text-muted py-3 d-none">No follow-up records yet.</div>
                            <div id="followup_history_scrollwrap">
                                <table class="table table-sm table-striped table-bordered align-top mb-0" id="followup_history_table" style="min-width: 2180px;">
                                    <thead class="table-light"><tr>
                                        <th class="fh-pin fh-pin-1">Follow-up outcome</th>
                                        <th class="fh-pin fh-pin-2">Enquiry status</th>
                                        <th class="fh-pin fh-pin-3">Follow-up notes</th>
                                        <th>Student name</th>
                                        <th>Phone</th>
                                        <th>Follow-up date &amp; time</th>
                                        <th>Date (record)</th>
                                        <th>Responsible staff</th>
                                        <th>Follow-up type</th>
                                        <th>Mode of contact</th>
                                        <th>Next follow-up</th>
                                        <th style="min-width: 220px;">Remarks</th>
                                        <th style="min-width: 160px;">Comments</th>
                                        <th>Created by</th>
                                        <th>Last updated</th>
                                        <th style="min-width: 110px;">Resend email</th>
                                    </tr></thead>
                                    <tbody id="followup_history_tbody"></tbody>
                                </table>
                            </div>
                        </div>
                        <div id="fh_pane_resend" class="d-none">
                            <button type="button" class="btn btn-link btn-sm px-0 mb-3" id="fh_back_to_list">&larr; Back to history</button>
                            <h6 class="mb-3">Resend status email</h6>
                            <p class="small text-muted mb-2">Template is loaded for the enquiry status on this history row. Edit if needed, then send.</p>
                            <p class="small mb-2"><span class="fw-semibold">Enquiry status:</span> <span id="fh_resend_status_label"></span> <span class="text-muted">(#<span id="fh_resend_status_code"></span>)</span></p>
                            <input type="hidden" id="fh_resend_enquiry_id" value="">
                            <input type="hidden" id="fh_resend_status_code_val" value="">
                            <div class="mb-2">
                                <label class="form-label" for="fh_resend_subject">Subject</label>
                                <input type="text" class="form-control" id="fh_resend_subject" placeholder="Subject">
                            </div>
                            <div class="mb-2">
                                <label class="form-label" for="fh_resend_body">Message</label>
                                <textarea class="form-control" id="fh_resend_body" rows="10" placeholder="Email body"></textarea>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="fh_resend_save_default" value="1">
                                <label class="form-check-label" for="fh_resend_save_default">Save subject &amp; body as default template for this status</label>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" id="fh_resend_cancel_btn">Cancel</button>
                                <button type="button" class="btn btn-success" id="fh_resend_send_btn">Send email</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php include('includes/footer_includes.php'); ?>
        <script>
            window.STUDENT_ENQUIRY_AUTO_SAVE = <?php echo (isset($eqId) && (int)$eqId > 0) ? 'true' : 'false'; ?>;
            window.ENQUIRY_EDIT_PAGE = <?php echo (isset($eqId) && (int)$eqId > 0) ? 'true' : 'false'; ?>;
            window.ENQUIRY_LOCKED_START = <?php echo (!empty($enquiry_locked_start)) ? 'true' : 'false'; ?>;
            window.STUDENT_LOGIN_PAGE_URL = <?php echo json_encode(isset($student_login_page_url) ? $student_login_page_url : ''); ?>;
            function autosaveSetBadge(kind, label, state){
                var map = { enquiry:'#autosave_badge_enquiry', counsel:'#autosave_badge_counsel', followup:'#autosave_badge_followup' };
                var $b = $(map[kind]||'');
                if(!$b.length) return;
                $b.text(label);
                $b.removeClass('bg-secondary bg-success bg-danger bg-warning');
                if(state==='ok') $b.addClass('bg-success');
                else if(state==='err') $b.addClass('bg-danger');
                else if(state==='wait') $b.addClass('bg-warning');
                else $b.addClass('bg-secondary');
            }

            function enquiryEditingAllowed(){
                if(!window.ENQUIRY_EDIT_PAGE) return true;
                var $t = $('#enquiry_edit_mode_toggle');
                if(!$t.length) return true;
                return $t.is(':checked');
            }
            function applyEnquiryFormLock(locked){
                if(!window.ENQUIRY_EDIT_PAGE) return;
                if (document.body) {
                    document.body.classList.toggle('student-enquiry-controls-disabled', !!locked);
                }
                var $t = $('#enquiry_edit_mode_toggle');
                // Keep accordion section headers clickable in view-only so users can expand/collapse to read content.
                $('#student_enquiry_contact_bar').find(':input').not('#send_student_login_link_btn').prop('disabled', !!locked);
                $('#student_enquiry_form').find(':input').not($t).not('.accordion-button').prop('disabled', !!locked);
                $('#counselling_form').find(':input').not('.accordion-button').prop('disabled', !!locked);
                $('#followup_form_embed,#followup_pc_form_embed').find(':input').not('.accordion-button').prop('disabled', !!locked);
                $('#enquiry_form,#counseling_submit,#followup_check,#followup_pc_check,#followup_send_status_email,#counselling_send_status_email,#followup_open_calendar_btn,#followup_pc_open_calendar_btn,#contact_bar_open_calendar_btn,#counselling_open_calendar_btn').prop('disabled', !!locked);
                $('.btn-followup-history-accordion').prop('disabled', false);
                $('#fp_appointment_submit_btn').prop('disabled', !!locked);
                $('#enquiry_source_responsible_staff').prop('disabled', true);
                $('#counselling_member_name').prop('disabled', true);
                if($('#enquiry_edit_mode_hint').length){
                    $('#enquiry_edit_mode_hint').text(locked ? 'View only — turn on to edit.' : 'Turn off to view without editing.');
                }
                if (typeof window.__applyCounsellingOutcomePreselect === 'function') {
                    window.__applyCounsellingOutcomePreselect();
                }
            }
            $(function(){
                if(!window.ENQUIRY_EDIT_PAGE) return;
                var lockedStart = window.ENQUIRY_LOCKED_START === true;
                $('#enquiry_edit_mode_toggle').prop('checked', !lockedStart);
                applyEnquiryFormLock(lockedStart);
                $('#enquiry_edit_mode_toggle').on('change', function(){
                    applyEnquiryFormLock(!$(this).is(':checked'));
                });
            });

            $(document).on('click', '#send_student_login_link_btn', function () {
                var url = window.STUDENT_LOGIN_PAGE_URL || '';
                $('#student_login_link_url_display').text(url);
                var ref = ($('#current_enquiry_code').val() || '').trim();
                var name = ($('#member_name').val() || '').trim() || 'there';
                $('#login_link_email_subject').val('Student portal login' + (ref ? ' — ' + ref : ''));
                $('#login_link_email_body').val(
                    'Hi ' + name + ',\n\n' +
                    'You can sign in to the student portal here:\n' + url + '\n\n' +
                    'Use the email address we have on file for your account.\n\n' +
                    'Kind regards'
                );
                var m = document.getElementById('studentLoginLinkEmailModal');
                if (m && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(m).show();
                }
            });
            $(document).on('click', '#login_link_send_email_btn', function () {
                var enquiry_id = ($('#current_enquiry_code').val() || '').trim();
                var subject = ($('#login_link_email_subject').val() || '').trim();
                var body = ($('#login_link_email_body').val() || '');
                var override_to = ($('#email_address').val() || '').trim();
                if (!enquiry_id) {
                    $('.toast-text2').html('Missing enquiry reference.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                var $btn = $('#login_link_send_email_btn').prop('disabled', true).text('Sending...');
                $.post('includes/datacontrol', {
                    send_enquiry_status_email: 1,
                    enquiry_id: enquiry_id,
                    subject: subject,
                    body: body,
                    override_to: override_to
                }, function (data) {
                    $btn.prop('disabled', false).text('Send email');
                    if (String(data).trim() === '1') {
                        $('.toast-text2').html('Email sent.');
                        $('#borderedToast2Btn').trigger('click');
                        var modalEl = document.getElementById('studentLoginLinkEmailModal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            var ci = bootstrap.Modal.getInstance(modalEl);
                            if (ci) { ci.hide(); }
                        }
                    } else {
                        $('.toast-text2').html(data && String(data).trim() ? data : 'Failed to send email');
                        $('#borderedToast2Btn').trigger('click');
                    }
                }).fail(function () {
                    $btn.prop('disabled', false).text('Send email');
                    $('.toast-text2').html('Failed to send email');
                    $('#borderedToast2Btn').trigger('click');
                });
            });

            var checkPhone=0;
            function PhoneCheck(number){

                return new Promise(function (resolve, reject) {

                    var check_update=$('#check_update').val();
                    var oldenquiryFor='<?php echo $queryRes['st_enquiry_for']; ?>';
                    var oldNumber='<?php echo $queryRes['st_phno']; ?>';

                    var memberName=$('#member_name').val();     
                    var enquiryFor=$('#enquiry_for').val();                

                $.ajax({
                    type:'post',
                    data:{number:number,formName:'phoneNumberCheck',oldNumber:oldNumber,memberName:memberName,enquiryFor:enquiryFor,check_update:check_update,oldenquiryFor:oldenquiryFor},
                    url:'includes/datacontrol',
                    success:function(datas){
                        resolve(datas);
                    },
                    error: function (xhr, status, error) {
                        reject(new Error(status + ': ' + error));
                    }

                })

            });

            }

                        // Usage with async/await
            async function getData(number) {
            try {
                const data = await PhoneCheck(number);
                return data;

                // You can perform further operations with 'data' here
            } catch (error) {
                console.error(error);
            }
            }

            $(document).ready(function(){            

                $('.refered').on("change",function(){
                    var value=$(this).val();
                    if( value==0 || value==2 ){
                        $('.refered_field').hide();
                    }else{
                        $('.refered_field').show();
                    }                 
                })
                $('.rpl_parent').on("change",function(){
                    var value=$(this).val();
                    if( value==0 || value==2 ){
                        $('.rpl_child').hide();
                    }else{
                        $('.rpl_child').show();
                    }                 
                })
                $('#visa_condition').on("change",function(){
                    var value=$(this).val();
                    if( value==10 ){
                        $('.visa_note').show();
                    }else{
                        $('.visa_note').hide();
                    }                 
                })
              /*  $('#hear_about').on("change",function(){
                    var value=$(this).val();                    
                    if( value.includes('9') ){
                        $('.hear_about_child').show();
                    }else{
                        $('.hear_about_child').hide();
                    }                 
                }) */

                $('#course_type').on("change",function(){
                    var value=$(this).val();
                    if( value==1 ){
                        $('#rpl_form').show();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').hide();
                    }else if(value==5 || value==4){       
                        $('#rpl_form').hide();
                        $('#short_grp_form').show(); 
                        $('#regular_grp_form').hide();
                    }else if(value==3){                        
                        $('#rpl_form').hide();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').show();
                    }else{
                        $('#rpl_form').hide();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').hide();
                    }
                })
                $('.rpl_prev_parent').on("change",function(){
                    var value=$(this).val();
                    if( value==2 || value==0){
                        $('.rpl_prev_child').hide();
                    }else{                                            
                        $('.rpl_prev_child').show();
                    }
                })
                $('#appointment_booked').on("change",function(){
                    var value=$(this).val();
                    if( value==1){
                        $('#appointment_form').show();
                    }else{
                        $('#appointment_form').hide();
                    }
                })

                $('#enquiry_for').on('change',function(){
                    var value=$(this).val();
                    if(value==1){
                        $('#student_name_wrap').hide();
                    }else if(value==2){
                        $('#student_name_wrap').show();
                    }
                });
                function toggleEnquirySourceStaffField(){
                    var v = parseInt($('#enquiry_source').val(), 10) || 0;
                    var showCreateStaff = <?php echo json_encode(!$is_student_portal && isset($eqId) && (int)$eqId === 0); ?>;
                    if (showCreateStaff || v === 2 || v === 4 || v === 5 || v === 6) {
                        $('#enquiry_source_staff_wrap').show();
                    } else {
                        $('#enquiry_source_staff_wrap').hide();
                        $('#enquiry_source_staff_error').hide();
                    }
                    $('#enquiry_source_phone_calendar_wrap').toggle(v === 2);
                }
                $('#enquiry_source').on('change', toggleEnquirySourceStaffField);
                $('#enquiry_source_responsible_staff').on('change', function(){
                    $('#enquiry_source_staff_error').hide();
                    $(this).removeClass('is-invalid');
                });
                toggleEnquirySourceStaffField();
            })

            function buildStudentEnquiryFormData(){
                var enquiryForVal = ($('#enquiry_for').val()||'0').toString();
                var studentName = enquiryForVal === '1' ? $('#member_name').val().trim() : $('#student_name').val().trim();
                var contactName=$('#contact_num').val().trim();
                var emailAddress=$('#email_address').val().trim();
                var enquiryDate=$('#enquiry_date').val();
                var surname=$('#surname').val();
                var suburb=$('#suburb').val();
                var stuState=$('#stu_state').val() == 0 ? '' : $('#stu_state').val();
                var postCode=$('#post_code').val();
                var visit_before=$('#visit_before').val()==0 ? '' :$('#visit_before').val();
                var hear_about='';
                var hearedby = ($('#enquiry_source_responsible_staff').length ? ($('#enquiry_source_responsible_staff').val() || '') : '').trim();
                var esSrc = parseInt($('#enquiry_source').val(), 10) || 0;
                var plan_to_start_date=$('#plan_to_start_date').val();
                var refer_select=$('#refer_select').val();
                var referer_name=$('#referer_name').val();
                var refer_alumni=$('#refer_alumni').val();
                var shore=$('#shore').val();
                var comments=$('#comments').length ? $('#comments').val() : '';
                var remarks=[];
                var appointment_booked=$('#appointment_booked').val();
                $('.remarks_check:checkbox:checked').each(function(){ remarks.push(this.value); });
                var streetDetails=$('#street_no').val();
                var ethnicity=$('#ethnicity').val();
                var prefComment=$('#pref_comment').val();
                var enquiryFor=$('#enquiry_for').val()==0 ? '' : $('#enquiry_for').val();
                var courseType=$('#course_type').val();
                var courses=[];
                $('.courses_check:checkbox:checked').each(function(){ courses.push(this.value); });
                var payment=$('#payment_fee').val().trim();
                var memberName=$('#member_name').val().trim();
                var visaStatus=$('#visa_condition').val();
                var visaNote=$('#visa_note').val();
                var visaCondition=$('.visa_status').val();
                var reg_grp_names=$('#reg_grp_names').val();
                if(visaStatus==7 && visaNote==''){ window.visaNoteStatus=1; }else{ window.visaNoteStatus=0; }
                if(courseType==1){ submitRpl(); }
                else if(courseType==5 || courseType==4){ submitShortGroup(); }
                else if(courseType==3){ $('#reg_grp_names').removeClass('invalid-div'); }
                if(appointment_booked==1){ submitSlot(); }
                courses=courses.filter(function(item){ return item !== '0'; });
                remarks=remarks.filter(function(item){ return item !== '0'; });
                var checkId=$("#check_update").val();
                return {formName:'student_enquiry',studentName:studentName,contactName:contactName,emailAddress:emailAddress,courses:courses,payment:payment,checkId:checkId,visaStatus:visaStatus,surname:surname,enquiryDate:enquiryDate,suburb:suburb,stuState:stuState,postCode:postCode,visit_before:visit_before,hear_about:hear_about,hearedby:hearedby,memberName:memberName,plan_to_start_date:plan_to_start_date,refer_select:refer_select,referer_name:referer_name,refer_alumni:refer_alumni,visaNote:visaNote,prefComment:prefComment,comments:comments,appointment_booked:appointment_booked,visaCondition:visaCondition,remarks:remarks,reg_grp_names:reg_grp_names,streetDetails:streetDetails,enquiryFor:enquiryFor,courseType:courseType,shore:shore,ethnicity:ethnicity,enquiry_source:$('#enquiry_source').val()||0,location:($('#location').val()||'').trim(),enquiry_college:($('#enquiry_college').length ? $('#enquiry_college').val() : 0)||0,contact_notes:($('#st_contact_notes').length ? ($('#st_contact_notes').val()||'') : '').trim(),rpl_arrays:JSON.stringify(rpl_array),short_grps:JSON.stringify(short_grp),slot_books:JSON.stringify(slot_book),admin_id:"<?php echo $_SESSION['user_id']; ?>",formId:<?php echo $form_id; ?>,rpl_status:'<?php echo $rpl_status; ?>',short_grp_status:'<?php echo $short_grp_status; ?>',reg_grp_status:'<?php echo $reg_grp_status; ?>',slot_book_status:'<?php echo $slot_book_status; ?>'};
            }

            var enquiryAutoSaveTimer = null;
            var enquirySaveSeq = 0;
            function studentEnquiryValidEmail(){
                var em = ($('#email_address').val()||'').trim();
                var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return em.length>0 && re.test(em);
            }
            /** Create-enquiry page: server returns 1|st_id when counselling/follow-up created or linked an enquiry — open that enquiry. */
            function studentEnquiryNavigateAfterSideSave(data){
                if (window.ENQUIRY_EDIT_PAGE) return false;
                var s = (data === null || data === undefined) ? '' : String(data).trim();
                var m = /^1\|(\d+)$/.exec(s);
                if (!m) return false;
                window.location.href = 'student_enquiry.php?eq=' + encodeURIComponent(btoa(m[1]));
                return true;
            }
            async function performStudentEnquirySave(silent){
                silent = !!silent;
                if(!enquiryEditingAllowed()) return;
                var checkId = $("#check_update").val();
                // Autosave only runs when editing an existing enquiry (checkId set). Manual submit must always reach the API for new enquiries (checkId 0).
                if(silent && (!checkId || checkId==='0')) return;
                if(!studentEnquiryValidEmail()){
                    if(!silent){
                        $('.toast-text2').html('Please enter a valid email address.');
                        $('#borderedToast2Btn').trigger('click');
                    } else {
                        autosaveSetBadge('enquiry','Enquiry: need valid email','err');
                    }
                    return;
                }
                var esChk = parseInt($('#enquiry_source').val(), 10) || 0;
                if ((esChk === 2 || esChk === 4 || esChk === 5 || esChk === 6) && !($('#enquiry_source_responsible_staff').val() || '').toString().trim()) {
                    $('#enquiry_source_staff_error').show();
                    $('#enquiry_source_responsible_staff').addClass('is-invalid');
                    if (!silent) {
                        $('.toast-text2').html('Please select responsible staff for Phone call, Email, WhatsApp, or Facebook / Instagram ads.');
                        $('#borderedToast2Btn').trigger('click');
                    } else {
                        autosaveSetBadge('enquiry','Enquiry: select responsible staff','err');
                    }
                    return;
                }
                $('#enquiry_source_staff_error').hide();
                $('#enquiry_source_responsible_staff').removeClass('is-invalid');
                var seq = ++enquirySaveSeq;
                if(!silent){
                    var enquiryIdRec = await getData($('#contact_num').val().trim());
                    if(enquiryIdRec && String(enquiryIdRec).split('||')[0]==='1'){
                        $('#contact_num').closest('div').find('.phone_error').show();
                        $('#contact_num').closest('div').find('#phone_err_id').html(String(enquiryIdRec).split('||')[1]||'');
                    } else {
                        $('#contact_num').closest('div').find('.phone_error').hide();
                    }
                }
                var details = buildStudentEnquiryFormData();
                if(!details) return;
                if(silent){ autosaveSetBadge('enquiry','Enquiry: saving…','wait'); }
                else {
                    $('#loader-container').css('display','flex');
                    $('#student_enquiry_form').css('opacity','0.1');
                }
                $.ajax({
                    type:'post',
                    url:'includes/datacontrol',
                    data:details,
                    success:function(data){
                        if(silent){
                            if(seq !== enquirySaveSeq) return;
                            if(data==0 || data==='0'){
                                autosaveSetBadge('enquiry','Enquiry: failed','err');
                            }else if(data==='enquiry_source_staff_required' || data=='enquiry_source_staff_required'){
                                autosaveSetBadge('enquiry','Enquiry: select responsible staff','err');
                                $('#enquiry_source_staff_error').show();
                                $('#enquiry_source_responsible_staff').addClass('is-invalid');
                            }else if(data==='email_duplicate' || data=='email_duplicate' || data==='email_duplicate_student_users' || data=='email_duplicate_student_users'){
                                autosaveSetBadge('enquiry','Enquiry: email conflict','err');
                            }else if(data==2 || data=='2'){
                                autosaveSetBadge('enquiry','Enquiry: saved '+new Date().toLocaleTimeString(),'ok');
                            }else{
                                autosaveSetBadge('enquiry','Enquiry: saved '+new Date().toLocaleTimeString(),'ok');
                            }
                            return;
                        }
                        if(data==0 || data==='0'){
                            $('.toast-text2').html('Cannot add record. Please try again later');
                            $('#borderedToast2Btn').trigger('click');
                        }else if(data==='invalid_email' || data=='invalid_email'){
                            $('.toast-text2').html('Please enter a valid email address.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                        }else if(data==='enquiry_source_staff_required' || data=='enquiry_source_staff_required'){
                            $('.toast-text2').html('Please select responsible staff for Phone call, Email, WhatsApp, or Facebook / Instagram ads.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#enquiry_source_staff_error').show();
                            $('#enquiry_source_responsible_staff').addClass('is-invalid');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                        }else if(data==='email_duplicate' || data=='email_duplicate'){
                            $('.toast-text2').html('That email is already used by another enquiry.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                        }else if(data==='email_duplicate_student_users' || data=='email_duplicate_student_users'){
                            $('.toast-text2').html('That email is already registered to another student account.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                        }else if(data==2){
                            document.getElementById('student_enquiry_form').reset();
                            $('#toast-text').html('Record Updated Successfully');
                            $('#borderedToast1Btn').trigger('click');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                            setTimeout(function(){ location.reload(); }, 500);
                        }else{
                            document.getElementById('student_enquiry_form').reset();
                            $('#toast-text').html('Enquiry saved successfully');
                            $('#borderedToast1Btn').trigger('click');
                            $('#myModalLabel').html('Enquiry ID Created:');
                            $('.modal-body').html(data);
                            $('#model_trigger').trigger('click');
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                            setTimeout(function(){ location.reload(); }, 500);
                        }
                    },
                    error:function(){
                        if(silent && seq === enquirySaveSeq){ autosaveSetBadge('enquiry','Enquiry: error','err'); }
                        else if(!silent){ $('#loader-container').hide(); $('#student_enquiry_form').css('opacity',''); }
                    }
                });
            }

            $(document).on('click','#enquiry_form',async function(){ await performStudentEnquirySave(false); });

            $(document).on('input change','#student_enquiry_form :input, #student_enquiry_contact_bar :input', function(e){
                if(!window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                if(!enquiryEditingAllowed()) return;
                if($(e.target).attr('id')==='enquiry_form') return;
                clearTimeout(enquiryAutoSaveTimer);
                enquiryAutoSaveTimer = setTimeout(function(){ performStudentEnquirySave(true); }, 1000);
            })


            function genQR(){                

                // $.ajax({
                //     url:'includes/datacontrol',
                //     data:{admin_id:"<?php echo $_SESSION['user_id']; ?>",formName:'create_qr'},
                //     type:'post',
                //     success:function(data){
                        
                        var qrcodeContainer = document.getElementById('qrcode');
                        var updatedURL = removeLastSegmentFromURL(window.location.href)+'/common_enquiry.php';
                        var qrcode = new QRCode(qrcodeContainer, {
                        text: updatedURL,
                        width: 128,
                        height: 128,
                        });

                        var downloadLink = document.getElementById('downloadLink');

                        var qrCodeDataURL = qrcodeContainer.querySelector('canvas').toDataURL('image/png');
                        
                        downloadLink.href = qrCodeDataURL;
                        downloadLink.click();
                    
                    // }
                // })

            }

            var rpl_array=<?php echo $rpl_arrays; ?>;
            var short_grp=<?php echo $short_grps ?>;
            var slot_book=<?php echo $slot_books ?>;
            function removeLastSegmentFromURL(url) {
            // Split the URL by "/"
            var segments = url.split("/");

            // Remove the last segment
            segments.pop();

            // Join the segments back together
            var updatedURL = segments.join("/");

            return updatedURL;
            }

            function submitRpl(){
               var rpl_exp=$('#rpl_exp').val()==0 ? '' : $('#rpl_exp').val();
               var exp_in=$('#exp_in').val()==0 ? '' : $('#exp_in').val();
               var exp_docs=$('#exp_docs').val()==0 ? '' : $('#exp_docs').val();
               var exp_prev=$('#exp_prev').val()==0 ? '' : $('#exp_prev').val();
               var exp_name=$('#exp_name').val();
               var exp_years=$('#exp_years').val();
               var exp_prev_name=$('#exp_prev_name').val();
               rpl_array={"rpl_exp":rpl_exp,"exp_in":exp_in,"exp_docs":exp_docs,"exp_prev":exp_prev,"exp_name":exp_name,"exp_years":exp_years,"exp_prev_name":exp_prev_name};
               return true;
            }

            function submitShortGroup(){
                var short_grp_org_name=$('#short_grp_org_name').val();
                var short_grp_date=$('#short_grp_date').val();
                var short_grp_num_std=$('#short_grp_num_std').val();
                var short_grp_ind_exp=$('#short_grp_ind_exp').val()==0 ? '' : $('#short_grp_ind_exp').val();
                var short_grp_con_type=$('#short_grp_con_type').val();
                var short_grp_con_num=$('#short_grp_con_num').val();
                var short_grp_con_name=$('#short_grp_con_name').val();
                var short_grp_con_email=$('#short_grp_con_email').val();
                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var short_grp_org_type=$('#short_grp_org_type').val()==0 ? '' : $('#short_grp_org_type').val();
                var short_grp_campus=$('#short_grp_campus').val()==0 ? '' : $('#short_grp_campus').val();
                var short_grp_before=$('#short_grp_before').val()==0 ? '' : $('#short_grp_before').val();

                // if(short_grp_org_name=='' || short_grp_date=='' || short_grp_num_std=='' || short_grp_ind_exp=='' || short_grp_con_type=='' || short_grp_con_num=='' || short_grp_con_name=='' || (short_grp_con_email!='' && !short_grp_con_email.match(emailregexp)==true ) || short_grp_org_type=='' || short_grp_campus=='' || short_grp_before==''){
                    

                //     if(short_grp_org_name==''){
                //         $('#short_grp_org_name').addClass('invalid-div');
                //         $('#short_grp_org_name').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_org_name').addClass('valid-div');
                //         $('#short_grp_org_name').removeClass('invalid-div');
                //     }
                //     if(short_grp_date==''){
                //         $('#short_grp_date').addClass('invalid-div');
                //         $('#short_grp_date').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_date').addClass('valid-div');
                //         $('#short_grp_date').removeClass('invalid-div');
                //     }
                //     if(short_grp_num_std==''){
                //         $('#short_grp_num_std').addClass('invalid-div');
                //         $('#short_grp_num_std').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_num_std').addClass('valid-div');
                //         $('#short_grp_num_std').removeClass('invalid-div');
                //     }
                //     if(short_grp_ind_exp==''){
                //         $('#short_grp_ind_exp').addClass('invalid-div');
                //         $('#short_grp_ind_exp').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_ind_exp').addClass('valid-div');
                //         $('#short_grp_ind_exp').removeClass('invalid-div');
                //     }
                //     if(short_grp_con_type==''){
                //         $('#short_grp_con_type').addClass('invalid-div');
                //         $('#short_grp_con_type').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_con_type').addClass('valid-div');
                //         $('#short_grp_con_type').removeClass('invalid-div');
                //     }
                //     if(short_grp_con_num==''){
                //         $('#short_grp_con_num').addClass('invalid-div');
                //         $('#short_grp_con_num').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_con_num').addClass('valid-div');
                //         $('#short_grp_con_num').removeClass('invalid-div');
                //     }
                //     if(short_grp_con_name==''){
                //         $('#short_grp_con_name').addClass('invalid-div');
                //         $('#short_grp_con_name').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_con_name').addClass('valid-div');
                //         $('#short_grp_con_name').removeClass('invalid-div');
                //     }
                //     if((short_grp_con_email!='' && !short_grp_con_email.match(emailregexp)==true )){
                //         $('#short_grp_con_email').addClass('invalid-div');
                //         $('#short_grp_con_email').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_con_email').addClass('valid-div');
                //         $('#short_grp_con_email').removeClass('invalid-div');
                //     }
                //     if(short_grp_before==''){
                //         $('#short_grp_before').addClass('invalid-div');
                //         $('#short_grp_before').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_before').addClass('valid-div');
                //         $('#short_grp_before').removeClass('invalid-div');
                //     }
                //     if(short_grp_campus==''){
                //         $('#short_grp_campus').addClass('invalid-div');
                //         $('#short_grp_campus').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_campus').addClass('valid-div');
                //         $('#short_grp_campus').removeClass('invalid-div');
                //     }
                //     if(short_grp_org_type==''){
                //         $('#short_grp_org_type').addClass('invalid-div');
                //         $('#short_grp_org_type').removeClass('valid-div');
                //     }else{
                //         $('#short_grp_org_type').addClass('valid-div');
                //         $('#short_grp_org_type').removeClass('invalid-div');
                //     }
                //     return false;


                // }else{

                short_grp={"short_grp_org_name":short_grp_org_name,"short_grp_org_type":short_grp_org_type,"short_grp_campus":short_grp_campus,"short_grp_date":short_grp_date,"short_grp_num_std":short_grp_num_std,"short_grp_ind_exp":short_grp_ind_exp,"short_grp_con_type":short_grp_con_type,"short_grp_con_num":short_grp_con_num,"short_grp_con_name":short_grp_con_name, "short_grp_con_email":short_grp_con_email,"short_grp_before":short_grp_before};
                return true;
            }

            function submitSlot(){
                var slot_book_time=$('#slot_book_time').val(); 
                var slot_book_purpose=$('#slot_book_purpose').val(); 
                var slot_book_date=$('#slot_book_date').val(); 
                var slot_book_by=$('#slot_book_by').val(); 
                var slot_book_link=$('#slot_book_link').val()==0 ? '' : $('#slot_book_link').val(); 
                slot_book={"slot_book_time":slot_book_time,"slot_book_purpose":slot_book_purpose,"slot_book_date":slot_book_date,"slot_book_by":slot_book_by,"slot_book_link":slot_book_link};
                return true;
            }

            $(document).on('change','#counselling_form .mig_test',function(){
                var mig_test=$('#counselling_form .mig_test:checked').val();
                $('#counselling_form .mig_test_child').toggle(mig_test==1);
            });
            $(document).on('change','#counselling_form .aus_study',function(){
                var aus_study=$('#counselling_form .aus_study:checked').val();
                $('#counselling_form .aus_study_child').toggle(aus_study==1);
            });
            function buildAutoEnquiryContactPayload(){
                var enquiryForVal = ($('#enquiry_for').val()||'1').toString();
                var studentName = enquiryForVal === '1' ? ($('#member_name').val()||'').trim() : ($('#student_name').length ? ($('#student_name').val()||'').trim() : '');
                var memberName = ($('#member_name').val()||'').trim();
                return {
                    emailAddress: ($('#email_address').val()||'').trim(),
                    enquiryFor: enquiryForVal === '0' ? '1' : enquiryForVal,
                    studentName: studentName,
                    memberName: memberName,
                    contactName: ($('#contact_num').val()||'').trim(),
                    surname: ($('#surname').val()||'').trim()
                };
            }
            function buildCounselingFormData(){
                var $f=$('#counselling_form');
                var enquiry_id=($('#counselling_enquiry_id').length ? ($('#counselling_enquiry_id').val() || '') : '').toString().trim();
                if (enquiry_id === '0') enquiry_id = '';
                var counselling_date=$('#counselling_date').val().trim();
                var start_time=$('#counseling_timing').val().trim();
                var end_time=$('#counseling_end_timing').val().trim();
                var counseling_timing=(counselling_date && start_time) ? (counselling_date+' '+start_time) : '';
                var counseling_end_timing=(counselling_date && end_time) ? (counselling_date+' '+end_time) : (counselling_date && start_time ? counselling_date+' '+start_time : '');
                var counseling_type=$f.find('.counseling_type:checked').val() || '1';
                var member_name=$('#counselling_member_name').val();
                if(member_name) member_name=member_name.trim();
                var aus_duration=$('#aus_duration').val().trim();
                var work_status=$f.find('.work_status:checked').val() || '1';
                var visa_condition=$('#counselling_visa_condition').val();
                if(visa_condition===undefined || visa_condition===null) visa_condition='';
                if(visa_condition=='0')visa_condition='';
                var education=$('#counselling_education').val();
                var aus_study=$f.find('.aus_study:checked').val() || '1';
                var qualification=$('#counselling_qualification').val();
                var eng_rate=$('#counselling_eng_rate').val();
                var mig_test=$f.find('.mig_test:checked').val() || '1';
                var vaccine_status=$f.find('.vaccine_status:checked').val() || '1';
                var remarks=[];$f.find('.counselling_remarks:checked').each(function(){remarks.push(this.value);});
                var checkId=$('#counselling_check_update').val();
                var counsellingOutcomeActual = ($('#counselling_outcome_actual').val() || ($('#counselling_outcome').val() || '')).toString();
                return $.extend({formName:'counseling_form',vaccine_status:vaccine_status,job_nature:$('#counselling_job_nature').val(),module_result:$('#counselling_module_result').val(),eng_rate:eng_rate,mig_test:mig_test,overall_result:$('#counselling_overall_result').val(),course:$('#counselling_course').val(),university_name:$('#counselling_university_name').val(),qualification:qualification,counseling_timing:counseling_timing,counseling_end_timing:counseling_end_timing,enquiry_id:enquiry_id,counseling_type:counseling_type,member_name:member_name,preferred_intake_date:$('#counselling_preferred_intake_date').val(),mode_of_study:$('#counselling_mode_of_study').val(),aus_duration:aus_duration,work_status:work_status,visa_condition:visa_condition,education:education,remarks:remarks,aus_study:aus_study,counselling_notes:($('#counselling_notes').val()||'').trim(),counselling_outcome:counsellingOutcomeActual,checkId:checkId,admin_id:"<?php echo $_SESSION['user_id']; ?>"}, buildAutoEnquiryContactPayload());
            }
            var counselAutoSaveTimer=null;
            var counselSaveSeq=0;
            function performCounselingSave(silent){
                silent=!!silent;
                if(!enquiryEditingAllowed()) return;
                var checkId=$('#counselling_check_update').val();
                var hasExistingCounsel = !!checkId && checkId !== '0';
                var enquiryId = ($('#counselling_enquiry_id').val() || '').toString().trim();
                if (enquiryId === '0') enquiryId = '';
                // For new counselling records (checkId=0), allow manual submit but skip autosave.
                if(!hasExistingCounsel && silent) return;
                if(!hasExistingCounsel && !enquiryId){
                    if(!studentEnquiryValidEmail()){
                        if(!silent){
                            $('.toast-text2').html('Please enter a valid email address in Student contact above.');
                            $('#borderedToast2Btn').trigger('click');
                        }
                        return;
                    }
                }
                if(silent && !window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                if(!silent){
                    var $f=$('#counselling_form');
                    $f.find('.invalid-div').removeClass('invalid-div');
                    $f.find('.error-feedback').hide();
                }
                var details=buildCounselingFormData();
                var seq=++counselSaveSeq;
                if(silent){ autosaveSetBadge('counsel','Counseling: saving…','wait'); }
                $.ajax({type:'post',url:'includes/datacontrol',data:details,success:function(data){
                    if(silent){
                        if(seq!==counselSaveSeq) return;
                        if(data==1||data=='1'||/^1\|\d+$/.test(String(data).trim())){ autosaveSetBadge('counsel','Counseling: saved '+new Date().toLocaleTimeString(),'ok'); }
                        else { autosaveSetBadge('counsel','Counseling: failed','err'); }
                        return;
                    }
                    if(data==1||data=='1'||/^1\|\d+$/.test(String(data).trim())){
                        if(studentEnquiryNavigateAfterSideSave(data)) return;
                        $('#toast-text').html('Record Added Successfully');$('#borderedToast1Btn').trigger('click');setTimeout(function(){location.reload();},400);
                    }
                    else if(data==='invalid_email' || data=='invalid_email'){$('.toast-text2').html('Please enter a valid email address in Student contact above.');$('#borderedToast2Btn').trigger('click');}
                    else{$('.toast-text2').html('Cannot add record. Please try again later');$('#borderedToast2Btn').trigger('click');}
                },error:function(){
                    if(silent && seq===counselSaveSeq){ autosaveSetBadge('counsel','Counseling: error','err'); }
                }});
            }
            $(document).on('click','#counseling_submit',function(){ performCounselingSave(false); });
            $(document).on('input change','#counselling_form :input',function(e){
                if(!window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                if(!enquiryEditingAllowed()) return;
                var id=$(e.target).attr('id');
                if(id==='counseling_submit') return;
                clearTimeout(counselAutoSaveTimer);
                counselAutoSaveTimer=setTimeout(function(){ performCounselingSave(true); },1000);
            });
            // live clear validation when user fixes counselling fields
            $(document).on('input change','#counselling_form input, #counselling_form select',function(){
                var $field=$(this);
                if($field.hasClass('invalid-div') && $field.val().toString().trim()!==''){
                    $field.removeClass('invalid-div');
                    $field.closest('.mb-3').find('.error-feedback').hide();
                }
            });
            function followupAutoResizeEmailBody(){
                var el = document.getElementById('followup_email_body');
                if(!el) return;
                var minH = 120;
                var maxH = 448;
                el.style.height = 'auto';
                el.style.overflowY = 'hidden';
                var sh = el.scrollHeight;
                var h = Math.min(maxH, Math.max(minH, sh));
                el.style.height = h + 'px';
                el.style.overflowY = sh > maxH ? 'auto' : 'hidden';
            }
            function loadFollowupTemplateForCurrentStatus(opts){
                opts = opts || {};
                var showModal = !!opts.showModal;
                var prefix = (opts.prefix && typeof opts.prefix === 'string') ? opts.prefix : 'followup_';
                var status = $('#' + prefix + 'enquiry_flow_status').val();
                if (status === '' || status === null || status === undefined) { return; }
                var enquiry_id = ($('#' + prefix + 'enquiry_id').val() || '').toString().trim();
                if (prefix === 'followup_pc_') {
                    $('#followupStatusEmailModal').data('emailStatusSource', 'pc');
                } else {
                    $('#followupStatusEmailModal').data('emailStatusSource', 'enquiry');
                }
                $.post('includes/datacontrol', { get_enquiry_status_template: 1, status_code: status, enquiry_id: enquiry_id }, function(data){
                    try{
                        var j = JSON.parse(data);
                        $('#followup_email_subject').val(j.subject||'');
                        $('#followup_email_body').val(j.body||'');
                    }catch(e){
                        // ignore parse errors, leave fields as-is
                    }
                    setTimeout(followupAutoResizeEmailBody, 0);
                }).always(function(){
                    if (showModal) {
                        var m = document.getElementById('followupStatusEmailModal');
                        if (m && typeof bootstrap !== 'undefined') { bootstrap.Modal.getOrCreateInstance(m).show(); }
                    }
                });
            }
            $(document).on('input','#followup_email_body', followupAutoResizeEmailBody);
            $('#collapseFollowup').on('shown.bs.collapse', function(){
                setTimeout(followupAutoResizeEmailBody, 50);
            });
            $('#collapseFollowupPostCounselling').on('shown.bs.collapse', function(){
                setTimeout(followupAutoResizeEmailBody, 50);
            });
            $(document).on('change','#followup_enquiry_flow_status',function(){
                $('#followup_enquiry_status_actual').val(($(this).val() || '').toString());
                loadFollowupTemplateForCurrentStatus({ showModal: true, prefix: 'followup_' });
            });
            $(document).on('change','#followup_pc_enquiry_flow_status',function(){
                $('#followup_pc_enquiry_status_actual').val(($(this).val() || '').toString());
                loadFollowupTemplateForCurrentStatus({ showModal: true, prefix: 'followup_pc_' });
            });
            function followupOutcomeToStatusMap(){
                return { 'No Answer':'3', 'Call Back Later':'3', 'Booked Counselling':'2', 'Requested More Information':'2', 'Application Started':'4', 'Enrolled':'6', 'Not Interested':'7', 'Do not Call':'7', 'Wrong No':'7', 'Enrolled Elsewhere':'7', 'Course not Offered':'7', 'Funding Enquiry':'7' };
            }
            function toggleFollowupCalendarBtnForPrefix(prefix){
                var outcome = ($('#' + prefix + 'follow_up_outcome').val() || '').toString();
                var show = (outcome === 'No Answer' || outcome === 'Call Back Later' || outcome === 'Booked Counselling');
                $('#' + prefix + 'calendar_btn_wrap').toggle(!!show);
            }
            /** Mirrors server mapping in includes/enquiry_status_auto_map.php */
            function applyFollowupOutcomeToEnquiryStatusFromOutcome(prefix){
                prefix = prefix || 'followup_';
                var o = ($('#' + prefix + 'follow_up_outcome').val() || '').toString().trim();
                var map = followupOutcomeToStatusMap();
                if(Object.prototype.hasOwnProperty.call(map, o)){
                    $('#' + prefix + 'enquiry_flow_status').val(map[o]);
                    $('#' + prefix + 'enquiry_status_actual').val(map[o]);
                    loadFollowupTemplateForCurrentStatus({ showModal: false, prefix: prefix });
                }
            }
            $(document).on('change','#followup_follow_up_outcome',function(){
                applyFollowupOutcomeToEnquiryStatusFromOutcome('followup_');
                toggleFollowupCalendarBtnForPrefix('followup_');
            });
            $(document).on('change','#followup_pc_follow_up_outcome',function(){
                applyFollowupOutcomeToEnquiryStatusFromOutcome('followup_pc_');
                toggleFollowupCalendarBtnForPrefix('followup_pc_');
            });
            function fpPrepareAndOpenAppointmentModal(){
                var enquiryDateStr = ($('#enquiry_date').val() || '').toString().trim();
                var today = new Date();
                var todayStr = today.toISOString().slice(0,10);
                var minDate = todayStr;
                if(enquiryDateStr && enquiryDateStr > minDate){
                    minDate = enquiryDateStr;
                }
                $('#fp_appointment_date').attr('min', minDate);
                var currentApptDate = ($('#fp_appointment_date').val() || '').toString().trim();
                if(!currentApptDate || currentApptDate < minDate){
                    $('#fp_appointment_date').val(minDate);
                    currentApptDate = minDate;
                }
                var nowTimeStr = today.toTimeString().slice(0,5);
                if(currentApptDate === todayStr){
                    $('#fp_appointment_time,#fp_appointment_time_to').attr('min', nowTimeStr);
                } else {
                    $('#fp_appointment_time,#fp_appointment_time_to').removeAttr('min');
                }
                var enquiryForVal = ($('#enquiry_for').val()||'0').toString();
                var studentName = enquiryForVal === '1' ? ($('#member_name').val()||'').trim() : ($('#student_name').val()||'').trim();
                var studentPhone = ($('#contact_num').val()||'').trim();
                var studentEmail = ($('#email_address').val()||'').trim();
                $('#fp_student_name').val(studentName);
                $('#fp_student_phone').val(studentPhone);
                $('#fp_student_email').val(studentEmail);
                $('#fp_attendee_type_id').val('1');
                $('#fp_student_info_section').show();
                $('#fp_business_info_section').hide();
                var modal = new bootstrap.Modal(document.getElementById('followupAppointmentModal'));
                modal.show();
            }
            $(document).on('click','#followup_open_calendar_btn',function(){
                window.__fpBookFromContactBarPhone = false;
                window.__fpBookFromCounsellingReschedule = false;
                var enquiryId = ($('#followup_enquiry_id').val() || '').toString().trim();
                $('#fp_connected_enquiry_id').val(enquiryId);
                fpPrepareAndOpenAppointmentModal();
            });
            $(document).on('click','#followup_pc_open_calendar_btn',function(){
                window.__fpBookFromContactBarPhone = false;
                window.__fpBookFromCounsellingReschedule = false;
                var enquiryId = ($('#followup_pc_enquiry_id').val() || '').toString().trim();
                $('#fp_connected_enquiry_id').val(enquiryId);
                fpPrepareAndOpenAppointmentModal();
            });
            $(document).on('click','#contact_bar_open_calendar_btn',function(){
                window.__fpBookFromCounsellingReschedule = false;
                var src = parseInt($('#enquiry_source').val(), 10) || 0;
                if (src !== 2) { return; }
                if (typeof studentEnquiryValidEmail === 'function' && !studentEnquiryValidEmail()) {
                    $('.toast-text2').html('Please enter a valid email address before booking.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                if (!($('#enquiry_source_responsible_staff').val() || '').toString().trim()) {
                    $('#enquiry_source_staff_error').show();
                    $('#enquiry_source_responsible_staff').addClass('is-invalid');
                    $('.toast-text2').html('Please select responsible staff for Phone call.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                window.__fpBookFromContactBarPhone = true;
                var enquiryId = ($('#followup_enquiry_id').val() || '').toString().trim();
                $('#fp_connected_enquiry_id').val(enquiryId);
                fpPrepareAndOpenAppointmentModal();
            });
            function toggleCounsellingRescheduleCalendarWrap(){
                var v = ($('#counselling_outcome').val() || '').toString().trim();
                $('#counselling_reschedule_calendar_wrap').toggle(v === 'Rescheduled');
            }
            function counsellingSessionPayloadForTemplate(){
                return {
                    counselling_session_date: ($('#counselling_date').val() || '').toString().trim(),
                    counselling_session_start: ($('#counseling_timing').val() || '').toString().trim(),
                    counselling_session_end: ($('#counseling_end_timing').val() || '').toString().trim()
                };
            }
            function counsellingAutoResizeEmailBody(){
                var el = document.getElementById('counselling_email_body');
                if(!el) return;
                var minH = 120;
                var maxH = 448;
                el.style.height = 'auto';
                el.style.overflowY = 'hidden';
                var sh = el.scrollHeight;
                var h = Math.min(maxH, Math.max(minH, sh));
                el.style.height = h + 'px';
                el.style.overflowY = sh > maxH ? 'auto' : 'hidden';
            }
            function loadCounsellingTemplateForCurrentSelection(opts){
                opts = opts || {};
                var showModal = !!opts.showModal;
                var status = ($('#counselling_email_template_status').val() || '').toString().trim();
                if (!status) { return; }
                var enquiry_id = ($('#counselling_enquiry_id').val() || '').toString().trim();
                var post = $.extend({ get_enquiry_status_template: 1, status_code: status, enquiry_id: enquiry_id }, counsellingSessionPayloadForTemplate());
                $.post('includes/datacontrol', post, function(data){
                    try{
                        var j = (typeof data === 'object' && data !== null) ? data : JSON.parse(data);
                        $('#counselling_email_subject').val(j.subject||'');
                        $('#counselling_email_body').val(j.body||'');
                    }catch(e){}
                    setTimeout(counsellingAutoResizeEmailBody, 0);
                }).always(function(){
                    if (showModal) {
                        var m = document.getElementById('counsellingStatusEmailModal');
                        if (m && typeof bootstrap !== 'undefined') { bootstrap.Modal.getOrCreateInstance(m).show(); }
                    }
                });
            }
            function applyCounsellingOutcomeToEmailTemplate(){
                $('#counselling_email_template_status').val('');
                $('#counselling_email_subject,#counselling_email_body').val('');
                counsellingAutoResizeEmailBody();
                var cm = document.getElementById('counsellingStatusEmailModal');
                if(cm){ var ci = bootstrap.Modal.getInstance(cm); if(ci) ci.hide(); }
            }
            var counsellingTemplateReloadTimer = null;
            $(document).on('change input', '#counselling_date, #counseling_timing, #counseling_end_timing', function(){
                clearTimeout(counsellingTemplateReloadTimer);
                counsellingTemplateReloadTimer = setTimeout(function(){
                    if(($('#counselling_email_template_status').val() || '').toString().trim()){
                        loadCounsellingTemplateForCurrentSelection();
                    }
                }, 250);
            });
            $(document).on('change','#counselling_email_template_status', function(){
                var v = ($(this).val() || '').toString().trim();
                if(v){ loadCounsellingTemplateForCurrentSelection({ showModal: true }); }
                else {
                    $('#counselling_email_subject,#counselling_email_body').val('');
                    counsellingAutoResizeEmailBody();
                    var cm = document.getElementById('counsellingStatusEmailModal');
                    if(cm){ var ci = bootstrap.Modal.getInstance(cm); if(ci) ci.hide(); }
                }
            });
            $(document).on('input','#counselling_email_body', counsellingAutoResizeEmailBody);
            $(document).on('change','#counselling_outcome', function(){
                $('#counselling_outcome_actual').val(($('#counselling_outcome').val() || '').toString());
                toggleCounsellingRescheduleCalendarWrap();
                applyCounsellingOutcomeToEmailTemplate();
            });
            $(document).on('click','#counselling_open_calendar_btn',function(){
                window.__fpBookFromContactBarPhone = false;
                window.__fpBookFromCounsellingReschedule = true;
                if (typeof studentEnquiryValidEmail === 'function' && !studentEnquiryValidEmail()) {
                    $('.toast-text2').html('Please enter a valid email address before booking.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                var enquiryId = ($('#counselling_enquiry_id').val() || $('#followup_enquiry_id').val() || $('#followup_pc_enquiry_id').val() || '').toString().trim();
                $('#fp_connected_enquiry_id').val(enquiryId);
                fpPrepareAndOpenAppointmentModal();
            });
            // Follow-up appointment popup: attendee type toggle
            $(document).on('change','#fp_attendee_type_id',function(){
                var v = $(this).val();
                if(v=='1'){ $('#fp_student_info_section').show(); $('#fp_business_info_section').hide(); }
                else if(v=='2'){ $('#fp_student_info_section').hide(); $('#fp_business_info_section').show(); }
                else { $('#fp_student_info_section').hide(); $('#fp_business_info_section').hide(); }
                $('#fp_student_name,#fp_student_phone,#fp_student_email,#fp_business_name,#fp_business_contact').prop('required',false);
            });
            $(document).on('change','#fp_meeting_type',function(){
                var v = $(this).val();
                if(v=='Online'){ $('#fp_platform_section,#fp_meeting_link_section').show(); $('#fp_location_section').hide(); }
                else if(v=='Face to Face'){ $('#fp_location_section').show(); $('#fp_platform_section,#fp_meeting_link_section').hide(); }
                else { $('#fp_location_section,#fp_platform_section,#fp_meeting_link_section').hide(); }
                $('#fp_location_id,#fp_platform_id').prop('required',false);
            });
            // Follow-up appointment time helpers (match main appointment page behaviour)
            var FP_MIN_GAP_MINUTES = 1;
            function fpTimeToMinutes(hhmm){
                if(!hhmm || typeof hhmm!=='string') return null;
                var p = hhmm.trim().split(':'); if(p.length<2) return null;
                var h=parseInt(p[0],10), m=parseInt(p[1],10);
                if(isNaN(h)||isNaN(m)) return null;
                return h*60+m;
            }
            function fpMinutesToTime(m){
                m=Math.max(0,Math.min(m,24*60-1));
                var h=Math.floor(m/60), mn=m%60;
                return (h<10?'0':'')+h+':' + (mn<10?'0':'')+mn;
            }
            function fpAddMinutes(hhmm,mins){
                var mm=fpTimeToMinutes(hhmm);
                return mm==null?null:fpMinutesToTime(mm+mins);
            }
            function fpUpdateTimeSlotError(){
                var fromVal=($('#fp_appointment_time').val()||'').toString().trim();
                var toVal=($('#fp_appointment_time_to').val()||'').toString().trim();
                var fromM=fpTimeToMinutes(fromVal), toM=fpTimeToMinutes(toVal);
                var invalid=(fromVal && toVal && fromM!=null && toM!=null && fromM>=toM);
                var $err=$('#fp_time_slot_range_error');
                if(invalid){
                    $err.show().css('display','block');
                    $('#fp_appointment_time,#fp_appointment_time_to').addClass('invalid-div').removeClass('valid-div');
                }else{
                    $err.hide();
                    $('#fp_appointment_time,#fp_appointment_time_to').removeClass('invalid-div');
                }
            }
            function fpApplyAppointmentDateMin(){
                var today=new Date();
                var todayStr=today.toISOString().slice(0,10);
                var selectedDate=($('#fp_appointment_date').val()||'').toString().trim();
                var nowTimeStr=today.toTimeString().slice(0,5);
                if(selectedDate===todayStr){
                    $('#fp_appointment_time').attr('min',nowTimeStr);
                }else{
                    $('#fp_appointment_time').removeAttr('min');
                }
                fpUpdateToMin();
            }
            function fpUpdateToMin(){
                var fromVal=($('#fp_appointment_time').val()||'').toString().trim();
                if(fromVal){
                    var minTo=fpAddMinutes(fromVal,1);
                    if(minTo) $('#fp_appointment_time_to').attr('min',minTo);
                }else{
                    $('#fp_appointment_time_to').removeAttr('min');
                }
            }
            function fpEnsureFromBeforeTo(){
                var fromVal=($('#fp_appointment_time').val()||'').toString().trim();
                var toVal=($('#fp_appointment_time_to').val()||'').toString().trim();
                if(!fromVal && !toVal){ fpUpdateTimeSlotError(); return; }
                if(fromVal && !toVal){
                    var toNew=fpAddMinutes(fromVal,FP_MIN_GAP_MINUTES);
                    if(toNew) $('#fp_appointment_time_to').val(toNew);
                    fpUpdateTimeSlotError(); return;
                }
                if(toVal && !fromVal){
                    var fromNew=fpAddMinutes(toVal,-FP_MIN_GAP_MINUTES);
                    if(fromNew) $('#fp_appointment_time').val(fromNew);
                    fpUpdateTimeSlotError(); return;
                }
                var fromM=fpTimeToMinutes(fromVal), toM=fpTimeToMinutes(toVal);
                if(fromM==null||toM==null){ fpUpdateTimeSlotError(); return; }
                if(fromM>=toM){
                    var toNew=fpAddMinutes(fromVal,FP_MIN_GAP_MINUTES);
                    if(toNew) $('#fp_appointment_time_to').val(toNew);
                }
                fpUpdateTimeSlotError();
            }
            function fpOnFromTimeUpdate(){
                var val=($('#fp_appointment_time').val()||'').toString().trim();
                if(!val){ fpUpdateTimeSlotError(); return; }
                var toNew=fpAddMinutes(val,FP_MIN_GAP_MINUTES);
                if(toNew) $('#fp_appointment_time_to').val(toNew);
                fpEnsureFromBeforeTo();
                fpUpdateToMin();
                fpApplyAppointmentDateMin();
            }
            function fpOnToTimeUpdate(){
                var val=($('#fp_appointment_time_to').val()||'').toString().trim();
                var fromVal=($('#fp_appointment_time').val()||'').toString().trim();
                if(val && fromVal){
                    var fromM=fpTimeToMinutes(fromVal), toM=fpTimeToMinutes(val);
                    if(fromM!=null && toM!=null && toM<=fromM){
                        var toNew=fpAddMinutes(fromVal,FP_MIN_GAP_MINUTES);
                        if(toNew) $('#fp_appointment_time_to').val(toNew);
                    }
                }
                fpEnsureFromBeforeTo();
                fpUpdateToMin();
                fpApplyAppointmentDateMin();
            }
            // Wire up follow-up appointment time/date events
            $(document).on('change','#fp_appointment_date',fpApplyAppointmentDateMin);
            $(document).on('change input','#fp_appointment_time',fpOnFromTimeUpdate);
            $(document).on('change input','#fp_appointment_time_to',fpOnToTimeUpdate);
            // Initialise when modal opens (safe to call multiple times)
            fpEnsureFromBeforeTo();
            fpUpdateToMin();
            $('#fp_share_all').on('change',function(){ var c=$(this).is(':checked'); $('.fp-share-with-item').prop('checked',c); });
            $('.fp-share-with-item').on('change',function(){ if(!$(this).is(':checked')) $('#fp_share_all').prop('checked',false); });
            $(document).on('submit','#fp_appointment_form',function(e){
                e.preventDefault();
                var $f = $(this);
                $f.find('.error-feedback').hide();
                $f.find('[required]').prop('required',false);
                var date = $('#fp_appointment_date').val();
                var time = $('#fp_appointment_time').val();
                var state = $('#fp_timezone_state').val();
                if(date && time && state){
                    var stateDt = date + ' ' + time;
                    $('#fp_appointment_time_state').val(stateDt);
                    $('#fp_appointment_time_adelaide').val(stateDt);
                    $('#fp_appointment_time_india').val(stateDt);
                    $('#fp_appointment_time_philippines').val(stateDt);
                }
                var formData = new FormData(this);
                if (window.__fpBookFromContactBarPhone) {
                    formData.append('set_flow_status_booked_counselling', '1');
                    var fpEq = ($('#fp_connected_enquiry_id').val() || '').toString().trim();
                    if (!fpEq) {
                        formData.append('auto_create_enquiry_phone_flow', '1');
                    }
                    var ef = ($('#enquiry_for').val() || '1').toString();
                    formData.append('cb_email', ($('#email_address').val() || '').trim());
                    formData.append('cb_enquiry_for', ef);
                    formData.append('cb_student_name', ($('#student_name').val() || '').trim());
                    formData.append('cb_member_name', ($('#member_name').val() || '').trim());
                    formData.append('cb_contact_num', ($('#contact_num').val() || '').trim());
                    formData.append('cb_surname', ($('#surname').val() || '').trim());
                    formData.append('cb_responsible_staff', ($('#enquiry_source_responsible_staff').val() || '').trim());
                    formData.append('cb_location', ($('#location').val() || '').trim());
                }
                if (window.__fpBookFromCounsellingReschedule) {
                    formData.append('set_flow_status_counselling_pending', '1');
                    var fpEqCr = ($('#fp_connected_enquiry_id').val() || '').toString().trim();
                    if (!fpEqCr) {
                        formData.append('auto_create_enquiry_counselling_reschedule_flow', '1');
                    }
                    var ef2 = ($('#enquiry_for').val() || '1').toString();
                    formData.append('cb_email', ($('#email_address').val() || '').trim());
                    formData.append('cb_enquiry_for', ef2);
                    formData.append('cb_student_name', ($('#student_name').val() || '').trim());
                    formData.append('cb_member_name', ($('#member_name').val() || '').trim());
                    formData.append('cb_contact_num', ($('#contact_num').val() || '').trim());
                    formData.append('cb_surname', ($('#surname').val() || '').trim());
                }
                $('#fp_appointment_submit_btn').prop('disabled',true);
                $.ajax({ type:'POST', url:'includes/datacontrol', data:formData, contentType:false, processData:false,
                    success:function(res){
                        var r = (res||'').toString().trim();
                        var contactPhoneFlow = !!window.__fpBookFromContactBarPhone;
                        var counsReschedFlow = !!window.__fpBookFromCounsellingReschedule;
                        var mNav = /^1\|(\d+)$/.exec(r);
                        if ((contactPhoneFlow || counsReschedFlow) && mNav) {
                            $('#followupAppointmentModal').modal('hide');
                            var sid = mNav[1];
                            window.__fpBookFromContactBarPhone = false;
                            window.__fpBookFromCounsellingReschedule = false;
                            if (window.ENQUIRY_EDIT_PAGE) {
                                window.location.reload();
                            } else {
                                window.location.href = 'student_enquiry.php?eq=' + encodeURIComponent(btoa(sid));
                            }
                            return;
                        }
                        if (contactPhoneFlow && r === '1') {
                            $('#followupAppointmentModal').modal('hide');
                            $('#toast-text').html('Appointment saved. Enquiry updated to Booked Counselling.');
                            $('#borderedToast1Btn').trigger('click');
                            $f[0].reset();
                            $('#fp_appointment_submit_btn').prop('disabled', false);
                            window.__fpBookFromContactBarPhone = false;
                            setTimeout(function(){ window.location.reload(); }, 500);
                            return;
                        }
                        if (counsReschedFlow && r === '1') {
                            $('#followupAppointmentModal').modal('hide');
                            $('#toast-text').html('Appointment saved. Enquiry status set to Counselling Pending.');
                            $('#borderedToast1Btn').trigger('click');
                            $f[0].reset();
                            $('#fp_appointment_submit_btn').prop('disabled', false);
                            window.__fpBookFromCounsellingReschedule = false;
                            setTimeout(function(){ window.location.reload(); }, 500);
                            return;
                        }
                        if(r==='1'){
                            $('#followupAppointmentModal').modal('hide');
                            $('#toast-text').html('Appointment saved successfully!');
                            $('#borderedToast1Btn').trigger('click');
                            $f[0].reset();
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                            $('#followup_enquiry_flow_status option[value="9"], #followup_pc_enquiry_flow_status option[value="9"]').prop('disabled', false);
                        } else if(r==='2'){
                            $('.toast-text2').html('Time slot already booked for this person. Choose a different time.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                        } else if(r==='3'){
                            $('.toast-text2').html('This time falls in a blocked period. Choose a different time.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                        } else if(r==='invalid_email'){
                            $('.toast-text2').html('Please enter a valid email on the contact card before booking.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                        } else if(r==='contact_phone_staff_required'){
                            $('.toast-text2').html('Please select responsible staff for Phone call.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                        } else {
                            $('.toast-text2').html('Cannot save appointment. Please try again.');
                            $('#borderedToast2Btn').trigger('click');
                            $('#fp_appointment_submit_btn').prop('disabled',false);
                        }
                    },
                    error:function(){ $('.toast-text2').html('An error occurred.'); $('#borderedToast2Btn').trigger('click'); $('#fp_appointment_submit_btn').prop('disabled',false); }
                });
            });
            $('#followupAppointmentModal').on('shown.bs.modal',function(){ $('#fp_attendee_type_id').trigger('change'); $('#fp_meeting_type').trigger('change'); });
            $('#followupAppointmentModal').on('hidden.bs.modal', function(){
                window.__fpBookFromContactBarPhone = false;
                window.__fpBookFromCounsellingReschedule = false;
            });
            $(document).ready(function(){
                toggleFollowupCalendarBtnForPrefix('followup_');
                toggleFollowupCalendarBtnForPrefix('followup_pc_');
                toggleCounsellingRescheduleCalendarWrap();
            });
            $(document).on('click','#followup_send_status_email',function(){
                var src = ($('#followupStatusEmailModal').data('emailStatusSource') || 'enquiry').toString();
                var pfx = (src === 'pc') ? 'followup_pc_' : 'followup_';
                var enquiry_id=$('#' + pfx + 'enquiry_id').val();
                var status_code=($('#' + pfx + 'enquiry_flow_status').val() || '').toString().trim();
                if(status_code===''){ return; }
                var subject=$('#followup_email_subject').val().trim();
                var body=$('#followup_email_body').val().trim();
                var save_as_default = $('#followup_save_template_default').is(':checked') ? 1 : 0;
                var $btn=$('#followup_send_status_email').prop('disabled',true).text('Sending...');
                $.post('includes/datacontrol',{ send_enquiry_status_email: 1, enquiry_id: enquiry_id, status_code: status_code, subject: subject, body: body, save_as_default: save_as_default },function(data){
                    if(data=='1'){
                        $('#toast-text').html('Email sent successfully'); $('#borderedToast1Btn').trigger('click');
                        var fm = document.getElementById('followupStatusEmailModal');
                        if(fm){ var fi = bootstrap.Modal.getInstance(fm); if(fi) fi.hide(); }
                        $('#followup_send_status_email').prop('disabled',false).text('Send email');
                    } else {
                        $btn.prop('disabled',false).text('Send email');
                        $('.toast-text2').html(data||'Failed to send email'); $('#borderedToast2Btn').trigger('click');
                    }
                });
            });
            $(document).on('click','#counselling_send_status_email', function(){
                var enquiry_id = ($('#counselling_enquiry_id').val() || '').toString().trim();
                var status_code = ($('#counselling_email_template_status').val() || '').toString().trim();
                var subject = $('#counselling_email_subject').val().trim();
                var body = $('#counselling_email_body').val().trim();
                var save_as_default = $('#counselling_save_template_default').is(':checked') ? 1 : 0;
                if(!enquiry_id){ return; }
                if(!status_code){ return; }
                if(!subject || !body){
                    $('.toast-text2').html('Subject and message are required.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                var $btn = $('#counselling_send_status_email').prop('disabled', true).text('Sending...');
                var post = $.extend({
                    send_enquiry_status_email: 1,
                    enquiry_id: enquiry_id,
                    status_code: status_code,
                    subject: subject,
                    body: body,
                    save_as_default: save_as_default
                }, counsellingSessionPayloadForTemplate());
                $.post('includes/datacontrol', post, function(data){
                    if(data=='1'){
                        $('#toast-text').html('Email sent successfully');
                        $('#borderedToast1Btn').trigger('click');
                        var cm = document.getElementById('counsellingStatusEmailModal');
                        if(cm){ var ci = bootstrap.Modal.getInstance(cm); if(ci) ci.hide(); }
                        $('#counselling_send_status_email').prop('disabled',false).text('Send email');
                    } else {
                        $btn.prop('disabled', false).text('Send email');
                        $('.toast-text2').html(data||'Failed to send email');
                        $('#borderedToast2Btn').trigger('click');
                    }
                });
            });
            $('#enquiryAccordionGroup #collapseCounseling').on('shown.bs.collapse', function(){
                setTimeout(function(){
                    $('#counselling_outcome_actual').val(($('#counselling_outcome').val() || '').toString());
                    counsellingAutoResizeEmailBody();
                }, 50);
            });
            $('#followupStatusEmailModal').on('shown.bs.modal', function(){ setTimeout(followupAutoResizeEmailBody, 0); });
            $('#counsellingStatusEmailModal').on('shown.bs.modal', function(){ setTimeout(counsellingAutoResizeEmailBody, 0); });
            // Load default email template once when form is ready (for current status)
            loadFollowupTemplateForCurrentStatus({ showModal: false, prefix: 'followup_' });
            loadFollowupTemplateForCurrentStatus({ showModal: false, prefix: 'followup_pc_' });
            setTimeout(followupAutoResizeEmailBody, 100);
            setTimeout(function(){ $('#counselling_outcome_actual').val(($('#counselling_outcome').val() || '').toString()); counsellingAutoResizeEmailBody(); }, 350);

            function buildFollowupFormDataFromPrefix(prefix){
                var enquiryForVal = ($('#enquiry_for').val()||'0').toString();
                var student_name = enquiryForVal === '1' ? $('#member_name').val().trim() : $('#student_name').val().trim();
                var contact_num=$('#contact_num').val().trim();
                var contacted_person=$('#' + prefix + 'contacted_person').val().trim();
                var contacted_time=$('#' + prefix + 'contacted_time').val().trim();
                var date = ($('#followup_date').val() || '').trim();
                var contactMode = $('#followup_mode_contacted').val();
                var followupType=$('#' + prefix + 'followup_type').val();
                var enquiry_flow_status = ($('#' + prefix + 'enquiry_status_actual').val() || $('#' + prefix + 'enquiry_flow_status').val() || '').toString().trim();
                var follow_up_notes=$('#' + prefix + 'follow_up_notes').val().trim();
                var next_followup_date=$('#followup_next_followup_date').val();
                var follow_up_outcome=$('#' + prefix + 'follow_up_outcome').val();
                var enquiry_id=($('#' + prefix + 'enquiry_id').val() || '').toString().trim();
                if (enquiry_id === '0') enquiry_id = '';
                var formSel = (prefix === 'followup_pc_') ? '#followup_pc_form_embed' : '#followup_form_embed';
                var remarks=[];$(formSel + ' .followup_remarks:checked').each(function(){remarks.push(this.value);});
                var checkId=$('#' + prefix + 'check_update').val();
                if(!date) date = contacted_time ? contacted_time.slice(0,10) : '';
                var followup_stage = (prefix === 'followup_pc_') ? 'post_counselling' : 'enquiry';
                return $.extend({formName:'followup_call',followup_stage:followup_stage,student_name:student_name,date:date,contacted_person:contacted_person,contacted_time:contacted_time,contactMode:contactMode||followupType,followup_type:followupType,enquiry_flow_status:enquiry_flow_status,follow_up_notes:follow_up_notes,next_followup_date:next_followup_date,follow_up_outcome:follow_up_outcome,contact_num:contact_num,enquiry_id:enquiry_id,remarks:remarks,checkId:checkId,admin_id:"<?php echo $_SESSION['user_id']; ?>"}, buildAutoEnquiryContactPayload());
            }
            function buildFollowupFormData(){ return buildFollowupFormDataFromPrefix('followup_'); }
            function buildFollowupPcFormData(){ return buildFollowupFormDataFromPrefix('followup_pc_'); }
            var followupAutoSaveTimer=null;
            var followupSaveSeq=0;
            function performFollowupSave(silent, usePostCounselling){
                silent=!!silent;
                usePostCounselling = !!usePostCounselling;
                var prefix = usePostCounselling ? 'followup_pc_' : 'followup_';
                if(!enquiryEditingAllowed()) return;
                var checkId=$('#' + prefix + 'check_update').val();
                var hasExistingFollowup = !!checkId && checkId !== '0';
                var enquiryId = ($('#' + prefix + 'enquiry_id').val() || '').toString().trim();
                if (enquiryId === '0') enquiryId = '';
                if(!hasExistingFollowup && silent) return;
                if(!hasExistingFollowup && !enquiryId){
                    if(!studentEnquiryValidEmail()){
                        if(!silent){
                            $('.toast-text2').html('Please enter a valid email address in Student contact above.');
                            $('#borderedToast2Btn').trigger('click');
                        }
                        return;
                    }
                }
                if(silent && !window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                var details = usePostCounselling ? buildFollowupPcFormData() : buildFollowupFormData();
                var seq=++followupSaveSeq;
                if(silent){ autosaveSetBadge('followup','Follow-up: saving…','wait'); }
                $.ajax({type:'post',url:'includes/datacontrol',data:details,success:function(data){
                    if(silent){
                        if(seq!==followupSaveSeq) return;
                        if(data==1 || data=='1' || /^1\|\d+$/.test(String(data).trim())){ autosaveSetBadge('followup','Follow-up: saved '+new Date().toLocaleTimeString(),'ok'); }
                        else { autosaveSetBadge('followup','Follow-up: failed','err'); }
                        return;
                    }
                    if(data==1 || data=='1' || /^1\|\d+$/.test(String(data).trim())){
                        if(studentEnquiryNavigateAfterSideSave(data)) return;
                        $('#toast-text').html('Follow-up saved successfully');$('#borderedToast1Btn').trigger('click');setTimeout(function(){location.reload();},600);
                    }
                    else if(data==='invalid_email' || data=='invalid_email'){$('.toast-text2').html('Please enter a valid email address in Student contact above.');$('#borderedToast2Btn').trigger('click');}
                    else if(String(data).trim()==='followup_stage_mismatch'){$('.toast-text2').html('This follow-up belongs to a different section. Reload the page and use Post Enquiry vs Post Counselling forms only for their own records.');$('#borderedToast2Btn').trigger('click');}
                    else{$('.toast-text2').html(data && data.trim() ? data : 'Cannot save follow-up. Please try again.');$('#borderedToast2Btn').trigger('click');}
                },error:function(){
                    if(silent && seq===followupSaveSeq){ autosaveSetBadge('followup','Follow-up: error','err'); }
                }});
            }
            $(document).on('click','#followup_check',function(){ performFollowupSave(false, false); });
            $(document).on('click','#followup_pc_check',function(){ performFollowupSave(false, true); });
            $(document).on('input change','#followup_form_embed :input',function(e){
                if(!window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                if(!enquiryEditingAllowed()) return;
                var id=$(e.target).attr('id');
                if(id==='followup_check'||id==='followup_send_status_email') return;
                clearTimeout(followupAutoSaveTimer);
                followupAutoSaveTimer=setTimeout(function(){ performFollowupSave(true, false); },1000);
            });
            $(document).on('input change','#followup_pc_form_embed :input',function(e){
                if(!window.STUDENT_ENQUIRY_AUTO_SAVE) return;
                if(!enquiryEditingAllowed()) return;
                var id=$(e.target).attr('id');
                if(id==='followup_pc_check'||id==='followup_send_status_email') return;
                clearTimeout(followupAutoSaveTimer);
                followupAutoSaveTimer=setTimeout(function(){ performFollowupSave(true, true); },1000);
            });

            function followupHistoryStage(){
                return ($('#followupHistoryModal').data('fh-stage') || 'enquiry').toString().trim();
            }
            function fhShowFollowupHistoryList(){
                $('#fh_pane_resend').addClass('d-none');
                $('#fh_pane_list').removeClass('d-none');
                var st = followupHistoryStage();
                var title = (st === 'post_counselling') ? 'Post Counselling Follow Up — history' : 'Post Enquiry Follow Up — history';
                $('#followupHistoryModalLabel').text(title);
                var intro = $('#followup_history_intro');
                if (intro.length) {
                    intro.html((st === 'post_counselling')
                        ? 'Each row shows saved fields for <strong>Post Counselling Follow Up</strong> only (not mixed with Post Enquiry). Newest first. <strong>Follow-up outcome</strong>, <strong>Enquiry status</strong>, and <strong>Follow-up notes</strong> stay fixed on the left; scroll sideways for the rest. Use <strong>Resend email</strong> to send the status template for that row&rsquo;s enquiry status again.'
                        : 'Each row shows saved fields for <strong>Post Enquiry Follow Up</strong> only (not mixed with Post Counselling). Newest first. <strong>Follow-up outcome</strong>, <strong>Enquiry status</strong>, and <strong>Follow-up notes</strong> stay fixed on the left; scroll sideways for the rest. Use <strong>Resend email</strong> to send the status template for that row&rsquo;s enquiry status again.');
                }
            }
            function fhShowFollowupHistoryResend(){
                $('#fh_pane_list').addClass('d-none');
                $('#fh_pane_resend').removeClass('d-none');
                $('#followupHistoryModalLabel').text('Resend status email');
            }
            function followupHistoryEnquiryId(){
                return ($('#followupHistoryModal').data('fh-enquiry-id') || $('.btn-followup-history-accordion').first().data('enquiry-id') || '').toString().trim();
            }
            function loadFollowupHistoryTable(){
                var eid = followupHistoryEnquiryId();
                if (!eid) return;
                var st = followupHistoryStage();
                var emptyMsg = (st === 'post_counselling') ? 'No Post Counselling follow-up records yet.' : 'No Post Enquiry follow-up records yet.';
                $('#followup_history_loading').removeClass('d-none');
                $('#followup_history_empty').addClass('d-none').text(emptyMsg);
                $('#followup_history_tbody').empty();
                $.post('includes/datacontrol', { formName: 'fetch_followup_history', enquiry_id: eid, followup_stage: st }, function(raw){
                    $('#followup_history_loading').addClass('d-none');
                    var j;
                    try { j = (typeof raw === 'object' && raw !== null) ? raw : JSON.parse(raw); } catch (err) { j = { rows: [] }; }
                    var rows = (j && j.rows) ? j.rows : [];
                    var $tb = $('#followup_history_tbody');
                    if (!rows.length) {
                        $('#followup_history_empty').removeClass('d-none').text(emptyMsg);
                        return;
                    }
                    var cellPre = { whiteSpace: 'pre-wrap', wordBreak: 'break-word', verticalAlign: 'top' };
                    rows.forEach(function(r){
                        var sc = parseInt(r.flw_progress_state, 10);
                        if (isNaN(sc)) sc = 0;
                        var canResend = sc >= 1 && sc <= 10;
                        var $tr = $('<tr/>');
                        $tr.append($('<td/>').addClass('fh-pin fh-pin-1').text(r.flw_follow_up_outcome || ''));
                        $tr.append($('<td/>').addClass('fh-pin fh-pin-2').text(r.status_label || ''));
                        $tr.append($('<td/>').addClass('fh-pin fh-pin-3').css(cellPre).text(r.flw_follow_up_notes != null ? String(r.flw_follow_up_notes) : ''));
                        $tr.append($('<td/>').text(r.flw_name || ''));
                        $tr.append($('<td/>').css('font-weight', '600').text(r.flw_phone || ''));
                        $tr.append($('<td/>').text(r.contacted_time_fmt || ''));
                        $tr.append($('<td/>').text(r.flw_date_fmt || ''));
                        $tr.append($('<td/>').text(r.flw_contacted_person || ''));
                        $tr.append($('<td/>').text(r.flw_followup_type || ''));
                        $tr.append($('<td/>').text(r.flw_mode_contact || ''));
                        $tr.append($('<td/>').text(r.next_followup_fmt || ''));
                        $tr.append($('<td/>').css(cellPre).text(r.remarks_text != null ? String(r.remarks_text) : ''));
                        $tr.append($('<td/>').css(cellPre).text(r.flw_comments != null ? String(r.flw_comments) : ''));
                        $tr.append($('<td/>').text(r.created_by_name || ''));
                        $tr.append($('<td/>').text(r.last_updated_display != null ? String(r.last_updated_display) : ''));
                        var $tdAct = $('<td/>');
                        if (canResend) {
                            $('<button type="button" class="btn btn-sm btn-outline-primary fh-resend-btn"/>').text('Resend email')
                                .data('status-code', sc).data('status-label', r.status_label || ('Status ' + sc)).appendTo($tdAct);
                        } else {
                            $tdAct.append($('<span class="text-muted small"/>').text('—'));
                        }
                        $tr.append($tdAct);
                        $tb.append($tr);
                    });
                }).fail(function(){
                    $('#followup_history_loading').addClass('d-none');
                    $('#followup_history_empty').removeClass('d-none').text('Could not load history.');
                });
            }
            (function bindFollowupHistoryTableWheel(){
                var wrap = document.getElementById('followup_history_scrollwrap');
                if (!wrap || wrap.dataset.fhWheelBound === '1') return;
                wrap.dataset.fhWheelBound = '1';
                wrap.addEventListener('wheel', function(e){
                    if (this.scrollWidth <= this.clientWidth) return;
                    var tag = (e.target && e.target.tagName) ? String(e.target.tagName).toLowerCase() : '';
                    if (tag === 'input' || tag === 'textarea' || tag === 'select' || tag === 'button') return;
                    var d = e.deltaX + e.deltaY;
                    if (d === 0) return;
                    e.preventDefault();
                    this.scrollLeft += d;
                }, { passive: false });
            })();
            $(document).on('click', '.btn-followup-history-accordion', function(){
                var $b = $(this);
                $('#followupHistoryModal').data('fh-enquiry-id', $b.data('enquiry-id')).data('fh-stage', ($b.data('followup-stage') || 'enquiry').toString());
                fhShowFollowupHistoryList();
                var modal = new bootstrap.Modal(document.getElementById('followupHistoryModal'));
                modal.show();
                loadFollowupHistoryTable();
            });
            $('#followupHistoryModal').on('hidden.bs.modal', function(){
                fhShowFollowupHistoryList();
                $('#followup_history_tbody').empty();
                $('#followup_history_empty').addClass('d-none').text('No follow-up records yet.');
                $('#followupHistoryModal').removeData('fh-enquiry-id').removeData('fh-stage');
                $('#fh_resend_subject, #fh_resend_body').val('');
                $('#fh_resend_save_default').prop('checked', false);
            });
            $(document).on('click', '.fh-resend-btn', function(){
                var eid = followupHistoryEnquiryId();
                var sc = parseInt($(this).data('status-code'), 10);
                var label = ($(this).data('status-label') || '').toString();
                if (!eid || !sc) return;
                $('#fh_resend_enquiry_id').val(eid);
                $('#fh_resend_status_code_val').val(String(sc));
                $('#fh_resend_status_label').text(label || ('Status ' + sc));
                $('#fh_resend_status_code').text(String(sc));
                $('#fh_resend_subject, #fh_resend_body').val('');
                $('#fh_resend_save_default').prop('checked', false);
                $.post('includes/datacontrol', { get_enquiry_status_template: 1, status_code: sc, enquiry_id: eid }, function(data){
                    try {
                        var j = (typeof data === 'object' && data !== null) ? data : JSON.parse(data);
                        $('#fh_resend_subject').val(j.subject || '');
                        $('#fh_resend_body').val(j.body || '');
                    } catch (e2) {}
                    fhShowFollowupHistoryResend();
                });
            });
            $(document).on('click', '#fh_back_to_list, #fh_resend_cancel_btn', function(){
                fhShowFollowupHistoryList();
            });
            $(document).on('click', '#fh_resend_send_btn', function(){
                var eid = $('#fh_resend_enquiry_id').val();
                var sub = $('#fh_resend_subject').val().trim();
                var body = $('#fh_resend_body').val().trim();
                var sc = parseInt($('#fh_resend_status_code_val').val(), 10);
                if (!eid || !sub || !body) {
                    $('.toast-text2').html('Subject and message are required.');
                    $('#borderedToast2Btn').trigger('click');
                    return;
                }
                var $btn = $(this).prop('disabled', true).text('Sending…');
                var post = { send_enquiry_status_email: 1, enquiry_id: eid, subject: sub, body: body };
                if ($('#fh_resend_save_default').is(':checked') && ((sc >= 1 && sc <= 11) || (sc >= 12 && sc <= 14))) {
                    post.save_as_default = 1;
                    post.status_code = sc;
                }
                $.post('includes/datacontrol', post, function(res){
                    $btn.prop('disabled', false).text('Send email');
                    if (res == '1') {
                        $('#toast-text').html('Email sent successfully');
                        $('#borderedToast1Btn').trigger('click');
                        fhShowFollowupHistoryList();
                        loadFollowupHistoryTable();
                    } else {
                        $('.toast-text2').html(res || 'Failed to send email');
                        $('#borderedToast2Btn').trigger('click');
                    }
                }).fail(function(){
                    $btn.prop('disabled', false).text('Send email');
                    $('.toast-text2').html('Request failed.');
                    $('#borderedToast2Btn').trigger('click');
                });
            });

        </script>
    </body>
</html>
