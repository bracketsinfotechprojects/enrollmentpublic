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
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="student_enrol_form" id="student_enrol_form">
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
                                                        <option value="0">--select--</option>
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
                                                            Please enter the Upload Photo
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
                                                        <label class="form-label" for="mobile_num">Contact Number</label>
                                                        <input type="text" class="form-control number-field" id="mobile_num" placeholder="Contact Number" value="<?php echo $queryRes['st_mobile'] ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
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
                                                        <label class="form-label" for="source">How did you hear about Milton College</label>
                                                        <select name="source" class="form-select" id="source">
                                                            <option value="0">--select--</option>
                                                        <?php 
                                                        while($sourceRes=mysqli_fetch_array($source)){
                                                        ?>                                                            
                                                            <option value="<?php echo $sourceRes['source_id']; ?>" <?php echo $sourceRes['source_id']==$queryRes['st_source'] ? 'selected' : ''; ?>><?php echo $sourceRes['source_name']; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 <?php echo $enrolId!=0 ? 'd-none' : '' ?>">
                                                    <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-end">
                                                    <label class="form-label" for="enquiry_id">Enquiry ID</label>
                                                    <label class="btn btn-primary" id="lookedup"><i class="mdi mdi-eye"></i> Student Lookup</label>    
                                                </div>
                                                    <input type="text" id="enquiry_id" class="form-control" placeholder="Enquiry ID" name='enquiry_id' value="<?php echo $queryRes['st_enquiry_id']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if($enrolId==0){ ?>
                                                <button class="btn btn-primary" id="enrolment_check" type="button" >Submit Form</button>
                                            <?php }else{ ?>
                                                <button class="btn btn-primary" id="enrolment_check" type="button" >Update Details</button>
                                            <?php } ?>    
                                            <input type="hidden" value="<?php echo $enrolId; ?>" id="check_update">                                        
                                        </form>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
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

$(document).on('click','#lookedup',function(){
    studetnLookup();
    $('#model_trigger1').trigger('click');
})

            $(document).on('click','#enrolment_check',function(){
                var given_name=$('#given_name').val().trim();
                var name_main=$('#name_main').val().trim();
                var qualifications=$('#qualifications').val()==0 ? '' : $('#qualifications').val();
                var source=$('#source').val()==0 ? '' : $('#source').val();
                var middle_name=$('#middle_name').val().trim();
                var courses=$('#courses').val()==0 ? '' : $('#courses').val();
                var courseName=$('#courses').find(":selected").text();
                var venue=$('#venue_name').val()==0 ? '' : $('#venue_name').val();
                var enquiry_id=$('#enquiry_id').val();
                var contactName=$('#mobile_num').val();
                var emailAddress=$('#email').val();
                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;

                if(qualifications==''|| given_name=='' ||name_main=='' ||source==''||middle_name==''||venue==''||  ( contactName=='' || contactName.length!=10 ) ||emailAddress==''|| (emailAddress!='' && !emailAddress.match(emailregexp)==true ) ){
                    if(qualifications==''){
                        $('#qualifications').addClass('invalid-div');
                        $('#qualifications').removeClass('valid-div');
                        $('#qualifications').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qualifications').addClass('valid-div');
                        $('#qualifications').removeClass('invalid-div');                        
                        $('#qualifications').closest('div').find('.error-feedback').hide();
                    }
                    if( emailAddress=='' ||  (emailAddress!='' && !emailAddress.match(emailregexp)==true) ){
                        $('#email').addClass('invalid-div');
                        $('#email').removeClass('valid-div');
                        $('#email').closest('div').find('.error-feedback').show();
                    }else{
                        $('#email').addClass('valid-div');
                        $('#email').removeClass('invalid-div');                        
                        $('#email').closest('div').find('.error-feedback').hide();
                    }
                    if(contactName==''){
                        $('#mobile_num').addClass('invalid-div');
                        $('#mobile_num').removeClass('valid-div');
                        $('#mobile_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#mobile_num').addClass('valid-div');
                        $('#mobile_num').removeClass('invalid-div');                        
                        $('#mobile_num').closest('div').find('.error-feedback').hide();
                    }
                    if(courses==''){
                        $('#courses').addClass('invalid-div');
                        $('#courses').removeClass('valid-div');
                        $('#courses').closest('div').find('.error-feedback').show();
                    }else{
                        $('#courses').addClass('valid-div');
                        $('#courses').removeClass('invalid-div');                        
                        $('#courses').closest('div').find('.error-feedback').hide();
                    }
                    if(qualifications==''){
                        $('#qualifications').addClass('invalid-div');
                        $('#qualifications').removeClass('valid-div');
                        $('#qualifications').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qualifications').addClass('valid-div');
                        $('#qualifications').removeClass('invalid-div');                        
                        $('#qualifications').closest('div').find('.error-feedback').hide();
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
                    if(name_main==''){
                        $('#name_main').addClass('invalid-div');
                        $('#name_main').removeClass('valid-div');
                        $('#name_main').closest('div').find('.error-feedback').show();
                    }else{
                        $('#name_main').addClass('valid-div');
                        $('#name_main').removeClass('invalid-div');
                        $('#name_main').closest('div').find('.error-feedback').hide();
                    }
                    if(source==''){
                        $('#source').addClass('invalid-div');
                        $('#source').removeClass('valid-div');
                        $('#source').closest('div').find('.error-feedback').show();
                    }else{
                        $('#source').addClass('valid-div');
                        $('#source').removeClass('invalid-div');
                        $('#source').closest('div').find('.error-feedback').hide();
                    }
                    if(middle_name==''){
                        $('#middle_name').addClass('invalid-div');
                        $('#middle_name').removeClass('valid-div');
                        $('#middle_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#middle_name').addClass('valid-div');
                        $('#middle_name').removeClass('invalid-div');
                        $('#middle_name').closest('div').find('.error-feedback').hide();
                    }
                    if(venue==''){
                        $('#venue_name').addClass('invalid-div');
                        $('#venue_name').removeClass('valid-div');
                        $('#venue_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#venue_name').addClass('valid-div');
                        $('#venue_name').removeClass('invalid-div');
                        $('#venue_name').closest('div').find('.error-feedback').hide();
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