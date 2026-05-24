<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_id'] === ''){
    header('Location: index.php');
    exit;
}
// Students (user_type=0 or 'student') use student area only – admin login is index.php
if(isset($_SESSION['user_type']) && ((int)$_SESSION['user_type']===0 || $_SESSION['user_type']==='student')){
    header('Location: student_docs.php');
    exit;
}
if(isset($_SESSION['user_type'])){

    $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");
    $filterCols=mysqli_fetch_array(mysqli_query($connection,"SELECT GROUP_CONCAT(column_name SEPARATOR ',') as column_names FROM information_schema.COLUMNS WHERE table_name LIKE 'student_enquiry'"));
    $filterColsArray=explode(',',$filterCols['column_names']);

    $filterDropDown=['Student ID','Student Name','Contact Number','Email','Course Type','Date','States','Course','Visa Condition','Visa Status'];
    
    $filterCols2=mysqli_fetch_array(mysqli_query($connection,"SELECT GROUP_CONCAT(column_name SEPARATOR ',') as column_names FROM information_schema.COLUMNS WHERE table_name LIKE 'slot_book'"));
    $filterCols2Array=explode(',',$filterCols2['column_names']);

    $filterDropDown2=['Student Name','Enquiry ID','Phone','Email','Purpose','Booked by','Sent Link','Appointment Time'];
    
    $filterCols3=mysqli_fetch_array(mysqli_query($connection,"SELECT GROUP_CONCAT(column_name SEPARATOR ',') as column_names FROM information_schema.COLUMNS WHERE table_name LIKE 'counseling_details'"));
    $filterCols3Array=explode(',',$filterCols3['column_names']);

    $filterDropDown3=['Student Name','Enquiry ID','Phone','Email','Type','Team Member','Start Date','End Date'];

    $enquriesConvQry=mysqli_query($connection,"SELECT (SELECT COUNT(*) FROM student_enquiry WHERE st_enquiry_id NOT IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '')) AS not_converted, (SELECT COUNT(*) FROM student_enquiry WHERE st_enquiry_id IN (SELECT st_enquiry_id FROM student_enrolment WHERE st_enquiry_id != '')) AS converted");

    $enquriesConv=mysqli_fetch_array($enquriesConvQry);

    if($enquriesConv['converted']!=0 && $enquriesConv['not_converted']!=0){

        $converted=round((intval($enquriesConv['converted']) / intval($enquriesConv['not_converted'])+intval($enquriesConv['converted'])))* 100;    
        $notconverted=round((intval($enquriesConv['not_converted']) / intval($enquriesConv['not_converted']+$enquriesConv['converted']))) * 100;
        $totalEnq=intval($enquriesConv['not_converted'])+intval($enquriesConv['converted']);
        $per_conv= round((intval($enquriesConv['converted'])/$totalEnq)*100);        
        $per_notconv= round((intval($enquriesConv['not_converted'])/$totalEnq)*100);        

    }else{

                
        $converted=0;    
        $notconverted=0;
        $totalEnq=0;
        $per_conv= 0;        
        $per_notconv= 0;        


    }

    $totalTimeCounsil=mysqli_query($connection,"SELECT SUM(TIMESTAMPDIFF(DAY, counsil_timing, counsil_end_time)) AS days, SUM(TIMESTAMPDIFF(HOUR, counsil_timing, counsil_end_time)) % 24 AS hours, SUM(TIMESTAMPDIFF(MINUTE, counsil_timing, counsil_end_time)) % 60 AS mins FROM `counseling_details` WHERE `counsil_enquiry_status` = 0;");
    $timeSpent=mysqli_fetch_array($totalTimeCounsil);
    if($timeSpent['days']!=0 || $timeSpent['days']!=''){        
        $timeSpentRes=$timeSpent['days'];
        $timeSpentHrsRes=$timeSpent['hours'];
        $timeSpentMnsRes=$timeSpent['mins'];
    }else{
        $timeSpentRes=0;
        $timeSpentHrsRes=0;
        $timeSpentMnsRes=0;
    }

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" /> 

        <?php 
        include('includes/app_includes.php');
         ?>
        <style>
            #filter-dropdowns td{
                margin:0;
                padding:0;
                padding-top:1rem;
            }
            #filter-dropdowns .dropdown-toggle{
                padding: 3px 10px 3px 10px;;
            }
            tbody tr td:nth-child(5) {
                white-space: break-spaces;
                width:20%;
            }
            #datatable tbody tr td:nth-child(7) {
                white-space: break-spaces;
                width:10%;
            }
            #datatable tbody tr td:nth-child(3) {
                white-space: break-spaces;
                width:10%;
            }


