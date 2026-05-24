<?php
include('includes/dbconnect.php');
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: index.php');
    exit;
}
if (@$_SESSION['user_type'] != 1 && @$_SESSION['user_type'] != 2) {
    header('Location: dashboard.php');
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where  = "WHERE 1=1";
if ($search !== '') {
    $s = mysqli_real_escape_string($connection, $search);
    $where .= " AND (office_student_id LIKE '%$s%' OR given_name LIKE '%$s%' OR surname LIKE '%$s%' OR email_address LIKE '%$s%' OR mobile_num LIKE '%$s%' OR enquiry_id LIKE '%$s%')";
}

$result = mysqli_query($connection, "SELECT * FROM enrolment_form_new $where ORDER BY created_at DESC");
$rows   = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Enrolment List – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Enrolment List</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Enrolment List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search + New -->
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body py-2">
                                <form method="GET" class="d-flex gap-2 align-items-center flex-wrap">
                                    <input type="text" name="search" class="form-control" style="max-width:320px;"
                                           placeholder="Search by name, ID, email, mobile…"
                                           value="<?php echo htmlspecialchars($search); ?>">
                                    <button type="submit" class="btn btn-primary"><i class="ti ti-search me-1"></i>Search</button>
                                    <?php if ($search !== ''): ?>
                                    <a href="enrolment_list.php" class="btn btn-outline-secondary"><i class="ti ti-x me-1"></i>Clear</a>
                                    <?php endif; ?>
                                    <a href="enrolment_form_new.php" class="btn btn-success ms-auto"><i class="ti ti-plus me-1"></i>New Enrolment</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Status</th>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile</th>
                                                <th>Course</th>
                                                <th>Submitted By</th>
                                                <th>Date</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($rows) > 0): ?>
                                            <?php foreach ($rows as $i => $r):
                                                $courses_arr = json_decode($r['courses'] ?? '[]', true);
                                                $course_label = '';
                                                if (!empty($courses_arr)) {
                                                    $cids = array_map('intval', $courses_arr);
                                                    $in   = implode(',', $cids);
                                                    $cr   = mysqli_query($connection, "SELECT course_sname FROM courses WHERE course_id IN ($in)");
                                                    $names = [];
                                                    while ($c = mysqli_fetch_assoc($cr)) $names[] = $c['course_sname'];
                                                    $course_label = implode(', ', $names);
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo $i + 1; ?></td>
                                                <td>
                                                    <?php
                                                    $us = $r['updated_status'] ?? '';
                                                    if ($us === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                    <?php elseif ($us === 'resolve_query'): ?>
                                                    <span class="badge bg-primary">Query Resolved</span>
                                                    <?php elseif ($us === 'raise_query'): ?>
                                                    <span class="badge bg-danger">Query Raised</span>
                                                    <?php elseif ($us === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                    <?php else: ?>
                                                    <span class="badge bg-secondary">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-primary"><?php echo htmlspecialchars($r['office_student_id'] ?: '—'); ?></span></td>
                                                <td>
                                                    <?php echo htmlspecialchars(trim($r['given_name'] . ' ' . $r['surname']) ?: '—'); ?>
                                                    <?php if ($r['enquiry_id']): ?>
                                                    <br><small class="text-muted">Enquiry: <?php echo htmlspecialchars($r['enquiry_id']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($r['email_address'] ?: '—'); ?></td>
                                                <td><?php echo htmlspecialchars($r['mobile_num'] ?: '—'); ?></td>
                                                <td><small><?php echo htmlspecialchars($course_label ?: '—'); ?></small></td>
                                                <td>
                                                    <small>
                                                        <?php echo htmlspecialchars($r['username'] ?: '—'); ?>
                                                        <br><span class="badge bg-secondary"><?php echo htmlspecialchars($r['user_type']); ?></span>
                                                    </small>
                                                </td>
                                                <td><small><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></small></td>
                                                <td class="text-center">
                                                    <a href="enrolment_view.php?id=<?php echo $r['id']; ?>"
                                                       class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="ti ti-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <?php else: ?>
                                            <tr>
                                                <td colspan="10" class="text-center py-4 text-muted">
                                                    <i class="ti ti-file-off fs-3 d-block mb-2"></i>
                                                    No enrolments found<?php echo $search ? ' for "' . htmlspecialchars($search) . '"' : ''; ?>.
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($rows) > 0): ?>
                                <div class="px-3 py-2 text-muted small border-top">
                                    <?php echo count($rows); ?> record<?php echo count($rows) !== 1 ? 's' : ''; ?> found
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
</body>
</html>
