<?php 
session_start();
// print_r($_SESSION);
if(@$_SESSION['user_type']==1){
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />

        <?php include('includes/app_includes.php'); ?>

    </head>

    <body data-topbar="colored">

        <!-- Begin page -->
        <div id="layout-wrapper">

            
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Dashboard</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <!-- <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li> -->
                                            <!-- <li class="breadcrumb-item active">Light Sidebar</li> -->
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
                                        <h4 class="card-title mb-4">Student Enquiries</h4>  
                                            <table id="datatable" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Enquiry ID</th>
                                                        <th scope="col">Student Name</th>
                                                        <th scope="col">Contact Number</th>
                                                        <th scope="col">Email</th>
                                                        <th scope="col">Course</th>
                                                        <th scope="col">Fee</th>
                                                        <th scope="col">Visa Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                    <!-- end card-body -->
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Students Enrolled</h4>  
                                            <table id="datatable_enrol" class="table table-striped table-bordered  nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Name</th>
                                                        <th scope="col">Given Name</th>
                                                        <th scope="col">Middle Name</th>
                                                        <th scope="col">Qualification</th>
                                                        <th scope="col">Venue</th>
                                                        <th scope="col">Source</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                    <!-- end card-body -->
                                </div>
                                <!-- end card -->
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Invoices</h4>  
                                            <table id="datatable_invoices" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Student Name</th>
                                                        <th scope="col">Course</th>
                                                        <th scope="col">Course Fee</th>
                                                        <th scope="col">Paid Amount</th>
                                                        <th scope="col">Due Amount</th>
                                                        <th scope="col">Payment Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                    <!-- end card-body -->
                                </div>
                                <!-- end card -->
                            </div>
                            <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                scrollX: true,
                    ajax: 'includes/datacontrol?name=studentEnquiry',
                        columns: [
                        { data: 'st_enquiry_id' },                                    
                        { data: 'std_name' },                                    
                        { data: 'std_phno' },
                        { data: 'std_email' },
                        { data: 'std_course' },
                        { data: 'std_fee' },
                        { data: 'std_visa_status' },
                    ],
                });

                $('#datatable_enrol').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                scrollX: true,
                    ajax: 'includes/datacontrol?name=student_enrol',
                        columns: [
                        { data: 'st_enrol_name' },                                    
                        { data: 'st_enrol_givenname' },
                        { data: 'st_enrol_middlename' },
                        { data: 'st_enrol_qual' },
                        { data: 'st_enrol_venue' },
                        { data: 'st_enrol_source' },
                    ],
                });
                
                $('#datatable_invoices').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                scrollX: true,
                    ajax: 'includes/datacontrol?name=student_invoices',
                        columns: [
                        { data: 'inv_std_name' },                                    
                        { data: 'inv_course' },
                        { data: 'inv_fee' },
                        { data: 'inv_paid' },
                        { data: 'inv_due' },
                        { data: 'inv_payment_date' },
                    ],
                });
            })
        </script>
    </body>
</html>
<?php } else{
    header('Location: student_enquiry.php');
    // echo "testss";
} ?>