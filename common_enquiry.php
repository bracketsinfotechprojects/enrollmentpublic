<?php include('includes/dbconnect.php'); ?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Student Enquiry</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            .main-content{
                margin:0;
            }
            .page-content{
                padding:20px 0px 0px 0px;
            }
        </style>
    </head>

    <body data-topbar="colored">

    <div id="loader-container">
        <div class="loader"></div>
    </div>

        <!-- Begin page -->
        <div id="layout-wrapper">        
            
            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                    <?php                         

                            $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");
                            $visaStatus=mysqli_query($connection,"SELECT * from visa_statuses where visa_state_status!=1");


                            $Updatestatus=1;
                            $eqId=base64_decode($_GET['data']);
                                
                    ?>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Enquiry Form</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                    <!-- <div class="jelly" id="jelly_loader"></div> -->
                                        <form class="student_enquiry_form" id="student_enquiry_form">
                                            <div class="row">
                                                <div class="col-sm">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="email_address">Email<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="email_address" placeholder="Email Address" >
                                                        <div class="error-feedback">
                                                            Please enter the Email Address
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_date">Date<span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="enquiry_date" >
                                                        <div class="error-feedback">
                                                            Please select the Date
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="surname">Surname<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="surname" placeholder="Surname" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Surname
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="student_name">First Name<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="student_name" placeholder="Student Name">
                                                        <div class="error-feedback">
                                                            Please enter the First name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_for">Enquiring For<span class="asterisk">*</span></label>
                                                        <select name="enquiry_for" class="form-select" id="enquiry_for">
                                                        <option value="0">--select--</option>
                                                        <option value="1">Self</option>
                                                        <option value="2">Family member</option>
                                                        </select>  
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="member_name">Name<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="member_name" placeholder="Name" value="<?php echo $queryRes['st_name']; ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="contact_num">Mobile<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control number-field" maxlength="10" id="contact_num" placeholder="Contact Number" >
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
                                                        </div>
                                                        <div class="phone_error">
                                                            Entered Number Already exist.
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="street_no">Street No / Name</label>
                                                        <input type="text" class="form-control street_no" id="street_no" placeholder="Street No / Name" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Street Details
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="suburb">Suburb</label>
                                                        <input type="text" class="form-control suburb" id="suburb" placeholder="Suburb" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Suburb
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
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
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="post_code">Post Code<span class="asterisk">*</span></label>
                                                        <input type="tel" class="form-control number-field" maxlength="6" id="post_code" placeholder="Post Code" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Post Code
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                                <div class="col-sm">

                                                    <div class="col-md-12">
                                                            <div class="mb-3">
                                                                <label class="form-label" for="courses">Which Course are you interested in?<span class="asterisk">*</span></label>
                                                                <?php 
                                                                $counts=1;
                                                                while($coursesRes=mysqli_fetch_array($courses)){

                                                                    if($queryRes['st_course']!=''){
                                                                        $coursesSel=json_decode($queryRes['st_course']);
                                                                    }else{
                                                                        $coursesSel=[];   
                                                                    }


                                                                    
                                                                // for($i=1;$i<count($st_remarks);$i++){                                            

                                                                    if(in_array($counts,$coursesSel)){
                                                                        $checked='checked';
                                                                    }else{
                                                                        $checked='';
                                                                    }                                                            

                                                                    echo '<div class="form-check"><input type="checkbox" class="courses_check form-check-input" id="course_check_'.$counts.'" '.$checked.' value="'.$counts.'">';
                                                                    echo '<label for="course_check_'.$counts.'">'.$coursesRes["course_sname"].'-'.$coursesRes["course_name"].'</label></div>';
                                                                    $counts++;
                                                                }

                                                                ?>
                                                                <div class="courses_error">
                                                                    Please select the Courses
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="hear_about">How did you hear about us?<span class="asterisk">*</span></label><br>
                                                                        <select name="hear_about" class="selectpicker hear_about" data-selected-text-format="count" multiple id="hear_about" title="Heared From">
                                                                        <?php  
                                                                            $st_heared=['Word of Mouth','Family or Friends','Website','Gumtree','Facebook','Instagram','Linkedin','Mail outs','Migration Agency','Other:'];
                                                                            $hear_select_opt='';                                                            
                                                                            $heared_about=$queryRes['st_heared']=='' ? array() : json_decode($queryRes['st_heared']);
                                                                            for($i=0;$i<count($st_heared);$i++){

                                                                                if(in_array($i,$heared_about) && count($heared_about)!=0){
                                                                                    $checked="selected";
                                                                                }else{
                                                                                    $checked= "";
                                                                                }                                                            

                                                                                $hear_select_opt.= '<option value="'.$i.'" '.$checked.'>'.$st_heared[$i].'</option>';
                                                                                if($i==4){
                                                                                    $hear_select_opt.='<optgroup Label="Social Media">';
                                                                                }else if($i==7){
                                                                                    $hear_select_opt.='</optgroup>';
                                                                                }
                                                                            }
                                                                            echo $hear_select_opt;
                                                                        ?>
                                                                        <!-- <optgroup label="Social Media"> -->
                                                                            <!-- <option value="2">test</option> -->
                                                                        <!-- </optgroup> -->
                                                                            </select>
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 hear_about_child" style="display:<?php echo $queryRes['st_heared']=='' ? 'none' : (in_array(9,json_decode($queryRes['st_heared'])) ? 'block' : 'none' ); ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="hearedby">Specify How you heared about us</label>
                                                                        <input type="text" class="form-control" id="hearedby" value="<?php echo $queryRes['st_hearedby']; ?>" >
                                                                        <div class="error-feedback">
                                                                            Please enter the source heared
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="visit_before">Have you visited us before?<span class="asterisk">*</span></label>
                                                        <select name="visit_before" class="form-select" id="visit_before">
                                                        <option value="0">--select--</option>
                                                        <option value="1">Yes</option>
                                                        <option value="2">No</option>
                                                        </select>  
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="courses">Which Course are you interested in?<span class="asterisk">*</span></label>
                                                        <select name="courses" class="form-select" multiple id="courses">
                                                        <option value="0">--select--</option>

                                                        </select>    
                                                        <div class="error-feedback">
                                                            Please select the Courses
                                                        </div>
                                                    </div>
                                                </div> -->
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="plan_to_start_date">When do you plan to start?</label>
                                                        <input type="date" class="form-control" id="plan_to_start_date" value="" >
                                                        <div class="error-feedback">
                                                            Please select the Plan to Start Date
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="refer_select">Have you been referred by someone?<span class="asterisk">*</span></label>
                                                        <select name="refer_select" class="form-select refered" id="refer_select">
                                                        <option value="0">--select--</option>
                                                        <option value="1">Yes</option>
                                                        <option value="2">No</option>
                                                        </select>                                                          
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 refered_field" style="display:none">
                                                    <div class="mb-3">
                                                         <label class="form-label" for="referer_name">Please specify his / her name</label>
                                                        <input type="text" class="form-control" id="referer_name" value="" placeholder="Name">
                                                        <div class="error-feedback">
                                                            Please Enter his / her name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 refered_field" style="display:none">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="refer_alumni">Is he / she an alumni<span class="asterisk">*</span></label>
                                                        <select name="refer_alumni" class="form-select" id="refer_alumni">
                                                        <option value="0">--select--</option>
                                                        <option value="1">Yes</option>
                                                        <option value="2">No</option>
                                                        </select>                                                          
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                         <label class="form-label" for="pref_comment">Any preferences or requirements or expectations regarding this course</label>
                                                        <input type="text" class="form-control" id="pref_comment" value="" placeholder="Requirements">
                                                    </div>
                                                </div>

                                                    </div>

                                            </div>
                                            <button class="btn btn-primary" type="button" id="enquiry_form">Submit Enquiry</button>
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
                <!-- End Page-content -->                            
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
        <script>

            var checkPhone=0;   
            function PhoneCheck(number){

                return new Promise(function (resolve, reject) {

                    var check_update=$('#check_update').val();
                    var oldenquiryFor='<?php echo $queryRes['st_enquiry_for']; ?>';
                    var oldNumber='<?php echo $queryRes['st_phno']; ?>';

                    var memberName=$('#member_name').val();     
                    var enquiryFor=$('#enquiry_for').val();   

                $.ajax({
                    type:'post',
                    data:{number:number,formName:'phoneNumberCheck',oldNumber:oldNumber,memberName:memberName,enquiryFor:enquiryFor,check_update:check_update,oldenquiryFor:oldenquiryFor},
                    url:'includes/datacontrol',
                    success:function(datas){
                        resolve(datas);
                    },
                    error: function (xhr, status, error) {
                        reject(new Error(status + ': ' + error));
                    }

                })

            });

            }

                        // Usage with async/await
            async function getData(number) {
            try {
                const data = await PhoneCheck(number);
                return data;

                // You can perform further operations with 'data' here
            } catch (error) {
                console.error(error);
            }
            }

            $(document).ready(function(){
                $('.refered').on("change",function(){
                    var value=$(this).val();
                    if( value==0 || value==2 ){
                        $('.refered_field').hide();
                    }else{
                        $('.refered_field').show();
                    }                 
                })

                $('#hear_about').on("change",function(){
                    var value=$(this).val();                    
                    if( value.includes('9') ){
                        $('.hear_about_child').show();
                    }else{
                        $('.hear_about_child').hide();
                    }                 
                })

                $('#enquiry_for').on('change',function(){
                    var value=$(this).val();
                    if( value==1){
                        $('#member_name').val($('#student_name').val());
                        $('#member_name').prop('readonly',true);
                    }else{
                        $('#member_name').prop('readonly',false);
                        $('#member_name').val('');
                    }
                })

                $('#student_name').keyup(function(){
                    if($('#enquiry_for').val()==1){
                        $('#member_name').val($('#student_name').val());
                    }
                })

            })

            $(document).on('click','#enquiry_form',async() =>{
                var studentName=$('#student_name').val().trim();
                var contactName=$('#contact_num').val().trim();
                var emailAddress=$('#email_address').val().trim();
                var enquiryDate=$('#enquiry_date').val();

                var surname=$('#surname').val();
                var suburb=$('#suburb').val();
                var stuState=$('#stu_state').val();
                var postCode=$('#post_code').val();
                var visit_before=$('#visit_before').val()==0 ? '' : $('#visit_before').val();
                var hear_about=$('#hear_about').val();
                var hearedby=$('#hearedby').val();
                var plan_to_start_date=$('#plan_to_start_date').val();
                var refer_select=$('#refer_select').val();
                var referer_name=$('#referer_name').val();
                var memberName=$('#member_name').val().trim();
                var refer_alumni=$('#refer_alumni').val();
                var prefComment=$('#pref_comment').val();
                var streetDetails=$('#street_no').val();
                var enquiryFor=$('#enquiry_for').val()==0 ? '' : $('#enquiry_for').val();

                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                // var courses=$('#courses').val()==0 ? '' : $('#courses').val();

                var courses=[];

                $('.courses_check:checkbox:checked').each(function() {
                    courses.push(this.value);
                });

                if(refer_select==0){
                    refer_select_error=0;
                }else if(refer_select==1){

                    if(refer_alumni==0){
                        refer_select_error=0;
                    }else{
                        refer_select_error=1;
                    }

                }else{
                    refer_select_error=1;
                }


                var enquiryIdRec=await getData(contactName); 
                if(enquiryIdRec.split('||')[0]==1 || ( contactName=='' || contactName.length!=10 ) ){
                    var phoneChecks=1;
                }else{
                    var phoneChecks=0;
                }

                
                if(hear_about.length==0){
                    hear_about_error=0;
                }else if(hear_about.includes('9')){

                    if(hearedby==''){
                        hear_about_error=0;
                    }else{
                        hear_about_error=1;
                    }

                }else{
                    hear_about_error=1;
                }

                if(studentName==''|| phoneChecks==1 ||emailAddress==''|| (emailAddress!='' && !emailAddress.match(emailregexp)==true ) ||courses.length==0|| enquiryDate=='' || hear_about_error==0 || refer_select_error==0 || surname=='' || enquiryFor==''|| postCode=='' || visit_before=='' ){

                    if(refer_select_error==0){
                        if(refer_select==0){
                            $('#refer_select').addClass('invalid-div');
                            $('#refer_select').removeClass('valid-div');
                            $('#refer_select').closest('div').find('.error-feedback').show();
                        }else if(refer_select==1){

                            if(refer_alumni==0){
                                $('#refer_alumni').addClass('invalid-div');
                                $('#refer_alumni').removeClass('valid-div');
                                $('#refer_alumni').closest('div').find('.error-feedback').show();
                            }else{
                                $('#refer_alumni').addClass('valid-div');
                                $('#refer_alumni').removeClass('invalid-div');
                                $('#refer_alumni').closest('div').find('.error-feedback').hide();
                            }

                        }else{
                            $('#refer_select').addClass('valid-div');
                            $('#refer_select').removeClass('invalid-div');
                            $('#refer_select').closest('div').find('.error-feedback').hide();
                        }
                    }

                    if(hear_about_error==0){
                        if(hear_about.length==0){
                            $('#hear_about').addClass('invalid-div');
                            $('#hear_about').removeClass('valid-div');
                            $('#hear_about').closest('div').find('.error-feedback').show();
                        }else if(hear_about.includes('9')){

                            if(hearedby==''){
                                $('#hearedby').addClass('invalid-div');
                                $('#hearedby').removeClass('valid-div');
                                $('#hearedby').closest('div').find('.error-feedback').show();
                            }else{
                                $('#hearedby').addClass('valid-div');
                                $('#hearedby').removeClass('invalid-div');
                                $('#hearedby').closest('div').find('.error-feedback').hide();
                            }

                        }else{
                            $('#hear_about').addClass('valid-div');
                            $('#hear_about').removeClass('invalid-div');
                            $('#hear_about').closest('div').find('.error-feedback').hide();
                        }   
                    }

                    if(studentName==''){
                        $('#student_name').addClass('invalid-div');
                        $('#student_name').removeClass('valid-div');
                        $('#student_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#student_name').addClass('valid-div');
                        $('#student_name').removeClass('invalid-div');
                        $('#student_name').closest('div').find('.error-feedback').hide();
                    }
                    if(contactName=='' || contactName.length!=10 ){
                        $('#contact_num').addClass('invalid-div');
                        $('#contact_num').removeClass('valid-div');
                        $('#contact_num').closest('div').find('.error-feedback').show();
                    }else if(enquiryIdRec.split('||')[0]==1){
                        $('#contact_num').addClass('invalid-div');
                        $('#contact_num').removeClass('valid-div');                        
                        $('#contact_num').closest('div').find('.error-feedback').hide();     
                        $('#contact_num').closest('div').find('.phone_error').show();
                    }else{
                        $('#contact_num').addClass('valid-div');
                        $('#contact_num').removeClass('invalid-div');
                        $('#contact_num').closest('div').find('.error-feedback').hide();
                        $('#contact_num').closest('div').find('.phone_error').hide();
                    }
                    if(emailAddress=='' || (emailAddress!='' && (!emailAddress.match(emailregexp)==true))){
                        $('#email_address').addClass('invalid-div');
                        $('#email_address').removeClass('valid-div');
                        $('#email_address').closest('div').find('.error-feedback').show();
                    }else{
                        $('#email_address').addClass('valid-div');
                        $('#email_address').removeClass('invalid-div');
                        $('#email_address').closest('div').find('.error-feedback').hide();
                    }
                    if(courses.length==0){
                        // $('#courses').addClass('invalid-div');
                        // $('#courses').removeClass('valid-div');
                        $('.courses_error').show();
                    }else{
                        // $('#courses').addClass('valid-div');
                        // $('#courses').removeClass('invalid-div');
                        $('.courses_error').hide();
                    }


                    if(enquiryDate==''){
                        $('#enquiry_date').addClass('invalid-div');
                        $('#enquiry_date').removeClass('valid-div');
                        $('#enquiry_date').closest('div').find('.error-feedback').show();
                    }else{
                        $('#enquiry_date').addClass('valid-div');
                        $('#enquiry_date').removeClass('invalid-div');
                        $('#enquiry_date').closest('div').find('.error-feedback').hide();
                    }

                    if(memberName=='' ){
                        $('#member_name').addClass('invalid-div');
                        $('#member_name').removeClass('valid-div');
                        $('#member_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#member_name').addClass('valid-div');
                        $('#member_name').removeClass('invalid-div');
                        $('#member_name').closest('div').find('.error-feedback').hide();
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

                    if(enquiryFor==''){
                        $('#enquiry_for').addClass('invalid-div');
                        $('#enquiry_for').removeClass('valid-div');
                        $('#enquiry_for').closest('div').find('.error-feedback').show();
                    }else{
                        $('#enquiry_for').addClass('valid-div');
                        $('#enquiry_for').removeClass('invalid-div');
                        $('#enquiry_for').closest('div').find('.error-feedback').hide();
                    }

                    if(postCode==''){
                        $('#post_code').addClass('invalid-div');
                        $('#post_code').removeClass('valid-div');
                        $('#post_code').closest('div').find('.error-feedback').show();
                    }else{
                        $('#post_code').addClass('valid-div');
                        $('#post_code').removeClass('invalid-div');
                        $('#post_code').closest('div').find('.error-feedback').hide();
                    }

                    if(visit_before==''){
                        $('#visit_before').addClass('invalid-div');
                        $('#visit_before').removeClass('valid-div');
                        $('#visit_before').closest('div').find('.error-feedback').show();
                    }else{
                        $('#visit_before').addClass('valid-div');
                        $('#visit_before').removeClass('invalid-div');
                        $('#visit_before').closest('div').find('.error-feedback').hide();
                    }

                }else{
                    var checkId=$("#check_update").val();
                    $('#loader-container').css('display','flex');
                    // $('#jelly_loader').show();
                    $('#student_enquiry_form').css('opacity','0.1');

                    courses=courses.filter(item => item !== '0');
                    
                    details={formName:'student_enquiry_common',studentName:studentName,contactName:contactName,emailAddress:emailAddress,courses:courses,checkId:checkId,surname:surname,suburb:suburb,stuState:stuState,postCode:postCode,visit_before:visit_before,hear_about:JSON.stringify(hear_about),hearedby:hearedby,plan_to_start_date:plan_to_start_date,refer_select:refer_select,prefComment:prefComment,memberName:memberName,referer_name:referer_name,refer_alumni:refer_alumni,streetDetails:streetDetails,enquiryFor:enquiryFor,admin_id:0,form_type:1};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        success:function(data){
                            if(data==0){
                                $('.toast-text2').html('Cannot add record. Please try again later');
                                $('#borderedToast2Btn').trigger('click');
                            }else if(data==2){
                                document.getElementById('student_enquiry_form').reset();
                                $('#toast-text').html('Record Updated Successfully');
                                $('#borderedToast1Btn').trigger('click');
                                // window.location.href="dashboard.php";
                                // $('#jelly_loader').hide();
                                $('#loader-container').hide();
                                $('#student_enquiry_form').css('opacity','');          
                                setTimeout(() => {location.reload();}, 700); 
                                // var UpdateStatus='<?php echo $Updatestatus; ?>';
                                // if(UpdateStatus==1){
                                //     location.reload();
                                // }
                            }else{
                                document.getElementById('student_enquiry_form').reset();
                                $('#toast-text').html('New Enquiry Added');
                                $('#borderedToast1Btn').trigger('click');

                                $('#myModalLabel').html('Enquiry ID Created:');
                                $('.modal-body').html(data);
                                $('#model_trigger').trigger('click');
                                // $('#jelly_loader').hide();
                                $('#loader-container').hide();
                                setTimeout(() => {location.reload();}, 700); 
                                $('#student_enquiry_form').css('opacity','');
                                // var UpdateStatus='<?php echo $Updatestatus; ?>';
                                // if(UpdateStatus==1){
                                //     location.reload();
                                // }
                            }
                        }
                    })
                }

            })


            function genQR(){                

                $.ajax({
                    url:'includes/datacontrol',
                    data:{admin_id:"<?php echo $_SESSION['user_id']; ?>",formName:'create_qr'},
                    type:'post',
                    success:function(data){                
                        var updatedURL = removeLastSegmentFromURL(window.location.href);        
                    
                        var qrcode = new QRCode(qrcodeContainer, {
                        text: updatedURL+'/common_enquiry.php?data='+data, 
                        width: 128,
                        height: 128,
                        });
                        var qrcodeContainer = document.getElementById('qrcode');

                        var downloadLink = document.getElementById('downloadLink');

                        var qrCodeDataURL = qrcodeContainer.querySelector('canvas').toDataURL('image/png');

                        downloadLink.href = qrCodeDataURL;
                        downloadLink.click();
                    
                    }
                })

            }

            function removeLastSegmentFromURL(url) {
                // Split the URL by "/"
                var segments = url.split("/");

                // Remove the last segment
                segments.pop();

                // Join the segments back together
                var updatedURL = segments.join("/");

                return updatedURL;
                }

        </script>
    </body>
</html>
