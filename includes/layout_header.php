<?php
// includes/layout_header.php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
requireLogin();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? APP_NAME) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
  --bg: #0f1117;
  --surface: #181b23;
  --surface2: #1e2230;
  --border: #2a2f3e;
  --border2: #343a4d;
  --accent: #4f6ef7;
  --accent2: #7c3aed;
  --accent-glow: rgba(79,110,247,0.18);
  --green: #22c55e;
  --red: #ef4444;
  --yellow: #f59e0b;
  --text: #e8eaf0;
  --text2: #9ba3b8;
  --text3: #5c6480;
  --radius: 10px;
  --radius-sm: 6px;
  --shadow: 0 4px 24px rgba(0,0,0,0.35);
  --sidebar-w: 240px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}

/* Scrollbar */
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:var(--surface)}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* Layout */
.app-shell{display:flex;height:100vh;overflow:hidden}
.sidebar{width:var(--sidebar-w);min-width:var(--sidebar-w);background:var(--surface);border-right:1px solid var(--border);display:flex;flex-direction:column;overflow-y:auto}
.main-content{flex:1;overflow-y:auto;display:flex;flex-direction:column}

/* Sidebar */
.sidebar-logo{padding:20px 20px 16px;border-bottom:1px solid var(--border)}
.sidebar-logo a{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--text);font-weight:700;font-size:16px}
.logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--accent),var(--accent2));border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px}
.sidebar-nav{padding:12px 0;flex:1}
.nav-section-label{padding:6px 20px;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text3)}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;color:var(--text2);text-decoration:none;font-size:13.5px;font-weight:500;transition:all .15s;border-left:3px solid transparent;position:relative}
.nav-item:hover{background:var(--surface2);color:var(--text)}
.nav-item.active{background:var(--accent-glow);color:var(--accent);border-left-color:var(--accent)}
.nav-item i{width:18px;text-align:center;font-size:13px}
.nav-badge{margin-left:auto;background:var(--accent);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px}
.sidebar-footer{padding:16px 20px;border-top:1px solid var(--border)}
.user-info{display:flex;align-items:center;gap:10px}
.avatar{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color:#fff;flex-shrink:0}
.user-name{font-size:13px;font-weight:600;color:var(--text)}
.user-role{font-size:11px;color:var(--text3);text-transform:capitalize}
.logout-btn{margin-left:auto;color:var(--text3);text-decoration:none;font-size:13px;transition:color .15s}
.logout-btn:hover{color:var(--red)}

/* Top bar */
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 28px;height:56px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:10}
.topbar-title{font-size:16px;font-weight:700;color:var(--text)}
.topbar-subtitle{font-size:13px;color:var(--text3);margin-left:4px}
.topbar-spacer{flex:1}
.page-body{padding:28px;flex:1}

/* Buttons */
.btn{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border:none;border-radius:var(--radius-sm);font-family:inherit;font-size:13.5px;font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;white-space:nowrap}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover{background:#3d5bf0;transform:translateY(-1px);box-shadow:0 4px 16px rgba(79,110,247,.4)}
.btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border2)}
.btn-secondary:hover{background:var(--border);color:var(--text)}
.btn-danger{background:var(--red);color:#fff}
.btn-danger:hover{background:#dc2626}
.btn-success{background:var(--green);color:#fff}
.btn-sm{padding:5px 11px;font-size:12.5px}
.btn-icon{padding:7px;aspect-ratio:1}

/* Cards */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px}
.card-title{font-size:14px;font-weight:700;color:var(--text)}
.card-body{padding:20px}

/* Stats */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px}
.stat-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px 20px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--accent),var(--accent2))}
.stat-value{font-size:28px;font-weight:700;color:var(--text);line-height:1.1}
.stat-label{font-size:12px;color:var(--text3);margin-top:4px;text-transform:uppercase;letter-spacing:.5px}
.stat-change{font-size:11px;color:var(--green);margin-top:6px}
.stat-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:28px;color:var(--border2)}

/* Table */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
th{padding:10px 16px;text-align:left;font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text3);background:var(--surface2);border-bottom:1px solid var(--border)}
td{padding:12px 16px;border-bottom:1px solid var(--border);font-size:13.5px;color:var(--text2)}
tr:hover td{background:var(--surface2)}
tr:last-child td{border-bottom:none}

