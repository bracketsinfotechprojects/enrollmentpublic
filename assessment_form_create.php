<?php
include('includes/dbconnect.php');
session_start();

if (@$_SESSION['user_type'] != '' && @$_SESSION['user_type'] != 1) {
    header('Location: index.php'); exit;
}
$user_id = $_SESSION['user_id'] ?? 0;
 $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['form_name']     ?? '');
    $description   = trim($_POST['description']   ?? '');
    $status_str    = 'draft';
    $marks         = intval($_POST['total_marks']   ?? 0);
    $passing_marks = intval($_POST['passing_marks'] ?? 0);
    $duration      = intval($_POST['duration']      ?? 0);

    $status_map = ['draft' => 0, 'active' => 1, 'inactive' => 2];
    $status_int = $status_map[$status_str] ?? 0;

    if ($name === '') {
        $error = 'Assessment name is required.';
    } elseif (strlen($name) > 50) {
        $error = 'Assessment name must be 50 characters or fewer.';
    } elseif ($passing_marks > $marks && $marks > 0) {
        $error = 'Passing marks cannot exceed total marks.';
    } else {
        $unique_id = 'ASM' . date('ymd') . rand(100, 999);
        $esc_uid   = mysqli_real_escape_string($connection, $unique_id);
        $esc_name  = mysqli_real_escape_string($connection, $name);
        $esc_desc  = mysqli_real_escape_string($connection, $description);

        $ins = mysqli_query($connection,
            "INSERT INTO `assessment` (assessment_unique_id, assessment_name, description, marks, passing_marks, duration, status)
             VALUES ('$esc_uid', '$esc_name', '$esc_desc', $marks, $passing_marks, $duration, $status_int)"
        );

        if ($ins) {
            header('Location: assessment_new.php?created=1');
            exit;
        } else {
            $error = 'Database error: ' . mysqli_error($connection);
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Create New Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        body { background: #0d1117; }

        .cf-topbar {
            background: #161b27;
            border-bottom: 1px solid #1e2535;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .cf-back-btn {
            background: #1e2535;
            border: 1px solid #2d3348;
            color: #e2e8f0;
            border-radius: 7px;
            padding: 7px 16px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background .15s;
        }
        .cf-back-btn:hover { background: #252b3b; color: #fff; }
        .cf-topbar-title {
            color: #f1f5f9;
            font-size: 1.05rem;
            font-weight: 700;
            margin: 0;
        }

        .cf-wrap {
            max-width: 680px;
            margin: 48px auto;
            padding: 0 16px;
        }

        .cf-card {
            background: #161b27;
            border: 1px solid #1e2535;
            border-radius: 12px;
            overflow: hidden;
        }
        .cf-card-header {
            padding: 20px 28px 16px;
            border-bottom: 1px solid #1e2535;
        }
        .cf-card-title {
            color: #f1f5f9;
            font-size: 1rem;
            font-weight: 700;
            margin: 0;
        }
        .cf-card-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 22px;
        }

        .cf-field label {
            display: block;
            color: #cbd5e1;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .cf-field label .req { color: #ef4444; margin-left: 3px; }

        .cf-input, .cf-textarea, .cf-select {
            width: 100%;
            background: #1e2535;
            border: 1px solid #2d3348;
            color: #e2e8f0;
            border-radius: 8px;
            padding: 11px 16px;
            font-size: 0.9rem;
            outline: none;
            transition: border-color .15s;
            box-sizing: border-box;
        }
        .cf-input::placeholder, .cf-textarea::placeholder { color: #475569; }
        .cf-input:focus, .cf-textarea:focus, .cf-select:focus { border-color: #635bff; }
        .cf-textarea { resize: vertical; min-height: 110px; }
        .cf-select { appearance: none; cursor: pointer; }
        .cf-select option { background: #1e2535; }

        .cf-select-wrap { position: relative; }
        .cf-select-wrap::after {
            content: '▾';
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            pointer-events: none;
            font-size: 0.85rem;
        }
        .cf-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        .cf-hint {
            color: #475569;
            font-size: 0.78rem;
            margin-top: 5px;
        }

        .cf-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 0 28px 28px;
        }
        .cf-btn-create {
            background: #635bff;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 11px 24px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background .15s;
        }
        .cf-btn-create:hover { background: #4f46e5; }
        .cf-btn-cancel {
            background: transparent;
            color: #94a3b8;
            border: 1px solid #2d3348;
            border-radius: 8px;
            padding: 10px 22px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s, color .15s;
        }
        .cf-btn-cancel:hover { background: #1e2535; color: #e2e8f0; }

        .cf-error {
            background: #2d1515;
            border: 1px solid #7f1d1d;
            color: #fca5a5;
            border-radius: 8px;
            padding: 11px 16px;
            font-size: 0.875rem;
            margin: 0 28px 4px;
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
<!-- Top bar (standalone — no sidebar on this page) -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Assessment New</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Assessment New</li>
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
                                    <input type="text"
                                        name="form_name"
                                        class="cf-input"
                                        placeholder="e.g. Midterm Assessment, Final Project, etc."
                                        value="<?php echo htmlspecialchars($_POST['form_name'] ?? ''); ?>"
                                        autofocus
                                        required>
                                </div>

                                <div class="cf-field">
                                    <label>Description</label>
                                    <textarea name="description"
                                            class="cf-textarea"
                                            placeholder="Optional description..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="cf-row">
                                    <div class="cf-field">
                                        <label>Total Marks</label>
                                        <input type="number" name="total_marks" class="cf-input" min="0"
                                               placeholder="e.g. 100"
                                               value="<?php echo intval($_POST['total_marks'] ?? 0) ?: ''; ?>">
                                    </div>
                                    <div class="cf-field">
                                        <label>Passing Marks</label>
                                        <input type="number" name="passing_marks" class="cf-input" min="0"
                                               placeholder="e.g. 40"
                                               value="<?php echo intval($_POST['passing_marks'] ?? 0) ?: ''; ?>">
                                    </div>
                                </div>

                                <div class="cf-field">
                                    <label>Duration (minutes)</label>
                                    <input type="number" name="duration" class="cf-input" min="0"
                                           placeholder="e.g. 60"
                                           value="<?php echo intval($_POST['duration'] ?? 0) ?: ''; ?>">
                                    <div class="cf-hint">Leave 0 for no time limit</div>
                                </div>

                                

                            </div><!-- /.cf-card-body -->

                            <div class="cf-actions">
                                <button type="submit" class="cf-btn-create">
                                    <i class="ti ti-arrow-right"></i> Create Assessment
                                </button>
                                <a href="assessment_new.php" class="cf-btn-cancel">Cancel</a>
                            </div>

                        </div><!-- /.cf-card -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
