<?php
include('includes/dbconnect.php');
session_start();

if(@$_SESSION['user_type']!='' && @$_SESSION['user_type']!=1){
    header('Location: index.php');
    exit;
}

$search     = trim($_GET['q'] ?? '');
$search_esc = mysqli_real_escape_string($connection, $search);
$where      = $search_esc !== '' ? "WHERE assessment_name LIKE '%$search_esc%' OR assessment_unique_id LIKE '%$search_esc%'" : '';

$res = mysqli_query($connection,
    "SELECT * FROM `assessment` $where ORDER BY created_date DESC"
);

$assessments = [];
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $assessments[] = $row;
    }
}
$total = count($assessments);

$status_labels = [0 => 'Draft', 1 => 'Active', 2 => 'Inactive'];
$status_badges = [0 => 'badge-draft', 1 => 'badge-active', 2 => 'badge-inactive'];

function fmt_duration(int $min): string {
    if (!$min) return '—';
    if ($min < 60) return $min . ' min';
    $h = floor($min / 60); $m = $min % 60;
    return $h . 'h' . ($m ? ' ' . $m . 'm' : '');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Assessment</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .forms-card {
            background: #1a1f2e;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,.18);
        }
        .forms-card-header {
            background: #1a1f2e;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            border-bottom: 1px solid #2d3348;
        }
        .forms-title { color:#fff; font-size:1.25rem; font-weight:700; margin:0; }
        .forms-title span { font-size:.9rem; font-weight:400; color:#94a3b8; margin-left:6px; }
        .forms-search-wrap { display:flex; align-items:center; gap:10px; }
        .forms-search {
            background:#252b3b; border:1px solid #3a4258; color:#e2e8f0;
            border-radius:8px; padding:8px 16px; font-size:.875rem; width:240px; outline:none;
        }
        .forms-search::placeholder { color:#64748b; }
        .forms-search:focus { border-color:#635bff; }
        .btn-search {
            background:#2d3348; border:1px solid #3a4258; color:#94a3b8;
            border-radius:8px; padding:8px 14px; cursor:pointer;
        }
        .btn-search:hover { background:#3a4258; }
        .btn-new-form {
            background:#635bff; color:#fff; border:none; border-radius:8px;
            padding:8px 20px; font-weight:600; font-size:.875rem; cursor:pointer;
            text-decoration:none; display:inline-flex; align-items:center; gap:6px;
        }
        .btn-new-form:hover { background:#4f46e5; color:#fff; }

        .forms-table { width:100%; border-collapse:collapse; }
        .forms-table thead tr { background:#252b3b; border-bottom:1px solid #2d3348; }
        .forms-table thead th {
            color:#64748b; font-size:.72rem; font-weight:600;
            text-transform:uppercase; letter-spacing:.6px; padding:12px 16px;
        }
        .forms-table tbody tr { border-bottom:1px solid #232837; transition:background .12s; }
        .forms-table tbody tr:last-child { border-bottom:none; }
        .forms-table tbody tr:hover { background:#1e2535; }
        .forms-table tbody td { padding:12px 16px; color:#e2e8f0; font-size:.875rem; vertical-align:middle; }

        .assess-name { font-weight:700; color:#f1f5f9; font-size:.92rem; }
        .assess-desc { color:#64748b; font-size:.78rem; margin-top:2px; }
        .uid-badge {
            background:#1e2535; color:#94a3b8; border-radius:5px;
            padding:2px 8px; font-size:.75rem; font-family:monospace;
        }

        .badge-draft    { background:#3d2f10; color:#f59e0b; border-radius:20px; padding:3px 12px; font-size:.75rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
        .badge-active   { background:#0f2e1a; color:#22c55e; border-radius:20px; padding:3px 12px; font-size:.75rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
        .badge-inactive { background:#1e2535; color:#64748b; border-radius:20px; padding:3px 12px; font-size:.75rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; }
        .badge-dot { width:6px; height:6px; border-radius:50%; display:inline-block; }
        .badge-draft    .badge-dot { background:#f59e0b; }
        .badge-active   .badge-dot { background:#22c55e; }
        .badge-inactive .badge-dot { background:#64748b; }

        .action-btns { display:flex; gap:6px; justify-content:flex-end; }
        .action-btn {
            width:34px; height:34px; border-radius:7px; border:1px solid #2d3348;
            background:#252b3b; color:#94a3b8; display:inline-flex;
            align-items:center; justify-content:center; cursor:pointer;
            text-decoration:none; transition:background .12s, color .12s; font-size:.85rem;
        }
        .action-btn:hover { background:#3a4258; color:#e2e8f0; }
        .action-btn.delete { border-color:#7f1d1d; background:#c0392b; color:#fff; }
        .action-btn.delete:hover { background:#e74c3c; }

        .empty-row td { text-align:center; color:#64748b; padding:48px 16px !important; font-size:.9rem; }
        .meta-val { color:#94a3b8; }
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
                            <h4 class="mb-sm-0">Assessment</h4>
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Assessment</li>
                            </ol>
                        </div>
                    </div>
                </div>

                <?php if (!empty($_GET['created'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    Assessment created successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php elseif (!empty($_GET['updated'])): ?>
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    Assessment updated successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="forms-card">
                    <div class="forms-card-header">
                        <h5 class="forms-title">
                            Assessments <span>(<?php echo $total; ?>)</span>
                        </h5>
                        <div class="forms-search-wrap">
                            <form method="GET" style="display:flex;gap:8px;align-items:center;">
                                <input type="text" name="q" class="forms-search"
                                       placeholder="Search assessments…"
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn-search">
                                    <i class="ti ti-search"></i>
                                </button>
                            </form>
                            <a href="assessment_form_create.php" class="btn-new-form">
                                <i class="ti ti-plus"></i> New Assessment
                            </a>
                        </div>
                    </div>

                    <table class="forms-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Assessment Name</th>
                                <th>Unique ID</th>
                                <th>Marks / Passing</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="text-align:right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($assessments)): ?>
                            <tr class="empty-row">
                                <td colspan="8">
                                    <i class="ti ti-clipboard-list" style="font-size:2rem;display:block;margin-bottom:8px;color:#3a4258;"></i>
                                    <?php echo $search ? 'No assessments matched your search.' : 'No assessments yet. Create your first one.'; ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assessments as $i => $a):
                                $sid    = intval($a['status']);
                                $blabel = $status_labels[$sid] ?? 'Draft';
                                $bcls   = $status_badges[$sid]  ?? 'badge-draft';
                            ?>
                            <tr>
                                <td class="meta-val"><?php echo $i + 1; ?></td>
                                <td>
                                    <div class="assess-name"><?php echo htmlspecialchars($a['assessment_name']); ?></div>
                                    <?php if (!empty($a['description'])): ?>
                                    <div class="assess-desc"><?php echo htmlspecialchars(mb_strimwidth($a['description'], 0, 60, '…')); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="uid-badge"><?php echo htmlspecialchars($a['assessment_unique_id']); ?></span></td>
                                <td class="meta-val">
                                    <?php echo $a['marks'] ?: '—'; ?>
                                    <?php if ($a['passing_marks']): ?> / <span style="color:#22c55e"><?php echo $a['passing_marks']; ?></span><?php endif; ?>
                                </td>
                                <td class="meta-val"><?php echo fmt_duration($a['duration']); ?></td>
                                <td>
                                    <span class="<?php echo $bcls; ?>">
                                        <span class="badge-dot"></span>
                                        <?php echo $blabel; ?>
                                    </span>
                                </td>
                                <td class="meta-val" style="font-size:.8rem;">
                                    <?php echo date('d M Y', strtotime($a['created_date'])); ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <a href="assessment_edit.php?id=<?php echo intval($a['assessment_id']); ?>"
                                           class="action-btn" title="Edit">
                                            <i class="ti ti-pencil"></i>
                                        </a>
                                        <a href="add_question.php?id=<?php echo intval($a['assessment_id']); ?>"
                                           class="action-btn" title="Add Question" style="width:auto;padding:0 10px;gap:5px;font-size:.78rem;font-weight:600;">
                                            <i class="ti ti-circle-plus"></i> Question
                                        </a>
                                        <?php if(intval($a['status']) === 1): ?>
                                        <a href="assign_student.php?id=<?php echo intval($a['assessment_id']); ?>"
                                           class="action-btn" title="Assign" style="width:auto;padding:0 10px;gap:5px;font-size:.78rem;font-weight:600;background:#0f2e1a;border-color:#166534;color:#22c55e;">
                                            <i class="ti ti-user-plus"></i> Assign
                                        </a>
                                        <?php endif; ?>
                                        <button class="action-btn delete" title="Delete"
                                                onclick="deleteAssessment(<?php echo intval($a['assessment_id']); ?>, '<?php echo htmlspecialchars(addslashes($a['assessment_name'])); ?>')">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script>
function deleteAssessment(id, name) {
    if (!confirm('Delete assessment "' + name + '"? This cannot be undone.')) return;
    fetch('assessment_new_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=delete_assessment&assessment_id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { location.reload(); }
        else { alert(data.message || 'Failed to delete.'); }
    })
    .catch(() => alert('Request failed.'));
}
</script>
</body>
</html>
