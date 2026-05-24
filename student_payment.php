<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php');
    exit;
}
$ut = @$_SESSION['user_type'];
if ($ut !== 0 && $ut !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$student_user_id = intval($_SESSION['user_id']);

// Get student email & name
$student_email = '';
$student_name  = $_SESSION['user_name'] ?? '';
$suRes = mysqli_query($connection,
    "SELECT email, full_name FROM student_users WHERE id = $student_user_id LIMIT 1"
);
if ($suRes && mysqli_num_rows($suRes) > 0) {
    $su = mysqli_fetch_assoc($suRes);
    $student_email = $su['email'];
    $student_name  = $su['full_name'] ?: $student_name;
}

// Get completed enrolment record
$enrolment = null;
if ($student_email !== '') {
    $esc_email = mysqli_real_escape_string($connection, $student_email);
    $enrRes = mysqli_query($connection,
        "SELECT * FROM enrolment_form_new
         WHERE email_address = '$esc_email' AND status = 'complete'
         ORDER BY id DESC LIMIT 1"
    );
    if ($enrRes && mysqli_num_rows($enrRes) > 0) {
        $enrolment = mysqli_fetch_assoc($enrRes);
    }
}

if (!$enrolment) {
    header('Location: student_docs.php');
    exit;
}

$student_full_name  = trim(($enrolment['given_name'] ?? '') . ' ' . ($enrolment['surname'] ?? '')) ?: $student_name;
$student_id_display = $enrolment['office_student_id'] ?? '—';
$enrolment_ref      = $enrolment['enquiry_id'] ?? '';
$course_name        = ''; // pulled from details JSON if available
if (!empty($enrolment['details'])) {
    $det = json_decode($enrolment['details'], true);
    if (isset($det['courses']) && is_array($det['courses'])) {
        $course_ids = array_map('intval', $det['courses']);
        if ($course_ids) {
            $cids = implode(',', $course_ids);
            $cRes = mysqli_query($connection, "SELECT course_sname, course_name FROM courses WHERE course_id IN ($cids) LIMIT 1");
            if ($cRes && mysqli_num_rows($cRes) > 0) {
                $cr = mysqli_fetch_assoc($cRes);
                $course_name = $cr['course_sname'] . ' – ' . $cr['course_name'];
            }
        }
    }
}

// Ensure student_invoices table exists
mysqli_query($connection, "CREATE TABLE IF NOT EXISTS student_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrolment_db_id INT NOT NULL,
    invoice_number VARCHAR(50) NOT NULL,
    student_name VARCHAR(200),
    student_id VARCHAR(100),
    email_address VARCHAR(200),
    course_enrolled TEXT,
    enrolment_ref VARCHAR(100),
    branch VARCHAR(100),
    issue_date DATE,
    due_date DATE,
    payment_terms VARCHAR(50),
    invoice_type VARCHAR(100),
    payment_method VARCHAR(100),
    funding_type VARCHAR(100),
    currency VARCHAR(10) DEFAULT 'AUD',
    line_items JSON,
    subtotal DECIMAL(10,2) DEFAULT 0,
    gst_amount DECIMAL(10,2) DEFAULT 0,
    discount DECIMAL(10,2) DEFAULT 0,
    total_due DECIMAL(10,2) DEFAULT 0,
    status VARCHAR(30) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Auto-generate invoice number
$inv_prefix   = 'NCA-' . date('Y') . '-';
$lastRes      = mysqli_query($connection, "SELECT invoice_number FROM student_invoices ORDER BY id DESC LIMIT 1");
$next_num     = 1;
if ($lastRes && mysqli_num_rows($lastRes) > 0) {
    $last = mysqli_fetch_assoc($lastRes)['invoice_number'] ?? '';
    if (preg_match('/(\d+)$/', $last, $m)) {
        $next_num = intval($m[1]) + 1;
    }
}
$invoice_number = $inv_prefix . str_pad($next_num, 3, '0', STR_PAD_LEFT);
$issue_date     = date('Y-m-d');
$due_date       = date('Y-m-d', strtotime('+30 days'));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Make Payment – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .section-card-header {
            background: #1e3a5f;
            color: #fff;
            border-radius: 6px 6px 0 0;
            padding: 14px 20px;
        }
        .section-card-header .sub-title {
            font-size: 0.8rem;
            opacity: .75;
            margin: 0;
        }
        .section-card-header h6 { margin: 0; font-weight: 600; font-size: 1rem; }
        .section-card-header i { font-size: 1.3rem; }
        .invoice-label {
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #6c757d;
            margin-bottom: 4px;
        }
        .tab-nav { border-bottom: 2px solid #dee2e6; margin-bottom: 24px; }
        .tab-nav .nav-link {
            color: #495057;
            border: none;
            padding: 10px 18px;
            font-size: 0.875rem;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
        }
        .tab-nav .nav-link.active {
            color: #0d6efd;
            border-bottom-color: #0d6efd;
            background: transparent;
        }
        .tab-nav .nav-link i { margin-right: 5px; }
        .line-items-table th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            background: #f8f9fa;
        }
        .line-items-table td { vertical-align: middle; }
        .totals-box { min-width: 280px; }
        .totals-box .row { padding: 4px 0; font-size: 0.9rem; }
        .totals-box .total-due { font-size: 1.2rem; font-weight: 700; color: #0d6efd; }
        .btn-add-line { font-size: 0.85rem; }
        .remove-line-btn { color: #dc3545; background: none; border: none; font-size: 1.1rem; cursor: pointer; }
        .remove-line-btn:hover { color: #a71d2a; }
        input[readonly] { background-color: #f8f9fa; }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <!-- Page Title -->
                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Make Payment</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="student_invoice.php">Invoices</a></li>
                                    <li class="breadcrumb-item active">Make Payment</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Nav -->
                <ul class="nav tab-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="student_invoice.php">
                            <i class="ti ti-layout-dashboard"></i>All Invoices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="student_payment.php">
                            <i class="ti ti-plus"></i>Make Payment
                        </a>
                    </li>
                </ul>

                <form id="paymentForm">

                <!-- ── Student & Invoice Details ─────────────────────────── -->
                <div class="card mb-4 p-0">
                    <div class="section-card-header d-flex align-items-center gap-3">
                        <div style="background:rgba(255,255,255,.15);border-radius:6px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="ti ti-user-circle"></i>
                        </div>
                        <div>
                            <h6>Student &amp; Invoice Details</h6>
                            <p class="sub-title">Link this payment to your enrolment</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="invoice-label">Student Name <span class="text-danger">*</span></div>
                                <input type="text" class="form-control" name="student_name"
                                       value="<?php echo htmlspecialchars($student_full_name); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <div class="invoice-label">Student ID</div>
                                <input type="text" class="form-control" name="student_id"
                                       value="<?php echo htmlspecialchars($student_id_display); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <div class="invoice-label">Email Address <span class="text-danger">*</span></div>
                                <input type="email" class="form-control" name="email_address"
                                       value="<?php echo htmlspecialchars($student_email); ?>" readonly>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="invoice-label">Course Enrolled <span class="text-danger">*</span></div>
                                <input type="text" class="form-control" name="course_enrolled"
                                       value="<?php echo htmlspecialchars($course_name ?: 'Not specified'); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <div class="invoice-label">Enrolment ID</div>
                                <input type="text" class="form-control" name="enrolment_id"
                                       value="<?php echo htmlspecialchars($enrolment_ref); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <div class="invoice-label">Branch</div>
                                <select class="form-select" name="branch">
                                    <option value="Adelaide" <?php echo ($enrolment['branch'] ?? '') === 'Adelaide' ? 'selected' : ''; ?>>Adelaide</option>
                                    <option value="Sydney">Sydney</option>
                                    <option value="Melbourne">Melbourne</option>
                                    <option value="Brisbane">Brisbane</option>
                                    <option value="Perth">Perth</option>
                                    <option value="Online">Online</option>
                                </select>
                            </div>
                        </div>

                        <!-- Invoice Settings -->
                        <hr class="my-4">
                        <p class="invoice-label mb-3" style="font-size:0.8rem;letter-spacing:.06em;color:#1e3a5f;">INVOICE SETTINGS</p>
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="invoice-label">Invoice # <span class="text-danger">*</span></div>
                                <input type="text" class="form-control" name="invoice_number"
                                       id="invoice_number"
                                       value="<?php echo htmlspecialchars($invoice_number); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Issue Date <span class="text-danger">*</span></div>
                                <input type="date" class="form-control" name="issue_date"
                                       value="<?php echo $issue_date; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Due Date <span class="text-danger">*</span></div>
                                <input type="date" class="form-control" name="due_date"
                                       value="<?php echo $due_date; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Payment Terms</div>
                                <select class="form-select" name="payment_terms">
                                    <option value="Net 7">Net 7</option>
                                    <option value="Net 14">Net 14</option>
                                    <option value="Net 30" selected>Net 30</option>
                                    <option value="Net 60">Net 60</option>
                                    <option value="Due on Receipt">Due on Receipt</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="invoice-label">Invoice Type</div>
                                <select class="form-select" name="invoice_type">
                                    <option value="Tuition Fee">Tuition Fee</option>
                                    <option value="Resource Fee">Resource Fee</option>
                                    <option value="Material Fee">Material Fee</option>
                                    <option value="Enrolment Fee">Enrolment Fee</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Payment Method</div>
                                <select class="form-select" name="payment_method">
                                    <option value="Bank Transfer (EFT)">Bank Transfer (EFT)</option>
                                    <option value="Credit Card">Credit Card</option>
                                    <option value="Debit Card">Debit Card</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Cheque">Cheque</option>
                                    <option value="Online Payment">Online Payment</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Funding Type</div>
                                <select class="form-select" name="funding_type">
                                    <option value="Fee-for-Service">Fee-for-Service</option>
                                    <option value="Government Funded">Government Funded</option>
                                    <option value="VET Student Loan">VET Student Loan</option>
                                    <option value="Scholarship">Scholarship</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="invoice-label">Currency</div>
                                <select class="form-select" name="currency">
                                    <option value="AUD" selected>AUD $</option>
                                    <option value="USD">USD $</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Line Items ──────────────────────────────────────────── -->
                <div class="card mb-4 p-0">
                    <div class="section-card-header d-flex align-items-center gap-3">
                        <div style="background:rgba(255,255,255,.15);border-radius:6px;width:40px;height:40px;display:flex;align-items:center;justify-content:center;">
                            <i class="ti ti-list-details"></i>
                        </div>
                        <div>
                            <h6>Line Items</h6>
                            <p class="sub-title">Add fees, materials and other charges</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table line-items-table align-middle" id="lineItemsTable">
                                <thead>
                                    <tr>
                                        <th style="min-width:260px;">Description</th>
                                        <th style="min-width:140px;">Unit</th>
                                        <th style="width:80px;">QTY</th>
                                        <th style="width:120px;">Unit Price</th>
                                        <th style="width:90px;">GST?</th>
                                        <th style="width:120px;">Amount</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="lineItemsBody">
                                    <tr class="line-item-row">
                                        <td><input type="text" class="form-control form-control-sm" name="li_desc[]" placeholder="e.g. Tuition Fee"></td>
                                        <td>
                                            <select class="form-select form-select-sm" name="li_unit[]">
                                                <option value="Course">Course</option>
                                                <option value="Item">Item</option>
                                                <option value="Hour">Hour</option>
                                                <option value="Session">Session</option>
                                                <option value="Month">Month</option>
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control form-control-sm li-qty" name="li_qty[]" value="1" min="1" step="1"></td>
                                        <td><input type="number" class="form-control form-control-sm li-price" name="li_price[]" value="0" min="0" step="0.01"></td>
                                        <td>
                                            <select class="form-select form-select-sm li-gst" name="li_gst[]">
                                                <option value="Yes">Yes</option>
                                                <option value="No">No</option>
                                            </select>
                                        </td>
                                        <td><input type="text" class="form-control form-control-sm li-amount" name="li_amount[]" value="0.00" readonly></td>
                                        <td><button type="button" class="remove-line-btn" onclick="removeLineItem(this)" title="Remove"><i class="ti ti-x"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <button type="button" class="btn btn-outline-primary btn-add-line mt-1" onclick="addLineItem()">
                            <i class="ti ti-plus me-1"></i>+ Add Line Item
                        </button>

                        <!-- Totals -->
                        <div class="d-flex justify-content-end mt-4">
                            <div class="totals-box">
                                <div class="row justify-content-between">
                                    <div class="col-auto text-muted">Subtotal</div>
                                    <div class="col-auto fw-semibold" id="subtotalDisplay">$0.00</div>
                                </div>
                                <div class="row justify-content-between">
                                    <div class="col-auto text-muted">GST (10%)</div>
                                    <div class="col-auto fw-semibold" id="gstDisplay">$0.00</div>
                                </div>
                                <div class="row justify-content-between align-items-center">
                                    <div class="col-auto text-muted">Discount</div>
                                    <div class="col-auto">
                                        <input type="number" id="discountInput" name="discount" class="form-control form-control-sm text-end"
                                               style="width:90px;" value="0" min="0" step="0.01" oninput="recalculate()">
                                    </div>
                                </div>
                                <hr class="my-2">
                                <div class="row justify-content-between">
                                    <div class="col-auto fw-bold">Total Due</div>
                                    <div class="col-auto total-due" id="totalDueDisplay">$0.00</div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="student_invoice.php" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4" id="submitPaymentBtn">
                                <i class="ti ti-send me-1"></i>Submit Payment Request
                            </button>
                        </div>
                    </div>
                </div>

                </form>

            </div>
        </div>
    </div>
</div>

<?php include('includes/footer_includes.php'); ?>
<script>
// ── Line item template ────────────────────────────────────────────
function lineItemHTML() {
    return `<tr class="line-item-row">
        <td><input type="text" class="form-control form-control-sm" name="li_desc[]" placeholder="e.g. Resource Fee"></td>
        <td>
            <select class="form-select form-select-sm" name="li_unit[]">
                <option value="Course">Course</option>
                <option value="Item">Item</option>
                <option value="Hour">Hour</option>
                <option value="Session">Session</option>
                <option value="Month">Month</option>
            </select>
        </td>
        <td><input type="number" class="form-control form-control-sm li-qty" name="li_qty[]" value="1" min="1" step="1"></td>
        <td><input type="number" class="form-control form-control-sm li-price" name="li_price[]" value="0" min="0" step="0.01"></td>
        <td>
            <select class="form-select form-select-sm li-gst" name="li_gst[]">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
        </td>
        <td><input type="text" class="form-control form-control-sm li-amount" name="li_amount[]" value="0.00" readonly></td>
        <td><button type="button" class="remove-line-btn" onclick="removeLineItem(this)" title="Remove"><i class="ti ti-x"></i></button></td>
    </tr>`;
}

function addLineItem() {
    document.getElementById('lineItemsBody').insertAdjacentHTML('beforeend', lineItemHTML());
    bindRowEvents();
    recalculate();
}

function removeLineItem(btn) {
    var rows = document.querySelectorAll('#lineItemsBody .line-item-row');
    if (rows.length === 1) { return; } // keep at least one
    btn.closest('.line-item-row').remove();
    recalculate();
}

// ── Recalculate totals ────────────────────────────────────────────
function recalculate() {
    var subtotal = 0, gstTotal = 0;
    document.querySelectorAll('#lineItemsBody .line-item-row').forEach(function(row) {
        var qty   = parseFloat(row.querySelector('.li-qty').value)   || 0;
        var price = parseFloat(row.querySelector('.li-price').value) || 0;
        var gst   = row.querySelector('.li-gst').value === 'Yes';
        var amt   = qty * price;
        row.querySelector('.li-amount').value = amt.toFixed(2);
        subtotal += amt;
        if (gst) gstTotal += amt * 0.10;
    });
    var discount = parseFloat(document.getElementById('discountInput').value) || 0;
    var total    = subtotal + gstTotal - discount;
    document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('gstDisplay').textContent      = '$' + gstTotal.toFixed(2);
    document.getElementById('totalDueDisplay').textContent = '$' + Math.max(0, total).toFixed(2);
}

function bindRowEvents() {
    document.querySelectorAll('#lineItemsBody .line-item-row').forEach(function(row) {
        ['li-qty', 'li-price'].forEach(function(cls) {
            var el = row.querySelector('.' + cls);
            if (el && !el.dataset.bound) {
                el.dataset.bound = '1';
                el.addEventListener('input', recalculate);
            }
        });
        var gstEl = row.querySelector('.li-gst');
        if (gstEl && !gstEl.dataset.bound) {
            gstEl.dataset.bound = '1';
            gstEl.addEventListener('change', recalculate);
        }
    });
}

// ── Form submit ───────────────────────────────────────────────────
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('submitPaymentBtn');

    // Basic validation
    var desc = document.querySelectorAll('input[name="li_desc[]"]');
    var valid = true;
    desc.forEach(function(d) { if (!d.value.trim()) { d.classList.add('is-invalid'); valid = false; } else { d.classList.remove('is-invalid'); } });
    if (!valid) { return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Submitting…';

    var fd = new FormData(this);
    fd.append('formName',        'submit_student_payment');
    fd.append('enrolment_db_id', '<?php echo intval($enrolment['id']); ?>');
    fd.append('subtotal',        document.getElementById('subtotalDisplay').textContent.replace('$',''));
    fd.append('gst_amount',      document.getElementById('gstDisplay').textContent.replace('$',''));
    fd.append('total_due',       document.getElementById('totalDueDisplay').textContent.replace('$',''));

    fetch('includes/datacontrol.php', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                Toastify({ text: 'Payment request submitted successfully!', duration: 3000, backgroundColor: '#28a745' }).showToast();
                setTimeout(function() { window.location.href = 'student_invoice.php'; }, 1500);
            } else {
                Toastify({ text: 'Error: ' + (res.message || 'Failed to submit'), duration: 4000, backgroundColor: '#dc3545' }).showToast();
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-send me-1"></i>Submit Payment Request';
            }
        })
        .catch(function() {
            Toastify({ text: 'Network error. Please try again.', duration: 4000, backgroundColor: '#dc3545' }).showToast();
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-send me-1"></i>Submit Payment Request';
        });
});

// Init
document.addEventListener('DOMContentLoaded', function() {
    bindRowEvents();
    recalculate();
});
</script>
</body>
</html>
