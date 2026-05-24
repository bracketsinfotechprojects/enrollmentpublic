<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
// print_r($_SESSION);
if(@$_SESSION['user_type']==1){

    $query=mysqli_fetch_all(mysqli_query($connection,"select DISTINCT(st_unique_id) from student_attendance"),MYSQLI_ASSOC); 
    $queryCrs=mysqli_fetch_all(mysqli_query($connection,"select DISTINCT(st_course_unit) from student_attendance"),MYSQLI_ASSOC); 

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Attendance</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
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
                                    <h4 class="mb-sm-0">Attendance</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Attendance</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <?php 
                        
                        $query=mysqli_query($connection,"SELECT DISTINCT(st_unique_id) FROM `student_attendance`");                        
                        ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                            <table id="datatable" class="table nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">Student ID</th>
                                                        <th scope="col">Student Name</th>
                                                        

                                                        <?php
                                                        
                                                        foreach($queryCrs as $value){
                                                            echo '<th scope="col">'.$value['st_course_unit'].'</th>';
                                                        }

                                                        ?>

                                                        <!-- <th scope="col">Course</th> -->
                                                        <th scope="col">Mobile</th>
                                                        <th scope="col">Email</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                <?php 
                                                $tbody='';
                                                $stdUnit=array();
                                                while($queryRes=mysqli_fetch_array($query)){ 
                                                    $skip=0;
                                                    $dateAttended='';
                                                    $id=$queryRes['st_unique_id'];
                                                    $stdUnit[$id]=array();
                                                    echo "SELECT * FROM `student_enrolment` WHERE st_unique_id='$id'";
                                                    $selectName=mysqli_fetch_array(mysqli_query($connection,"SELECT * FROM `student_enrolment` WHERE st_unique_id='$id'"));
                                                    print_r($selectName);
                                                    // exit;
                                                        if($selectName['st_unique_id']!=''){
                                                    ?>

                                                        <?php 
                                                        echo '<tr>';
                                                        echo "<td>".$queryRes['st_unique_id']."</td>";
                                                        echo "<td>".$selectName['st_given_name'].' '.$selectName['st_middle_name']."</td>";
                                                        foreach($queryCrs as $value){
                                                        $coursedunit=$value['st_course_unit'];
                                                        // echo "SELECT GROUP_CONCAT(st_unit_date SEPARATOR ', ') AS st_unit_dates from student_attendance where st_course_unit='$coursedunit' AND st_unique_id='$id'";
                                                        $checkUnitDate=mysqli_fetch_array(mysqli_query($connection,"SELECT GROUP_CONCAT(st_unit_date SEPARATOR ', ') AS st_unit_dates from student_attendance where st_course_unit='$coursedunit' AND st_unique_id='$id'"));
                                                        


                                                        echo '<td>'.$checkUnitDate['st_unit_dates'].'</td>';


                                                        }

                                                        
                                                        echo "<td>".$selectName['st_mobile']."</td>";
                                                        echo "<td>".$selectName['st_email']."</td>";
                                                        echo "</tr>";

                                                        ?>
                                                 <?php } 
                                                 } 
                                                 ?>

                                                </tbody>
                                            </table>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>
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
                $('#datatable').DataTable({lengthMenu: [50, 100, 150],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                scrollX: true,
                });
            });
        </script>
    </body>
</html>
<?php }else{ 

header("Location: index.php");

}
?>