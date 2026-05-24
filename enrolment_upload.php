<?php 
include('includes/dbconnect.php'); 
session_start();

if(@$_SESSION['user_type']==1){
    $users=mysqli_query($connection,"SELECT * FROM users WHERE user_type=0 ORDER BY user_id DESC");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Create / Edit User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
</head>

<body data-topbar="colored">
    <div id="layout-wrapper">

        <?php include('includes/header.php'); ?>
        <?php include('includes/sidebar.php'); ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <!-- Page Title -->
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                <h4 class="mb-sm-0">Create / Edit User</h4>
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">User Management</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Form + List -->
                    <form id="create_user_form" class="row">
                        <!-- LEFT SIDE: CREATE / EDIT FORM -->
                        <div class="col-xl-6">
                            <div class="card">
                                <div class="card-body">
                                    <h4 class="card-title text-center" id="form_title">Create User</h4>

                                    <input type="hidden" id="edit_user_id" value="">

                                    <div class="mb-3">
                                        <label class="form-label">User Name</label>
                                        <input type="text" id="user_name" name="user_name" class="form-control" placeholder="Enter User Name" required>
                                        <div class="error-feedback">Please enter the user name</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">User Email</label>
                                        <input type="email" id="user_email" name="user_email" class="form-control" placeholder="Enter Email" required>
                                        <div class="error-feedback">Please enter a valid email</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" id="user_password" name="user_password" class="form-control" placeholder="Enter Password" required>
                                        <div class="error-feedback">Please enter a password</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">User Type</label>
                                        <select id="user_type" name="user_type" class="form-select" required>
                                            <option value="">-- Select --</option>
                                            <option value="1">Admin</option>
                                            <option value="0">User</option>
                                        </select>
                                        <div class="error-feedback">Please select a user type</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">User Status</label>
                                        <select id="user_status" name="user_status" class="form-select">
                                            <option value="0">Active</option>
                                            <option value="1">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="text-center">
                                        <button type="button" id="create_user_submit" class="btn btn-primary w-50">Create User</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form><!-- End Row -->

                </div>
            </div>
        </div>

    </div>

    <div class="rightbar-overlay"></div>
    <?php include('includes/footer_includes.php'); ?>

    <script>
        // CREATE / UPDATE USER
     
    </script>

</body>
</html>

<?php 
}else{ 
    header("Location: index.php");
}
?>