.container {
  overflow: scroll;
  height: 80vh;
  margin-top:2rem;
  /* width: 300px; */
}

#appointments_table , #counsel_table,#table-filter {
  border-collapse: collapse;
}

#table-filter th,
#table-filter td, #appointments_table th , #appointments_table td, #counsel_table th , #counsel_table td {
  max-width: 300px;
  padding: 8px 16px;
  border: 1px solid #ddd;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

#table-filter thead ,#appointments_table thead ,#counsel_table thead {
  position: sticky;
  inset-block-start: 0;
  background-color: #ddd;
}

            #table-filter::-webkit-scrollbar-track
            {
                -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
                background-color: #F2F2F2;
                border-radius:5px;
            }

            #table-filter::-webkit-scrollbar
            {
                width: 10px;
                height:5px;
                background-color: #F2F2F2;
            }

            #table-filter::-webkit-scrollbar-thumb
            {
                background-color: var(--color);
                border: 2px solid var(--color);
                border-radius:5px;
            }
       </style>

    </head>

    <body>

        <div class="main-wrapper">

            
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>
            
            <div class="page-wrapper">
                <div class="content pb-0">
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
                        /* Admin (1) and Staff (2): same dashboard. Students redirected above. */
                        if((int)$_SESSION['user_type'] === 1 || (int)$_SESSION['user_type'] === 2){
                        ?>

                        <div class="row">
                            <div class="col-xl-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Enquiry Records</h5>
                                            <div>
                                                <ul class="list-unstyled">
                                                    <li class="py-3">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs align-self-center me-3">
                                                                <div class="avatar-title rounded-circle bg-light text-primary font-size-18">
                                                                    <i class="ri-checkbox-circle-line"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <div class="d-flex justify-content-between"><p class="text-muted mb-2">Converted</p><p><?php echo $enquriesConv['converted']; ?></p></div>
                                                                <div class="progress progress-sm animated-progess">
                                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $per_conv; ?>%" aria-valuenow="<?php echo $per_conv; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalEnq; ?>"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="py-3">
                                                        <div class="d-flex">
                                                            <div class="avatar-xs align-self-center me-3">
                                                                <div class="avatar-title rounded-circle bg-light text-primary font-size-18">
                                                                    <i class="ri-calendar-2-line"></i>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                            <div class="d-flex justify-content-between"><p class="text-muted mb-2">Not Converted</p><p><?php echo $enquriesConv['not_converted']; ?></p></div>
                                                                <div class="progress progress-sm animated-progess">
                                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $per_notconv; ?>%" aria-valuenow="<?php echo $per_notconv; ?>" aria-valuemin="0" aria-valuemax="<?php echo $totalEnq; ?>"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <!-- end card-body -->
                                    </div>
                                    <!-- end card -->
                                </div>
                                <div class="col-xl-3 col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex text-muted">
                                            <div class="flex-shrink-0  me-3 align-self-center">
                                                <div class="avatar-sm">
                                                    <div class="avatar-title bg-light rounded-circle text-primary font-size-20">
                                                        <i class="ri-group-line"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <p class="mb-1">Total Counselings Time</p>
                                                <h5 class="mb-3"><?php echo $timeSpentRes.' Days '. $timeSpentHrsRes.' Hours '.$timeSpentMnsRes.' Minutes';  ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- end card-body -->
                                </div>
                                <!-- end card -->
                            </div>
                        </div>

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
                                                                    <th>Visa Condiition</th>
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
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingTwo">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                                    Follow Up Calls
                                                    </button>
                                                </h2>
                                                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                            <table id="datatable_followup" class="table table-striped table-bordered  nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Enquiry ID</th>
                                                                <th scope="col">Student Name</th>
                                                                <th scope="col">Contact Number</th>
                                                                <th scope="col">Contacted Person</th>
                                                                <th scope="col">Contacted Time</th>
                                                                <th scope="col">Date</th>
                                                                <th scope="col">Mode of Contact</th>
                                                                <th scope="col">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        </tbody>
                                                    </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingThree">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_counsel" aria-expanded="false" aria-controls="collapse_counsel">
                                                    Counselings
                                                    </button>
                                                </h2>
                                                <div id="collapse_counsel" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                            <table id="datatable_counseling" class="table table-striped table-bordered  nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th scope="col">Team Member Name</th>
                                                                <th scope="col">Counseling Type</th>
                                                                <th scope="col">Currently Working</th>
                                                                <th scope="col">Visa Condition</th>
                                                                <th scope="col">Education</th>
                                                                <th scope="col">Counseling Date</th>
                                                                <th scope="col">Time Spent</th>
                                                                <th scope="col">Action</th>
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
                                        <h4 class="card-title mb-4">Counseling</h4>

                                        <div class="row mb-3">
                                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                            <div class="columns-report">
                                               <select class="selectpicker boot-select" title="Columns" data-live-search="true" id="counsel_select" >
                                                <option value="<?php echo $filterCols3Array['2']; ?>" id="option_2" data-value="2" data-name="Team Member">Team Member</option>
                                                <option value="<?php echo $filterCols3Array['15']; ?>" id="option_15" data-value="15" data-name="Counseling Type">Counseling Type</option>
                                                <option value="<?php echo $filterCols3Array['22']; ?>" id="option_22" data-value="22" data-name="Date">Date</option>
                                               </select> 
                                            </div>
                                            <div class="columns-report counsel_input">
                                                <input type="text" id="counsel_column_2" placeholder="value" class="counsel_column_value form-control">
                                                <input type="text" id="counsel_column_22" placeholder="value" class="counsel_column_value form-control">
                                                <select name="counsel_column_15" class="counsel_column_value form-select" title="Counsel Type" id="counsel_column_15" style="width:auto;">
                                                <option value="1" data="1">Face to Face</option>
                                                <option value="2" data="2">Video</option>
                                                </select> 
                                                <!-- <input type="text" id="counsel_column_15" placeholder="value" class="counsel_column_value form-control"> -->
                                            </div>
                                            <div class="columns-report">
                                            <button type="button" class="btn btn-success" id="submit_column3_filter">Submit</button>
                                            </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button" class="btn btn-secondary" id="counsel_export">Export <i class="fas fa-file-export export-filter"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row counsel-badges-div mb-3 align-items-start">
                                        </div>  
                                    <div class="container" style="height:30vh">
                                        <table id="counsel_table" >
                                            <thead>
                                                <tr>
                                                <?php
                                                
                                                foreach($filterDropDown3 as $key=>$value){
                                                    if($key>=5){
                                                        $class="";
                                                    }else{
                                                        $class="";
                                                    }
                                                    echo "<th class='$class'>$value</th>";
                                                }

                                                ?>
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

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Appointments</h4>

                                        <div class="row mb-3">
                                            <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <div class="d-flex align-items-center flex-wrap gap-2">
                                            <div class="columns-report">
                                               <select class="selectpicker boot-select" title="Columns" data-live-search="true" id="appoint_select" >
                                                <option value="<?php echo $filterCols2Array['5']; ?>" id="option_5" data-value="5" data-name="Team Member">Team Member</option>
                                                <option value="<?php echo $filterCols2Array['2']; ?>" id="option_2" data-value="2" data-name="Booking Date">Booking Date</option>
                                               </select> 
                                            </div>
                                            <div class="columns-report appoint_input">
                                                <input type="text" id="appoint_column_5" placeholder="value" class="appoint_column_value form-control">
                                                <input type="text" id="appoint_column_2" placeholder="value" class="appoint_column_value form-control">
                                            </div>
                                            <div class="columns-report">
                                            <button type="button" class="btn btn-success" id="submit_column2_filter">Submit</button>
                                            </div>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <button type="button"  class="btn btn-secondary" id="appoint_export">Export <i class="fas fa-file-export export-filter"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row appoint-badges-div mb-3 align-items-start">
                                        </div>  
                                        <div class="container" style="height:30vh">
                                        <table id="appointments_table">
                                            <thead>
                                                <tr>
                                                <?php
                                                
                                                foreach($filterDropDown2 as $key=>$value){
                                                    if($key>=5){
                                                        $class="";
                                                        // $class="imp-none";
                                                    }else{
                                                        $class="";
                                                    }
                                                    echo "<th class='$class'>$value</th>";
                                                }

                                                ?>
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

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title mb-4">Student Details Export</h4>

                                <div class="row g-2 align-items-center mb-3">
                                    <div class="col-12 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                        <div class="d-flex align-items-center flex-wrap gap-2">
                                            <div style="min-width:220px;">
                                                <select class="selectpicker boot-select w-100" title="Columns" data-live-search="true" id="column_select" >
                                            <option value="0" id="option_0" data-value="0" data-name="0">-- select --</option>
                                            <option value="<?php echo $filterColsArray['1']; ?>" id="option_1" data-value="1" data-name="Enquiry ID">Enquiry ID</option>
                                            <option value="<?php echo $filterColsArray['5']; ?>" id="option_5" data-value="5" data-name="Phone">Phone</option>
                                            <option value="<?php echo $filterColsArray['8']; ?>" id="option_8" data-value="8" data-name="Course Type">Course Type</option>
                                            <option value="<?php echo $filterColsArray['7']; ?>" id="option_7" data-value="7" data-name="Course">Course</option>
                                            <option value="<?php echo $filterColsArray['11']; ?>" id="option_11" data-value="11" data-name="States">States</option>
                                            <option value="<?php echo $filterColsArray['14']; ?>" id="option_14" data-value="14" data-name="Heared From">Heared From</option>
                                            <option value="<?php echo $filterColsArray['22']; ?>" id="option_22" data-value="22" data-name="Ethinicity">Ethinicity</option>
                                            <option value="<?php echo $filterColsArray['35']; ?>" id="option_35" data-value="35" data-name="Date">Date</option>
                                                </select>
                                            </div>

                                            <div class="input-div">
                                                <div class="d-flex flex-wrap gap-2">
                                            <input type="text" id="column_1" placeholder="value" class="column_value form-control" style="min-width:220px;">
                                            <input type="text" id="column_5" placeholder="value" class="column_value form-control" style="min-width:220px;">
                                            <input type="text" id="column_22" placeholder="value" class="column_value form-control" style="min-width:220px;">
                                            <input type="text" id="column_35" placeholder="value" class="column_value form-control" style="min-width:220px;">
                                            <select name="column_8" class="column_value form-select" title="Course Type" id="column_8" style="min-width:220px;">
                                                <?php  
                                                $st_course_type=['-- select --','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
                                                for($i=0;$i<count($st_course_type);$i++){     
                                                    $count=$i;                                                       
                                                    echo '<option value="'.$count.'" data="'.$st_course_type[$i].'">'.$st_course_type[$i].'</option>';
                                                }
                                                ?>
                                            </select>
                                            <select name="column_11" class="column_value form-select" title="States" id="column_11" style="min-width:220px;">
                                                <?php  
                                                $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
                                                for($i=0;$i<count($st_states);$i++){     
                                                    $count=$i;                                                       
                                                    echo '<option value="'.$count.'" data="'.$st_states[$i].'">'.$st_states[$i].'</option>';
                                                }
                                                ?>
                                            </select>
                                            <select name="column_14" class="column_value form-select" title="Heared From" id="column_14" style="min-width:220px;">
                                                <?php  
                                                $st_heared=['Word of Mouth','Family or Friends','Website','Gumtree','Facebook','Instagram','Linkedin','Mail outs','Migration Agency','Other:'];
                                                for($i=0;$i<count($st_heared);$i++){     
                                                    $count=$i;                                                       
                                                    echo '<option value="'.$count.'" data="'.$st_heared[$i].'">'.$st_heared[$i].'</option>';
                                                }
                                                ?>
                                            </select>
                                                </div>
                                            </div>

                                            <button type="button" class="btn btn-success" id="submit_column_filter">Submit</button>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-end flex-wrap gap-2">
                                            <div class="me-2">
                                                <strong>Total Records: </strong><span id="total_filter_records"></span>
                                            </div>
                                            <div class="btn-group">
                                                <i class="fas fa-filter dropdown-toggle" data-bs-auto-close="false" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                                <div class="dropdown-menu">
                                                    <select name="filter-select" class="selectpicker boot-select" data-selected-text-format="count" multiple id="filter-select" title="Filter Columns">
                                                        <?php
                                                        foreach($filterDropDown as $key=>$value){
                                                            if($key<=4){
                                                                $select='selected';
                                                            }else{
                                                                $select='';
                                                            }
                                                            $key=$key+1;
                                                            echo "<option value='$key' class='filter-opt' $select>$value</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-secondary" id="student_export">Export <i class="fas fa-file-export"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                <div class="row badges-div align-items-start"></div>
                <hr class="bg-dark border-2 border-top border-dark">

                <div class="row align-items-end">
                    <div class="col-md-12">
                        <div class="container">
                            <table id="table-filter" class="table nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                <tr>
                                    <?php
                                    foreach($filterDropDown as $key=>$value){
                                        if($key>=5){
                                            $class="imp-none";
                                        }else{
                                            $class="";
                                        }
                                        echo "<th class='$class'>$value</th>";
                                    }
                                    ?>
                                    </tr>
                                </thead>
                                <tbody id="student_filter_body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer_includes.php'); ?>


<script>
$(function() {
    var options = {};
    options.ranges = {
              'Today': [moment(), moment()],
              'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
              'Last 7 Days': [moment().subtract(6, 'days'), moment()],
              'Last 30 Days': [moment().subtract(29, 'days'), moment()],
              'This Month': [moment().startOf('month'), moment().endOf('month')],
              'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            };
    $('#column_35 , #appoint_column_2, #counsel_column_22').daterangepicker(options);
});

$(document).on('click','#submit_column_filter',function(){
    var columnId = $('#column_select option:selected').attr('data-value');
    var columnName = $('#column_select option:selected').attr('data-name');
    var mainColName = $('#column_select').val();

    if(!columnId || columnId === '0') {
        return;
    }

    var badgeId = $('#badge_'+columnId).val();
    var colValue = ($('#column_'+columnId).val() || '').trim();

    if(colValue !== '' && columnName != 0 && ( badgeId==undefined || badgeId=='' )){
        $('.badges-div').append('<button type="button" data-col-name="'+mainColName+'" id="badge_'+columnId+'" data-id="'+columnId+'" main-value="'+colValue+'" class="btn btn-secondary btn-rounded badge-button close-badge"><i class="mdi mdi-close-circle"></i> <span>'+columnName+'</span></button>');
        filterMain();
    }
})

$(document).on('click','.close-badge',function(){
    $(this).remove();
    var idCol=$(this).attr('data-id');
    var nameCol=$(this).children("span").text();
    var mainCol=$(this).attr('data-col-name');
    if(idCol && idCol !== '0') {
        $('#column_select').append('<option value="'+mainCol+'" id="option_'+idCol+'" data-value="'+idCol+'" data-name="'+nameCol+'">'+nameCol+'</option>');
    }
    $('#column_select').val('0').change();
    $('#column_select').selectpicker('refresh');
    filterMain();
})

function filterMain(){
    var columnData={};
    $('.badge-button').each(function(){
        var option=$(this).attr('data-id');
        $('#option_'+option).remove();
        $('.column_value').val('');
        $('.column_value').hide();
        $('#column_select').selectpicker('refresh');     
        $('#column_select').val('0').change();
        
        var columnName=$(this).attr('data-col-name');
        var columnValue=$(this).attr('main-value');
        columnData[columnName]=columnValue;
        
    })    
        
    fetchFilter(columnData);

}

function fetchFilter(objFilter){
    $.ajax({
        type:'post',
        url:'includes/datacontrol',
        data:{objFilter,formName:'fetchEnquiries'},
        success:function(data){
            $('#student_filter_body').html(data);
            $('#total_filter_records').html($('#student_filter_body tr').length);                        
        }
    })
}

$(document).on('change','#column_select',function(){
    var inputType = $('#column_select option:selected').attr('data-value');
    $('.column_value').hide();
    if(inputType && inputType !== '0') {
        $('#column_'+inputType).show();
    }
})

fetchFilter('');

$(document).on('click', '#student_export', function(){
    var divToPrint = $("#table-filter");
    if(divToPrint.length === 0){
        return;
    }
    $(divToPrint).find('.imp-none').remove();
    newWin = window.open('', '_top', '', '');
    newWin.document.write('<!DOCTYPE html PUBLIC \'-//W3C//DTD XHTML 1.0 Transitional//EN\' \'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\'><html xmlns=\'http://www.w3.org/1999/xhtml\'><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><meta http-equiv=\'Content-Type\' content=\'text/html; charset=iso-8859-1\' /><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"><title>Student Details Export</title><style>#student_filter_body th , #student_filter_body td { white-space: pre; }  .td_scroll_height { width:max-content; }@page { size:Legal landscape; } table, th, td { border: 1px solid;border-collapse: collapse;text-align: left;padding: 5px 10px;}  body { background: #FFF;color: #000;font-size: 12pt;padding: 0;} .print_div{ display:flex;justify-content:center; } .print_logo{ height: 85px;width: 10%;margin: 0;padding: 0;}  </style></head><body><div class="print_div"><img class="print_logo" src="assets/images/logo-dark.webp"></div><table>'+$(divToPrint).html()+'</table></body></html>');
    newWin.print();
    newWin.close();
})

$(document).on('change','#filter-select',function(){
    $('#table-filter thead tr th').addClass('imp-none');  
    $('#table-filter tbody tr td').addClass('imp-none');  
    $('#filter-select option:selected').map(function(){
        var index=this.value;

        $('#table-filter thead tr th:nth-child('+index+')').removeClass('imp-none');
        $('#table-filter thead tr th:nth-child('+index+')').addClass('imp-block');
        
        $('#table-filter tbody tr td:nth-child('+index+')').removeClass('imp-none');
        $('#table-filter tbody tr td:nth-child('+index+')').addClass('imp-block');
    });
})   

$(document).on('click','#submit_column2_filter',function(){
    var columnName=$('#appoint_select option:selected').attr('data-name');                    
    var mainColName=$('#appoint_select').val();
    var columnId=$('#appoint_select option:selected').attr('data-value');
    var badgeId=$('#appoint_badge_'+columnId).val();   
    var colValue=$('#appoint_column_'+columnId).val();                                              
if(colValue!='' && columnId!=undefined && columnName!=0 && ( badgeId==undefined || badgeId=='' )){
    $('.appoint-badges-div').append('<button type="button" data-col-name="'+mainColName+'" id="appoint_badge_'+columnId+'" data-id="'+columnId+'" main-value="'+colValue+'" class="btn btn-secondary btn-rounded appoint-badge-button appoint_close-badge"><i class="mdi mdi-close-circle"></i> <span>'+columnName+'</span></button>');
    appoint_filterMain();
}
})

$(document).on('click','.appoint_close-badge',function(){
    $(this).remove();
    var idCol=$(this).attr('data-id');
    var nameCol=$(this).children("span").text();
    var mainCol=$(this).attr('data-col-name');
    $('#appoint_select').append('<option value="'+mainCol+'" id="option_'+idCol+'" data-value="'+idCol+'" data-name="'+nameCol+'">'+nameCol+'</option>');
    $('#appoint_select').val(0).change();
    $('#appoint_select').selectpicker('refresh');  
    appoint_filterMain();
})


function appoint_filterMain(){
    var columnData={};
    $('.appoint-badge-button').each(function(){
        var option=$(this).attr('data-id');
        $('#option_'+option).remove();
        $('.appoint_column_value').val('');
        $('.appoint_column_value').hide();
        $('#appoint_select').selectpicker('refresh');     
        $('#appoint_select').val(0).change();
        
        var columnName=$(this).attr('data-col-name');
        var columnValue=$(this).attr('main-value');
        columnData[columnName]=columnValue;
        
    })    
        
    appoint_fetch(columnData);

}

function appoint_fetch(objFilter){
    $.ajax({
        type:'post',
        url:'includes/datacontrol',
        data:{objFilter,formName:'fetchAppoints'},
        success:function(data){
            $('#appointments_table tbody').html(data);
            // $('#total_filter_records').html($('#student_filter_body tr').length);                        
        }
    })
}

$(document).on('change','#appoint_select',function(){                                                
    var inputType=$('#appoint_select option:selected').attr('data-value');
    $('.appoint_column_value').hide();
    console.log(inputType);
    $('#appoint_column_'+inputType).show();
})

appoint_fetch('');

$(document).on('click','#submit_column3_filter',function(){
    var columnName=$('#counsel_select option:selected').attr('data-name');                    
    var mainColName=$('#counsel_select').val();
    var columnId=$('#counsel_select option:selected').attr('data-value');
    var badgeId=$('#counsel_badge_'+columnId).val();   
    var colValue=$('#counsel_column_'+columnId).val();                                              
if(colValue!='' && columnId!=undefined && columnName!=0 && ( badgeId==undefined || badgeId=='' )){
    $('.counsel-badges-div').append('<button type="button" data-col-name="'+mainColName+'" id="counsel_badge_'+columnId+'" data-id="'+columnId+'" main-value="'+colValue+'" class="btn btn-secondary btn-rounded counsel-badge-button counsel_close-badge"><i class="mdi mdi-close-circle"></i> <span>'+columnName+'</span></button>');
    counsel_filterMain();
}
})

$(document).on('click','.counsel_close-badge',function(){
    $(this).remove();
    var idCol=$(this).attr('data-id');
    var nameCol=$(this).children("span").text();
    var mainCol=$(this).attr('data-col-name');
    $('#counsel_select').append('<option value="'+mainCol+'" id="option_'+idCol+'" data-value="'+idCol+'" data-name="'+nameCol+'">'+nameCol+'</option>');
    $('#counsel_select').val(0).change();
    $('#counsel_select').selectpicker('refresh');  
    counsel_filterMain();
})


function counsel_filterMain(){
    var columnData={};
    $('.counsel-badge-button').each(function(){
        var option=$(this).attr('data-id');
        $('#option_'+option).remove();
        $('.counsel_column_value').val('');
        $('.counsel_column_value').hide();
        $('#counsel_select').selectpicker('refresh');     
        $('#counsel_select').val(0).change();
        
        var columnName=$(this).attr('data-col-name');
        var columnValue=$(this).attr('main-value');
        columnData[columnName]=columnValue;
        
    })    
        
    counsel_fetch(columnData);

}

function counsel_fetch(objFilter){
    $.ajax({
        type:'post',
        url:'includes/datacontrol',
        data:{objFilter,formName:'fetchCounsel'},
        success:function(data){
            $('#counsel_table tbody').html(data);
            // $('#total_filter_records').html($('#student_filter_body tr').length);                        
        }
    })
}

$(document).on('change','#counsel_select',function(){                                                
    var inputType=$('#counsel_select option:selected').attr('data-value');
    $('.counsel_column_value').hide();
    console.log(inputType);
    $('#counsel_column_'+inputType).show();
})

counsel_fetch('');
</script>
<?php } ?>
</body>
</html>
<?php
  } else{ 
    header('Location: student_enquiry.php');
} 
?>