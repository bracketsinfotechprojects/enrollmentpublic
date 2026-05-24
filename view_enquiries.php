<?php
include('includes/dbconnect.php');
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: index.php');
    exit;
}
$ut = (int)@$_SESSION['user_type'];
if($ut !== 1 && $ut !== 2){
    header('Location: dashboard.php');
    exit;
}
/** View Enquiries: bulk + row delete — admins (user_type 1) only */
$view_enq_can_delete = ($ut === 1);
$courses_q = mysqli_query($connection, "SELECT course_id, course_sname, course_name FROM courses WHERE course_status!=1 ORDER BY course_sname");
$sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
$staff_q = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status!=1 ORDER BY user_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Enquiries | Enquiry List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .enquiry-card{ transition: transform .15s ease; }
        .enquiry-card:hover{ transform: translateY(-2px); }
        .table-next-fup{ min-width: 140px; }
        .table-enquiry-list thead th{ white-space: nowrap; }
        #enquiry_table tbody tr td.col-enq-select{ text-align: center; vertical-align: middle; }
        .enq-col-select{ width: 42px; }
        /* Narrower Stage / Status — legend on hover via .course-tooltip (same as Course) */
        .table-enquiry-list:not(.enq-list-staff) thead th:nth-child(2),
        .table-enquiry-list:not(.enq-list-staff) tbody td:nth-child(2){
            max-width: 76px;
        }
        .table-enquiry-list:not(.enq-list-staff) thead th:nth-child(8),
        .table-enquiry-list:not(.enq-list-staff) tbody td:nth-child(8){
            max-width: 132px;
        }
        .table-enquiry-list.enq-list-staff thead th:nth-child(1),
        .table-enquiry-list.enq-list-staff tbody td:nth-child(1){
            max-width: 76px;
        }
        .table-enquiry-list.enq-list-staff thead th:nth-child(7),
        .table-enquiry-list.enq-list-staff tbody td:nth-child(7){
            max-width: 132px;
        }
        .table-enquiry-list .enq-stage-badge{
            width: 58px !important;
            min-width: 58px;
            max-width: 58px;
            padding-left: 0.35rem;
            padding-right: 0.35rem;
            font-size: 0.7rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-enquiry-list .enq-status-badge{
            width: 120px !important;
            min-width: 120px;
            max-width: 120px;
            padding-left: 0.4rem;
            padding-right: 0.4rem;
            font-size: 0.72rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        /* Action column: icon-only View + Delete — equal square buttons */
        .view-enq-actions .view-enq-btn{
            width: 2.125rem;
            min-width: 2.125rem;
            height: 2.125rem;
            min-height: 2.125rem;
            padding: 0;
            box-sizing: border-box;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
        }
        .view-enq-actions .view-enq-btn i.ti{
            font-size: 1.1rem;
            line-height: 1;
        }
        .view-enq-filters .btn-toolbar-actions .btn.btn-sm{
            min-height: 31px;
        }
        .view-enq-filters .form-control,
        .view-enq-filters .form-select{ box-shadow: none; }
        /* Make all filter controls same height & width */
        .view-enq-filters .form-control-sm,
        .view-enq-filters .form-select-sm{
            height: 38px;
            line-height: 1.4;
            width: 100%;
        }
        table.table-bordered.dataTable th:last-child, table.table-bordered.dataTable th:last-child, table.table-bordered.dataTable td:last-child, table.table-bordered.dataTable td:last-child{
            text-align: center;
        }
        .view-enq-filters input[type="date"].form-control-sm{
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
        /* Outcome column: date buttons (No Answer, Call Back Later, Booked Counselling) */
        .btn-fup-date{
            cursor: pointer;
            border: none;
            padding: 0 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            height: 25px;
            box-sizing: border-box;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 115px;
        }
        .badge{
            width: 140px;
        }
        table.table-bordered.dataTable th:first-child, table.table-bordered.dataTable th:first-child, table.table-bordered.dataTable td:first-child, table.table-bordered.dataTable td:first-child{
            text-align: center;
        }
        .btn-fup-date.btn-fup-no-answer{ background: #fd7e14; color: #fff; }
        .btn-fup-date.btn-fup-callback{ background: #0d6efd; color: #fff; border: 2px double rgba(255,255,255,0.4); }
        .btn-fup-date.btn-fup-booked{ background: #e6a800; color: #fff; }
        /* Outcome column: direct labels (Progressing, Converted, Provide Info., Lost) */
        .btn-fup-outcome{
            display: inline-flex;
            padding: 0 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 500;
            color: #fff;
            border: none;
            align-items: center;
            justify-content: center;
            height: 25px;
            box-sizing: border-box;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 115px;
        }
        .btn-fup-outcome.btn-fup-progressing{ background: #0d6efd; }
        .btn-fup-outcome.btn-fup-converted{ background: #198754; }
        .btn-fup-outcome.btn-fup-provide-info{ background: #495057; border: 1px solid #fd7e14; }
        .btn-fup-outcome.btn-fup-lost{ background: #dc3545; }

        /* Outcome column: col 3 when checkbox present, col 2 for staff (Stage is immediately left of Outcome) */
        table.table-enquiry-list td:nth-child(3) .badge,
        table.table-enquiry-list.enq-list-staff td:nth-child(2) .badge{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 25px;
            width: 115px;
            box-sizing: border-box;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 0 12px;
        }
        /* Neat spacing for course list tooltip */
        .tooltip.course-tooltip-popover .tooltip-inner{
            text-align: left;
            line-height: 1.6;
            padding: 0.5rem 0.75rem;
            white-space: normal;
            max-width: 600px; /* wider tooltip for course list */
            min-width: 260px;
        }
        /* DataTables + enquiry table */
        #enquiry_table_wrapper .dataTables_filter{ display: none; }
        #enquiry_table_wrapper .dataTables_length{ margin-bottom: 0.75rem; }
        #enquiry_table_wrapper .dataTables_info{ padding-top: 0.5rem; }
        #enquiry_table_wrapper .dataTables_paginate{ padding-top: 0.5rem; }
    </style>
</head>
<body class="page-view-enquiries">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">View Enquiries</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="student_enquiry.php">Create Enquiry</a></li>
                                    <li class="breadcrumb-item active">Enquiry List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard cards -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-primary">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">Today</p>
                                        <h5 class="mb-0" id="card_today">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-primary bg-opacity-10 rounded"><i class="ti ti-calendar text-primary fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-info">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">This Week</p>
                                        <h5 class="mb-0" id="card_week">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-info bg-opacity-10 rounded"><i class="ti ti-calendar-week text-info fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-secondary">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">This Month</p>
                                        <h5 class="mb-0" id="card_month">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-secondary bg-opacity-10 rounded"><i class="ti ti-calendar-month text-secondary fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-warning">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">Follow-ups Due Today</p>
                                        <h5 class="mb-0" id="card_followups">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-warning bg-opacity-10 rounded"><i class="ti ti-phone-call text-warning fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-success">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">Converted</p>
                                        <h5 class="mb-0" id="card_converted">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-success bg-opacity-10 rounded"><i class="ti ti-school text-success fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-4 col-6">
                        <div class="card enquiry-card border-danger">
                            <div class="card-body py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-1 small">Lost</p>
                                        <h5 class="mb-0" id="card_lost">0</h5>
                                    </div>
                                    <div class="avatar-sm bg-danger bg-opacity-10 rounded"><i class="ti ti-user-off text-danger fs-4"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & List -->
                <div class="card">
                    <div class="card-body">
                        <div class="row g-3 mb-3 view-enq-filters">
                            <div class="col-md-2">
                                <label class="form-label small">Search (name, phone, email)</label>
                                <input type="text" id="filter_search" class="form-control form-control-sm" placeholder="Search...">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Course</label>
                                <select id="filter_course" class="form-select form-select-sm">
                                    <option value="0">All</option>
                                    <?php while($c = mysqli_fetch_assoc($courses_q)){ echo '<option value="'.(int)$c['course_id'].'">'.htmlspecialchars($c['course_sname'].' - '.$c['course_name']).'</option>'; } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Status</label>
                                <select id="filter_status" class="form-select form-select-sm">
                                    <option value="-1">All</option>
                                    <option value="1">New</option>
                                    <option value="2">Contacted</option>
                                    <option value="3">Follow-up Pending</option>
                                    <option value="4">In Progress</option>
                                    <option value="5">Enrol Pending</option>
                                    <option value="6">Converted</option>
                                    <option value="7">Closed / Lost</option>
                                    <option value="8">Invalid/Duplicate</option>
                                    <option value="9">Booked Counselling</option>
                                    <option value="10">Re-enquired</option>
                                    <option value="11">Counselling Pending</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Source</label>
                                <select id="filter_source" class="form-select form-select-sm">
                                    <option value="-1">All</option>
                                    <?php for($i=1;$i<count($sources);$i++){ echo '<option value="'.$i.'">'.htmlspecialchars($sources[$i]).'</option>'; } ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Counsellor</label>
                                <select id="filter_counsellor" class="form-select form-select-sm">
                                    <option value="0">All</option>
                                    <?php 
                                    if($staff_q){
                                        mysqli_data_seek($staff_q,0);
                                        while($u = mysqli_fetch_assoc($staff_q)){
                                            echo '<option value="'.(int)$u['user_id'].'">'.htmlspecialchars($u['user_name']).'</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Date From</label>
                                <input type="date" id="filter_date_from" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Date To</label>
                                <input type="date" id="filter_date_to" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Sort by</label>
                                <select id="sort_by" class="form-select form-select-sm">
                                    <option value="latest">Latest</option>
                                    <option value="followup_date">Follow-up Date</option>
                                    <option value="status">Status</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="btn-toolbar-actions d-flex flex-wrap align-items-center gap-1 w-100">
                                    <button type="button" id="btn_apply" class="btn btn-primary btn-sm">Apply</button>
                                    <button type="button" id="btn_reset" class="btn btn-outline-secondary btn-sm">Reset</button>
                                    <?php if(!empty($view_enq_can_delete)){ ?>
                                    <button type="button" id="btn_bulk_delete_enquiries" class="btn btn-outline-danger btn-sm" title="Delete selected enquiries">
                                        <i class="ti ti-trash me-1"></i>Delete selected
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="enquiry_table" class="table table-hover table-bordered table-enquiry-list mb-0 w-100<?php echo empty($view_enq_can_delete) ? ' enq-list-staff' : ''; ?>">
                                <thead class="table-light">
                                    <tr>
                                        <?php if(!empty($view_enq_can_delete)){ ?>
                                        <th class="enq-col-select"><input type="checkbox" id="enq_select_all" class="form-check-input" title="Select all on this page" aria-label="Select all on this page"></th>
                                        <?php } ?>
                                        <th class="enq-col-stage">Stage</th>
                                        <th class="table-next-fup">Outcome</th>
                                        <th>Enquiry Date</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Course</th>
                                        <th class="enq-col-status">Status</th>
                                        <th style="min-width:96px">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Appointment Details modal (for date-click from Outcome column) -->
<div class="modal fade" id="viewEnqAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Appointment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewEnq_appointment_details_content">
                <p class="text-muted">Loading...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="viewEnq_edit_appointment_btn">Edit</button>
                <button type="button" class="btn btn-success" id="viewEnq_mark_completed_btn" style="display:none;">Mark as Completed</button>
                <button type="button" class="btn btn-warning" id="viewEnq_mark_no_show_btn" style="display:none;">Mark as No-Show</button>
                <button type="button" class="btn btn-danger" id="viewEnq_cancel_appointment_btn" style="display:none;">Cancel</button>
                <button type="button" class="btn btn-info" id="viewEnq_time_in_btn" style="display:none;">Time In</button>
                <button type="button" class="btn btn-info" id="viewEnq_time_out_btn" style="display:none;">Time Out</button>
            </div>
        </div>
    </div>
</div>
<!-- Cancel appointment confirmation -->
<div class="modal fade" id="viewEnqCancelConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancel Appointment?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to cancel this appointment?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" id="viewEnq_confirm_cancel_btn">Yes, cancel it</button>
            </div>
        </div>
    </div>
</div>
<?php include('includes/footer_includes.php'); ?>
<script>
$(function(){
    var VIEW_ENQ_CAN_DELETE = <?php echo !empty($view_enq_can_delete) ? 'true' : 'false'; ?>;
    var viewEnqCurrentAppointmentId = null;
    var enquiryTable = null;

    function reloadEnquiryTable(resetPaging){
        if(enquiryTable && enquiryTable.ajax){
            enquiryTable.ajax.reload(null, resetPaging !== false);
        }
    }

    $(document).on('click', '.btn-fup-date[data-appointment-id]', function(){
        var id = $(this).data('appointment-id');
        if(!id) return;
        viewEnqCurrentAppointmentId = id;
        $('#viewEnq_appointment_details_content').html('<p class="text-muted">Loading...</p>');
        $('#viewEnq_mark_completed_btn, #viewEnq_mark_no_show_btn, #viewEnq_cancel_appointment_btn, #viewEnq_time_in_btn, #viewEnq_time_out_btn').hide();
        $('#viewEnqAppointmentModal').modal('show');
        $.post('includes/datacontrol', { formName: 'get_appointment_details', appointment_id: id }, function(html){
            $('#viewEnq_appointment_details_content').html(html && html.trim() ? html : '<p class="text-muted">No details found.</p>');
            var status = $('#viewEnq_appointment_details_content #appointment_status_hidden').val();
            if(status == 'scheduled'){
                $('#viewEnq_mark_completed_btn').show();
                $('#viewEnq_mark_no_show_btn').show();
                $('#viewEnq_cancel_appointment_btn').show();
                $('#viewEnq_time_in_btn').show();
            }
        });
    });

    $('#viewEnq_edit_appointment_btn').on('click', function(){
        if(viewEnqCurrentAppointmentId) window.location.href = 'appointment_booking.php?id=' + btoa(viewEnqCurrentAppointmentId);
    });
    $('#viewEnq_mark_completed_btn').on('click', function(){ viewEnqUpdateStatus('completed'); });
    $('#viewEnq_mark_no_show_btn').on('click', function(){ viewEnqUpdateStatus('no-show'); });
    $('#viewEnq_cancel_appointment_btn').on('click', function(){ $('#viewEnqCancelConfirmModal').modal('show'); });
    $('#viewEnq_time_in_btn').on('click', function(){ viewEnqRecordTimeInOut('in'); });
    $('#viewEnq_time_out_btn').on('click', function(){ viewEnqRecordTimeInOut('out'); });
    $('#viewEnq_confirm_cancel_btn').on('click', function(){
        $('#viewEnqCancelConfirmModal').modal('hide');
        viewEnqUpdateStatus('cancelled');
    });

    function viewEnqUpdateStatus(status){
        if(!viewEnqCurrentAppointmentId) return;
        $.post('includes/datacontrol', { formName: 'update_appointment_status', appointment_id: viewEnqCurrentAppointmentId, status: status }, function(res){
            if(res == '1'){
                $('#toast-text').html('Appointment status updated successfully');
                if(document.getElementById('borderedToast1Btn')) $('#borderedToast1Btn').trigger('click');
                $('#viewEnqAppointmentModal').modal('hide');
                reloadEnquiryTable(false);
            } else {
                $('#toast-text2').html('Cannot update appointment status');
                if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
            }
        });
    }
    function viewEnqRecordTimeInOut(type){
        if(!viewEnqCurrentAppointmentId) return;
        $.post('includes/datacontrol', { formName: 'record_time_in_out', appointment_id: viewEnqCurrentAppointmentId, type: type }, function(res){
            if(res == '1'){
                $('#toast-text').html('Time recorded successfully');
                if(document.getElementById('borderedToast1Btn')) $('#borderedToast1Btn').trigger('click');
                $.post('includes/datacontrol', { formName: 'get_appointment_details', appointment_id: viewEnqCurrentAppointmentId }, function(html){
                    $('#viewEnq_appointment_details_content').html(html && html.trim() ? html : '');
                    var status = $('#viewEnq_appointment_details_content #appointment_status_hidden').val();
                    if(status == 'scheduled'){
                        $('#viewEnq_mark_completed_btn').show();
                        $('#viewEnq_mark_no_show_btn').show();
                        $('#viewEnq_cancel_appointment_btn').show();
                        $('#viewEnq_time_in_btn').show();
                        $('#viewEnq_time_out_btn').hide();
                    } else {
                        $('#viewEnq_mark_completed_btn, #viewEnq_mark_no_show_btn, #viewEnq_cancel_appointment_btn, #viewEnq_time_in_btn, #viewEnq_time_out_btn').hide();
                    }
                });
                reloadEnquiryTable(false);
            } else {
                $('#toast-text2').html('Cannot record time');
                if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
            }
        });
    }
    function loadDashboard(){
        var d = {
            formName: 'fetchEnquiryDashboard',
            search: $('#filter_search').val(),
            filter_course: $('#filter_course').val(),
            filter_status: $('#filter_status').val(),
            filter_date_from: $('#filter_date_from').val(),
            filter_date_to: $('#filter_date_to').val(),
            filter_counsellor: $('#filter_counsellor').val(),
            filter_source: $('#filter_source').val()
        };
        $.post('includes/datacontrol', d, function(data){
            try {
                var j = typeof data === 'string' ? JSON.parse(data) : data;
                $('#card_today').text(j.total_today || 0);
                $('#card_week').text(j.total_week || 0);
                $('#card_month').text(j.total_month || 0);
                $('#card_followups').text(j.followups_due_today || 0);
                $('#card_converted').text(j.converted || 0);
                $('#card_lost').text(j.lost || 0);
            } catch(e) {}
        });
    }
    enquiryTable = $('#enquiry_table').DataTable({
        processing: true,
        serverSide: true,
        searching: false,
        ordering: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: 'includes/datacontrol',
            type: 'POST',
            data: function(d){
                d.formName = 'fetchEnquiryList';
                d.search = $('#filter_search').val();
                d.filter_course = $('#filter_course').val();
                d.filter_status = $('#filter_status').val();
                d.filter_date_from = $('#filter_date_from').val();
                d.filter_date_to = $('#filter_date_to').val();
                d.filter_counsellor = $('#filter_counsellor').val();
                d.filter_source = $('#filter_source').val();
                d.sort_by = $('#sort_by').val();
            }
        },
        columns: VIEW_ENQ_CAN_DELETE ? [
            { data: 0, className: 'col-enq-select', render: function(x){ return x; } },
            { data: 1, render: function(x){ return x; } },
            { data: 2, render: function(x){ return x; } },
            { data: 3, render: function(x){ return x; } },
            { data: 4, render: function(x){ return x; } },
            { data: 5, render: function(x){ return x; } },
            { data: 6, render: function(x){ return x; } },
            { data: 7, render: function(x){ return x; } },
            { data: 8, render: function(x){ return x; } }
        ] : [
            { data: 0, render: function(x){ return x; } },
            { data: 1, render: function(x){ return x; } },
            { data: 2, render: function(x){ return x; } },
            { data: 3, render: function(x){ return x; } },
            { data: 4, render: function(x){ return x; } },
            { data: 5, render: function(x){ return x; } },
            { data: 6, render: function(x){ return x; } },
            { data: 7, render: function(x){ return x; } }
        ],
        language: {
            processing: 'Loading...',
            emptyTable: 'No records',
            zeroRecords: 'No records',
            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
            infoEmpty: 'Showing 0 to 0 of 0 entries',
            infoFiltered: '(filtered from _MAX_ total entries)',
            paginate: { previous: '&laquo;', next: '&raquo;' }
        },
        drawCallback: function(){
            if(VIEW_ENQ_CAN_DELETE && document.getElementById('enq_select_all')){
                $('#enq_select_all').prop('checked', false);
            }
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded mb-0');
            document.querySelectorAll('#enquiry_table .course-tooltip').forEach(function(el){
                var t = bootstrap.Tooltip.getInstance(el);
                if(t){ t.dispose(); }
            });
        }
    });

    function viewEnqDeleteSingle(stId){
        if(!VIEW_ENQ_CAN_DELETE) return;
        if(!stId) return;
        if(typeof Swal === 'undefined'){
            alert('Delete requires SweetAlert2.');
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Delete this enquiry?',
            text: 'The enquiry will be removed from the list (soft delete).',
            input: 'text',
            inputPlaceholder: 'Reason to delete',
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            inputValidator: function(value){
                if(!value || !String(value).trim()) return 'Please enter a reason';
            }
        }).then(function(result){
            if(!result.isConfirmed) return;
            $.post('includes/datacontrol', {
                formName: 'delete_enq',
                eq_id: stId,
                tableName: 'student_enquiry',
                colPrefix: 'st',
                note: result.value
            }, function(data){
                if(String(data).trim() === '1'){
                    $('#toast-text').html('Enquiry deleted');
                    if(document.getElementById('borderedToast1Btn')) $('#borderedToast1Btn').trigger('click');
                    reloadEnquiryTable(false);
                    loadDashboard();
                } else {
                    $('#toast-text2').html('Could not delete enquiry');
                    if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
                }
            });
        });
    }

    $(document).on('click', '.btn-enq-delete', function(e){
        e.preventDefault();
        if(!VIEW_ENQ_CAN_DELETE) return;
        viewEnqDeleteSingle($(this).data('st-id'));
    });

    $(document).on('click', '.btn-enq-lln', function(e){
        e.preventDefault();
        if(!VIEW_ENQ_CAN_DELETE) return;
        var stId = $(this).data('st-id');
        if(!stId) return;
        var $btn = $(this);
        var doProcess = true;
        if(typeof Swal !== 'undefined'){
            doProcess = false;
            Swal.fire({
                icon: 'question',
                title: 'Process to LLN?',
                text: 'This will create a counseling record for this enquiry.',
                showCancelButton: true,
                confirmButtonText: 'Process',
                cancelButtonText: 'Cancel'
            }).then(function(result){
                if(result.isConfirmed){
                    processToLLN(stId, $btn);
                }
            });
        }
        if(doProcess){
            processToLLN(stId, $btn);
        }
    });

    function processToLLN(stId, $btn){
        $.post('includes/datacontrol', {
            formName: 'process_to_lln',
            st_id: stId
        }, function(res){
            var result = String(res).trim();
            if(result === '1'){
                $('#toast-text').html('Enquiry sent to LLN successfully');
                if(document.getElementById('borderedToast1Btn')) $('#borderedToast1Btn').trigger('click');
                // Immediately disable and rename the button
                if($btn){
                    $btn.removeClass('btn-outline-secondary btn-enq-lln')
                        .addClass('btn-primary')
                        .text('Proceed to LLN')
                        .prop('disabled', true)
                        .removeAttr('data-st-id');
                }
                reloadEnquiryTable(false);
                loadDashboard();
            } else if(result === 'exists'){
                $('#toast-text2').html('LLN record already exists for this enquiry');
                if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
            } else {
                $('#toast-text2').html('Cannot process to LLN');
                if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
            }
        });
    }

    if(VIEW_ENQ_CAN_DELETE){
    $('#enq_select_all').on('change', function(){
        var on = $(this).prop('checked');
        $('#enquiry_table tbody .enq-row-cb').prop('checked', on);
    });

    $(document).on('change', '#enquiry_table tbody .enq-row-cb', function(){
        var $rows = $('#enquiry_table tbody .enq-row-cb');
        var total = $rows.length;
        var checked = $rows.filter(':checked').length;
        $('#enq_select_all').prop('checked', total > 0 && checked === total);
    });

    $('#btn_bulk_delete_enquiries').on('click', function(){
        var ids = [];
        $('#enquiry_table tbody .enq-row-cb:checked').each(function(){ ids.push($(this).val()); });
        if(!ids.length){
            if(typeof Swal !== 'undefined'){
                Swal.fire({ icon: 'info', title: 'No selection', text: 'Select one or more enquiries using the checkboxes.' });
            } else {
                alert('Select one or more enquiries first.');
            }
            return;
        }
        if(typeof Swal === 'undefined'){
            alert('Bulk delete requires SweetAlert2.');
            return;
        }
        Swal.fire({
            icon: 'warning',
            title: 'Delete ' + ids.length + ' enquiry(s)?',
            text: 'These enquiries will be removed from the list (soft delete).',
            input: 'text',
            inputPlaceholder: 'Reason to delete',
            showCancelButton: true,
            confirmButtonText: 'Delete all',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            inputValidator: function(value){
                if(!value || !String(value).trim()) return 'Please enter a reason';
            }
        }).then(function(result){
            if(!result.isConfirmed) return;
            $.post('includes/datacontrol', {
                formName: 'bulk_delete_enquiry',
                ids: JSON.stringify(ids),
                note: result.value
            }, function(res){
                var j = typeof res === 'string' ? (function(){ try { return JSON.parse(res); } catch(e){ return {}; } })() : res;
                if(j && j.ok){
                    $('#toast-text').html('Selected enquiries deleted');
                    if(document.getElementById('borderedToast1Btn')) $('#borderedToast1Btn').trigger('click');
                    reloadEnquiryTable(false);
                    loadDashboard();
                    $('#enq_select_all').prop('checked', false);
                } else {
                    $('#toast-text2').html('Could not delete selected enquiries');
                    if(document.getElementById('borderedToast2Btn')) $('#borderedToast2Btn').trigger('click');
                }
            });
        });
    });
    }

    loadDashboard();
    $('#btn_apply').on('click', function(){
        reloadEnquiryTable(true);
        loadDashboard();
    });
    $('#btn_reset').on('click', function(){
        $('#filter_search').val('');
        $('#filter_counsellor').val('0');
        $('#filter_course').val('0');
        $('#filter_status,#filter_source').val('-1');
        $('#filter_date_from,#filter_date_to').val('');
        $('#sort_by').val('latest');
        reloadEnquiryTable(true);
        loadDashboard();
    });
    $('#filter_search').on('keypress', function(e){
        if(e.which===13){
            reloadEnquiryTable(true);
            loadDashboard();
        }
    });
    // Quick UX: apply Status filter immediately on change.
    $('#filter_status').on('change', function(){
        reloadEnquiryTable(true);
        loadDashboard();
    });
    // Initialise Bootstrap tooltips for dynamically loaded course cells (neat multiline list)
    $(document).on('mouseenter', '.course-tooltip', function () {
        var instance = bootstrap.Tooltip.getInstance(this);
        if(!instance){
            // Prepare HTML title once from newline-separated title
            var raw = $(this).attr('title') || '';
            var html = raw.replace(/\n/g, '<br><br>');
            $(this).attr('data-bs-original-title', html);
            instance = new bootstrap.Tooltip(this, {
                html: true,
                trigger: 'manual',
                customClass: 'course-tooltip-popover'
            });
        }
        instance.show();
    });
    // Hide tooltip when mouse leaves the course text
    $(document).on('mouseleave', '.course-tooltip', function () {
        var instance = bootstrap.Tooltip.getInstance(this);
        if(instance){
            instance.hide();
        }
    });
});
</script>
</body>
</html>
