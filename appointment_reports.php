<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){

// Fetch employees and appointment purposes for filters
$reportUsers = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
$appointmentPurposes = mysqli_query($connection, "SELECT purpose_id, purpose_name FROM appointment_purposes WHERE purpose_status != 1 ORDER BY purpose_name");
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Appointment List View</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        
        <!-- Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <style>
            .chart-container {
                position: relative;
                height: 300px;
                width: 100%;
                overflow: hidden;
                padding: 10px;
            }
            .chart-container canvas {
                max-width: 100% !important;
                max-height: 100% !important;
            }
            .card-body {
                overflow: hidden;
            }
            /* Status badge colors (shared with calendar) */
            .status-badge {
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
                color: #fff;
                display: inline-block;
            }
            .status-scheduled { background: #0bb197; }   /* Pending */
            .status-completed { background: #0ac074; }
            .status-cancelled { background: #ff3d60; }
            .status-no-show  { background: #fcb92c; color: #000; }
            .status-missed   { background: #74788d; }
            .view-enq-filters .form-control,
            .view-enq-filters .form-select {
                box-shadow: none;
            }
        </style>
    </head>

    <body>

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
                                    <h4 class="mb-sm-0">Appointment List View</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item"><a href="appointment_calendar.php">Appointments</a></li>
                                            <li class="breadcrumb-item active">List View</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <!-- Filter Section -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Filter Appointments</h5>
                                        <div class="row g-3 mb-2 view-enq-filters">
                                            <div class="col-md-2">
                                                <label class="form-label small">Date Range</label>
                                                <select class="form-select form-select-sm" id="date_range">
                                                    <option value="today">Today</option>
                                                    <option value="tomorrow">Tomorrow</option>
                                                    <option value="week">This Week</option>
                                                    <option value="month" selected>This Month</option>
                                                    <option value="custom">Custom Range</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2" id="custom_date_section" style="display:none;">
                                                <label class="form-label small">Start Date</label>
                                                <input type="date" class="form-control form-control-sm" id="start_date">
                                            </div>
                                            <div class="col-md-2" id="custom_date_section2" style="display:none;">
                                                <label class="form-label small">End Date</label>
                                                <input type="date" class="form-control form-control-sm" id="end_date">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Status</label>
                                                <select class="form-select form-select-sm" id="status_filter">
                                                    <option value="">All</option>
                                                    <option value="scheduled">Pending</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="cancelled">Cancelled</option>
                                                    <option value="no-show">No-show</option>
                                                    <option value="missed">Missed</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Purpose</label>
                                                <select class="form-select form-select-sm" id="purpose_filter">
                                                    <option value="0">All</option>
                                                    <?php 
                                                    if($appointmentPurposes){
                                                        mysqli_data_seek($appointmentPurposes, 0);
                                                        while($p = mysqli_fetch_assoc($appointmentPurposes)){
                                                            echo '<option value="'.(int)$p['purpose_id'].'">'.htmlspecialchars($p['purpose_name']).'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small">Employee</label>
                                                <select class="form-select form-select-sm" id="staff_filter">
                                                    <option value="0">All Employees</option>
                                                    <?php 
                                                    mysqli_data_seek($reportUsers, 0);
                                                    $currentUserId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
                                                    while($u = mysqli_fetch_array($reportUsers)){
                                                        $selected = ($currentUserId && $currentUserId == (int)$u['user_id']) ? ' selected' : '';
                                                        echo '<option value="'.$u['user_id'].'"'.$selected.'">'.htmlspecialchars($u['user_name']).'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" id="btn_apply" class="btn btn-primary btn-sm me-1">Apply</button>
                                                <button type="button" id="btn_reset" class="btn btn-outline-secondary btn-sm">Reset</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-truncate font-size-14 mb-2">Total Appointments</p>
                                                <h4 class="mb-2" id="total_appointments">0</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-primary rounded-circle">
                                                    <i class="mdi mdi-calendar-clock font-size-18"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-truncate font-size-14 mb-2">Attended</p>
                                                <h4 class="mb-2 text-success" id="attended_count">0</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-success rounded-circle">
                                                    <i class="mdi mdi-check-circle font-size-18"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-truncate font-size-14 mb-2">Missed/No-Show</p>
                                                <h4 class="mb-2 text-danger" id="missed_count">0</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-danger rounded-circle">
                                                    <i class="mdi mdi-close-circle font-size-18"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-grow-1">
                                                <p class="text-truncate font-size-14 mb-2">Cancelled</p>
                                                <h4 class="mb-2 text-warning" id="cancelled_count">0</h4>
                                            </div>
                                            <div class="avatar-sm">
                                                <span class="avatar-title bg-warning rounded-circle">
                                                    <i class="mdi mdi-cancel font-size-18"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Appointments -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Today's Appointments</h4>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time Slot</th>
                                                        <th>Purpose</th>
                                                        <th>Attendee</th>
                                                        <th>Staff Member</th>
                                                        <th>Status</th>
                                                        <th>Meeting Type</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="today_appointments_body">
                                                    <!-- Loaded via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming Appointments -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Upcoming Appointments</h4>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time Slot</th>
                                                        <th>Purpose</th>
                                                        <th>Attendee</th>
                                                        <th>Staff Member</th>
                                                        <th>Status</th>
                                                        <th>Meeting Type</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="upcoming_appointments_body">
                                                    <!-- Loaded via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Detailed Appointments List -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Detailed Appointments</h4>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped" id="appointments_table">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time Slot</th>
                                                        <th>Purpose</th>
                                                        <th>Attendee</th>
                                                        <th>Staff Member</th>
                                                        <th>Status</th>
                                                        <th>Meeting Type</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="appointments_table_body">
                                                    <!-- Loaded via AJAX -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
            </div>

        </div>

        <!-- Appointment details modal with actions (same as calendar) -->
        <div class="modal fade" id="appointmentDetailsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Appointment Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="appointment_details_content">
                        <!-- Content loaded via AJAX -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="edit_appointment_btn">Edit</button>
                        <button type="button" class="btn btn-success" id="mark_completed_btn" style="display:none;">Mark as Completed</button>
                        <button type="button" class="btn btn-warning" id="mark_no_show_btn" style="display:none;">Mark as No-Show</button>
                        <button type="button" class="btn btn-danger" id="cancel_appointment_btn" style="display:none;">Cancel</button>
                        <button type="button" class="btn btn-info" id="time_in_btn" style="display:none;">Time In</button>
                        <button type="button" class="btn btn-info" id="time_out_btn" style="display:none;">Time Out</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel confirmation modal -->
        <div class="modal fade" id="cancelConfirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cancel Appointment?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to cancel this appointment?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                        <button type="button" class="btn btn-danger" id="confirm_cancel_btn">Yes, cancel it</button>
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/footer_includes.php'); ?>
        
        <script>
            var statusChart, purposeChart, staffChart, dailyTrendChart;
            var currentAppointmentId = null;
            var loggedUserId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0; ?>;
            
            $(document).ready(function() {
                $('#date_range').on('change', function() {
                    if($(this).val() == 'custom') {
                        $('#custom_date_section').show();
                        $('#custom_date_section2').show();
                    } else {
                        $('#custom_date_section').hide();
                        $('#custom_date_section2').hide();
                    }
                });

                $('#btn_apply').on('click', function(){
                    loadReports();
                });

                // Keep "All Employees" as default so reports open with complete data.

                $('#btn_reset').on('click', function(){
                    $('#date_range').val('month');
                    $('#start_date').val('');
                    $('#end_date').val('');
                    $('#status_filter').val('');
                    $('#purpose_filter').val('0');
                    $('#staff_filter').val('0');
                    $('#custom_date_section').hide();
                    $('#custom_date_section2').hide();
                    loadReports();
                });
                
                loadReports();

                // Modal button handlers (same behaviour as calendar view)
                $('#edit_appointment_btn').on('click', function() {
                    if(currentAppointmentId) {
                        window.location.href = 'appointment_booking.php?id=' + btoa(currentAppointmentId);
                    }
                });
                
                $('#mark_completed_btn').on('click', function() {
                    updateAppointmentStatus('completed');
                });
                
                $('#mark_no_show_btn').on('click', function() {
                    updateAppointmentStatus('no-show');
                });
                
                $('#cancel_appointment_btn').on('click', function() {
                    $('#cancelConfirmModal').modal('show');
                });
                
                $('#time_in_btn').on('click', function() {
                    recordTimeInOut('in');
                });
                
                $('#time_out_btn').on('click', function() {
                    recordTimeInOut('out');
                });

                $('#confirm_cancel_btn').on('click', function(){
                    $('#cancelConfirmModal').modal('hide');
                    updateAppointmentStatus('cancelled');
                });
            });
            
            function loadReports() {
                var dateRange = $('#date_range').val();
                var startDate = '';
                var endDate = '';
                var statusFilter = $('#status_filter').val();
                var staffFilter = $('#staff_filter').val();
                var purposeFilter = $('#purpose_filter').val();
                
                if(dateRange == 'custom') {
                    startDate = $('#start_date').val();
                    endDate = $('#end_date').val();
                }
                
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {
                        formName: 'get_appointment_reports',
                        date_range: dateRange,
                        start_date: startDate,
                        end_date: endDate,
                        status_filter: statusFilter,
                        staff_filter: staffFilter,
                        purpose_filter: purposeFilter
                    },
                    success: function(response) {
                        try {
                            var data = JSON.parse(response);
                            
                            // Update summary cards
                            $('#total_appointments').text(data.summary.total);
                            $('#attended_count').text(data.summary.attended);
                            $('#missed_count').text(data.summary.missed);
                            $('#cancelled_count').text(data.summary.cancelled);
                            
                            // Update charts - use setTimeout to ensure DOM is ready
                            setTimeout(function() {
                                updateStatusChart(data.charts.status);
                                updatePurposeChart(data.charts.purpose);
                                updateStaffChart(data.charts.staff);
                                updateDailyTrendChart(data.charts.daily);
                            }, 100);
                            
                            // Update tables
                            updateTodayAppointmentsTable(data.appointments);
                            updateUpcomingAppointmentsTable(data.appointments);
                            updateAppointmentsTable(data.appointments);
                        } catch(e) {
                            console.error('Error parsing report data:', e);
                            console.error('Response:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        console.error('Status:', status);
                    }
                });
            }
            
            function updateStatusChart(data) {
                var ctx = document.getElementById('statusChart');
                if(!ctx) return;
                
                if(statusChart) {
                    statusChart.destroy();
                    statusChart = null;
                }
                
                // Ensure we have data
                if(!data.labels || data.labels.length === 0) {
                    data.labels = ['No Data'];
                    data.values = [0];
                }
                
                statusChart = new Chart(ctx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: ['#0ac074', '#ff3d60', '#fcb92c', '#74788d', '#4aa3ff']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            animateRotate: true,
                            animateScale: false,
                            duration: 1000
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        layout: {
                            padding: {
                                top: 5,
                                bottom: 5,
                                left: 5,
                                right: 5
                            }
                        }
                    }
                });
            }
            
            function updatePurposeChart(data) {
                var ctx = document.getElementById('purposeChart');
                if(!ctx) return;
                
                if(purposeChart) {
                    purposeChart.destroy();
                    purposeChart = null;
                }
                
                // Ensure we have data
                if(!data.labels || data.labels.length === 0) {
                    data.labels = ['No Data'];
                    data.values = [0];
                }
                
                purposeChart = new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Appointments',
                            data: data.values,
                            backgroundColor: '#0bb197'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: {
                            duration: 1000
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
            
            function updateStaffChart(data) {
                var ctx = document.getElementById('staffChart');
                if(!ctx) return;
                
                if(staffChart) {
                    staffChart.destroy();
                    staffChart = null;
                }
                
                // Ensure we have data
                if(!data.labels || data.labels.length === 0) {
                    data.labels = ['No Data'];
                    data.values = [0];
                }
                
                staffChart = new Chart(ctx.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Appointments',
                            data: data.values,
                            backgroundColor: '#564ab1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: {
                            duration: 1000
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
            
            function updateDailyTrendChart(data) {
                var ctx = document.getElementById('dailyTrendChart');
                if(!ctx) return;
                
                if(dailyTrendChart) {
                    dailyTrendChart.destroy();
                    dailyTrendChart = null;
                }
                
                // Ensure we have data
                if(!data.labels || data.labels.length === 0) {
                    data.labels = ['No Data'];
                    data.values = [0];
                }
                
                dailyTrendChart = new Chart(ctx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Appointments',
                            data: data.values,
                            borderColor: '#0bb197',
                            backgroundColor: 'rgba(11, 177, 151, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        animation: {
                            duration: 1000
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            }
                        }
                    }
                });
            }
            
            function updateTodayAppointmentsTable(appointments) {
                var tbody = $('#today_appointments_body');
                tbody.empty();
                if(!appointments || !appointments.length){
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No appointments</td></tr>');
                    return;
                }
                var today = new Date().toISOString().slice(0,10);
                var any = false;
                appointments.forEach(function(appt){
                    if(appt.date_raw === today){
                        any = true;
                        var row = '<tr>' +
                            '<td>'+escapeHtml(appt.date_display || appt.datetime)+'</td>' +
                            '<td>'+escapeHtml(appt.time_slot)+'</td>' +
                            '<td>'+escapeHtml(appt.purpose)+'</td>' +
                            '<td>'+escapeHtml(appt.attendee)+'</td>' +
                            '<td>'+escapeHtml(appt.staff)+'</td>' +
                            '<td><span class="status-badge status-'+appt.status+'">'+escapeHtml(formatStatus(appt.status))+'</span></td>' +
                            '<td>'+escapeHtml(appt.meeting_type)+'</td>' +
                            '<td><button type="button" class="btn btn-sm btn-primary" onclick="openAppointmentDetails('+appt.id+')">View</button></td>' +
                            '</tr>';
                        tbody.append(row);
                    }
                });
                if(!any){
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No appointments</td></tr>');
                }
            }

            function updateUpcomingAppointmentsTable(appointments) {
                var tbody = $('#upcoming_appointments_body');
                tbody.empty();
                if(!appointments || !appointments.length){
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No appointments</td></tr>');
                    return;
                }
                var today = new Date().toISOString().slice(0,10);
                var any = false;
                appointments.forEach(function(appt){
                    if(appt.date_raw > today && appt.status !== 'cancelled'){
                        any = true;
                        var row = '<tr>' +
                            '<td>'+escapeHtml(appt.date_display || appt.datetime)+'</td>' +
                            '<td>'+escapeHtml(appt.time_slot)+'</td>' +
                            '<td>'+escapeHtml(appt.purpose)+'</td>' +
                            '<td>'+escapeHtml(appt.attendee)+'</td>' +
                            '<td>'+escapeHtml(appt.staff)+'</td>' +
                            '<td><span class="status-badge status-'+appt.status+'">'+escapeHtml(formatStatus(appt.status))+'</span></td>' +
                            '<td>'+escapeHtml(appt.meeting_type)+'</td>' +
                            '<td><button type="button" class="btn btn-sm btn-primary" onclick="openAppointmentDetails('+appt.id+')">View</button></td>' +
                            '</tr>';
                        tbody.append(row);
                    }
                });
                if(!any){
                    tbody.append('<tr><td colspan="8" class="text-center text-muted">No appointments</td></tr>');
                }
            }

            function updateAppointmentsTable(appointments) {
                var tbody = $('#appointments_table_body');
                tbody.empty();
                
                if(!appointments || appointments.length == 0) {
                    tbody.append('<tr><td colspan="8" class="text-center">No appointments found</td></tr>');
                    return;
                }
                
                appointments.forEach(function(apt) {
                    var statusClass = 'status-' + apt.status;
                    var statusText = formatStatus(apt.status);
                    
                    var row = '<tr>' +
                        '<td>' + escapeHtml(apt.date_display || apt.datetime) + '</td>' +
                        '<td>' + escapeHtml(apt.time_slot) + '</td>' +
                        '<td>' + escapeHtml(apt.purpose) + '</td>' +
                        '<td>' + escapeHtml(apt.attendee) + '</td>' +
                        '<td>' + escapeHtml(apt.staff) + '</td>' +
                        '<td><span class="status-badge ' + statusClass + '">' + escapeHtml(statusText) + '</span></td>' +
                        '<td>' + escapeHtml(apt.meeting_type) + '</td>' +
                        '<td><button type="button" class="btn btn-sm btn-primary" onclick="openAppointmentDetails('+apt.id+')">View</button></td>' +
                        '</tr>';
                    tbody.append(row);
                });
            }

            function formatStatus(status){
                if(!status) return '-';
                return status.replace(/-/g,' ').replace(/\b\w/g,function(c){return c.toUpperCase();});
            }

            function escapeHtml(s){
                return (s || '').toString()
                    .replace(/&/g,'&amp;')
                    .replace(/</g,'&lt;')
                    .replace(/>/g,'&gt;')
                    .replace(/"/g,'&quot;')
                    .replace(/'/g,'&#39;');
            }

            function openAppointmentDetails(id){
                currentAppointmentId = id;
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {
                        formName: 'get_appointment_details',
                        appointment_id: id
                    },
                    success: function(response){
                        $('#appointment_details_content').html(response);
                        $('#appointmentDetailsModal').modal('show');

                        // Show/hide action buttons based on status (same logic as calendar)
                        var status = $('#appointment_status_hidden').val();
                        if(status == 'scheduled') {
                            $('#mark_completed_btn').show();
                            $('#mark_no_show_btn').show();
                            $('#cancel_appointment_btn').show();
                            $('#time_in_btn').show();
                            $('#time_out_btn').hide();
                        } else if(status == 'completed') {
                            $('#mark_completed_btn').hide();
                            $('#mark_no_show_btn').hide();
                            $('#cancel_appointment_btn').hide();
                            $('#time_in_btn').hide();
                            $('#time_out_btn').hide();
                        } else {
                            $('#mark_completed_btn').hide();
                            $('#mark_no_show_btn').hide();
                            $('#cancel_appointment_btn').hide();
                            $('#time_in_btn').hide();
                            $('#time_out_btn').hide();
                        }
                    }
                });
            }

            function updateAppointmentStatus(status) {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {
                        formName: 'update_appointment_status',
                        appointment_id: currentAppointmentId,
                        status: status
                    },
                    success: function(response) {
                        if(response == '1') {
                            $('#toast-text').html('Appointment status updated successfully');
                            $('#borderedToast1Btn').trigger('click');
                            $('#appointmentDetailsModal').modal('hide');
                            loadReports();
                        } else {
                            $('.toast-text2').html('Cannot update appointment status');
                            $('#borderedToast2Btn').trigger('click');
                        }
                    }
                });
            }

            function recordTimeInOut(type) {
                $.ajax({
                    url: 'includes/datacontrol',
                    type: 'POST',
                    data: {
                        formName: 'record_time_in_out',
                        appointment_id: currentAppointmentId,
                        type: type
                    },
                    success: function(response) {
                        if(response == '1') {
                            $('#toast-text').html('Time recorded successfully');
                            $('#borderedToast1Btn').trigger('click');
                            if(currentAppointmentId){
                                openAppointmentDetails(currentAppointmentId);
                            }
                            loadReports();
                        } else {
                            $('.toast-text2').html('Cannot record time');
                            $('#borderedToast2Btn').trigger('click');
                        }
                    }
                });
            }
        </script>
    </body>
</html>
<?php }else{ 
    header("Location: index.php");
}
?>

