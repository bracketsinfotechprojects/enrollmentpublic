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
$courses_q = mysqli_query($connection, "SELECT course_id, course_sname, course_name FROM courses WHERE course_status!=1 ORDER BY course_sname");
$sources = array('','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads','Agent / referral (legacy)');
$staff_q = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status!=1 ORDER BY user_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enquiry Reports &amp; Analytics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .report-card{ margin-bottom: 1.25rem; }
        .report-card .card-body{ padding: 1rem 1.25rem; }
        .report-table{ font-size: 0.9rem; }
        .report-table th{ white-space: nowrap; }
        .stat-big{ font-size: 1.5rem; font-weight: 600; }
        .view-enq-filters .form-control,
        .view-enq-filters .form-select{ box-shadow: none; }
        /* Make all filter controls same height & width */
        .view-enq-filters .form-control-sm,
        .view-enq-filters .form-select-sm{
            height: 38px;
            line-height: 1.4;
            width: 100%;
        }
        .view-enq-filters input[type="date"].form-control-sm{
            padding-top: 0.25rem;
            padding-bottom: 0.25rem;
        }
    </style>
</head>
<body>
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>
    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Enquiry Reports &amp; Analytics</h4>
                            <div class="page-title-right">
                                <form method="post" action="includes/datacontrol" target="_blank" class="d-inline" id="export_excel_form">
                                    <input type="hidden" name="formName" value="exportEnquiryReportsExcel">
                                    <input type="hidden" name="search" id="export_search">
                                    <input type="hidden" name="filter_course" id="export_filter_course">
                                    <input type="hidden" name="filter_status" id="export_filter_status">
                                    <input type="hidden" name="filter_source" id="export_filter_source">
                                    <input type="hidden" name="filter_counsellor" id="export_filter_counsellor">
                                    <input type="hidden" name="filter_date_from" id="export_filter_date_from">
                                    <input type="hidden" name="filter_date_to" id="export_filter_date_to">
                                    <button type="submit" class="btn btn-success btn-sm me-1"><i class="ti ti-file-spreadsheet me-1"></i>Export Excel</button>
                                </form>
                                <form method="post" action="includes/datacontrol" target="_blank" class="d-inline" id="export_pdf_form">
                                    <input type="hidden" name="formName" value="exportEnquiryReportsPdf">
                                    <input type="hidden" name="search" id="export_pdf_search">
                                    <input type="hidden" name="filter_course" id="export_pdf_filter_course">
                                    <input type="hidden" name="filter_status" id="export_pdf_filter_status">
                                    <input type="hidden" name="filter_source" id="export_pdf_filter_source">
                                    <input type="hidden" name="filter_counsellor" id="export_pdf_filter_counsellor">
                                    <input type="hidden" name="filter_date_from" id="export_pdf_filter_date_from">
                                    <input type="hidden" name="filter_date_to" id="export_pdf_filter_date_to">
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="ti ti-file-type-pdf me-1"></i>Export PDF</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters (same as View Enquiries) -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3 view-enq-filters">
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
                                    <option value="3">Follow-up Required</option>
                                    <option value="4">Interested</option>
                                    <option value="5">Documents Collected</option>
                                    <option value="6">Enrolled</option>
                                    <option value="7">Not Interested</option>
                                    <option value="8">Invalid/Duplicate</option>
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
                                    <?php if($staff_q){ mysqli_data_seek($staff_q,0); while($u = mysqli_fetch_assoc($staff_q)){ echo '<option value="'.(int)$u['user_id'].'">'.htmlspecialchars($u['user_name']).'</option>'; } } ?>
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
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="btn_apply" class="btn btn-primary btn-sm me-1">Apply</button>
                                <button type="button" id="btn_reset" class="btn btn-outline-secondary btn-sm">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="reports_loading" class="text-center py-4 text-muted">Loading reports...</div>
                <div id="reports_content" class="d-none">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card report-card border-primary">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted mb-2">Conversion Rate</h6>
                                    <div class="stat-big text-primary" id="stat_conversion">0%</div>
                                    <p class="mb-0 small text-muted"><span id="stat_converted">0</span> converted / <span id="stat_total">0</span> total enquiries</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card report-card border-danger">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted mb-2">Lost Enquiries</h6>
                                    <div class="stat-big text-danger" id="stat_lost">0</div>
                                    <p class="mb-0 small text-muted">Not Interested</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card report-card border-info">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted mb-2">Follow-up Effectiveness</h6>
                                    <div class="stat-big text-info" id="stat_followup">0</div>
                                    <p class="mb-0 small text-muted">Converted among those with follow-up</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card report-card">
                                <div class="card-header"><h5 class="card-title mb-0">Enquiries by Course</h5></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table report-table table-hover mb-0">
                                            <thead class="table-light"><tr><th>Course</th><th class="text-end">Count</th></tr></thead>
                                            <tbody id="report_by_course"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card report-card">
                                <div class="card-header"><h5 class="card-title mb-0">Enquiries by Source</h5></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table report-table table-hover mb-0">
                                            <thead class="table-light"><tr><th>Source</th><th class="text-end">Count</th></tr></thead>
                                            <tbody id="report_by_source"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card report-card">
                                <div class="card-header"><h5 class="card-title mb-0">Counsellor Performance</h5></div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table report-table table-hover mb-0">
                                            <thead class="table-light"><tr><th>Counsellor</th><th class="text-end">Enquiries</th><th class="text-end">Converted</th><th class="text-end">Conversion %</th></tr></thead>
                                            <tbody id="report_counsellor"></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card report-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted mb-2">Follow-up Effectiveness</h6>
                                    <p class="mb-1">Enquiries with at least one follow-up: <strong id="followup_with">0</strong></p>
                                    <p class="mb-0">Converted among those with follow-up: <strong id="followup_converted">0</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card report-card">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted mb-2">Lost Enquiries</h6>
                                    <p class="mb-0">Total enquiries marked as <strong>Not Interested</strong>: <strong id="lost_total">0</strong>. Track reasons (e.g. fee, visa, timing) in remarks for future reporting.</p>
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
$(function(){
    function getReportFilters(){
        return {
            formName: 'fetchEnquiryReports',
            search: $('#filter_search').val(),
            filter_course: $('#filter_course').val(),
            filter_status: $('#filter_status').val(),
            filter_source: $('#filter_source').val(),
            filter_counsellor: $('#filter_counsellor').val(),
            filter_date_from: $('#filter_date_from').val(),
            filter_date_to: $('#filter_date_to').val()
        };
    }
    function syncExportForms(){
        var s = $('#filter_search').val(), c = $('#filter_course').val(), st = $('#filter_status').val(), sr = $('#filter_source').val(), co = $('#filter_counsellor').val(), df = $('#filter_date_from').val(), dt = $('#filter_date_to').val();
        $('#export_search').val(s); $('#export_filter_course').val(c); $('#export_filter_status').val(st); $('#export_filter_source').val(sr); $('#export_filter_counsellor').val(co); $('#export_filter_date_from').val(df); $('#export_filter_date_to').val(dt);
        $('#export_pdf_search').val(s); $('#export_pdf_filter_course').val(c); $('#export_pdf_filter_status').val(st); $('#export_pdf_filter_source').val(sr); $('#export_pdf_filter_counsellor').val(co); $('#export_pdf_filter_date_from').val(df); $('#export_pdf_filter_date_to').val(dt);
    }
    function loadReports(){
        $('#reports_loading').removeClass('d-none');
        $('#reports_content').addClass('d-none');
        $.post('includes/datacontrol', getReportFilters(), function(data){
            $('#reports_loading').addClass('d-none');
            $('#reports_content').removeClass('d-none');
            try {
                var d = typeof data === 'string' ? JSON.parse(data) : data;
                $('#stat_conversion').text((d.conversion_rate || 0) + '%');
                $('#stat_converted').text(d.converted_count || 0);
                $('#stat_total').text(d.total_enquiries || 0);
                $('#stat_lost').text(d.lost_count || 0);
                $('#lost_total').text(d.lost_count || 0);
                $('#stat_followup').text(d.followup_effectiveness ? d.followup_effectiveness.converted_with_followup : 0);
                $('#followup_with').text(d.followup_effectiveness ? d.followup_effectiveness.with_followup : 0);
                $('#followup_converted').text(d.followup_effectiveness ? d.followup_effectiveness.converted_with_followup : 0);
                var byCourse = d.by_course || [];
                var t = '';
                byCourse.forEach(function(r){ t += '<tr><td>'+escapeHtml(r.course)+'</td><td class="text-end">'+r.count+'</td></tr>'; });
                $('#report_by_course').html(t || '<tr><td colspan="2" class="text-muted">No data</td></tr>');
                var bySource = d.by_source || [];
                t = '';
                bySource.forEach(function(r){ t += '<tr><td>'+escapeHtml(r.source)+'</td><td class="text-end">'+r.count+'</td></tr>'; });
                $('#report_by_source').html(t || '<tr><td colspan="2" class="text-muted">No data</td></tr>');
                var cp = d.counsellor_performance || [];
                t = '';
                cp.forEach(function(r){ t += '<tr><td>'+escapeHtml(r.counsellor)+'</td><td class="text-end">'+r.enquiries+'</td><td class="text-end">'+r.converted+'</td><td class="text-end">'+r.rate+'%</td></tr>'; });
                $('#report_counsellor').html(t || '<tr><td colspan="4" class="text-muted">No data</td></tr>');
                syncExportForms();
            } catch(e) {
                $('#reports_content').html('<div class="alert alert-danger">Failed to load report data.</div>');
            }
        }).fail(function(){ $('#reports_loading').addClass('d-none'); $('#reports_content').removeClass('d-none').html('<div class="alert alert-danger">Failed to load reports.</div>'); });
    }
    $('#export_excel_form, #export_pdf_form').on('submit', function(){ syncExportForms(); });
    $('#btn_apply').on('click', function(){ loadReports(); });
    $('#btn_reset').on('click', function(){
        $('#filter_search').val('');
        $('#filter_course').val('0');
        $('#filter_status,#filter_source').val('-1');
        $('#filter_counsellor').val('0');
        $('#filter_date_from,#filter_date_to').val('');
        loadReports();
    });
    $('#filter_search').on('keypress', function(e){ if(e.which===13){ e.preventDefault(); loadReports(); } });
    function escapeHtml(s){ var d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
    loadReports();
});
</script>
</body>
</html>
