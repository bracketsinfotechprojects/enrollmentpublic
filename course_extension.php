<?php include('includes/dbconnect.php'); ?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Application for Course Extension Form</title>
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
            .asterisk {
                color: red;
            }
            .error-feedback {
                color: red;
                font-size: 12px;
                display: none;
                margin-top: 5px;
            }
            .invalid-div {
                border-color: red !important;
            }
            .valid-div {
                border-color: green !important;
            }
            @keyframes slideInDown {
                from {
                    transform: translate3d(0, -100%, 0);
                    visibility: visible;
                    opacity: 0;
                }
                to {
                    transform: translate3d(0, 0, 0);
                    opacity: 1;
                }
            }
            .animated-popup {
                animation: slideInDown 0.4s ease-out;
            }
            .swal2-container-custom {
                z-index: 10000 !important;
            }
            .swal2-container {
                z-index: 10000 !important;
            }
        </style>
    </head>

    <body data-topbar="colored">

    <div id="loader-container" style="display:none;">
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

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Application for Course Extension Form</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form id="course_extension_form">
                                            <input type="hidden" name="formName" value="course_extension">
                                            
                                            <!-- INSTRUCTIONS -->
                                            <div class="alert alert-info mb-4">
                                                <strong>INSTRUCTIONS:</strong><br>
                                                Learners must use this form when requesting to extend their course enrolment period:<br>
                                                • Before applying for an extension it is recommended you discuss this matter with your Client Services Coordinator<br>
                                                • Complete all sections of the form, and return to Client Services.
                                            </div>

                                            <!-- 1. LEARNER DETAILS -->
                                            <h5 class="mb-3">1. Learner Details</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Title (Please tick) <span class="asterisk">*</span></label>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="title" id="title_mr" value="Mr">
                                                                <label class="form-check-label" for="title_mr">Mr</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="title" id="title_ms" value="Ms">
                                                                <label class="form-check-label" for="title_ms">Ms</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="title" id="title_mrs" value="Mrs">
                                                                <label class="form-check-label" for="title_mrs">Mrs</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="title" id="title_miss" value="Miss">
                                                                <label class="form-check-label" for="title_miss">Miss</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="title" id="title_other" value="Other">
                                                                <label class="form-check-label" for="title_other">Other</label>
                                                            </div>
                                                        </div>
                                                        <div class="error-feedback">Please select a title</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Family Name <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="family_name" name="family_name" required>
                                                        <div class="error-feedback">Please enter family name</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Given Names <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="given_names" name="given_names" required>
                                                        <div class="error-feedback">Please enter given names</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="mb-3">
                                                        <label class="form-label">Residential Address <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="residential_address" name="residential_address" required>
                                                        <div class="error-feedback">Please enter residential address</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Post Code <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="post_code" name="post_code" maxlength="10" required>
                                                        <div class="error-feedback">Please enter post code</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Contact Number <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                                                        <div class="error-feedback">Please enter contact number</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Email <span class="asterisk">*</span></label>
                                                        <input type="email" class="form-control" id="email" name="email" required>
                                                        <div class="error-feedback">Please enter a valid email address</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 2. ENROLMENT DETAILS -->
                                            <h5 class="mb-3">2. Enrolment Details</h5>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Course Code</label>
                                                        <input type="text" class="form-control" id="course_code" name="course_code">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Course Title</label>
                                                        <input type="text" class="form-control" id="course_title" name="course_title">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Enrolment Date</label>
                                                        <input type="date" class="form-control" id="enrolment_date" name="enrolment_date">
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 3. REQUEST FOR COURSE EXTENSION -->
                                            <h5 class="mb-3">3. Request for Course Extension</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Course Extension <span class="asterisk">*</span></label>
                                                        <div class="d-flex flex-column gap-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="reason_for_extension" id="reason_medical" value="Own medical condition">
                                                                <label class="form-check-label" for="reason_medical">Own medical condition</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="reason_for_extension" id="reason_bereavement" value="Bereavement">
                                                                <label class="form-check-label" for="reason_bereavement">Bereavement</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="reason_for_extension" id="reason_family" value="Family circumstances">
                                                                <label class="form-check-label" for="reason_family">Family circumstances</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="reason_for_extension" id="reason_other" value="Other">
                                                                <label class="form-check-label" for="reason_other">Other, provide details:</label>
                                                            </div>
                                                        </div>
                                                        <div class="error-feedback">Please select reason for extension</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" id="reason_other_section" style="display:none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Please provide details <span class="asterisk">*</span></label>
                                                        <textarea class="form-control" id="reason_other_details" name="reason_other_details" rows="4" placeholder="Please provide details for your extension request"></textarea>
                                                        <div class="error-feedback">Please provide details</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="alert alert-warning">
                                                        <small><strong>Please note:</strong> Course extension rollover fees apply, refer to www.nationalcollege.edu.au for fee information.</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 5. ACKNOWLEDGEMENT -->
                                            <h5 class="mb-3">5. Acknowledgement</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="alert alert-info">
                                                        <strong>By signing this Application for Course Extension I accept:</strong><br>
                                                        • The application is subject to the approval by National College Australia, and the agreed Terms and Conditions of Enrolment.
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Full Name <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="full_name" name="full_name" required>
                                                        <div class="error-feedback">Please enter full name</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Signature <span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="signature" name="signature" required>
                                                        <div class="error-feedback">Please enter signature</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Date <span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="submission_date" name="submission_date" required>
                                                        <div class="error-feedback">Please select date</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mt-3">
                                                <div class="col-md-12 text-center">
                                                    <button type="submit" class="btn btn-primary waves-effect waves-light" id="submit_btn">
                                                        Submit Extension Form
                                                    </button>
                                                </div>
                                            </div>
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
            $(document).ready(function(){
                // Show/hide reason other details
                $('input[name="reason_for_extension"]').on('change', function(){
                    if($(this).val() == 'Other'){
                        $('#reason_other_section').show();
                        $('#reason_other_details').prop('required', true);
                    } else {
                        $('#reason_other_section').hide();
                        $('#reason_other_details').prop('required', false);
                    }
                });

                // Form submission
                $('#course_extension_form').on('submit', function(e){
                    e.preventDefault();
                    
                    var isValid = true;
                    var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                    
                    // Validate required fields
                    $(this).find('input[required], select[required], textarea[required]').each(function(){
                        if(!$(this).val()){
                            isValid = false;
                            $(this).addClass('invalid-div');
                            $(this).removeClass('valid-div');
                            $(this).closest('.mb-3').find('.error-feedback').show();
                        } else {
                            $(this).addClass('valid-div');
                            $(this).removeClass('invalid-div');
                            $(this).closest('.mb-3').find('.error-feedback').hide();
                        }
                    });

                    // Validate radio buttons
                    if(!$('input[name="title"]:checked').val()){
                        isValid = false;
                        $('input[name="title"]').closest('.mb-3').find('.error-feedback').show();
                    }

                    if(!$('input[name="reason_for_extension"]:checked').val()){
                        isValid = false;
                        $('input[name="reason_for_extension"]').closest('.mb-3').find('.error-feedback').show();
                    }

                    // Validate email
                    var email = $('#email').val();
                    if(email && !email.match(emailregexp)){
                        isValid = false;
                        $('#email').addClass('invalid-div');
                        $('#email').closest('.mb-3').find('.error-feedback').show();
                    }

                    // If Other is selected, validate details
                    if($('input[name="reason_for_extension"]:checked').val() == 'Other'){
                        if(!$('#reason_other_details').val()){
                            isValid = false;
                            $('#reason_other_details').addClass('invalid-div');
                            $('#reason_other_details').closest('.mb-3').find('.error-feedback').show();
                        }
                    }

                    if(!isValid){
                        Swal.fire({
                            position: 'center',
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Please fill all required fields correctly',
                            confirmButtonColor: '#dc3545'
                        });
                        return;
                    }

                    // Submit form
                    $('#loader-container').css('display','flex');
                    $('#course_extension_form').css('opacity','0.1');
                    
                    var formData = new FormData(this);
                    
                    $.ajax({
                        type: 'POST',
                        url: 'includes/datacontrol',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response){
                            $('#loader-container').hide();
                            $('#course_extension_form').css('opacity','');
                            
                            if(response == '0' || response == ''){
                                Swal.fire({
                                    position: 'center',
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Cannot submit form. Please try again.',
                                    confirmButtonColor: '#dc3545'
                                });
                                $('.toast-text2').html('Cannot submit form. Please try again later');
                                $('#borderedToast2Btn').trigger('click');
                            } else {
                                // Show toast notification
                                $('#toast-text').html('<strong>Success!</strong> Your extension form has been submitted successfully. Reference ID: <strong>' + response + '</strong>');
                                $('#borderedToast1Btn').trigger('click');
                                
                                // Show Bootstrap Modal with success message
                                $('#myModalLabel').html('Form Submitted Successfully!');
                                $('.modal-body').html(
                                    '<div class="text-center mb-4">' +
                                    '<div class="mb-3"><i class="ti ti-check-circle" style="font-size: 64px; color: #0ac074;"></i></div>' +
                                    '<h4 class="text-success mb-3">Success!</h4>' +
                                    '<p class="mb-4">Your Application for Course Extension has been submitted successfully.</p>' +
                                    '<div class="alert alert-light border" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">' +
                                    '<p class="mb-2" style="font-weight: 600; color: #495057; margin: 0;">Reference ID:</p>' +
                                    '<p class="mb-0" style="font-size: 28px; font-weight: bold; color: #0bb197; letter-spacing: 2px;">' + response + '</p>' +
                                    '</div>' +
                                    '<p class="text-muted mt-3" style="font-size: 14px;">You will receive a confirmation email shortly. Please keep your Reference ID for future reference.</p>' +
                                    '</div>'
                                );
                                $('#model_trigger').trigger('click');
                                
                                // Reset form after modal is shown
                                setTimeout(() => {
                                    document.getElementById('course_extension_form').reset();
                                }, 100);
                                
                                // Reload page when modal is closed
                                $('#myModal').on('hidden.bs.modal', function () {
                                    location.reload();
                                });
                            }
                        },
                        error: function(){
                            $('#loader-container').hide();
                            $('#course_extension_form').css('opacity','');
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred. Please try again.',
                                confirmButtonColor: '#dc3545'
                            });
                            $('.toast-text2').html('An error occurred. Please try again.');
                            $('#borderedToast2Btn').trigger('click');
                        }
                    });
                });
            });
        </script>
    </body>
</html>
