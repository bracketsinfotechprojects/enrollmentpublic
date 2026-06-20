<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: index.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: invoices_create.php');
    exit;
}

$result = mysqli_query($connection, "SELECT * FROM enrolment_form_new WHERE id = $id LIMIT 1");
if (!$result || mysqli_num_rows($result) === 0) {
    header('Location: invoices_create.php');
    exit;
}
$r          = mysqli_fetch_assoc($result);
$enquiry_id = $r['enquiry_id'] ?? '';

$student_enrol_id = null;
$student_user_id  = null;
if ($enquiry_id !== '') {
    $enq_esc = mysqli_real_escape_string($connection, $enquiry_id);
    $enq_res = mysqli_query($connection,
        "SELECT st_id, student_user_id FROM student_enquiry WHERE st_enquiry_id = '$enq_esc' LIMIT 1"
    );
    if ($enq_res && mysqli_num_rows($enq_res) > 0) {
        $enq_row          = mysqli_fetch_assoc($enq_res);
        $student_enrol_id = $enq_row['st_id'];
        $student_user_id  = $enq_row['student_user_id'];
    }
}

function val($v, $fallback = '—') {
    return (isset($v) && $v !== '' && $v !== null) ? htmlspecialchars($v) : $fallback;
}

$gender_map = [1 => 'Male', 2 => 'Female', 3 => 'Other'];

