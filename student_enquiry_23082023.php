<?php 
session_start();
if(@$_SESSION['user_type']!=''){
?>
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
                                    <h4 class="mb-sm-0">Student's Enquiry</h4>

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

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="student_enquiry_form" id="student_enquiry_form">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="student_name">Student's Name</label>
                                                        <input type="text" class="form-control" id="student_name" placeholder="Student Name" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the student's name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="contact_num">Contact No</label>
                                                        <input type="text" class="form-control number-field" maxlength="10" id="contact_num" placeholder="Contact Number" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Contact Number
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="email_address">Email</label>
                                                        <input type="text" class="form-control" id="email_address" placeholder="Email Address" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Email Address
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="courses">Course Interested In</label>
                                                        <select name="courses" class="form-control" id="courses">
                                                            <option value="0">--select--</option>
                                                            <option value="1">Basic</option>
                                                            <option value="2">Intermediate</option>
                                                            <option value="3">Expert</option>
                                                        </select>    
                                                        <div class="error-feedback">
                                                            Please select the Courses
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="payment_fee">Fee Promised</label>
                                                        <input type="text" class="form-control number-field" maxlength="7" id="payment_fee" placeholder="0.00" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Promised Fee
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="visa_status">Visa Condition</label>
                                                        <select name="visa_status" class="form-control" id="visa_status">
                                                            <option value="0">--select--</option>
                                                            <option value="1">Pending</option>
                                                            <option value="2">Approved</option>
                                                            <option value="3">Declined</option>
                                                        </select> 
                                                        <div class="error-feedback">
                                                            Please select a visa status
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-primary" type="button" id="enquiry_form">Submit Enquiry</button>
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
            $(document).on('click','#enquiry_form',function(){
                var studentName=$('#student_name').val().trim();
                var contactName=$('#contact_num').val().trim();
                var emailAddress=$('#email_address').val().trim();
                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var courses=$('#courses').val()==0 ? '' : $('#courses').val();
                var payment=$('#payment_fee').val().trim();
                var visaStatus=$('#visa_status').val()==0 ? '' : $('#visa_status').val();

                if(studentName==''|| ( contactName=='' || contactName.length!=10 ) ||emailAddress==''|| (emailAddress!='' && !emailAddress.match(emailregexp)==true ) ||courses==''||payment==''||visaStatus==''){
                    if(studentName==''){
                        $('#student_name').addClass('invalid-div');
                        $('#student_name').removeClass('valid-div');
                        $('#student_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#student_name').addClass('valid-div');
                        $('#student_name').removeClass('invalid-div');
                        console.log($('#student_name').parents('div').find('.error-feedback').attr('class'));
                        $('#student_name').closest('div').find('.error-feedback').hide();
                    }
                    if(contactName=='' || contactName.length!=10 ){
                        $('#contact_num').addClass('invalid-div');
                        $('#contact_num').removeClass('valid-div');
                        $('#contact_num').closest('div').find('.error-feedback').show();
                    }else{
                        $('#contact_num').addClass('valid-div');
                        $('#contact_num').removeClass('invalid-div');
                        $('#contact_num').closest('div').find('.error-feedback').hide();
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
                    if(courses==''){
                        $('#courses').addClass('invalid-div');
                        $('#courses').removeClass('valid-div');
                        $('#courses').closest('div').find('.error-feedback').show();
                    }else{
                        $('#courses').addClass('valid-div');
                        $('#courses').removeClass('invalid-div');
                        $('#courses').closest('div').find('.error-feedback').hide();
                    }
                    if(payment==''){
                        $('#payment_fee').addClass('invalid-div');
                        $('#payment_fee').removeClass('valid-div');
                        $('#payment_fee').closest('div').find('.error-feedback').show();
                    }else{
                        $('#payment_fee').addClass('valid-div');
                        $('#payment_fee').removeClass('invalid-div');
                        $('#payment_fee').closest('div').find('.error-feedback').hide();
                    }
                    if(visaStatus==''){
                        $('#visa_status').addClass('invalid-div');
                        $('#visa_status').removeClass('valid-div');
                        $('#visa_status').closest('div').find('.error-feedback').show();
                    }else{
                        $('#visa_status').addClass('valid-div');
                        $('#visa_status').removeClass('invalid-div');
                        $('#visa_status').closest('div').find('.error-feedback').hide();
                    }
                }else{
                    details={formName:'student_enquiry',studentName:studentName,contactName:contactName,emailAddress:emailAddress,courses:courses,payment:payment,visaStatus:visaStatus};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        success:function(data){
                            if(data==0){
                            document.getElementById('student_enquiry_form').reset();
                            $('#toast-text').html('New Enquiry Added');
                                $('#borderedToast1Btn').trigger('click');
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
