<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
    
            $qualifications=mysqli_query($connection,"SELECT * from qualifications where qualification_status!=1");
        $venue=mysqli_query($connection,"SELECT * from venue where venue_status!=1");
        $source=mysqli_query($connection,"SELECT * from source where source_status!=1");
        $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");

    if(isset($_GET['enrol'])){
        $Updatestatus=1;
        $enrolId=base64_decode($_GET['enrol']);
        $queryRes=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enrolment where st_enrol_status!=1 and st_enrol_id=$enrolId"));

    }else{
        $Updatestatus=0;
        $enrolId=0;
    }

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Enrolment</title>
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
                                    <h4 class="mb-sm-0">Student's Enrolment Form</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Student's Enquiry</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <!-- end row -->
                        <div class="row">
                        <form class="student_enrol_form" id="student_enrol_form">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">                                        
                                        <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="rto_name">RTO Name</label>
                                                            <input type="text" placeholder="RTO Name" name="rto_name" class="form-control" id="rto_name">
                                                        <div class="error-feedback">
                                                            Please enter the RTO Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="courses">Course Name</label><br>
                                                        <select  name="courses" class="selectpicker courses" data-selected-text-format="count" multiple id="courses" title="Courses">
                                                        <!-- <option value="0">--select--</option> -->
                                                        <?php 
                                                        while($coursesRes=mysqli_fetch_array($courses)){
                                                        ?>                                                            
                                                            <option value="<?php echo $coursesRes['course_id']; ?>" <?php echo $queryRes['st_enrol_course']==$coursesRes['course_id'] ? 'selected' : ''; ?>><?php echo $coursesRes['course_sname'].'-'.$coursesRes['course_name']; ?></option>
                                                            <?php } ?>
                                                        </select>    
                                                        <div class="error-feedback">
                                                            Please select the Courses
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="branch_name">Branch Name</label>
                                                            <input type="text" name="branch_name" placeholder="Branch Name" class="form-control" id="branch_name">
                                                        <div class="error-feedback">
                                                            Please enter the Branch Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="photo_upload">Photo Upload</label>
                                                            <input type="file" name="photo_upload" class="form-control" id="photo_upload">
                                                        <div class="error-feedback">
                                                            Please Upload Photo
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="given_name">Given Name</label>
                                                        <input type="text" class="form-control" id="given_name" placeholder="Given Name" value="<?php echo $queryRes['st_given_name'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Given name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="surname">Surname</label>
                                                        <input type="text" class="form-control" id="surname" placeholder="Surname" value="<?php echo $queryRes['st_surname'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Surname
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="dob">Date of Birth</label>
                                                        <input type="date" class="form-control" id="dob" placeholder="DOB" value="<?php echo $queryRes['st_dob'] ?>" >
                                                        <div class="error-feedback">
                                                            Please Select the Date of Birth
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="birth_country">Country of Birth</label>
                                                        <input type="text" class="form-control" id="birth_country" placeholder="Country of Birth" value="<?php echo $queryRes['st_birth_country'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Birth Country Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="street_details">Door No & Street</label>
                                                        <input type="text" class="form-control" id="street_details" placeholder="Street" value="<?php echo $queryRes['st_street_details'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Street Details
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="sub_urb">Suburb</label>
                                                        <input type="text" class="form-control" id="sub_urb" placeholder="Suburb" value="<?php echo $queryRes['st_sub_urb'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Suburb
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="stu_state">State</label>
                                                        <select name="stu_state" id="stu_state" class="form-control">
                                                        <?php  
                                                        $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
                                                        for($i=0;$i<count($st_states);$i++){
                                                            $checked= $i==$queryRes['st_state'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_states[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please enter the State
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="post_code">Post Code</label>
                                                        <input type="tel" maxlength="6" class="form-control number-field" id="post_code" placeholder="Post Code" value="<?php echo $queryRes['st_post_code'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Post Code
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="tel_num">TelePhone Number</label>
                                                        <input type="text" class="form-control number-field" id="tel_num" placeholder="Telephone Number" value="<?php echo $queryRes['st_tel_num'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the TelePhone Number
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="email">Email Address</label>
                                                        <input type="text" class="form-control" id="email" placeholder="Email Address" value="<?php echo $queryRes['st_email'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Email Address
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="mobile_num">Contact Number</label>
                                                        <input type="text" class="form-control number-field" id="mobile_num" placeholder="Contact Number" value="<?php echo $queryRes['st_mobile'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                     
                                        <!-- </form> -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">     
                                        <h3 class="card-title">Emergency Details</h3>                                   
                                        <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="em_full_name">Full Name</label>
                                                        <input type="text" class="form-control" id="em_full_name" placeholder="Full Name" value="<?php echo $queryRes['st_em_full_name'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Full Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="em_relation">Relationship</label>
                                                        <input type="text" class="form-control" id="em_relation" placeholder="Relationship" value="<?php echo $queryRes['st_em_relation'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Relationship
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="em_mobile_num">Contact Number</label>
                                                        <input type="text" class="form-control number-field" id="em_mobile_num" placeholder="Contact Number" value="<?php echo $queryRes['st_em_mobile_num'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Emergency transport and treatment and agree to pay all cost related to the Emergency</label><br>
                                                        <input type="radio" id="em_agree_check1" name="em_agree_check" class="form-check-input em_agree_check" value="1" <?php echo $counsil_Query['st_em_agree_check']=='' ? "checked" : ( $counsil_Query['st_em_agree_check']==1 ? 'checked' : '' ); ?>>
                                                        <label for="em_agree_check1" >Yes</label>                                                        
                                                        <input type="radio" id="em_agree_check2" name="em_agree_check" class="form-check-input em_agree_check" value="2" <?php echo $counsil_Query['st_em_agree_check']==2 ? 'checked' : '' ; ?>>
                                                        <label for="em_agree_check2" >No</label>   
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="usi_id">USI</label>
                                                        <input type="text" class="form-control" id="usi_id" placeholder="USI" value="<?php echo $queryRes['st_usi_id'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the USI
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="usi_id">Of the following categories, which best describes your current employment status?</label>
                                                        <select name="stu_state" id="emp_status" class="form-control">
                                                        <?php  
                                                        $st_emp_status=['--select--','Full time employee (More than 35 hours)','Part time employee (Less than 35 hours)','Self employed - Not employing others Employer','Employed - Unpaid family worker in a family business','Unemployed - Seeking full time work','Unemployed - Seeking part time work','Not employed - Not seeking employment'];
                                                        for($i=0;$i<count($st_emp_status);$i++){
                                                            $checked= $i==$queryRes['st_emp_status'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_emp_status[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select the Employment Status
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="usi_id">Are You: </label>
                                                        <select name="self_status" id="self_status" class="form-control">
                                                        <?php  
                                                        $st_self_status=['--select--','A sole supporting parent','A person with a history of short term employment experience','A person returning to the workforce after an absence of 12 month or more','A person who requires assistance with reading and writing'];
                                                        for($i=0;$i<count($st_self_status);$i++){
                                                            $checked= $i==$queryRes['st_self_status'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_self_status[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="usi_id">Citizenship</label>
                                                        <select name="st_citizen" id="st_citizen" class="form-control">
                                                        <?php  
                                                        $st_citizen=['--select--','Australian Citizen','New Zealand Citizen
                                                        ','Australian Permanent Resident','Humanitarian Visa','Temporary Resident'];
                                                        for($i=0;$i<count($st_citizen);$i++){
                                                            $checked= $i==$queryRes['st_citizen'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_citizen[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 citizen_child" style="display:<?php echo $queryRes['st_citizen']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_citizen'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="st_citizen_other">Specify Visa type</label>
                                                        <input type="text" class="form-control" id="st_citizen_other" value="<?php echo $queryRes['st_citizen_other']; ?>" >
                                                        <div class="error-feedback">
                                                            please specify Visa type
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Gender</label><br>
                                                        <input type="radio" id="gender_check1" name="gender_check" class="form-check-input gender_check" value="1" <?php echo $counsil_Query['st_gender']=='' ? "checked" : ( $counsil_Query['st_gender']==1 ? 'checked' : '' ); ?>>
                                                        <label for="gender_check1" >Male</label>                                                        
                                                        <input type="radio" id="gender_check2" name="gender_check" class="form-check-input gender_check" value="2" <?php echo $counsil_Query['st_gender']==2 ? 'checked' : '' ; ?>>
                                                        <label for="gender_check2" >Female</label>   
                                                        <input type="radio" id="gender_check3" name="gender_check" class="form-check-input gender_check" value="3" <?php echo $counsil_Query['st_gender']==3 ? 'checked' : '' ; ?>>
                                                        <label for="gender_check3" >Other</label>   
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Do you wish to apply for recognition or credit transfer for your prior learning?</label><br>
                                                        <input type="radio" id="cred_tansf1" name="cred_tansf" class="form-check-input cred_tansf" value="1" <?php echo $counsil_Query['st_cred_tansf']=='' ? "checked" : ( $counsil_Query['st_cred_tansf']==1 ? 'checked' : '' ); ?>>
                                                        <label for="cred_tansf1" >Yes [Please obtain application kit]</label>                                                        
                                                        <input type="radio" id="cred_tansf2" name="cred_tansf" class="form-check-input cred_tansf" value="2" <?php echo $counsil_Query['st_cred_tansf']==2 ? 'checked' : '' ; ?>>
                                                        <label for="cred_tansf2" >No</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="highest_school">What is your highest COMPLETED school level?</label>
                                                        <select name="highest_school" id="highest_school" class="form-control">
                                                        <?php  
                                                        $st_highest_school=['--select--','Completed Year 12 Completed Year 11','Completed Year 10 Completed Year 9','Completed Year 8 Never Attended School'];
                                                        for($i=0;$i<count($st_highest_school);$i++){
                                                            $checked= $i==$queryRes['st_highest_school'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_highest_school[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Are you still attending secondary school</label><br>
                                                        <input type="radio" id="sec_school1" name="sec_school" class="form-check-input sec_school" value="1" <?php echo $counsil_Query['st_sec_school']=='' ? "checked" : ( $counsil_Query['st_sec_school']==1 ? 'checked' : '' ); ?>>
                                                        <label for="sec_school1" >Yes</label>                                                        
                                                        <input type="radio" id="sec_school2" name="sec_school" class="form-check-input sec_school" value="2" <?php echo $counsil_Query['st_sec_school']==2 ? 'checked' : '' ; ?>>
                                                        <label for="sec_school2" >No</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Were you Born in Australia</label><br>
                                                        <input type="radio" id="born_country1" name="born_country" class="form-check-input born_country" value="1" <?php echo $counsil_Query['st_born_country']=='' ? "checked" : ( $counsil_Query['st_born_country']==1 ? 'checked' : '' ); ?>>
                                                        <label for="born_country1" >Yes</label>                                                        
                                                        <input type="radio" id="born_country2" name="born_country" class="form-check-input born_country" value="2" <?php echo $counsil_Query['st_born_country']==2 ? 'checked' : '' ; ?>>
                                                        <label for="born_country2" >No</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6 born_country_child" style="display:<?php echo $queryRes['st_born_country']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_born_country'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="st_born_country">Specify Country Name</label>
                                                        <input type="text" class="form-control" placeholder="Country Name" id="st_born_country" value="<?php echo $queryRes['st_born_country_other']; ?>" >
                                                        <div class="error-feedback">
                                                            please Specify Country Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Are you of Aboriginal or Torres Strait Islander origin?</label><br><span>[For persons of both Aboriginal & Torres Strait Island origin, mark both ‘Yes’ boxes]</span><br>
                                                        <input type="radio" id="origin1" name="origin" class="form-check-input origin" value="1" <?php echo $counsil_Query['st_origin']=='' ? "checked" : ( $counsil_Query['st_origin']==1 ? 'checked' : '' ); ?>>
                                                        <label for="origin1" >No</label>                                                        
                                                        <input type="radio" id="origin2" name="origin" class="form-check-input origin" value="2" <?php echo $counsil_Query['st_origin']==2 ? 'checked' : '' ; ?>>
                                                        <label for="origin2" >Yes, Aboriginal</label>     
                                                        <input type="radio" id="origin3" name="origin" class="form-check-input origin" value="3" <?php echo $counsil_Query['st_origin']==3 ? 'checked' : '' ; ?>>
                                                        <label for="origin3" >Yes, Torres Strait Islander</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Do you speak a language other than English at home?</label><br>
                                                        <input type="radio" id="lan_spoken1" name="lan_spoken" class="form-check-input lan_spoken" value="1" <?php echo $counsil_Query['st_lan_spoken']==1 ? 'checked' : '' ; ?>>
                                                        <label for="lan_spoken1" >Yes</label>                                                        
                                                        <input type="radio" id="lan_spoken2" name="lan_spoken" class="form-check-input lan_spoken" value="2" <?php echo $counsil_Query['st_lan_spoken']=='' ? "checked" : ( $counsil_Query['st_lan_spoken']==2 ? 'checked' : '' ); ?>>
                                                        <label for="lan_spoken2" >No</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6 lan_spoken_child" style="display:<?php echo $queryRes['st_lan_spoken']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_lan_spoken'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="lan_spoken_other">Specify Language</label>
                                                        <input type="text" class="form-control" placeholder="Language" id="lan_spoken_other" value="<?php echo $queryRes['st_lan_spoken_other']; ?>" >
                                                        <div class="error-feedback">
                                                            please Specify Language
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Do you consider yourself to have a Disability, Impairment or Long-term condition?</label><br>
                                                        <input type="radio" id="disability1" name="disability" class="form-check-input disability" value="1" <?php echo $counsil_Query['st_disability']==1 ? 'checked' : '' ; ?>>
                                                        <label for="disability1" >Yes</label>                                                        
                                                        <input type="radio" id="disability2" name="disability" class="form-check-input disability" value="2" <?php echo $counsil_Query['st_disability']=='' ? "checked" : ( $counsil_Query['st_disability']==2 ? 'checked' : '' ); ?>>
                                                        <label for="disability2" >No</label>     
                                                    </div>
                                                </div>
                                                <div class="col-md-6 disability_child" style="display:<?php echo $queryRes['st_disability']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_disability'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="st_disability">If YES, please indicate the areas of disability, impairment or long-term condition:[You may indicate more than one area]</label>
                                                        <select name="st_disability_type" data-selected-text-format="count" id="st_disability_type" class="selectpicker form-control" multiple title="Areas of disability">
                                                        <?php  
                                                        $st_disability_type=['Hearing/Deafness','Physical','Intellectual','Learning','Mental illness','Acquired brain impairment','VisionMedical condition','Other'];
                                                        for($i=0;$i<count($st_disability_type);$i++){
                                                            $checked='';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_disability_type[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            please Specify the Disability Type
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 disability_type_child" style="display:<?php echo $queryRes['st_disability_type']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_disability_type'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                    <label class="form-label" for="disability_type_other">Specify the name</label>
                                                        <input type="text" class="form-control" placeholder="Specify" id="disability_type_other" value="<?php echo $queryRes['st_disability_type_other']; ?>" >
                                                        <div class="error-feedback">
                                                            please Specify the Disability Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="study_reason">What is your highest COMPLETED school level?</label>
                                                        <select name="study_reason" id="study_reason" class="form-control">
                                                        <?php  
                                                        $st_study_reason=['--select--','To get a job','To develop my existing business','To start my own business','To try for a dierent career','To get a better job / promotion','It was a requirement of my job','I wanted extra skills for my job','To get into another course or study','For personal interest or self-development','Other Reason'];
                                                        for($i=0;$i<count($st_study_reason);$i++){
                                                            $checked= $i==$queryRes['st_study_reason'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_study_reason[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 study_reason_child" style="display:<?php echo $queryRes['st_study_reason']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_study_reason'])) ? 'block' : 'none' ); ?>">
                                                    <div class="mb-3">
                                                    <label class="form-label" for="disability_type_other">Specify the Reason</label>
                                                        <input type="text" class="form-control" placeholder="Specify" id="disability_type_other" value="<?php echo $queryRes['st_study_reason_other']; ?>" >
                                                        <div class="error-feedback">
                                                            please Specify the Reason
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">     
                                        <h3 class="card-title">Qualifications</h3>                                   
                                        <div class="row">
                                            <div class="col-md-6">
                                            <label class="form-label" for="disability_type_other">Have you SUCCESSSFULLY COMPLETED any of the following qualications? (AUS-Australia, INT-International )</label>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                    
                                                    </div>
                                                    <div class="col-md-2">
                                                        Aus
                                                    </div>
                                                    <div class="col-md-2">
                                                        Int
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_1">Bachelor degree or higher</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_1" id="qual_1" value="1" class="qual_1 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_1" id="qual_1" value="2" class="qual_1 form-check-input" >
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_2">Advanced diploma or Advanced associate degree</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_2" id="qual_2" value="1" class="qual_2 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_2" id="qual_2" value="2" class="qual_2 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_3">Diploma [or associate diploma]</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_3" id="qual_3" value="1" class="qual_3 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_3" id="qual_3" value="2" class="qual_3 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_4">Certicate IV [or adv cert / technician]</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_4" id="qual_4" value="1" class="qual_4 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_4" id="qual_4" value="2" class="qual_4 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_5">Certicate III [or trade certicate]</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_5" id="qual_5" value="1" class="qual_5 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_5" id="qual_5" value="2" class="qual_5 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_6">Certicate II </label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_6" id="qual_6" value="1" class="qual_6 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_6" id="qual_6" value="2" class="qual_6 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_7">Certicate I</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_7" id="qual_7" value="1" class="qual_7 form-check-input" checked>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_7" id="qual_7" value="2" class="qual_7 form-check-input">
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_8">Certicates other than the above</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_8" id="qual_8" value="1" class="qual_8 form-check-input" >
                                                    </div>
                                                    <div class="col-md-2">
                                                        <input type="radio" name="qual_8" id="qual_8" value="2" class="qual_8 form-check-input" checked>
                                                    </div>
                                                </div>
                                                <div class="mb-3 qual_8_child" style="display:none">  
                                                    <label for="qual_name_8_other">If yes, please state the name and year of your qualication/s?</label>
                                                    <input type="text" name="qual_name_8_other" id="qual_name_8_other" class="qual_name_8_other form-control" >
                                                    <div class="error-feedback">
                                                            please Specify the details
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">  
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_9">Have you successfully completed any of the above qualications through the AUSTRALIAN APPRENTICESHIP / TRAINEESHIP PROGRAM:</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                    <label for="qual_9_1">Yes</label>
                                                    <input type="radio" name="qual_9" id="qual_9_1" value="1" class="qual_9 form-check-input" >
                                                    </div>
                                                    <div class="col-md-2">
                                                    <label for="qual_9_2">No</label>
                                                    <input type="radio" name="qual_9" id="qual_9_2" value="2" class="qual_9 form-check-input" checked>
                                                    </div>
                                                </div>
                                                <div class="mb-3 qual_9_child" style="display:none">  
                                                    <label for="qual_name_9_other">(Date Completed)</label>
                                                    <input type="date" name="qual_name_9_other" id="qual_name_9_other" class="qual_name_9_other form-control" >
                                                    <div class="error-feedback">
                                                            please Specify the date
                                                    </div>
                                                </div>
                                                <div class="mb-3 d-flex">  
                                                    <div class="col-md-8">
                                                        <label for="qual_name_10">Have you successfully completed any of the above qualications through the any other government funded program (e.g. productivity places program).</label>
                                                    </div>
                                                    <div class="col-md-2">
                                                    <label for="qual_10_1">Yes</label>
                                                    <input type="radio" name="qual_10" id="qual_10_1" value="1" class="qual_10 form-check-input" >
                                                    </div>
                                                    <div class="col-md-2">
                                                    <label for="qual_10_2">No</label>
                                                    <input type="radio" name="qual_10" id="qual_10_2" value="2" class="qual_10 form-check-input" checked>
                                                    </div>
                                                </div>
                                                <div class="mb-3 qual_10_child" style="display:none">  
                                                    <label for="qual_name_10_other">(Name of the program)</label>
                                                    <input type="text" name="qual_name_10_other" id="qual_name_10_other" class="qual_name_10_other form-control" placeholder="name">
                                                    <div class="error-feedback">
                                                            please Specify the Name
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">                                        
                                        <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                    <?php if($enrolId==0){ ?>
                                                <button class="btn btn-primary" id="enrolment_check" type="button" >Submit Form</button>
                                            <?php }else{ ?>
                                                <button class="btn btn-primary" id="enrolment_check" type="button" >Update Details</button>
                                            <?php } ?>    
                                            <input type="hidden" value="<?php echo $enrolId; ?>" id="check_update"> 
                                                    </div>
                                                </div>
                                            </div>                                       
                                        <!-- </form> -->
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                            </form>
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

$('#st_citizen').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value==5 ){
        $('.citizen_child').show();
    }else{
        $('.citizen_child').hide();
    }                 
})
$('.born_country').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value==2 ){
        $('.born_country_child').show();
    }else{
        $('.born_country_child').hide();
    }                 
})
$('.lan_spoken').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value==1 ){
        $('.lan_spoken_child').show();
    }else{
        $('.lan_spoken_child').hide();
    }                 
})
$('.disability').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value==1 ){
        $('.disability_child').show();
    }else{
        $('.disability_child').hide();
        $('.disability_type_child').hide();
    }                 
})
$('#st_disability_type').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value.includes('7') ){
        $('.disability_type_child').show();
    }else{
        $('.disability_type_child').hide();
    }                 
})
$('#study_reason').on("change",function(){
    var value=$(this).val();                    
    // console.log(value);
    if( value==10 ){
        $('.study_reason_child').show();
    }else{
        $('.study_reason_child').hide();
    }                 
})
$('.qual_8').on("change",function(){    
    var value=$('.qual_8:checked').val();                    
    if( value==1 ){
        $('.qual_8_child').show();
    }else{
        $('.qual_8_child').hide();
    }                 
})
$('.qual_9').on("change",function(){    
    var value=$('.qual_9:checked').val();                    
    if( value==1 ){
        $('.qual_9_child').show();
    }else{
        $('.qual_9_child').hide();
    }                 
})
$('.qual_10').on("change",function(){    
    var value=$('.qual_10:checked').val();                    
    if( value==1 ){
        $('.qual_10_child').show();
    }else{
        $('.qual_10_child').hide();
    }                 
})

$(document).on('click','#lookedup',function(){
    studetnLookup();
    $('#model_trigger1').trigger('click');
})

            $(document).on('click','#enrolment_check',function(){
                var rto_name=$('#rto_name').val().trim();
                var courses=$('#courses').val();
                var branch_name=$('#branch_name').val().trim();
                var photo_upload=$('#photo_upload').val().trim();
                var given_name=$('#given_name').val().trim();
                var surname=$('#surname').val().trim();
                var dob=$('#dob').val().trim();
                var birth_country=$('#birth_country').val().trim();
                var street_details=$('#street_details').val().trim();
                var sub_urb=$('#sub_urb').val().trim();
                var post_code=$('#post_code').val().trim();
                var tel_num=$('#tel_num').val().trim();
                var mobile_num=$('#mobile_num').val().trim();
                var emailAddress=$('#email').val().trim();
                var stu_state=$('#stu_state').val()==0 ? '' : $('#stu_state').val();

                var em_full_name=$('#em_full_name').val().trim();
                var em_relation=$('#em_relation').val().trim();
                var em_mobile_num=$('#em_mobile_num').val().trim();
                var em_agree_check=$('#em_agree_check:checked').val();
                var usi_id=$('#usi_id').val();
                var emp_status=$('#emp_status').val()==0 ? '' : $('#emp_status').val();
                var self_status=$('#self_status').val()==0 ? '' : $('#self_status').val();
                var st_citizen=$('#st_citizen').val()==0 ? '' : $('#st_citizen').val();
                var highest_school=$('#highest_school').val()==0 ? '' : $('#highest_school').val();
                var study_reason=$('#study_reason').val()==0 ? '' : $('#study_reason').val();

                var gender_check=$('.gender_check:checked').val();
                var cred_tansf=$('.cred_tansf:checked').val();
                var sec_school=$('.sec_school:checked').val();
                var born_country=$('.born_country:checked').val();
                var origin=$('.origin:checked').val();
                var lan_spoken=$('.lan_spoken:checked').val();
                var disability=$('.disability:checked').val();

                var qual_1=$('.qual_1:checked').val();
                var qual_2=$('.qual_2:checked').val();
                var qual_3=$('.qual_3:checked').val();
                var qual_4=$('.qual_4:checked').val();
                var qual_5=$('.qual_5:checked').val();
                var qual_6=$('.qual_6:checked').val();
                var qual_7=$('.qual_7:checked').val();
                var qual_8=$('.qual_8:checked').val();
                var qual_9=$('.qual_9:checked').val();
                var qual_10=$('.qual_10:checked').val();

                if(born_country==2){
                    var st_born_country=$('#st_born_country').val().trim();
                    if(st_born_country==''){
                        var born_error=1;
                    }else{
                        var born_error=0;
                    }
                }else{
                    var born_error=0;
                }
                
                if(qual_8==1){
                    var qual_name_8_other=$('#qual_name_8_other').val().trim();
                    if(qual_name_8_other==''){
                        var qual_8_error=1;
                    }else{
                        var qual_8_error=0;
                    }
                }else{
                    var qual_8_error=0;
                }
                if(qual_10==1){
                    var qual_name_10_other=$('#qual_name_10_other').val().trim();
                    if(qual_name_10_other==''){
                        var qual_10_error=1;
                    }else{
                        var qual_10_error=0;
                    }
                }else{
                    var qual_10_error=0;
                }
                if(qual_9==1){
                    var qual_name_9_other=$('#qual_name_9_other').val().trim();
                    if(qual_name_9_other==''){
                        var qual_9_error=1;
                    }else{
                        var qual_9_error=0;
                    }
                }else{
                    var qual_9_error=0;
                }

                if(lan_spoken==1){
                    var lan_spoken_other=$('#lan_spoken_other').val().trim();
                    if(lan_spoken_other==''){
                        var lan_error=1;
                    }else{
                        var lan_error=0;
                    }
                }else{
                    var lan_error=0;
                }

                if(disability==1){
                    var st_disability_type=$('#st_disability_type').val();
                    var disability_type_other=$('#disability_type_other').val();
                    if(st_disability_type.length==0){
                        var disability_level_1=1;
                    }else{

                        if(st_disability_type.includes('7')){

                            if(disability_type_other==''){
                                var disability_level_1=0;
                                var disability_level_2=1;
                            }else{
                                var disability_level_1=0;
                                var disability_level_2=0;
                            }

                        }else{
                            var disability_level_1=0;
                            var disability_level_2=0;
                        }                        
                    }
                }else{
                    var disability_level_1=0;
                }

                if( rto_name=='' || courses.length==0 || branch_name=='' || photo_upload=='' || given_name=='' || surname=='' || dob=='' || birth_country=='' || street_details=='' || sub_urb=='' || stu_state=='' || post_code=='' || tel_num=='' || emailAddress==''|| (emailAddress!='' && !emailAddress.match(emailregexp)==true ) || mobile_num=='' || em_full_name=='' || em_relation=='' || em_mobile_num=='' || usi_id=='' || emp_status=='' || self_status=='' || st_citizen=='' || highest_school=='' || born_error==1 || lan_error==1 ||  disability_level_1==1 || ( disability_level_1!=1 && disability_level_2==1 ) ||  study_reason=='' || qual_8_error==1 || qual_9_error==1 || qual_10_error==1 ){
                    if(rto_name==''){
                        $('#rto_name').addClass('invalid-div');
                        $('#rto_name').removeClass('valid-div');
                        $('#rto_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#rto_name').addClass('valid-div');
                        $('#rto_name').removeClass('invalid-div');                        
                        $('#rto_name').closest('div').find('.error-feedback').hide();
                    }
                    if(em_full_name==''){
                        $('#em_full_name').addClass('invalid-div');
                        $('#em_full_name').removeClass('valid-div');
                        $('#em_full_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#em_full_name').addClass('valid-div');
                        $('#em_full_name').removeClass('invalid-div');                        
                        $('#em_full_name').closest('div').find('.error-feedback').hide();
                    }
                    if(qual_8_error==1){
                        $('#qual_name_8_other').addClass('invalid-div');
                        $('#qual_name_8_other').removeClass('valid-div');
                        $('#qual_name_8_other').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qual_name_8_other').addClass('valid-div');
                        $('#qual_name_8_other').removeClass('invalid-div');                        
                        $('#qual_name_8_other').closest('div').find('.error-feedback').hide();
                    }
                    if(qual_9_error==1){
                        $('#qual_name_9_other').addClass('invalid-div');
                        $('#qual_name_9_other').removeClass('valid-div');
                        $('#qual_name_9_other').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qual_name_9_other').addClass('valid-div');
                        $('#qual_name_9_other').removeClass('invalid-div');                        
                        $('#qual_name_9_other').closest('div').find('.error-feedback').hide();
                    }
                    if(qual_10_error==1){
                        $('#qual_name_10_other').addClass('invalid-div');
                        $('#qual_name_10_other').removeClass('valid-div');
                        $('#qual_name_10_other').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qual_name_10_other').addClass('valid-div');
                        $('#qual_name_10_other').removeClass('invalid-div');                        
                        $('#qual_name_10_other').closest('div').find('.error-feedback').hide();
                    }
                    if(study_reason==''){
                        $('#study_reason').addClass('invalid-div');
                        $('#study_reason').removeClass('valid-div');
                        $('#study_reason').closest('div').find('.error-feedback').show();
                    }else{
                        $('#study_reason').addClass('valid-div');
                        $('#study_reason').removeClass('invalid-div');                        
                        $('#study_reason').closest('div').find('.error-feedback').hide();
                    }
                    if(disability_level_1==1){
                        $('button[data-id="st_disability_type"]').addClass('invalid-div');
                        $('button[data-id="st_disability_type"]').removeClass('valid-div');
                        $('button[data-id="st_disability_type"]').closest('.mb-3').find('.error-feedback').show();
                    }else if( disability_level_1!=1 && disability_level_2==1 ){
                        $('#disability_type_other').addClass('invalid-div');
                        $('#disability_type_other').removeClass('valid-div');                        
                        $('#disability_type_other').closest('div').find('.error-feedback').show();

                        $('button[data-id="st_disability_type"]').addClass('valid-div');
                        $('button[data-id="st_disability_type"]').removeClass('invalid-div');                        
                        $('button[data-id="st_disability_type"]').closest('.mb-3').find('.error-feedback').hide();
                    }else{
                        $('button[data-id="st_disability_type"]').addClass('valid-div');
                        $('button[data-id="st_disability_type"]').removeClass('invalid-div');                        
                        $('button[data-id="st_disability_type"]').closest('.mb-3').find('.error-feedback').hide();

                        $('#disability_type_other').addClass('valid-div');
                        $('#disability_type_other').removeClass('invalid-div');                        
                        $('#disability_type_other').closest('div').find('.error-feedback').hide();
                    }
                    if(born_error==1){
                        $('#st_born_country').addClass('invalid-div');
                        $('#st_born_country').removeClass('valid-div');
                        $('#st_born_country').closest('div').find('.error-feedback').show();
                    }else{
                        $('#st_born_country').addClass('valid-div');
                        $('#st_born_country').removeClass('invalid-div');                        
                        $('#st_born_country').closest('div').find('.error-feedback').hide();
                    }
                    if(lan_error==1){
                        $('#lan_spoken_other').addClass('invalid-div');
                        $('#lan_spoken_other').removeClass('valid-div');
                        $('#lan_spoken_other').closest('div').find('.error-feedback').show();
                    }else{
                        $('#lan_spoken_other').addClass('valid-div');
                        $('#lan_spoken_other').removeClass('invalid-div');                        
                        $('#lan_spoken_other').closest('div').find('.error-feedback').hide();
                    }
                    if(highest_school==''){
                        $('#highest_school').addClass('invalid-div');
                        $('#highest_school').removeClass('valid-div');
                        $('#highest_school').closest('div').find('.error-feedback').show();
                    }else{
                        $('#highest_school').addClass('valid-div');
                        $('#highest_school').removeClass('invalid-div');                        
                        $('#highest_school').closest('div').find('.error-feedback').hide();
                    }
                    if(st_citizen==''){
                        $('#st_citizen').addClass('invalid-div');
                        $('#st_citizen').removeClass('valid-div');
                        $('#st_citizen').closest('div').find('.error-feedback').show();
                    }else{
                        $('#st_citizen').addClass('valid-div');
                        $('#st_citizen').removeClass('invalid-div');                        
                        $('#st_citizen').closest('div').find('.error-feedback').hide();
                    }
                    if(self_status==''){
                        $('#self_status').addClass('invalid-div');
                        $('#self_status').removeClass('valid-div');
                        $('#self_status').closest('div').find('.error-feedback').show();
                    }else{
                        $('#self_status').addClass('valid-div');
                        $('#self_status').removeClass('invalid-div');                        
                        $('#self_status').closest('div').find('.error-feedback').hide();
                    }
                    if(emp_status==''){
                        $('#emp_status').addClass('invalid-div');
                        $('#emp_status').removeClass('valid-div');
                        $('#emp_status').closest('div').find('.error-feedback').show();
                    }else{
                        $('#emp_status').addClass('valid-div');
                        $('#emp_status').removeClass('invalid-div');                        
                        $('#emp_status').closest('div').find('.error-feedback').hide();
                    }
                    if(usi_id==''){
                        $('#usi_id').addClass('invalid-div');
                        $('#usi_id').removeClass('valid-div');
                        $('#usi_id').closest('div').find('.error-feedback').show();
                    }else{
                        $('#usi_id').addClass('valid-div');
                        $('#usi_id').removeClass('invalid-div');                        
                        $('#usi_id').closest('div').find('.error-feedback').hide();
                    }
                    if(em_mobile_num==''){
                        $('#em_mobile_num').addClass('invalid-div');
                        $('#em_mobile_num').removeClass('valid-div');
                        $('#em_mobile_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#em_mobile_num').addClass('valid-div');
                        $('#em_mobile_num').removeClass('invalid-div');                        
                        $('#em_mobile_num').closest('div').find('.error-feedback').hide();
                    }
                    if(em_relation==''){
                        $('#em_relation').addClass('invalid-div');
                        $('#em_relation').removeClass('valid-div');
                        $('#em_relation').closest('div').find('.error-feedback').show();
                    }else{
                        $('#em_relation').addClass('valid-div');
                        $('#em_relation').removeClass('invalid-div');                        
                        $('#em_relation').closest('div').find('.error-feedback').hide();
                    }
                    if(street_details==''){
                        $('#street_details').addClass('invalid-div');
                        $('#street_details').removeClass('valid-div');
                        $('#street_details').closest('div').find('.error-feedback').show();
                    }else{
                        $('#street_details').addClass('valid-div');
                        $('#street_details').removeClass('invalid-div');                        
                        $('#street_details').closest('div').find('.error-feedback').hide();
                    }
                    if(emailAddress=='' || (emailAddress!='' && (!emailAddress.match(emailregexp)==true))){
                        $('#email').addClass('invalid-div');
                        $('#email').removeClass('valid-div');
                        $('#email').closest('div').find('.error-feedback').show();
                    }else{
                        $('#email').addClass('valid-div');
                        $('#email').removeClass('invalid-div');
                        $('#email').closest('div').find('.error-feedback').hide();
                    }
                    if(tel_num==''){
                        $('#tel_num').addClass('invalid-div');
                        $('#tel_num').removeClass('valid-div');
                        $('#tel_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#tel_num').addClass('valid-div');
                        $('#tel_num').removeClass('invalid-div');                        
                        $('#tel_num').closest('div').find('.error-feedback').hide();
                    }
                    if(mobile_num==''){
                        $('#mobile_num').addClass('invalid-div');
                        $('#mobile_num').removeClass('valid-div');
                        $('#mobile_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#mobile_num').addClass('valid-div');
                        $('#mobile_num').removeClass('invalid-div');                        
                        $('#mobile_num').closest('div').find('.error-feedback').hide();
                    }
                    if(post_code==''){
                        $('#post_code').addClass('invalid-div');
                        $('#post_code').removeClass('valid-div');
                        $('#post_code').closest('div').find('.error-feedback').show();
                    }else{
                        $('#post_code').addClass('valid-div');
                        $('#post_code').removeClass('invalid-div');                        
                        $('#post_code').closest('div').find('.error-feedback').hide();
                    }
                    if(stu_state==''){
                        $('#stu_state').addClass('invalid-div');
                        $('#stu_state').removeClass('valid-div');
                        $('#stu_state').closest('div').find('.error-feedback').show();
                    }else{
                        $('#stu_state').addClass('valid-div');
                        $('#stu_state').removeClass('invalid-div');                        
                        $('#stu_state').closest('div').find('.error-feedback').hide();
                    }
                    if(sub_urb==''){
                        $('#sub_urb').addClass('invalid-div');
                        $('#sub_urb').removeClass('valid-div');
                        $('#sub_urb').closest('div').find('.error-feedback').show();
                    }else{
                        $('#sub_urb').addClass('valid-div');
                        $('#sub_urb').removeClass('invalid-div');                        
                        $('#sub_urb').closest('div').find('.error-feedback').hide();
                    }
                    if(branch_name==''){
                        $('#branch_name').addClass('invalid-div');
                        $('#branch_name').removeClass('valid-div');
                        $('#branch_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#branch_name').addClass('valid-div');
                        $('#branch_name').removeClass('invalid-div');                        
                        $('#branch_name').closest('div').find('.error-feedback').hide();
                    }
                    if(photo_upload==''){
                        $('#photo_upload').addClass('invalid-div');
                        $('#photo_upload').removeClass('valid-div');
                        $('#photo_upload').closest('div').find('.error-feedback').show();
                    }else{
                        $('#photo_upload').addClass('valid-div');
                        $('#photo_upload').removeClass('invalid-div');                        
                        $('#photo_upload').closest('div').find('.error-feedback').hide();
                    }
                    if(given_name==''){
                        $('#given_name').addClass('invalid-div');
                        $('#given_name').removeClass('valid-div');
                        $('#given_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#given_name').addClass('valid-div');
                        $('#given_name').removeClass('invalid-div');                        
                        $('#given_name').closest('div').find('.error-feedback').hide();
                    }
                    if(surname==''){
                        $('#surname').addClass('invalid-div');
                        $('#surname').removeClass('valid-div');
                        $('#surname').closest('div').find('.error-feedback').show();
                    }else{
                        $('#surname').addClass('valid-div');
                        $('#surname').removeClass('invalid-div');                        
                        $('#surname').closest('div').find('.error-feedback').hide();
                    }
                    if(dob==''){
                        $('#dob').addClass('invalid-div');
                        $('#dob').removeClass('valid-div');
                        $('#dob').closest('div').find('.error-feedback').show();
                    }else{
                        $('#dob').addClass('valid-div');
                        $('#dob').removeClass('invalid-div');                        
                        $('#dob').closest('div').find('.error-feedback').hide();
                    }
                    if(birth_country==''){
                        $('#birth_country').addClass('invalid-div');
                        $('#birth_country').removeClass('valid-div');
                        $('#birth_country').closest('div').find('.error-feedback').show();
                    }else{
                        $('#birth_country').addClass('valid-div');
                        $('#birth_country').removeClass('invalid-div');                        
                        $('#birth_country').closest('div').find('.error-feedback').hide();
                    }
                    if(courses.length==0){
                        $('button[data-id="courses"]').addClass('invalid-div');
                        $('button[data-id="courses"]').removeClass('valid-div');
                        $('button[data-id="courses"]').closest('.mb-3').find('.error-feedback').show();
                    }else{
                        $('button[data-id="courses"]').addClass('valid-div');
                        $('button[data-id="courses"]').removeClass('invalid-div');                        
                        $('button[data-id="courses"]').closest('.mb-3').find('.error-feedback').hide();
                    }
                }else{
                    var checkId=$("#check_update").val();
                    details={formName:'student_enrol',qualifications:qualifications,contactName:contactName,emailAddress:emailAddress,venues:venue,middle_name:middle_name,source:source,name_main:name_main,checkId:checkId,courseName:courseName,courses:courses,enquiry_id:enquiry_id,given_name:given_name};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        success:function(data){
                            if(data==1 || data==0){
                                $('.toast-text2').html('Cannot add record. Please try again later');
                                $('#borderedToast2Btn').trigger('click');
                            }else if(data==2){
                                document.getElementById('student_enrol_form').reset();
                                $('#enquiry_id').val('');
                                $('#toast-text').html('Record Updated Successfully');
                                $('#borderedToast1Btn').trigger('click');
                                window.location.href="dashboard.php";
                            }else{
                                document.getElementById('student_enrol_form').reset();
                                $('#enquiry_id').val('');
                                $('#toast-text').html('New Record added Successfully');
                                $('#borderedToast1Btn').trigger('click');

                                $('#myModalLabel').html('Student ID Created:');
                                $('.modal-body').html(data);
                                $('#model_trigger').trigger('click');
                            }
                        }
                    })
                }

            })
        </script>
    </body>
</html>
<?php }else{ 
header("Location: index.php");
}
?>