/* Status badges */
.badge{display:inline-flex;align-items:center;gap:5px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700}
.badge::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}
.badge-active{background:rgba(34,197,94,.12);color:var(--green)}
.badge-draft{background:rgba(245,158,11,.12);color:var(--yellow)}
.badge-inactive{background:rgba(239,68,68,.12);color:var(--red)}
.badge-new{background:rgba(79,110,247,.12);color:var(--accent)}

/* Forms */
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:13px;font-weight:600;color:var(--text2);margin-bottom:6px}
.form-label .req{color:var(--red)}
.form-control{width:100%;background:var(--surface2);border:1px solid var(--border2);border-radius:var(--radius-sm);padding:9px 12px;color:var(--text);font-family:inherit;font-size:13.5px;transition:border-color .15s,box-shadow .15s;outline:none}
.form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-glow)}
.form-control::placeholder{color:var(--text3)}
select.form-control{cursor:pointer}
textarea.form-control{resize:vertical;min-height:90px}
.form-help{font-size:11.5px;color:var(--text3);margin-top:4px}
.form-check{display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13.5px;color:var(--text2)}
.form-check input[type=checkbox],.form-check input[type=radio]{accent-color:var(--accent);width:15px;height:15px;cursor:pointer}

/* Alerts */
.alert{padding:12px 16px;border-radius:var(--radius-sm);font-size:13.5px;display:flex;align-items:flex-start;gap:10px;margin-bottom:16px}
.alert-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.25);color:#86efac}
.alert-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.25);color:#fca5a5}
.alert-info{background:rgba(79,110,247,.1);border:1px solid rgba(79,110,247,.25);color:#a5b4fc}

/* Misc */
.empty-state{text-align:center;padding:60px 20px;color:var(--text3)}
.empty-state i{font-size:48px;margin-bottom:16px;opacity:.4}
.empty-state h3{font-size:16px;color:var(--text2);margin-bottom:8px}
.text-accent{color:var(--accent)}
.flex{display:flex}.items-center{align-items:center}.gap-2{gap:8px}.gap-3{gap:12px}.justify-between{justify-content:space-between}
.mt-1{margin-top:4px}.mt-2{margin-top:8px}.mt-3{margin-top:12px}.mt-4{margin-top:16px}
.mb-4{margin-bottom:16px}.mb-6{margin-bottom:24px}
.mono{font-family:'JetBrains Mono',monospace;font-size:12px}
.truncate{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px}
</style>
</head>
<body>
<div class="app-shell">
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="<?= APP_URL ?>">
      <div class="logo-icon"><i class="fa-solid fa-bolt"></i></div>
      <?= APP_NAME ?>
    </a>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="<?= APP_URL ?>/index.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) === 'index.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-gauge"></i> Dashboard
    </a>
    <a href="<?= APP_URL ?>/forms.php" class="nav-item <?= in_array(basename($_SERVER['PHP_SELF']), ['forms.php','form_create.php','form_edit.php']) ? 'active' : '' ?>">
      <i class="fa-solid fa-file-alt"></i> Forms
    </a>
    <a href="<?= APP_URL ?>/submissions.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) === 'submissions.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-inbox"></i> Submissions
    </a>
    <?php if (isAdmin()): ?>
    <div class="nav-section-label mt-3">Admin</div>
    <a href="<?= APP_URL ?>/users.php" class="nav-item <?= (basename($_SERVER['PHP_SELF']) === 'users.php') ? 'active' : '' ?>">
      <i class="fa-solid fa-users"></i> Users
    </a>
    <?php endif; ?>
  </nav>
  <div class="sidebar-footer">
    <div class="user-info">
      <div class="avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
      <div>
        <div class="user-name"><?= e($user['username']) ?></div>
        <div class="user-role"><?= e($user['role']) ?></div>
      </div>
      <a href="<?= APP_URL ?>/logout.php" class="logout-btn" title="Logout"><i class="fa-solid fa-right-from-bracket"></i></a>
    </div>
  </div>
</aside>
<div class="main-content">
<?php // Page content starts here ?>
