<?php
include('includes/dbconnect.php');
session_start();

if (@$_SESSION['user_type'] != '' && @$_SESSION['user_type'] != 1) {
    header('Location: index.php'); exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: assessment_new.php'); exit; }

$row = mysqli_fetch_assoc(mysqli_query($connection, "SELECT * FROM `assessment` WHERE assessment_id = $id LIMIT 1"));
if (!$row) { header('Location: assessment_new.php'); exit; }

$error = '';
$status_map     = ['draft' => 0, 'active' => 1, 'inactive' => 2];
$status_reverse = [0 => 'draft', 1 => 'active', 2 => 'inactive'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['form_name']     ?? '');
    $description   = trim($_POST['description']   ?? '');
    $status_str    = 'draft';
    $marks         = intval($_POST['total_marks']   ?? 0);
    $passing_marks = intval($_POST['passing_marks'] ?? 0);
    $duration      = intval($_POST['duration']      ?? 0);
    $status_int    = $status_map[$status_str] ?? 0;

    if ($name === '') {
        $error = 'Assessment name is required.';
    } elseif (strlen($name) > 50) {
        $error = 'Assessment name must be 50 characters or fewer.';
    } elseif ($passing_marks > $marks && $marks > 0) {
        $error = 'Passing marks cannot exceed total marks.';
    } else {
        $esc_name = mysqli_real_escape_string($connection, $name);
        $esc_desc = mysqli_real_escape_string($connection, $description);

        $upd = mysqli_query($connection,
            "UPDATE `assessment`
             SET assessment_name = '$esc_name',
                 description     = '$esc_desc',
                 marks           = $marks,
                 passing_marks   = $passing_marks,
                 duration        = $duration,
                 status          = $status_int
             WHERE assessment_id = $id"
        );

        if ($upd) {
            header('Location: assessment_new.php?updated=1');
            exit;
        } else {
            $error = 'Database error: ' . mysqli_error($connection);
        }
    }

    // Re-populate $row with POST values on error
    $row['assessment_name'] = $_POST['form_name']     ?? $row['assessment_name'];
    $row['description']     = $_POST['description']   ?? $row['description'];
    $row['marks']           = $_POST['total_marks']   ?? $row['marks'];
    $row['passing_marks']   = $_POST['passing_marks'] ?? $row['passing_marks'];
    $row['duration']        = $_POST['duration']      ?? $row['duration'];
    $row['status']          = $status_map[$_POST['status'] ?? ''] ?? $row['status'];
}

$current_status = $status_reverse[intval($row['status'])] ?? 'draft';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Assessment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .cf-wrap { max-width:680px; margin:48px auto; padding:0 16px; }
        .cf-card { background:#161b27; border:1px solid #1e2535; border-radius:12px; overflow:hidden; }
        .cf-card-body { padding:28px; display:flex; flex-direction:column; gap:22px; }
        .cf-field label { display:block; color:#cbd5e1; font-size:.875rem; font-weight:600; margin-bottom:8px; }
        .cf-field label .req { color:#ef4444; margin-left:3px; }
        .cf-input, .cf-textarea, .cf-select {
            width:100%; background:#1e2535; border:1px solid #2d3348; color:#e2e8f0;
            border-radius:8px; padding:11px 16px; font-size:.9rem; outline:none;
            transition:border-color .15s; box-sizing:border-box;
        }
        .cf-input::placeholder, .cf-textarea::placeholder { color:#475569; }
        .cf-input:focus, .cf-textarea:focus, .cf-select:focus { border-color:#635bff; }
        .cf-textarea { resize:vertical; min-height:110px; }
        .cf-select { appearance:none; cursor:pointer; }
        .cf-select option { background:#1e2535; }
        .cf-select-wrap { position:relative; }
        .cf-select-wrap::after {
            content:'▾'; position:absolute; right:14px; top:50%;
            transform:translateY(-50%); color:#64748b; pointer-events:none; font-size:.85rem;
        }
        .cf-row { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .cf-hint { color:#475569; font-size:.78rem; margin-top:5px; }
        .cf-actions { display:flex; gap:12px; align-items:center; padding:0 28px 28px; }
        .cf-btn-save {
            background:#635bff; color:#fff; border:none; border-radius:8px;
            padding:11px 24px; font-weight:600; font-size:.9rem; cursor:pointer;
            display:inline-flex; align-items:center; gap:8px; transition:background .15s;
        }
        .cf-btn-save:hover { background:#4f46e5; }
        .cf-btn-cancel {
            background:transparent; color:#94a3b8; border:1px solid #2d3348;
            border-radius:8px; padding:10px 22px; font-size:.9rem; font-weight:500;
            cursor:pointer; text-decoration:none; transition:background .15s, color .15s;
        }
        .cf-btn-cancel:hover { background:#1e2535; color:#e2e8f0; }
        .cf-error {
            background:#2d1515; border:1px solid #7f1d1d; color:#fca5a5;
            border-radius:8px; padding:11px 16px; font-size:.875rem;
        }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content">
            <div class="container-fluid">

                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Edit Assessment</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="assessment_new.php">Assessment</a></li>
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <div class="cf-wrap">
                    <form method="POST">
                        <div class="cf-card">
                            <div class="cf-card-body">

                                <?php if ($error): ?>
                                <div class="cf-error"><?php echo htmlspecialchars($error); ?></div>
                                <?php endif; ?>

                                <div class="cf-field">
                                    <label>Assessment Name <span class="req">*</span></label>
                                    <input type="text" name="form_name" class="cf-input"
                                           placeholder="e.g. Midterm Assessment"
                                           value="<?php echo htmlspecialchars($row['assessment_name']); ?>"
                                           maxlength="50" autofocus required>
                                </div>

                                <div class="cf-field">
                                    <label>Description</label>
                                    <textarea name="description" class="cf-textarea"
                                              placeholder="Optional description..."><?php echo htmlspecialchars($row['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="cf-row">
                                    <div class="cf-field">
                                        <label>Total Marks</label>
                                        <input type="number" name="total_marks" class="cf-input" min="0"
                                               placeholder="e.g. 100"
                                               value="<?php echo intval($row['marks']) ?: ''; ?>">
                                    </div>
                                    <div class="cf-field">
                                        <label>Passing Marks</label>
                                        <input type="number" name="passing_marks" class="cf-input" min="0"
                                               placeholder="e.g. 40"
                                               value="<?php echo intval($row['passing_marks']) ?: ''; ?>">
                                    </div>
                                </div>

                                <div class="cf-field">
                                    <label>Duration (minutes)</label>
                                    <input type="number" name="duration" class="cf-input" min="0"
                                           placeholder="e.g. 60"
                                           value="<?php echo intval($row['duration']) ?: ''; ?>">
                                    <div class="cf-hint">Leave 0 for no time limit</div>
                                </div>

                                

                            </div>

                            <div class="cf-actions">
                                <button type="submit" class="cf-btn-save">
                                    <i class="ti ti-device-floppy"></i> Save Changes
                                </button>
                                <a href="assessment_new.php" class="cf-btn-cancel">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</div>
</body>
</html>
