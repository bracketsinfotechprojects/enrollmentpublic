<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
    
        $enquiryIds=mysqli_query($connection,"SELECT st_enquiry_id,st_name,st_phno from student_enquiry where st_enquiry_status!=1");

        if(isset($_GET['flw_id'])){

            $eqId=base64_decode($_GET['flw_id']);
            $followup_Query=mysqli_fetch_array(mysqli_query($connection,"SELECT * FROM `followup_calls` where flw_id='".$eqId."' AND flw_enquiry_status=0"));
            
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
                                    <h4 class="mb-sm-0">Follow Up Call</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Follow Up Call</li>
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
                                                        <label class="form-label" for="enquiry_id">Enquiry ID</label><br>
                                                        <select class="selectpicker" title="--select--" data-live-search="true" name="enquiry_id" id="enquiry_id">

                                                        <?php
                                                        if(mysqli_num_rows($enquiryIds)!=0){
                                                        while($enquiryIdsRes=mysqli_fetch_array($enquiryIds)){
                                                            
                                                            $checkQry=mysqli_query($connection,"SELECT * FROM `followup_calls` where flw_id='".$eqId."' AND flw_enquiry_status=0");                                                            

                                                            if(mysqli_num_rows($checkQry)!=0){

                                                                $checkQryData=mysqli_fetch_array($checkQry);

                                                                if($checkQryData['enquiry_id']==$enquiryIdsRes['st_enquiry_id']){
                                                                    $checked="selected";
                                                                }else{
                                                                    $checked="";
                                                                }                                                        

                                                            }

                                                            echo "<option value='".$enquiryIdsRes['st_enquiry_id']."' data-name='".$enquiryIdsRes['st_name']."' data-mobile='".$enquiryIdsRes['st_phno']."' ".$checked.">".$enquiryIdsRes['st_enquiry_id']."</option>";


                                                        }
                                                        }else{
                                                            echo "<option value='0'>No Enquiries Found</option>";
                                                        }

                                                        ?>

                                                        </select>
                                                        <div class="error-feedback">
                                                            Please Select the Enquiry ID
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="name_main">Student Name<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" placeholder="Student Name" id="student_name" value="<?php echo $followup_Query['flw_name']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Student Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="mobile_num">Contact Number<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control number-field" id="mobile_num" placeholder="Contact Number" value="<?php echo $followup_Query['flw_phone']; ?>" maxlength="10">
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="contacted_person">Contacted Person<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="contacted_person" placeholder="Contacted Person Name" value="<?php echo $followup_Query['flw_contacted_person']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Contacted Person Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="contacted_time">Contacted Time<span class="asterisk">*</span></label>
                                                        <input type="datetime-local" class="form-control" id="contacted_time" value="<?php echo $followup_Query['flw_contacted_time']=='' ? '' : date('Y-m-d H:i',strtotime($followup_Query['flw_contacted_time'])); ?>">
                                                        <div class="error-feedback">
                                                            Please select the time when you contacted
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="date">Date<span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="date" value="<?php echo $followup_Query['flw_date']=='' ? '' : date('Y-m-d',strtotime($followup_Query['flw_date'])); ?>">
                                                        <div class="error-feedback">
                                                            Please select the date
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="mode_contacted">Mode of Contact<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="mode_contacted" value="<?php echo $followup_Query['flw_mode_contact']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Mode of Contact
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="comments">Staff Notes</label>
                                                        <input type="text" class="form-control" id="comments" value="<?php echo $followup_Query['flw_comments']; ?>">
                                                        <div class="error-feedback">
                                                            Please Enter the  Comments
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" style="display:<?php echo $eqId!=0 ? 'block' : 'none' ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="progress_status">Status or Progress</label>
                                                        <input type="text" class="form-control" id="progress_status" value="<?php echo $followup_Query['flw_progress_state']; ?>" maxlength="255">
                                                        <div class="error-feedback">
                                                            Please Enter the Progress of Followup
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="remarks">Remarks</label>
                                                        <?php  
                                                        $st_remarks=['Seems to be interested to do course and need to contact asap','contacted and followed','Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose'];

                                                        if($followup_Query['flw_remarks']!=''){
                                                            $remarksSel=json_decode($followup_Query['flw_remarks']);
                                                        }else{
                                                            $remarksSel=[];   
                                                        }

                                                        for($i=1;$i<count($st_remarks);$i++){

                                                            if(in_array($i,$remarksSel)){
                                                                $checked='checked';
                                                            }else{
                                                                $checked='';
                                                            }

                                                            echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input" id="remark_check_"'.$i.' value="'.$i.'" '.$checked.'>';
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
                                                <button class="btn btn-primary" type="button" id="followup_check">Submit Enquiry</button>
                                                <?php }else{ ?>
                                                <button class="btn btn-primary" type="button" id="followup_check">Update Enquiry</button>
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

// $(document).on('click','#lookedup',function(){
//     studetnLookup();
//     $('#model_trigger1').trigger('click');
// })

$(document).on('change','#enquiry_id' , function(){

    $('#mobile_num').val($('#enquiry_id option:selected').data('mobile'));  
    $('#student_name').val($('#enquiry_id option:selected').data('name'));  
    $('#mobile_num').attr('readonly','readonly');
    $('#student_name').attr('readonly','readonly');
    
})

            $(document).on('click','#followup_check',function(){                
                var comments=$('#comments').val().trim();
                var contactMode=$('#mode_contacted').val();
                var student_name=$('#student_name').val().trim();
                var contacted_time=$('#contacted_time').val().trim();
                var contacted_person=$('#contacted_person').val().trim();
                var date=$('#date').val().trim();
                var enquiry_id=$('#enquiry_id').val();
                var contact_num=$('#mobile_num').val();
                var progress_status=$('#progress_status').val();

                var remarks=[]; 

                $('.remarks_check:checkbox:checked').each(function() {
                    remarks.push(this.value);
                });  

                if(date=='' || contactMode==''  ||contacted_person==''||student_name==''||contacted_time==''||  ( contact_num=='' || contact_num.length!=10 ) ){
                    // if(enquiry_id==''){
                    //     $('button[data-id="enquiry_id"]').addClass('invalid-div');
                    //     $('button[data-id="enquiry_id"]').removeClass('valid-div');
                    //     $('button[data-id="enquiry_id"]').closest('div').find('.error-feedback').show();
                    // }else{
                    //     $('button[data-id="enquiry_id"]').addClass('valid-div');
                    //     $('button[data-id="enquiry_id"]').removeClass('invalid-div');                        
                    //     $('button[data-id="enquiry_id"]').closest('div').find('.error-feedback').hide();
                    // }
                    if(( contact_num=='' || contact_num.length!=10 )){
                        $('#mobile_num').addClass('invalid-div');
                        $('#mobile_num').removeClass('valid-div');
                        $('#mobile_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#mobile_num').addClass('valid-div');
                        $('#mobile_num').removeClass('invalid-div');                        
                        $('#mobile_num').closest('div').find('.error-feedback').hide();
                    }
                    if(contactMode==''){
                        $('#mode_contacted').addClass('invalid-div');
                        $('#mode_contacted').removeClass('valid-div');
                        $('#mode_contacted').closest('div').find('.error-feedback').show();
                    }else{
                        $('#mode_contacted').addClass('valid-div');
                        $('#mode_contacted').removeClass('invalid-div');                        
                        $('#mode_contacted').closest('div').find('.error-feedback').hide();
                    }
                    if(contacted_time==''){
                        $('#contacted_time').addClass('invalid-div');
                        $('#contacted_time').removeClass('valid-div');
                        $('#contacted_time').closest('div').find('.error-feedback').show();
                    }else{
                        $('#contacted_time').addClass('valid-div');
                        $('#contacted_time').removeClass('invalid-div');                        
                        $('#contacted_time').closest('div').find('.error-feedback').hide();
                    }
                    if(contacted_person==''){
                        $('#contacted_person').addClass('invalid-div');
                        $('#contacted_person').removeClass('valid-div');
                        $('#contacted_person').closest('div').find('.error-feedback').show();
                    }else{
                        $('#contacted_person').addClass('valid-div');
                        $('#contacted_person').removeClass('invalid-div');
                        $('#contacted_person').closest('div').find('.error-feedback').hide();
                    }
                    if(date==''){
                        $('#date').addClass('invalid-div');
                        $('#date').removeClass('valid-div');
                        $('#date').closest('div').find('.error-feedback').show();
                    }else{
                        $('#date').addClass('valid-div');
                        $('#date').removeClass('invalid-div');
                        $('#date').closest('div').find('.error-feedback').hide();
                    }
                    if(student_name==''){
                        $('#student_name').addClass('invalid-div');
                        $('#student_name').removeClass('valid-div');
                        $('#student_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#student_name').addClass('valid-div');
                        $('#student_name').removeClass('invalid-div');
                        $('#student_name').closest('div').find('.error-feedback').hide();
                    }
                }else{
                    var checkId=$("#check_update").val();
                    details={formName:'followup_call',student_name:student_name,date:date,contacted_person:contacted_person,contacted_time:contacted_time,contactMode:contactMode,progress_status:progress_status,contact_num:contact_num,enquiry_id:enquiry_id,remarks:remarks,comments:comments,checkId:checkId,admin_id:"<?php echo $_SESSION['user_id']; ?>"};
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