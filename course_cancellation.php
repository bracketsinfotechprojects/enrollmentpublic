<?php include('includes/dbconnect.php'); ?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Course Cancellation Form</title>
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
                                    <h4 class="mb-sm-0">Course Cancellation Form</h4>
                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form id="course_cancellation_form">
                                            <input type="hidden" name="formName" value="course_cancellation">
                                            
                                            <!-- INSTRUCTIONS -->
                                            <div class="alert alert-info mb-4">
                                                <strong>INSTRUCTIONS:</strong><br>
                                                Learners must use this form when requesting to cancel their enrolment:<br>
                                                • Before applying for cancellation, it is recommended you discuss this matter with your Client Services Coordinator<br>
                                                • Complete all sections of the form, and return to Client Services
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
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Date of Birth</label>
                                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Gender</label>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="gender" id="gender_m" value="M">
                                                                <label class="form-check-label" for="gender_m">M</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="gender" id="gender_f" value="F">
                                                                <label class="form-check-label" for="gender_f">F</label>
                                                            </div>
                                                        </div>
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
                                                        <label class="form-label">Date of Enrolment</label>
                                                        <input type="date" class="form-control" id="date_of_enrolment" name="date_of_enrolment">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Cancellation <span class="asterisk">*</span></label>
                                                        <select class="form-select" id="reason_for_cancellation" name="reason_for_cancellation" required>
                                                            <option value="">-- Select Reason --</option>
                                                            <option value="Increased workload">Increased workload</option>
                                                            <option value="Transfer to another RTO">Transfer to another RTO</option>
                                                            <option value="Personal difficulties">Personal difficulties</option>
                                                            <option value="Dissatisfaction with the course">Dissatisfaction with the course</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div class="error-feedback">Please select reason for cancellation</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" id="reason_other_section" style="display:none;">
                                                    <div class="mb-3">
                                                        <label class="form-label">Please provide details</label>
                                                        <textarea class="form-control" id="reason_other_details" name="reason_other_details" rows="3"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Cancellation Effective From (insert date) <span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="cancellation_effective_date" name="cancellation_effective_date" required>
                                                        <div class="error-feedback">Please select cancellation effective date</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 3. CONDITION OF CANCELLATION -->
                                            <h5 class="mb-3">3. Condition of Cancellation</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Cancellation request is within the 10 calendar day cooling off period <span class="asterisk">*</span></label>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="cooling_off_period" id="cooling_yes" value="Yes" required>
                                                                <label class="form-check-label" for="cooling_yes">Yes</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="cooling_off_period" id="cooling_no" value="No" required>
                                                                <label class="form-check-label" for="cooling_no">No</label>
                                                            </div>
                                                        </div>
                                                        <div class="error-feedback">Please select cooling off period</div>
                                                        <div class="alert alert-warning mt-2">
                                                            <small><strong>Please note:</strong> Cancellations made within the cooling-off period will be issued a refund, less the non-refundable deposit. Cancellation requests made after the cooling-off period will not be issued a refund; or if you have a payment plan, the plan will not be cancelled, and direct debits will continue until all payments have been finished. Please refer to the Terms and Conditions of Enrolment for full details.</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 4. ACCOUNT DETAILS -->
                                            <h5 class="mb-3">4. Account Details</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Applicable</label>
                                                        <div class="d-flex gap-4">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="account_applicable" id="account_applicable_yes" value="Yes">
                                                                <label class="form-check-label" for="account_applicable_yes">Applicable</label>
                                                            </div>
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" name="account_applicable" id="account_applicable_no" value="No" checked>
                                                                <label class="form-check-label" for="account_applicable_no">Not Applicable</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12" id="account_details_section" style="display:none;">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Account Type</label>
                                                                <select class="form-select" id="account_type" name="account_type">
                                                                    <option value="">-- Select --</option>
                                                                    <option value="Savings">Savings</option>
                                                                    <option value="Debit">Debit</option>
                                                                    <option value="Cheque account">Cheque account</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="mb-3">
                                                                <label class="form-label">Name of Bank</label>
                                                                <input type="text" class="form-control" id="bank_name" name="bank_name">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="mb-3">
                                                                <label class="form-label">BSB</label>
                                                                <input type="text" class="form-control" id="bsb" name="bsb" maxlength="10">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <div class="mb-3">
                                                                <label class="form-label">Account Number</label>
                                                                <input type="text" class="form-control" id="account_number" name="account_number">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 5. ACKNOWLEDGEMENT -->
                                            <h5 class="mb-3">5. Acknowledgement</h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="alert alert-info">
                                                        <strong>By signing this Application for Enrolment Cancellation Form, I accept:</strong><br>
                                                        • The cancellation is subject to the approval by National College Australia, and the agreed Terms and Conditions of Enrolment.
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
                                                        Submit Cancellation Form
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
                $('#reason_for_cancellation').on('change', function(){
                    if($(this).val() == 'Other' || $(this).val() == 'Dissatisfaction with the course'){
                        $('#reason_other_section').show();
                    } else {
                        $('#reason_other_section').hide();
                    }
                });

                // Show/hide account details
                $('input[name="account_applicable"]').on('change', function(){
                    if($(this).val() == 'Yes'){
                        $('#account_details_section').show();
                    } else {
                        $('#account_details_section').hide();
                    }
                });

                // Form submission
                $('#course_cancellation_form').on('submit', function(e){
                    e.preventDefault();
                    
                    var isValid = true;
                    var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                    
                    // Validate required fields
                    $(this).find('input[required], select[required]').each(function(){
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

                    if(!$('input[name="cooling_off_period"]:checked').val()){
                        isValid = false;
                        $('input[name="cooling_off_period"]').closest('.mb-3').find('.error-feedback').show();
                    }

                    // Validate email
                    var email = $('#email').val();
                    if(email && !email.match(emailregexp)){
                        isValid = false;
                        $('#email').addClass('invalid-div');
                        $('#email').closest('.mb-3').find('.error-feedback').show();
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
                    $('#course_cancellation_form').css('opacity','0.1');
                    
                    var formData = new FormData(this);
                    
                    $.ajax({
                        type: 'POST',
                        url: 'includes/datacontrol',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response){
                            $('#loader-container').hide();
                            $('#course_cancellation_form').css('opacity','');
                            
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
                                $('#toast-text').html('<strong>Success!</strong> Your cancellation form has been submitted successfully. Reference ID: <strong>' + response + '</strong>');
                                $('#borderedToast1Btn').trigger('click');
                                
                                // Show Bootstrap Modal with success message
                                $('#myModalLabel').html('Form Submitted Successfully!');
                                $('.modal-body').html(
                                    '<div class="text-center mb-4">' +
                                    '<div class="mb-3"><i class="ti ti-check-circle" style="font-size: 64px; color: #0ac074;"></i></div>' +
                                    '<h4 class="text-success mb-3">Success!</h4>' +
                                    '<p class="mb-4">Your Course Cancellation Form has been submitted successfully.</p>' +
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
                                    document.getElementById('course_cancellation_form').reset();
                                }, 100);
                                
                                // Reload page when modal is closed
                                $('#myModal').on('hidden.bs.modal', function () {
                                    location.reload();
                                });
                            }
                        },
                        error: function(){
                            $('#loader-container').hide();
                            $('#course_cancellation_form').css('opacity','');
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
