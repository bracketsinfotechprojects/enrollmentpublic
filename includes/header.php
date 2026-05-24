<?php 
ini_set('display_errors', '0');
session_start(); ?>
<?php 
$CRM_ASSET_BASE = 'crm/html/template/assets';
$is_student = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student');
$header_home_url = ($is_student || (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 0)) ? 'student_docs.php' : 'dashboard.php';
$logout_url = $is_student ? 'student_logout.php' : 'index.php?name=logout';
?>
<header class="navbar-header">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <a href="<?php echo $header_home_url; ?>" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo.png" alt="logo"></span>
                    <span class="logo-sm"><img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo-small.png" alt="small logo"></span>
                </span>
                <span class="logo-dark">
                    <span class="logo-lg"><img src="<?php echo $CRM_ASSET_BASE; ?>/img/logo-white.svg" alt="dark logo"></span>
                </span>
            </a>

            <a id="mobile_btn" class="mobile-btn" href="#sidebar">
                <i class="ti ti-menu-deep fs-24"></i>
            </a>

            <button class="sidenav-toggle-btn btn border-0 p-0" id="toggle_btn2">
                <i class="ti ti-arrow-bar-to-right"></i>
            </button>

            <!-- <div class="me-auto d-flex align-items-center header-search d-lg-flex d-none">
                <div class="input-icon position-relative me-2">
                    <input type="text" class="form-control" placeholder="Search Keyword">
                    <span class="input-icon-addon d-inline-flex p-0 header-search-icon"><i class="ti ti-command"></i></span>
                </div>
            </div> -->
        </div>

        <div class="d-flex align-items-center">
            <div class="header-item d-flex d-lg-none me-2">
                <button class="topbar-link btn" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                    <i class="ti ti-search fs-16"></i>
                </button>
            </div>

            <div class="header-item">
                <div class="dropdown me-2">
                    <a href="javascript:void(0);" class="btn topbar-link btnFullscreen"><i class="ti ti-maximize"></i></a>
                </div>
            </div>

            <div class="header-item d-none d-sm-flex me-2">
                <button class="topbar-link btn topbar-link" id="light-dark-mode" type="button">
                    <i class="ti ti-moon fs-16"></i>
                </button>
            </div>

            <div class="header-item">
                <div class="dropdown">
                    <a href="javascript:void(0);" class="btn topbar-link" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-sm-inline-flex me-1"><?php echo @$_SESSION['user_name']; ?></span>
                        <i class="ti ti-chevron-down"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                            <!-- Item-->
                            <a href="profile_settings.php" class="dropdown-item">
                                <i class="ti ti-settings me-1 align-middle"></i>
                                <span class="align-middle">Settings</span>
                            </a> 
                        <div class="pt-2 mt-2 border-top">
                            <a href="<?php echo $logout_url; ?>" class="dropdown-item text-danger">
                                <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                                <span class="align-middle">Sign Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>