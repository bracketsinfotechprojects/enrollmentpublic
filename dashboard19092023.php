<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
// print_r($_SESSION);
if(isset($_SESSION['user_type'])){
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
        <style>
            tbody tr td:nth-child(5) {
                white-space: break-spaces;
                width:20%;
            }
            #datatable tbody tr td:nth-child(4) {
                white-space: break-spaces;
                width:10%;
            }
            #datatable tbody tr td:nth-child(3) {
                white-space: break-spaces;
                width:10%;
            }
            #student_filter_table {
                display: block;
                max-width: -moz-fit-content;
                max-width: fit-content;
                margin: 0 auto;
                overflow-x: auto;
                white-space: nowrap;
            }
/* 
            div.dataTables_wrapper {
                width: 800px;
                margin: 0 auto;
            } */

            #student_filter_table th, #student_filter_table td { min-width: 200px; }

            #student_filter_table::-webkit-scrollbar-track
            {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
                background-color: #F2F2F2;
                border-radius:5px;
            }

            #student_filter_table::-webkit-scrollbar
            {
                width: 10px;
                height:5px;
                background-color: #F2F2F2;
            }

            #student_filter_table::-webkit-scrollbar-thumb
            {
                background-color: var(--color);
                border: 2px solid var(--color);
                border-radius:5px;
            }
       </style>

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
                        <?php 
                        if($_SESSION['user_type']==1){
                        ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <!-- <h4 class="card-title">Student Records</h4> -->

                                        <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingOne">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                                    Student Enquiries
                                                    </button>
                                                </h2>
                                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                        <table id="datatable" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width:100%;">
                                                            <thead>
                                                                <tr>
                                                                    <th>Enquiry ID</th>
                                                                    <th>Student Name</th>
                                                                    <th>Contact Number</th>
                                                                    <th>Email</th>
                                                                    <th>Street</th>
                                                                    <th>Suburb</th>
                                                                    <th>Post Code</th>
                                                                    <th>Course</th>
                                                                    <th>Plan to Start</th>
                                                                    <th>Course Type</th>
                                                                    <th>Visited Before</th>
                                                                    <th>Refered By</th>
                                                                    <th>Fee</th>
                                                                    <th>Appointment</th>
                                                                    <th>Visa Status</th>
                                                                    <th>Visa Completed</th>
                                                                    <th>Staff Comments</th>
                                                                    <th>Preferences Or Requirements</th>
                                                                    <th>Remarks</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>

                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item d-none">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                    Students Enrolled
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                            <table id="datatable_enrol" class="table table-striped table-bordered  nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Name</th>
                                                                <th scope="col">Student ID</th>
                                                                <th scope="col">Enquiry ID</th>
                                                                <th scope="col">Given Name</th>
                                                                <th scope="col">Middle Name</th>
                                                                <th scope="col">Qualification</th>
                                                                <th scope="col">Venue</th>
                                                                <th scope="col">Source</th>
                                                                <th scope="col">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item d-none">
                                                <h2 class="accordion-header" id="headingThree">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                                    Invoices
                                                    </button>
                                                </h2>
                                                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                                <table id="datatable_invoices" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100% !important;">
                                                            <thead>
                                                                <tr>
                                                                    <th scope="col">Invoice ID</th>
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
                                                </div>
                                            </div>
                                        </div>
                                        <!-- end accordion -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Student Filter</h4>  
                                        <div class="d-flex" style="width:100%;">
                                            <input type="text" class="form-control" style="width:30%;" id="filter_input" placeholder="Enter the Text">
                                            <button id="student_filter" class="btn btn-dark ms-2" style="width:10%;">Export <i class="align-middle ms-2 mdi mdi-printer" style="font-size:20px;"></i></button>
                                        </div>
                                        <ul><li>Search with any of the columns to filter</li></ul>
                                        <div class="print_header"></div>
                                            <table id="student_filter_table" class="table nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th>Student ID</th>
                                                        <th>Student Name</th>
                                                        <th>Contact Number</th>
                                                        <th>Email</th>
                                                        <th>Course</th>
                                                        <th>State</th>
                                                        <th>Postal code</th>
                                                        <th>Enroled Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                <?php
                                                
                                                $query=mysqli_query($connection,"SELECT * from student_enquiry where st_enquiry_status!=1");
                                                while($queryRes=mysqli_fetch_array($query)){
                                                    $coursesNames=json_decode($queryRes['st_course']);
                                                    $coursesName='<div class="td_scroll_height">';
                                                    foreach($coursesNames as $value){
                                                        $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$value));
                                                        $coursesName.= $courses['course_sname'].'-'.$courses['course_name']." , <br>"; 
                                                    }
                                                    $coursesNamePos = strrpos($coursesName, ',');
                                                    $coursesName = substr($coursesName, 0, $coursesNamePos);
                                                    $coursesName.='</div>';

                                            
                                                    if($queryRes['st_visa_status']==1){
                                                        $visaStatus='<i class="mdi mdi-checkbox-blank-circle text-warning me-1"></i> Pending';
                                                    }else if($queryRes['st_visa_status']==2){
                                                        $visaStatus='<i class="mdi mdi-checkbox-blank-circle text-success me-1"></i> Approved';
                                                    }else{
                                                        $visaStatus='<i class="mdi mdi-checkbox-blank-circle text-danger me-1"></i> Declined';
                                                    }
                                                
                                                ?>
                                                    <tr>
                                                        <td><?php echo $queryRes['st_enquiry_id']; ?></td>
                                                        <td><?php echo $queryRes['st_surname'].' '.$queryRes['st_name']; ?></td>
                                                        <td><?php echo $queryRes['st_phno']; ?></td>
                                                        <td><?php echo $queryRes['st_email']; ?></td>
                                                        <td><?php echo $coursesName; ?></td>
                                                        <td><?php echo $queryRes['st_state']; ?></td>
                                                        <td><?php echo $queryRes['st_post_code']; ?></td>
                                                        <td><?php echo date('d M Y',strtotime($queryRes['st_enquiry_date'])); ?></td>
                                                    </tr>

                                                <?php } ?>    
                                                </tbody>
                                            </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">All Students</h4>  
                                            <table id="datatable-all" class="table nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Student ID</th>
                                                        <th scope="col">Student Name</th>
                                                        <th scope="col">Contact Number</th>
                                                        <th scope="col">Email</th>
                                                        <th scope="col">Course</th>
                                                        <th scope="col">Enroled Date</th>
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="row">
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
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                </div>
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
                                                        <th scope="col">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                </tbody>
                                            </table>
                                    </div>
                                </div>

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
                                </div>
                                
                            </div>
                            
                        </div> -->
                    
            <?php }else{ 
                            
                $enrolId=$_SESSION['user_log_id'];
                $queryRess=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enrolment where st_enrol_status!=1 and st_unique_id='$enrolId'"));
                $selectQry=mysqli_query($connection,"SELECT st_unique_id,st_doc_names FROM `student_docs` WHERE `st_unique_id`='$enrolId'");
                $course_id=$queryRess['st_enrol_course'];
                $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=$course_id"));                                
            ?>

                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="validationCustom01">Student ID</label>
                                                    <div class="">
                                                        <?php echo $_SESSION['user_log_id'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                <label class="form-label" for="validationCustom01">Name</label>
                                                    <div class="">
                                                        <?php echo $queryRess['st_given_name'].' '.$queryRess['st_middle_name'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                <label class="form-label" for="validationCustom01">Phone Number</label>
                                                    <div class="">
                                                        <?php echo $queryRess['st_mobile'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                <label class="form-label" for="validationCustom01">Email</label>
                                                    <div class="">
                                                        <?php echo $queryRess['st_email'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label" for="validationCustom01">Enquiry ID</label>
                                                    <div class="">
                                                        <?php echo $queryRess['st_enquiry_id'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                <label class="form-label" for="validationCustom01">Enroled Date</label>
                                                    <div class="">
                                                        <?php echo $queryRess['created_date'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                <label class="form-label" for="validationCustom01">Course</label>
                                                    <div class="">
                                                        <?php echo $courses['course_name'];  ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->

                            <?php } ?>
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
        <?php 
        if($_SESSION['user_type']==1){
        ?>
        <script>

            async function delete_enq(eq_id){

                Swal.fire({
                    icon: 'warning',
                    title: 'Are you Sure ?',
                    input: 'text',
                    showCancelButton:!0,
                    inputPlaceholder: 'Reason to Delete',
                    confirmButtonText:"Delete!",
                    confirmButtonColor:"#1cbb8c",
                    cancelButtonColor:"#ff3d60",
                    inputValidator: (value) => {
                        if (!value) {
                        return 'You need to write something!'
                        }
                    }
                    }).then(function(t){
                        if(t.isConfirmed){
                            deleteFun(eq_id,t.value);
                        }
                    })
                }

                function deleteFun(eq_id,note){

                $.ajax({
                    type:'post',
                    data:{eq_id:eq_id,note:note,formName:'delete_enq'},
                    url:'includes/datacontrol',
                    success:function(data){
                        if(data==1){
                        var table = $('#datatable').DataTable();
                            table.ajax.reload();
                        }else{
                            alert('Something went wrong. Please try again');
                        }
                    }
                })
            }


            function delete_enrol(enrol_id){
                $.ajax({
                    type:'post',
                    data:{enrol_id:enrol_id,formName:'delete_enrol'},
                    url:'includes/datacontrol',
                    success:function(data){
                        if(data==1){
                            console.log(data);
                        var table = $('#datatable_enrol').DataTable();
                            table.ajax.reload();
                        }else{
                            alert('Something went wrong. Please try again');
                        }
                    }
                })
            }

            $(document).ready(function () {
                $('#datatable').DataTable({
                    lengthMenu: [5, 10, 20],
                    language:{
                        paginate:{
                            previous:"<i class='mdi mdi-chevron-left'>",
                            next:"<i class='mdi mdi-chevron-right'>"}},
                            drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                    sScrollX: true,
                    responsive:false,
                    ajax: 'includes/datacontrol?name=studentEnquiry',
                        columns: [
                        { data: 'st_enquiry_id' },                                    
                        { data: 'std_name' },                                    
                        { data: 'std_phno' },
                        { data: 'std_email' },
                        { data: 'street' },
                        { data: 'suburb' },
                        { data: 'post_code' },
                        { data: 'std_course' },
                        { data: 'startplan_date' },
                        { data: 'st_coursetype' },
                        { data: 'visited' },
                        { data: 'referedby' },
                        { data: 'std_fee' },
                        { data: 'appointment' },
                        { data: 'Visa_condition' },
                        { data: 'std_visa_status' },
                        { data: 'staffComments' },
                        { data: 'preferences' },
                        { data: 'remarksNotes' },
                        { data: 'action' },
                    ],
                    // "dom": 'Blfrtip',
                    // "buttons": ['pdf']
    //                 dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>" +
    //   "<'row'<'col-sm-12'tr>>" +
    //   "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    // buttons: [
    //   {
    //     extend: 'pdf',
    //     className: "btn-sm",
    //     text: 'EXPORT',
    //     filename: function() {
    //     return "Enquiries"
    //     },
    //     title: function() {
    //     return "<div><div>Logo</div><div>Auztraining</div></div>"
    //     },
    //     exportOptions: {
    //       columns: [ 1, 2, 3, 4, 5, 6]
    //     }
    //   }
    // ]
                });
            //    $('#datatable-all').DataTable({
            //     lengthMenu: [5, 10, 20],
            //     language:{
            //         paginate:{
            //             previous:"<i class='mdi mdi-chevron-left'>",
            //             next:"<i class='mdi mdi-chevron-right'>"}},
            //    drawCallback:function(){
            //     $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
            //     },
            //     scrollX: true,
            //         ajax: 'includes/datacontrol?name=all_students',
            //             columns: [
            //             { data: 'st_unique_id' },                                    
            //             { data: 'st_enrol_name' },                                    
            //             { data: 'std_phno' },
            //             { data: 'std_email' },
            //             { data: 'course' },
            //             { data: 'std_date' },
            //             { data: 'action' },
            //         ]
            //     });
                // $('#datatable_enrol').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                // scrollX: true,
                //     ajax: 'includes/datacontrol?name=student_enrol',
                //         columns: [
                //         { data: 'st_enrol_name' },                                    
                //         { data: 'st_enrol_id' },                                    
                //         { data: 'st_enq_id' },                                    
                //         { data: 'st_enrol_givenname' },
                //         { data: 'st_enrol_middlename' },
                //         { data: 'st_enrol_qual' },
                //         { data: 'st_enrol_venue' },
                //         { data: 'st_enrol_source' },
                //         { data: 'action' },
                //     ],
                // });
                
                // $('#datatable_invoices').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                // scrollX: true,
                //     ajax: 'includes/datacontrol?name=student_invoices',
                //         columns: [
                //         { data: 'inv_id' },                                    
                //         { data: 'inv_std_name' },                                    
                //         { data: 'inv_course' },
                //         { data: 'inv_fee' },
                //         { data: 'inv_paid' },
                //         { data: 'inv_due' },
                //         { data: 'inv_payment_date' },
                //     ],
                // });

                $('#accordionExample').on('show.bs.collapse', function(e){
                setTimeout(function () {
                    $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
                }, 10);
                });
            })

            $("#filter_input").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#student_filter_table tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('#student_filter').click(function(){

                var divToPrint=$("#student_filter_table");
                var thfinder='';
                var tdfinder='';
                for(var i=0;i<tableArray.length;i++){
                    // var number=0;
                var number=tableArray[i];

                 thfinder+='thead tr th:eq("'+number+'"),';
                 tdfinder+='td:eq("'+number+'"),';

                }

                thfinder=thfinder.slice(0, -1);
                tdfinder=tdfinder.slice(0, -1);

                $(divToPrint).find(thfinder).remove();

                $(divToPrint).find("tbody tr").each(function() {
                $(this).find(tdfinder).remove();
                });


                newWin=  window.open('', '_top', '','');       
                newWin.document.write('<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.0 Transitional//EN\' \'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\'><html xmlns=\'http://www.w3.org/1999/xhtml\'><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv=\'Content-Type\' content=\'text/html; charset=iso-8859-1\' /><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"><title>Patient Feed</title><style>@page { size:Legal landscape; } table, th, td { border: 1px solid;border-collapse: collapse;text-align: left;padding: 5px 10px;}  body { background: #FFF;color: #000;font-size: 12pt;padding: 0;} .print_div{ display:flex;justify-content:center; } .print_logo{ height: 85px;width: 10%;margin: 0;padding: 0;}  </style></head><body><div class="print_div"><img class="print_logo" src="assets/images/logo-dark.webp"></div><table>'+$(divToPrint).html()+'</table></body></html>');
                newWin.print();
                newWin.close();

            })

            var tableColorIndex=[];
            var tablethCount=$('#student_filter_table').find('thead tr th').length;
            var tableArray = Array(tablethCount).fill(0).map((n, i) => n + i);
            $(document).on('click','#student_filter_table th',function(){
                var index=$(this)[0].cellIndex+1;
                $('#student_filter_table tbody tr td:nth-child('+index+')').toggleClass('table-bg');
                $(this).toggleClass('table-bg');
                if(tableArray.includes(index)){
                    tableArray = tableArray.filter(item => item !== index-1);
                }else{
                    tableArray.push(index);
                }
            })

        </script>
        <?php }else{ ?>
            <script>

            </script>
        <?php } ?>
    </body>
</html>
 <?php
  } else{ 
    header('Location: student_enquiry.php');
} 
?>