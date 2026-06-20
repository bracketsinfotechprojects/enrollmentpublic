<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Assessment List</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            :root {
                --theme-primary: #0d6efd;
                --theme-primary-hover: #0b5ed7;
            }
            .asterisk {
                color: var(--theme-primary);
            }
            .btn-primary {
                background-color: var(--theme-primary);
                border-color: var(--theme-primary);
            }
            .btn-primary:hover {
                background-color: var(--theme-primary-hover);
                border-color: var(--theme-primary-hover);
            }
            .dataTables_paginate .page-link,
            .dataTables_paginate .page-item.previous .page-link,
            .dataTables_paginate .page-item.next .page-link {
                color: var(--theme-primary) !important;
            }
            .dataTables_paginate .page-item.disabled .page-link {
                color: #878a99 !important;
            }
            .dataTables_paginate .page-item.active .page-link {
                background-color: var(--theme-primary) !important;
                color: #fff !important;
            }
            .dataTables_paginate .page-link i {
                color: inherit;
            }
            .page-item.active .page-link {
                background-color: var(--theme-primary) !important;
                border-color: var(--theme-primary) !important;
            }
        </style>
    </head>

    <body>

    <div id="loader-container" style="display:none;">
        <div class="loader"></div>
    </div>

        <!-- Begin page -->
        <div class="main-wrapper">

            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="page-wrapper">
                <div class="content pb-0">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Assessment List</h4>
                                    <div id="successMsg" style="display:none; color:green;">
    Assessment created successfully!
</div>
                                    <div class="page-title-right">
                                        <a href="create_assessment.php" class="btn btn-primary btn-sm"><i class="ti ti-plus"></i> Create New Assessment</a>
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Assessment</a></li>
                                            <li class="breadcrumb-item active">Assessment List</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">All Assessments</h4>  
                                        <table id="datatable" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Sr.No.</th>
                                                    <th>Assessment Name</th>
<th>Marks</th>
                                                     <th>Passing Mark</th>
                                                     <th>Duration</th>
                                                    <th>Created Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
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

        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    lengthMenu: [10, 25, 50, 100],
                    language:{
                        paginate:{
                            previous: "<i class='ti ti-chevron-left'></i>",
                            next: "<i class='ti ti-chevron-right'></i>"
                        }
                    },
                    drawCallback:function(){
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                    },
                    scrollX: true,
                    responsive: false,
                    ajax: {
                        url: 'includes/datacontrol.php?name=assessmentList',
                        dataSrc: 'data',
                        error: function(xhr, status, error) {
                            console.error('DataTables AJAX Error:', status, error);
                            console.log('Response Text:', xhr.responseText);
                            var errorMsg = '<div style="position:fixed;top:10px;right:10px;z-index:9999;max-width:500px;background:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb;border-radius:5px;font-family:Arial,sans-serif;font-size:14px;"><strong>DataTables Error:</strong> ';
                            errorMsg += (xhr.responseText ? 'Server returned invalid JSON. ' + xhr.responseText.substring(0, 200) : error);
                            errorMsg += '<br><small>Open browser console (F12) to see full response.</small></div>';
                            $('body').append(errorMsg);
                            setTimeout(function(){ $('.alert').fadeOut(); }, 10000);
                        }
                    },
                    columns: [
                        { data: 'sr_no' },
                        { data: 'assessment_name' },
{ data: 'marks' },
                         { data: 'passing_marks' },
                         { data: 'duration' },
                        { data: 'created_date' },
                        { data: 'action' }
                    ],
                });

                $(document).on('click', '.btn-view', function(){
                    var assessmentId = $(this).data('id');
                    window.location.href = 'create_question.php?id=' + assessmentId;
                });
            });
        </script>
    </body>
</html>
<?php 
} else {
    header("Location: index.php");
}
?>