// Courses
$courses_arr = json_decode($r['courses'] ?? '[]', true) ?: [];
$course_rows = [];
if (!empty($courses_arr)) {
    $in = implode(',', array_map('intval', $courses_arr));
    $cr = mysqli_query($connection, "SELECT course_id, course_sname, course_name FROM courses WHERE course_id IN ($in)");
    while ($c = mysqli_fetch_assoc($cr)) $course_rows[] = $c;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create Invoice – <?php echo val($r['given_name'] . ' ' . $r['surname']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .section-header {
            background-color: #d4edda;
            font-weight: 700;
            font-size: 0.92rem;
            padding: 7px 12px;
            border: 1px solid #aed4b5;
            margin-bottom: 0;
        }
        .section-body {
            border: 1px solid #aed4b5;
            border-top: none;
            padding: 14px 16px;
            margin-bottom: 18px;
            background: #fff;
        }
        .field-label { font-size: 0.78rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
        .field-value { font-size: 0.92rem; color: #212529; }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content">
            <div class="container-fluid">

                <!-- Page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Create Invoice</h4>
                            <div class="page-title-right d-flex gap-2 align-items-center">
                                <a href="invoices_create.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="mdi mdi-arrow-left me-1"></i>Back to List
                                </a>
                                <ol class="breadcrumb m-0 ms-2">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item"><a href="invoices_create.php">Invoices</a></li>
                                    <li class="breadcrumb-item active">Create</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Student header card -->
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo val(trim($r['given_name'] . ' ' . $r['surname'])); ?></h5>
                                <p class="mb-1 text-muted">
                                    <strong>Student ID:</strong>
                                    <span class="badge bg-primary fs-12"><?php echo val($r['office_student_id']); ?></span>
                                </p>
                                <p class="mb-0 text-muted" style="font-size:0.82rem;">
                                    Enrolled on <?php echo date('d M Y', strtotime($r['created_at'])); ?>
                                    &nbsp;<span class="badge bg-success">Complete</span>
                                </p>
                            </div>
                            <img src="assets/images/logo-dark.webp" alt="NCA" height="50" onerror="this.style.display='none'">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left: student + course details -->
                    <div class="col-lg-12">

                        <!-- STUDENT BASIC DETAILS -->
                        <div class="section-header">STUDENT DETAILS</div>
                        <div class="section-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="field-label">First Name</div>
                                    <div class="field-value"><?php echo val($r['given_name']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Last Name</div>
                                    <div class="field-value"><?php echo val($r['surname']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Student ID</div>
                                    <div class="field-value"><?php echo val($r['office_student_id']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Date of Birth</div>
                                    <div class="field-value"><?php echo $r['dob'] ? date('d/m/Y', strtotime($r['dob'])) : '—'; ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Gender</div>
                                    <div class="field-value"><?php echo val($gender_map[$r['gender_check']] ?? '—'); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">USI</div>
                                    <div class="field-value"><?php echo val($r['usi_id']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Email</div>
                                    <div class="field-value"><?php echo val($r['email_address']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Mobile</div>
                                    <div class="field-value"><?php echo val($r['mobile_num']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Home Phone</div>
                                    <div class="field-value"><?php echo val($r['home_phone']); ?></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="field-label">Address</div>
                                    <div class="field-value">
                                        <?php
                                        $addr = array_filter([
                                            $r['street_details'],
                                            $r['sub_urb'],
                                            $r['stu_state'],
                                            $r['post_code'],
                                        ]);
                                        echo $addr ? htmlspecialchars(implode(', ', $addr)) : '—';
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-label">Birth Country</div>
                                    <div class="field-value"><?php echo val($r['birth_country']); ?></div>
                                </div>
                                <div class="col-md-3">
                                    <div class="field-label">Enquiry ID</div>
                                    <div class="field-value"><?php echo val($r['enquiry_id']); ?></div>
                                </div>
                            </div>
                        </div>

                        <!-- COURSE DETAILS -->
                        <div class="section-header">COURSE DETAILS</div>
                        <div class="section-body">
                            <?php if (!empty($course_rows)): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Course Code</th>
                                            <th>Course Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($course_rows as $i => $c): ?>
                                        <tr>
                                            <td><?php echo $i + 1; ?></td>
                                            <td><?php echo htmlspecialchars($c['course_sname']); ?></td>
                                            <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted mb-0">No courses found for this enrolment.</p>
                            <?php endif; ?>
                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <div class="field-label">Mode of Delivery</div>
                                    <div class="field-value"><?php echo val($r['mode_delivery']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">RTO</div>
                                    <div class="field-value"><?php echo val($r['rto_name']); ?></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="field-label">Branch</div>
                                    <div class="field-value"><?php echo val($r['branch_name']); ?></div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Installments form -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Invoice Installments</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                                        <i class="mdi mdi-plus me-1"></i>Add Installment
                                    </button>
                                </div>

                                <form id="invoice-form">
                                    <input type="hidden"  name="enrolment_id"    value="<?php echo $r['id']; ?>">
                                    <input type="hidden" name="student_user_id" value="<?php echo htmlspecialchars($student_user_id ?? ''); ?>">

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0" id="installment-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="min-width:160px">Course <span class="text-danger">*</span></th>
                                                    <th style="min-width:130px">Invoice Type <span class="text-danger">*</span></th>
                                                    <th style="min-width:120px">Funding Type <span class="text-danger">*</span></th>
                                                    <th style="min-width:100px">Currency <span class="text-danger">*</span></th>
                                                    <th style="min-width:120px">Amount <span class="text-danger">*</span></th>
                                                    <th style="min-width:110px">Tax Amount</th>
                                                    <th style="min-width:130px">Issue Date <span class="text-danger">*</span></th>
                                                    <th style="min-width:130px">Due Date <span class="text-danger">*</span></th>
                                                    <th style="width:46px"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="installment-body">
                                                <!-- rows injected by JS -->
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-light fw-semibold">
                                                    <td colspan="4" class="text-end">Totals</td>
                                                    <td id="total-amount">$0.00</td>
                                                    <td id="total-gst">$0.00</td>
                                                    <td colspan="3"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                        <div id="amount-limit-alert" class="alert alert-danger d-none mt-2 mb-0 py-2">
                                            <i class="ti ti-alert-circle me-1"></i>
                                            Total installment amount (excl. tax) cannot exceed <strong>$1,500.00</strong>.
                                            Current total: <strong id="amount-limit-current"></strong>
                                        </div>
                                        <div class="text-muted mt-1" style="font-size:0.8rem;">
                                            <i class="ti ti-info-circle me-1"></i>Maximum total amount (excl. tax): <strong>$1,500.00</strong>
                                        </div>
                                    </div>

                                    <div class="mt-3 d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary px-4" onclick="submitInvoice()">
                                            <i class="mdi mdi-file-document-outline me-1"></i> Create Invoice
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div> <!-- content -->
    </div> <!-- page-wrapper -->
</div> <!-- main-wrapper -->

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>

<script>
const COURSES = <?php echo json_encode(array_map(function($c) {
    return ['id' => $c['course_id'], 'label' => $c['course_sname'] . ' – ' . $c['course_name']];
}, $course_rows)); ?>;

const INVOICE_TYPES  = ['Course Fee', 'Tuition Fee', 'Registration Fee', 'RPL/CT Application Fee', 'Material Fee', 'Re-assessment Fee', 'RPL', 'Other'];
const FUNDING_TYPES  = ['Fee-for-Service', 'Government Funded', 'Apprenticeship/Traineeship', 'International Student', 'VET Student Loan', 'Employer Funded', 'Other'];
const CURRENCIES     = ['AUD $', 'AUD', 'USD', 'GBP', 'EUR', 'NZD'];

let rowCount = 0;

function buildSelect(name, options, valueKey, labelKey) {
    let html = `<select class="form-control form-control-sm" name="${name}" required>`;
    html += `<option value="">Select</option>`;
    options.forEach(o => {
        const v = valueKey ? o[valueKey] : o;
        const l = labelKey ? o[labelKey] : o;
        html += `<option value="${v}">${l}</option>`;
    });
    html += `</select>`;
    return html;
}

function addRow() {
    const idx = rowCount++;
    const tbody = document.getElementById('installment-body');
    const tr = document.createElement('tr');
    tr.id = `row-${idx}`;

    let courseOpts = `<option value="">Select</option>`;
    COURSES.forEach(c => courseOpts += `<option value="${c.id}">${c.label}</option>`);

    let typeOpts   = `<option value="">Select</option>` + INVOICE_TYPES.map(t => `<option>${t}</option>`).join('');
    let fundOpts   = `<option value="">Select</option>` + FUNDING_TYPES.map(t => `<option>${t}</option>`).join('');
    let currOpts   = CURRENCIES.map((c, i) => `<option${i===0?' selected':''}>${c}</option>`).join('');

    tr.innerHTML = `
        <td><select class="form-control form-control-sm" name="installments[${idx}][course_id]" required>${courseOpts}</select></td>
        <td><select class="form-control form-control-sm" name="installments[${idx}][invoice_type]" required>${typeOpts}</select></td>
        <td><select class="form-control form-control-sm" name="installments[${idx}][funding_type]" required>${fundOpts}</select></td>
        <td><select class="form-control form-control-sm" name="installments[${idx}][currency]">${currOpts}</select></td>
        <td><input type="number" class="form-control form-control-sm amount-input" name="installments[${idx}][amount]" placeholder="0.00" step="0.01" min="0" required oninput="recalcTotals()"></td>
        <td><input type="number" class="form-control form-control-sm gst-input"    name="installments[${idx}][gst_amount]" placeholder="0.00" step="0.01" min="0" oninput="recalcTotals()"></td>
        <td><input type="date"   class="form-control form-control-sm" name="installments[${idx}][issue_date]" required></td>
        <td><input type="date"   class="form-control form-control-sm" name="installments[${idx}][due_date]"   required></td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(${idx})" title="Remove">
                <i class="ti ti-trash"></i>
            </button>
        </td>`;

    tbody.appendChild(tr);
    recalcTotals();
}

function removeRow(idx) {
    const tr = document.getElementById(`row-${idx}`);
    if (tr) tr.remove();
    recalcTotals();
    if (document.getElementById('installment-body').children.length === 0) rowCount = 0;
}

const AMOUNT_LIMIT = 1500;

function recalcTotals() {
    let sumAmt = 0, sumGst = 0;
    document.querySelectorAll('.amount-input').forEach(el => sumAmt += parseFloat(el.value) || 0);
    document.querySelectorAll('.gst-input').forEach(el  => sumGst += parseFloat(el.value) || 0);

    const amtEl    = document.getElementById('total-amount');
    const alertEl  = document.getElementById('amount-limit-alert');
    const curEl    = document.getElementById('amount-limit-current');
    const exceeded = sumAmt > AMOUNT_LIMIT;

    amtEl.textContent    = '$' + sumAmt.toFixed(2);
    amtEl.style.color    = exceeded ? '#dc3545' : '';
    amtEl.style.fontWeight = exceeded ? '700' : '';
    document.getElementById('total-gst').textContent = '$' + sumGst.toFixed(2);

    if (exceeded) {
        curEl.textContent = '$' + sumAmt.toFixed(2);
        alertEl.classList.remove('d-none');
    } else {
        alertEl.classList.add('d-none');
    }
}

function submitInvoice() {
    const tbody = document.getElementById('installment-body');
    const rows  = tbody.querySelectorAll('tr');
    if (rows.length === 0) {
        alert('Please add at least one installment.');
        return;
    }

    let sumAmt = 0;
    tbody.querySelectorAll('.amount-input').forEach(el => sumAmt += parseFloat(el.value) || 0);
    if (sumAmt > AMOUNT_LIMIT) {
        document.getElementById('amount-limit-alert').classList.remove('d-none');
        document.getElementById('amount-limit-current').textContent = '$' + sumAmt.toFixed(2);
        document.getElementById('total-amount').scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }

    let valid = true;
    tbody.querySelectorAll('[required]').forEach(el => {
        if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
        else el.classList.remove('is-invalid');
    });
    if (!valid) { alert('Please fill in all required fields.'); return; }

    // Collect installments into a plain array
    const installments = [];
    rows.forEach(tr => {
        const get = name => (tr.querySelector(`[name*="${name}"]`) || {}).value || '';
        installments.push({
            course_id:    get('course_id'),
            invoice_type: get('invoice_type'),
            funding_type: get('funding_type'),
            currency:     get('currency'),
            amount:       get('amount'),
            gst_amount:   get('gst_amount'),
            issue_date:   get('issue_date'),
            due_date:     get('due_date'),
        });
    });

    const data = new FormData();
    data.append('enrolment_id',      document.querySelector('[name="enrolment_id"]').value);
    data.append('student_user_id',   document.querySelector('[name="student_user_id"]').value);
    data.append('installments_json', JSON.stringify(installments));

    fetch('invoice_save.php', { method: 'POST', body: data })
        .then(r => r.text())
        .then(text => {
            let res;
            try { res = JSON.parse(text); } catch(e) {
                console.error('Server response:', text);
                alert('Server error:\n' + text.substring(0, 300));
                return;
            }
            if (res.success) {
                window.location.href = 'invoice_view.php?id=' + res.invoice_id;
            } else {
                alert('Error: ' + (res.message || 'Could not create invoice.'));
            }
        })
        .catch(err => alert('Request failed: ' + err.message));
}

// Start with one row
addRow();
</script>
</body>
</html>
