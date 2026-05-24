<?php
include 'includes/dbconnect.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: index.php');
    exit;
}
$ut = (int) @$_SESSION['user_type'];
if ($ut !== 1 && $ut !== 2) {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/includes/email_log_helper.php';
crm_email_ensure_log_table($connection);

$staff_list = array();
$sq = mysqli_query($connection, 'SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name');
if ($sq) {
    while ($sr = mysqli_fetch_assoc($sq)) {
        $staff_list[] = $sr;
    }
}

$category_options = array(
    '' => 'All categories',
    'general' => 'General',
    'enquiry_status' => 'Enquiry status email',
    'appointment_confirmation' => 'Appointment confirmation',
    'appointment_manual' => 'Appointment (manual)',
    'course_cancellation_submit' => 'Course cancellation (submit)',
    'course_extension_submit' => 'Course extension (submit)',
    'course_cancellation_update' => 'Course cancellation (office)',
    'course_extension_update' => 'Course extension (office)',
    'login_otp' => 'Login OTP',
    'public_enquiry_form' => 'Public enquiry form',
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Email Logs | CRM</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include 'includes/app_includes.php'; ?>
    <style>
        .email-log-filters .form-control-sm, .email-log-filters .form-select-sm { height: 38px; }
        .page-email-logs .email-log-filters.card-body { padding: 1.25rem 1.5rem; }
        .page-email-logs #email_logs_table_wrap { padding: 0.75rem 1.25rem 1.25rem; }
        .page-email-logs #email_logs_table thead th {
            white-space: nowrap;
            padding: 0.85rem 1.1rem;
            vertical-align: middle;
            font-weight: 600;
        }
        .page-email-logs #email_logs_table tbody td {
            padding: 0.8rem 1.1rem;
            vertical-align: middle;
            line-height: 1.45;
        }
        .page-email-logs #email_logs_table .btn-sm { padding: 0.35rem 0.65rem; }
        .page-email-logs .dataTables_wrapper .dataTables_length,
        .page-email-logs .dataTables_wrapper .dataTables_filter,
        .page-email-logs .dataTables_wrapper .dataTables_info,
        .page-email-logs .dataTables_wrapper .dataTables_paginate {
            padding-left: 0.25rem;
            padding-right: 0.25rem;
        }
        .email-log-meta dt { font-weight: 600; color: #64748b; font-size: 0.8rem; }
        .email-log-meta dd { margin-bottom: 0.5rem; }
        #email_log_iframe { width: 100%; min-height: 420px; border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; }
    </style>
</head>
<body class="page-email-logs">
<div class="main-wrapper">
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>
    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Email Logs</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Email Logs</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body email-log-filters">
                        <div class="row g-2 align-items-end">
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Date from</label>
                                <input type="date" id="el_filter_date_from" class="form-control form-control-sm">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Date to</label>
                                <input type="date" id="el_filter_date_to" class="form-control form-control-sm">
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Staff (sender)</label>
                                <select id="el_filter_staff" class="form-select form-select-sm">
                                    <option value="0">All staff</option>
                                    <?php foreach ($staff_list as $u) { ?>
                                    <option value="<?php echo (int) $u['user_id']; ?>"><?php echo htmlspecialchars($u['user_name'], ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Category</label>
                                <select id="el_filter_category" class="form-select form-select-sm">
                                    <?php foreach ($category_options as $k => $lab) { ?>
                                    <option value="<?php echo htmlspecialchars($k === '' ? '-1' : $k, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($lab, ENT_QUOTES, 'UTF-8'); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Status</label>
                                <select id="el_filter_status" class="form-select form-select-sm">
                                    <option value="">All</option>
                                    <option value="sent">Sent</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-md-4">
                                <label class="form-label small mb-1">Enquiry ID / st_id</label>
                                <input type="text" id="el_filter_enquiry" class="form-control form-control-sm" placeholder="e.g. EQ00043 or numeric">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small mb-1">Recipient email contains</label>
                                <input type="text" id="el_filter_to" class="form-control form-control-sm" placeholder="Filter by To…">
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label class="form-label small mb-1">Search (subject / recipient)</label>
                                <input type="search" id="el_filter_search" class="form-control form-control-sm" placeholder="DataTables search…">
                            </div>
                            <div class="col-lg-6 col-md-12 d-flex flex-wrap gap-2 pt-2 pt-lg-0">
                                <button type="button" class="btn btn-primary btn-sm" id="el_btn_apply">Apply filters</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="el_btn_reset">Reset</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div id="email_logs_table_wrap" class="table-responsive">
                            <table id="email_logs_table" class="table table-hover table-bordered mb-0 w-100 align-middle" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th>When</th>
                                        <th>Status</th>
                                        <th>Category</th>
                                        <th>To</th>
                                        <th>Subject</th>
                                        <th>Sent by</th>
                                        <th>Enquiry</th>
                                        <th style="min-width:88px">Detail</th>
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

<div class="modal fade" id="emailLogDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="email_log_detail_loading">Loading…</p>
                <div id="email_log_detail_wrap" class="d-none">
                    <div class="row">
                        <div class="col-md-5">
                            <dl class="email-log-meta small" id="email_log_detail_meta"></dl>
                        </div>
                        <div class="col-md-7">
                            <p class="small text-muted mb-1">Message (HTML preview)</p>
                            <iframe id="email_log_iframe" title="Email HTML preview" sandbox="allow-same-origin"></iframe>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_includes.php'; ?>
<script>
$(function(){
    var table = $('#email_logs_table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: false,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ajax: {
            url: 'includes/email_logs_fetch.php',
            type: 'POST',
            data: function(d){
                d.filter_staff = $('#el_filter_staff').val();
                d.filter_category = $('#el_filter_category').val();
                d.filter_status = $('#el_filter_status').val();
                d.filter_enquiry = $('#el_filter_enquiry').val();
                d.filter_to = $('#el_filter_to').val();
                d.filter_date_from = $('#el_filter_date_from').val();
                d.filter_date_to = $('#el_filter_date_to').val();
            }
        },
        columns: [
            { data: 0 }, { data: 1 }, { data: 2 }, { data: 3 }, { data: 4 }, { data: 5 }, { data: 6 }, { data: 7, orderable: false, searchable: false }
        ],
        language: {
            processing: 'Loading…',
            emptyTable: 'No emails logged yet',
            zeroRecords: 'No matching records',
            info: 'Showing _START_ to _END_ of _TOTAL_',
            infoEmpty: 'Showing 0 to 0 of 0',
            infoFiltered: '(filtered from _MAX_)',
            paginate: { previous: '&laquo;', next: '&raquo;' }
        },
        drawCallback: function(){
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded mb-0');
        }
    });

    $('#el_filter_search').on('keyup', function(){
        table.search(this.value).draw();
    });

    $('#el_btn_apply').on('click', function(){ table.ajax.reload(); });
    $('#el_btn_reset').on('click', function(){
        $('#el_filter_date_from,#el_filter_date_to').val('');
        $('#el_filter_staff').val('0');
        $('#el_filter_category').val('-1');
        $('#el_filter_status').val('');
        $('#el_filter_enquiry,#el_filter_to,#el_filter_search').val('');
        table.search('').draw();
        table.ajax.reload();
    });

    $(document).on('click', '.btn-email-log-detail', function(){
        var id = $(this).data('id');
        if(!id) return;
        $('#emailLogDetailModal').modal('show');
        $('#email_log_detail_loading').removeClass('d-none').text('Loading…');
        $('#email_log_detail_wrap').addClass('d-none');
        $('#email_log_detail_meta').empty();
        $.getJSON('includes/email_log_detail.php', { id: id }, function(res){
            $('#email_log_detail_loading').addClass('d-none');
            if(!res || !res.ok || !res.row){
                $('#email_log_detail_loading').removeClass('d-none').text('Could not load this log.');
                return;
            }
            var r = res.row;
            var h = '';
            h += '<dt>Log ID</dt><dd>' + r.id + '</dd>';
            h += '<dt>Sent at</dt><dd>' + (r.created_at || '—') + '</dd>';
            h += '<dt>Status</dt><dd>' + (r.send_status === 'failed' ? '<span class="badge bg-danger">Failed</span>' : '<span class="badge bg-success">Sent</span>') + '</dd>';
            if(r.error_message) h += '<dt>Error</dt><dd class="text-danger">' + $('<div/>').text(r.error_message).html() + '</dd>';
            h += '<dt>To</dt><dd>' + $('<div/>').text(r.recipient_to || '').html() + '</dd>';
            h += '<dt>Subject</dt><dd>' + $('<div/>').text(r.subject || '').html() + '</dd>';
            h += '<dt>Category</dt><dd>' + $('<div/>').text(r.email_category || '').html() + '</dd>';
            h += '<dt>Sent by</dt><dd>' + (r.sent_by_user_name ? $('<div/>').text(r.sent_by_user_name).html() + ' (#' + (r.sent_by_user_id||'') + ')' : '<span class="text-muted">—</span>') + '</dd>';
            h += '<dt>Enquiry ID</dt><dd>' + (r.st_enquiry_id ? $('<div/>').text(r.st_enquiry_id).html() : '<span class="text-muted">—</span>') + '</dd>';
            h += '<dt>st_id</dt><dd>' + (r.st_id ? r.st_id : '<span class="text-muted">—</span>') + '</dd>';
            h += '<dt>Request URI</dt><dd class="small text-break">' + (r.request_uri ? $('<div/>').text(r.request_uri).html() : '—') + '</dd>';
            h += '<dt>IP</dt><dd>' + (r.ip_address ? $('<div/>').text(r.ip_address).html() : '—') + '</dd>';
            if(r.meta_json) h += '<dt>Meta (JSON)</dt><dd><pre class="small bg-light p-2 rounded mb-0" style="max-height:160px;overflow:auto;">' + $('<div/>').text(r.meta_json).html() + '</pre></dd>';
            $('#email_log_detail_meta').html(h);
            var doc = $('#email_log_iframe')[0].contentDocument || $('#email_log_iframe')[0].contentWindow.document;
            doc.open();
            doc.write(r.body_html || '<p class="p-3 text-muted">(empty body)</p>');
            doc.close();
            $('#email_log_detail_wrap').removeClass('d-none');
        }).fail(function(){
            $('#email_log_detail_loading').removeClass('d-none').text('Request failed.');
        });
    });
});
</script>
</body>
</html>
