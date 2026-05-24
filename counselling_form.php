<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
    
        $enquiryIds=mysqli_query($connection,"SELECT st_enquiry_id,st_name,st_phno from student_enquiry where st_enquiry_status!=1");
        $visaStatus=mysqli_query($connection,"SELECT * from visa_statuses where visa_state_status!=1");
        

        if(isset($_GET['eq'])){
            $eqId=base64_decode($_GET['eq']);            
            $counsil_Query=mysqli_fetch_array(mysqli_query($connection,"SELECT * FROM `counseling_details` where counsil_id=".$eqId." AND counsil_enquiry_status=0"));

        }else{
            $eqId=0;
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

    <body>

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
                                    <h4 class="mb-sm-0">Counseling</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Counseling</li>
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
                                    <div class="card-body" id="followup_form_div">
                                        <form class="followup_form" id="followup_form">
                                        <div class="row">        
                                        <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="given_name">Enquiry ID<span class="asterisk">*</span></label><br>
                                                        <?php if($eqId==0){ ?>
                                                        <select class="selectpicker" title="--select--" data-live-search="true" name="enquiry_id" id="enquiry_id">

                                                        <?php
                                                        if(mysqli_num_rows($enquiryIds)!=0){
                                                        while($enquiryIdsRes=mysqli_fetch_array($enquiryIds)){
                                                            
                                                            $checkQry=mysqli_query($connection,"SELECT * FROM `counseling_details` where st_enquiry_id='".$enquiryIdsRes['st_enquiry_id']."' AND counsil_enquiry_status=0");


                                                            if(mysqli_num_rows($checkQry)==0){

                                                                echo "<option data='".$row_count."'  value='".$enquiryIdsRes['st_enquiry_id']."' data-name='".$enquiryIdsRes['st_name']."' data-mobile='".$enquiryIdsRes['st_phno']."'>".$enquiryIdsRes['st_enquiry_id']."</option>";

                                                            }else{

                                                                echo "<option value='".$enquiryIdsRes['st_enquiry_id']."' data-name='".$enquiryIdsRes['st_name']."' data-mobile='".$enquiryIdsRes['st_phno']."' disabled>".$enquiryIdsRes['st_enquiry_id']."</option>";

                                                            }

                                                        }
                                                        }else{
                                                            echo "<option value='0'>No Enquiries Found</option>";
                                                        }

                                                        ?>

                                                        </select>
                                                        <?php }else{ ?>

                                                        <input type="text" readonly class="form-control" style="width:20%" value="<?php echo $counsil_Query['st_enquiry_id']; ?>"  name="enquiry_id" id="enquiry_id">

                                                        <?php }?>
                                                        <div class="error-feedback">
                                                            Please Select the Enquiry ID
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="counseling_timing">Start Date & Time<span class="asterisk">*</span></label>
                                                        <input type="datetime-local" class="form-control" id="counseling_timing" value="<?php echo $counsil_Query['counsil_timing']=='' ? '' : date('Y-m-d H:i:s',strtotime($counsil_Query['counsil_timing'])) ?>">
                                                        <div class="error-feedback">
                                                            Please select the Date and Time
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="counseling_end_timing">End Date & Time</label>
                                                        <input type="datetime-local" class="form-control" id="counseling_end_timing" value="<?php echo $counsil_Query['counsil_end_time']=='' ? '' : date('Y-m-d H:i:s',strtotime($counsil_Query['counsil_end_time'])) ?>">
                                                        <div class="error-feedback">
                                                            Please select the Ending Date and Time
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" >Counseling Type<span class="asterisk">*</span></label><br>
                                                        <input type="radio" id="counseling_type1" name="counseling_type" class="form-check-input counseling_type"  value="1" <?php echo $counsil_Query['counsil_type']=='' ? "checked" : ( $counsil_Query['counsil_type']==1 ? 'checked' : '' ); ?>>
                                                        <label for="counseling_type1" >Face to Face</label>                                                        
                                                        <input type="radio" id="counseling_type2" name="counseling_type" class="form-check-input counseling_type" value="2" <?php echo $counsil_Query['counsil_type']==2 ? 'checked' : '' ; ?>>
                                                        <label for="counseling_type2" >Video</label>                                                        
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="member_name">Name of the member doing the couseling<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="member_name" placeholder="Team Member Name" value="<?php echo $counsil_Query['counsil_mem_name']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Member Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="aus_duration">How long the student has been to Aus<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="aus_duration" placeholder="Duration" value="<?php echo $counsil_Query['counsil_aus_stay_time']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Duration
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Currently Working<span class="asterisk">*</span></label><br>
                                                        <input type="radio" id="work_status1" name="work_status" class="form-check-input work_status" value="1" <?php echo $counsil_Query['counsil_work_status']=='' ? "checked" : ( $counsil_Query['counsil_work_status']==1 ? 'checked' : '' ); ?>>
                                                        <label for="counseling_type1" >Yes</label>                                                        
                                                        <input type="radio" id="work_status2" name="work_status" class="form-check-input work_status" value="2" <?php echo $counsil_Query['counsil_work_status']==2 ? 'checked' : '' ; ?>>
                                                        <label for="work_status2" >No</label>   
                                                    </div>
                                                </div>                                            
                                                <div class="col-md-6">
                                                <div class="mb-3">
                                                        <label class="form-label" for="visa_condition">Visa Condition<span class="asterisk">*</span></label>
                                                        <select name="visa_condition" class="form-select" id="visa_condition">
                                                        <?php 
                                                        while($visaRes=mysqli_fetch_array($visaStatus)){
                                                            if($visaRes['visa_id']==1){
                                                                echo "<option value='0'>--select--</option><optgroup label='Subclass 500 main applicant'>";
                                                            }
                                                        ?>                                                                                                      
                                                            <option value="<?php echo $visaRes['visa_id']; ?>" <?php echo $visaRes['visa_id']==$counsil_Query['counsil_visa_condition'] ? 'selected' : ''; ?>><?php echo $visaRes['visa_status_name']; ?></option>
                                                            <?php
                                                        if($visaRes['visa_id']==4){
                                                            echo '</optgroup>';
                                                        }

                                                        } ?>
                                                        </select> 
                                                        <div class="error-feedback">
                                                            Please select a visa status
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                <div class="mb-3">
                                                        <label class="form-label" for="education">Education Background</label>
                                                        <input type="text" class="form-control" id="education" value="<?php echo $counsil_Query['counsil_education']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the education
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                <div class="mb-3">
                                                        <label class="form-label">Is the Student Studying in Australia<span class="asterisk">*</span></label><br>
                                                        <input type="radio" id="aus_study1" name="aus_study" class="form-check-input aus_study" value="1" <?php echo $counsil_Query['counsil_aus_study_status']=='' ? "checked" : ( $counsil_Query['counsil_aus_study_status']==1 ? 'checked' : '' ); ?>>
                                                        <label for="aus_study1" >Yes</label>                                                        
                                                        <input type="radio" id="aus_study2" name="aus_study" class="form-check-input aus_study" value="2" <?php echo $counsil_Query['counsil_aus_study_status']==2 ? 'checked' : '' ; ?>>
                                                        <label for="aus_study2" >No</label>   
                                                    </div>
                                                </div>
                                                <div class="col-md-6 aus_study_child" style="display:<?php echo $counsil_Query['counsil_eng_rate']=='' ? 'block' : ($counsil_Query['counsil_eng_rate']==1 ? 'block' : 'none') ?>">
                                                <div class="mb-3">
                                                        <label class="form-label" for="course">What Course</label>
                                                        <input type="text" class="form-control" id="course" value="<?php echo $counsil_Query['counsil_course']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the course
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 aus_study_child" style="display:<?php echo $counsil_Query['counsil_eng_rate']=='' ? 'block' : ($counsil_Query['counsil_eng_rate']==1 ? 'block' : 'none') ?>">
                                                <div class="mb-3">
                                                        <label class="form-label" for="university_name">University Name</label>
                                                        <input type="text" class="form-control" id="university_name" value="<?php echo $counsil_Query['counsil_university']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the University Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                <div class="mb-3">
                                                        <label class="form-label" for="qualification">Any Experience or Qualification related to Aged care/Health care</label>
                                                        <input type="text" class="form-control" id="qualification" value="<?php echo $counsil_Query['counsil_qualification']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the Qualification
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="eng_rate">Rate your English ( 1 - 10 )</label>
                                                        <input type="tel" class="form-control rating-field" id="eng_rate" value="<?php echo $counsil_Query['counsil_eng_rate']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the Rating
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">IELTS or PTE given <span class="asterisk">*</span></label><br>
                                                        <input type="radio" id="mig_test1" name="mig_test" class="form-check-input mig_test" <?php echo $counsil_Query['counsil_migration_test']=='' ? "checked" : ( $counsil_Query['counsil_migration_test']==1 ? 'checked' : '' ); ?> value="1">
                                                        <label for="mig_test1" >Yes</label>                                                        
                                                        <input type="radio" id="mig_test2" name="mig_test" class="form-check-input mig_test" value="2" <?php echo $counsil_Query['counsil_migration_test']==2 ? 'checked' : '' ; ?>>
                                                        <label for="mig_test2" >No</label>   
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mig_test_child" style="display:<?php echo $counsil_Query['counsil_migration_test']=='' ? 'block' : ($counsil_Query['counsil_migration_test']==1 ? 'block' : 'none') ?>">
                                                <div class="mb-3">
                                                        <label class="form-label" for="overall_result">Overall Result</label>
                                                        <input type="text" class="form-control nummber-field" id="overall_result" value="<?php echo $counsil_Query['counsil_overall_result']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the result
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mig_test_child" style="display:<?php echo $counsil_Query['counsil_migration_test']=='' ? 'block' : ($counsil_Query['counsil_migration_test']==1 ? 'block' : 'none') ?>">
                                                <div class="mb-3">
                                                        <label class="form-label" for="module_result">Each Module Result</label>
                                                        <input type="text" class="form-control" id="module_result" value="<?php echo $counsil_Query['counsil_module_result']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the Module Result
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mig_test_child" style="display:<?php echo $counsil_Query['counsil_migration_test']=='' ? 'block' : ($counsil_Query['counsil_migration_test']==1 ? 'block' : 'none') ?>">
                                                <div class="mb-3">
                                                        <label class="form-label" for="job_nature">Nature of the Job explained</label>
                                                        <input type="text" class="form-control" id="job_nature" value="<?php echo $counsil_Query['counsil_job_nature']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the Job Nature Explained
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Vaccination Taken<span class="asterisk">*</span></label><br>
                                                        <input type="radio" id="vaccine_status1" name="vaccine_status" class="form-check-input vaccine_status" checked value="1" <?php echo $counsil_Query['counsil_vaccine_status']=='' ? "checked" : ( $counsil_Query['counsil_vaccine_status']==1 ? 'checked' : '' ); ?>>
                                                        <label for="vaccine_status2" >Yes</label>                                                        
                                                        <input type="radio" id="vaccine_status2" name="vaccine_status" class="form-check-input vaccine_status" <?php echo $counsil_Query['counsil_vaccine_status']==2 ? 'checked' : '' ; ?> value="2">
                                                        <label for="vaccine_status2" >No</label>   
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                         <label class="form-label" for="pref_comment">Any preferences or requirements or expectations regarding this course</label>
                                                        <input type="text" class="form-control" id="pref_comment" placeholder="Requirements" value="<?php echo $counsil_Query['counsil_pref_comments']; ?>">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="remarks">Remarks</label>
                                                        <?php  
                                                        $st_remarks=['Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];

                                                        if($counsil_Query['counsil_remarks']!=''){
                                                            $remarksSel=json_decode($counsil_Query['counsil_remarks']);
                                                        }else{
                                                            $remarksSel=[];   
                                                        }

                                                        for($i=1;$i<count($st_remarks);$i++){                                            

                                                            if(in_array($i,$remarksSel)){
                                                                $checked='checked';
                                                            }else{
                                                                $checked='';
                                                            }                                                            

                                                            echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input" id="remark_check_"'.$i.' '.$checked.' value="'.$i.'">';
                                                            echo '<label for="remark_check_"'.$i.'>'.$st_remarks[$i].'</label></div>';
                                                        }
                                                            ?>
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                                <?php if($eqId==0){ ?>
                                                        <button class="btn btn-primary" type="button" id="counseling_submit">Submit Enquiry</button>
                                                        <?php }else{ ?>
                                                        <button class="btn btn-primary" type="button" id="counseling_submit">Update Counseling</button>
                                                        <?php } ?>
                                                        <input type="hidden" value="<?php echo $eqId; ?>" id="check_update">
                                        </form>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                        </div>
                        <!-- end row -->

                    </div> <!-- container-fluid -->
                </div>
            </div>

        </div>
        <?php include('includes/footer_includes.php'); ?>
        <script>

            $(document).on('change','.mig_test',function(){
                var mig_test=$('.mig_test:checked').val();
                if(mig_test==1){
                    $('.mig_test_child').show();
                }else{
                    $('.mig_test_child').hide();
                }
            })
            $(document).on('change','.aus_study',function(){
                var aus_study=$('.aus_study:checked').val();
                if(aus_study==1){
                    $('.aus_study_child').show();
                }else{
                    $('.aus_study_child').hide();
                }
            })

            $(document).on('click','#counseling_submit',function(){                
                var counseling_timing=$('#counseling_timing').val().trim();
                var counseling_end_timing=$('#counseling_end_timing').val().trim();
                var counseling_type=$('.counseling_type').val();
                var member_name=$('#member_name').val().trim();
                var aus_duration=$('#aus_duration').val().trim();
                var work_status=$('.work_status:checked').val();
                var visa_condition=$('#visa_condition').val()==0 ? '' : $('#visa_condition').val();
                var education=$('#education').val();
                var aus_study=$('.aus_study:checked').val();
                var university_name=$('#university_name').val();
                var qualification=$('#qualification').val();
                var course=$('#course').val();
                var eng_rate=$('#eng_rate').val();
                var mig_test=$('.mig_test:checked').val();
                var overall_result=$('#overall_result').val();
                var module_result=$('#module_result').val();
                var pref_comment=$('#pref_comment').val();
                var enquiry_id=$('#enquiry_id').val();
                var job_nature=$('#job_nature').val();
                var vaccine_status=$('.vaccine_status:checked').val();

                var remarks=[]; 

                $('.remarks_check:checkbox:checked').each(function() {
                    remarks.push(this.value);
                });  

                if(aus_study==0){
                    aus_study_error=0;
                }else if(aus_study==1){

                    if(course=='' || university_name==''){
                        aus_study_error=0;
                    }else{
                        aus_study_error=1;
                    }

                }else{
                    aus_study_error=1;
                }
                if(mig_test==0){
                    mig_test_error=0;
                }else if(mig_test==1){

                    if(overall_result=='' || module_result=='' || job_nature==''){
                        mig_test_error=0;
                    }else{
                        mig_test_error=1;
                    }

                }else{
                    mig_test_error=1;
                }
                            

                if(enquiry_id=='' || counseling_timing=='' || counseling_type=='' || member_name==''  ||aus_duration==''||work_status==''||visa_condition==''|| education=='' || eng_rate=='' || vaccine_status == '' || qualification=='' || aus_study_error==0 || mig_test_error==0 ){

                    if(enquiry_id==''){
                        $('button[data-id="enquiry_id"]').addClass('invalid-div');
                        $('button[data-id="enquiry_id"]').removeClass('valid-div');
                        $('button[data-id="enquiry_id"]').closest('div').find('.error-feedback').show();
                    }else{
                        $('button[data-id="enquiry_id"]').addClass('valid-div');
                        $('button[data-id="enquiry_id"]').removeClass('invalid-div');                        
                        $('button[data-id="enquiry_id"]').closest('div').find('.error-feedback').hide();
                    }

                    if(aus_study_error==0){
                        if(aus_study==1){

                            if(course==''){
                                $('#course').addClass('invalid-div');
                                $('#course').removeClass('valid-div');
                                $('#course').closest('div').find('.error-feedback').show();
                            }else{
                                $('#course').addClass('valid-div');
                                $('#course').removeClass('invalid-div');
                                $('#course').closest('div').find('.error-feedback').hide();
                            }
                            if(university_name==''){
                                $('#university_name').addClass('invalid-div');
                                $('#university_name').removeClass('valid-div');
                                $('#university_name').closest('div').find('.error-feedback').show();
                            }else{
                                $('#university_name').addClass('valid-div');
                                $('#university_name').removeClass('invalid-div');
                                $('#university_name').closest('div').find('.error-feedback').hide();
                            }

                        }
                    }

                    if(mig_test_error==0){
                        
                        if(mig_test==1){
                            
                            if(overall_result==''){
                                $('#overall_result').addClass('invalid-div');
                                $('#overall_result').removeClass('valid-div');
                                $('#overall_result').closest('div').find('.error-feedback').show();
                            }else{
                                $('#overall_result').addClass('valid-div');
                                $('#overall_result').removeClass('invalid-div');
                                $('#overall_result').closest('div').find('.error-feedback').hide();
                            }
                            if(module_result==''){
                                $('#module_result').addClass('invalid-div');
                                $('#module_result').removeClass('valid-div');
                                $('#module_result').closest('div').find('.error-feedback').show();
                            }else{
                                $('#module_result').addClass('valid-div');
                                $('#module_result').removeClass('invalid-div');
                                $('#module_result').closest('div').find('.error-feedback').hide();
                            }
                            if(job_nature==''){
                                $('#job_nature').addClass('invalid-div');
                                $('#job_nature').removeClass('valid-div');
                                $('#job_nature').closest('div').find('.error-feedback').show();
                            }else{
                                $('#job_nature').addClass('valid-div');
                                $('#job_nature').removeClass('invalid-div');
                                $('#job_nature').closest('div').find('.error-feedback').hide();
                            }

                        }
                    }

                    if(counseling_timing==''){
                        $('#counseling_timing').addClass('invalid-div');
                        $('#counseling_timing').removeClass('valid-div');
                        $('#counseling_timing').closest('div').find('.error-feedback').show();
                    }else{
                        $('#counseling_timing').addClass('valid-div');
                        $('#counseling_timing').removeClass('invalid-div');                        
                        $('#counseling_timing').closest('div').find('.error-feedback').hide();
                    }
                    // if(counseling_end_timing==''){
                    //     $('#counseling_end_timing').addClass('invalid-div');
                    //     $('#counseling_end_timing').removeClass('valid-div');
                    //     $('#counseling_end_timing').closest('div').find('.error-feedback').show();
                    // }else{
                    //     $('#counseling_end_timing').addClass('valid-div');
                    //     $('#counseling_end_timing').removeClass('invalid-div');                        
                    //     $('#counseling_end_timing').closest('div').find('.error-feedback').hide();
                    // }
                    if(qualification==''){
                        $('#qualification').addClass('invalid-div');
                        $('#qualification').removeClass('valid-div');
                        $('#qualification').closest('div').find('.error-feedback').show();
                    }else{
                        $('#qualification').addClass('valid-div');
                        $('#qualification').removeClass('invalid-div');                        
                        $('#qualification').closest('div').find('.error-feedback').hide();
                    }
                    if(counseling_type==''){
                        $('#counseling_type').addClass('invalid-div');
                        $('#counseling_type').removeClass('valid-div');
                        $('#counseling_type').closest('div').find('.error-feedback').show();
                    }else{
                        $('#counseling_type').addClass('valid-div');
                        $('#counseling_type').removeClass('invalid-div');                        
                        $('#counseling_type').closest('div').find('.error-feedback').hide();
                    }                    

                    if(member_name==''){
                        $('#member_name').addClass('invalid-div');
                        $('#member_name').removeClass('valid-div');
                        $('#member_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#member_name').addClass('valid-div');
                        $('#member_name').removeClass('invalid-div');                        
                        $('#member_name').closest('div').find('.error-feedback').hide();
                    }
                    if(aus_duration==''){
                        $('#aus_duration').addClass('invalid-div');
                        $('#aus_duration').removeClass('valid-div');
                        $('#aus_duration').closest('div').find('.error-feedback').show();
                    }else{
                        $('#aus_duration').addClass('valid-div');
                        $('#aus_duration').removeClass('invalid-div');                        
                        $('#aus_duration').closest('div').find('.error-feedback').hide();
                    }
                    if(work_status==''){
                        $('#work_status').addClass('invalid-div');
                        $('#work_status').removeClass('valid-div');
                        $('#work_status').closest('div').find('.error-feedback').show();
                    }else{
                        $('#work_status').addClass('valid-div');
                        $('#work_status').removeClass('invalid-div');
                        $('#work_status').closest('div').find('.error-feedback').hide();
                    }
                    if(visa_condition==''){
                        $('#visa_condition').addClass('invalid-div');
                        $('#visa_condition').removeClass('valid-div');
                        $('#visa_condition').closest('div').find('.error-feedback').show();
                    }else{
                        $('#visa_condition').addClass('valid-div');
                        $('#visa_condition').removeClass('invalid-div');
                        $('#visa_condition').closest('div').find('.error-feedback').hide();
                    }

                    if(education==''){
                        $('#education').addClass('invalid-div');
                        $('#education').removeClass('valid-div');
                        $('#education').closest('div').find('.error-feedback').show();
                    }else{
                        $('#education').addClass('valid-div');
                        $('#education').removeClass('invalid-div');
                        $('#education').closest('div').find('.error-feedback').hide();
                    }
                    if(course==''){
                        $('#course').addClass('invalid-div');
                        $('#course').removeClass('valid-div');
                        $('#course').closest('div').find('.error-feedback').show();
                    }else{
                        $('#course').addClass('valid-div');
                        $('#course').removeClass('invalid-div');
                        $('#course').closest('div').find('.error-feedback').hide();
                    }
                    if(eng_rate==''){
                        $('#eng_rate').addClass('invalid-div');
                        $('#eng_rate').removeClass('valid-div');
                        $('#eng_rate').closest('div').find('.error-feedback').show();
                    }else{
                        $('#eng_rate').addClass('valid-div');
                        $('#eng_rate').removeClass('invalid-div');
                        $('#eng_rate').closest('div').find('.error-feedback').hide();
                    }
                    if(vaccine_status==''){
                        $('#vaccine_status').addClass('invalid-div');
                        $('#vaccine_status').removeClass('valid-div');
                        $('#vaccine_status').closest('div').find('.error-feedback').show();
                    }else{
                        $('#vaccine_status').addClass('valid-div');
                        $('#vaccine_status').removeClass('invalid-div');
                        $('#vaccine_status').closest('div').find('.error-feedback').hide();
                    }




                }else{
                    var checkId=$("#check_update").val();                

                    details={formName:'counseling_form',vaccine_status:vaccine_status,job_nature:job_nature,module_result:module_result,pref_comment:pref_comment,eng_rate:eng_rate,mig_test:mig_test,overall_result:overall_result,course:course,university_name:university_name,qualification:qualification,counseling_timing:counseling_timing,counseling_end_timing:counseling_end_timing,enquiry_id:enquiry_id,counseling_type:counseling_type,member_name:member_name,aus_duration:aus_duration,work_status:work_status,visa_condition:visa_condition,education:education,remarks:remarks,aus_study:aus_study,checkId:checkId,admin_id:"<?php echo $_SESSION['user_id']; ?>"};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        success:function(data){

                            if(data==1){
                                $('#toast-text').html('Record Added Successfully');
                                $('#borderedToast1Btn').trigger('click');
                                setTimeout(() => {
                                    location.reload();
                                }, 400);                                
                            }else{
                                $('.toast-text2').html('Cannot add record. Please try again later');
                                $('#borderedToast2Btn').trigger('click');
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