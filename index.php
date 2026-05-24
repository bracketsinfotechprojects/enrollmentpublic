<?php 
if(@$_GET['name']=='logout'){
    session_start();
    session_destroy();
}
session_start();
if(@$_SESSION['user_id']!=''){
    header('Location: dashboard.php');
}else{
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Login | Auztraining</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">

        <?php include('includes/app_includes.php'); ?>

    </head>

    <body class="bg-pattern">
        <div class="bg-overlay"></div>
        <div class="account-pages my-5 pt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-4 col-lg-6 col-md-8">
                        <div class="card">
                            <div class="card-body p-4">
                                <div class="">
                                    <div class="text-center">
                                        <a href="index.html" class="">
                                            <img src="assets/images/logo-dark.webp" alt="" height="80" class="auth-logo logo-dark mx-auto">
                                            <img src="assets/images/logo-dark.png" alt="" height="80" class="auth-logo logo-light mx-auto">
                                        </a>
                                    </div>
                                    <!-- end row -->
                                    <h4 class="font-size-18 text-muted mt-2 text-center">Welcome Back !</h4>
                                    <form class="form-horizontal" id="login_form">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="email">Email</label>
                                                    <input type="text" class="form-control" id="email" placeholder="Enter email">
                                                    <div class="error-feedback">
                                                            Please enter the Email Address
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <label class="form-label" for="password">Password</label>
                                                    <input type="password" class="form-control" id="password" placeholder="Enter password">
                                                    <div class="error-feedback">
                                                            Please enter the Password
                                                    </div>
                                                </div>
                                                <div class="mb-4" id="otp_wrap" style="display:none;">
                                                    <label class="form-label" for="login_otp">OTP</label>
                                                    <input type="text" class="form-control" id="login_otp" maxlength="6" placeholder="Enter 6-digit OTP">
                                                    <div class="error-feedback">Please enter valid OTP.</div>
                                                </div>

                                                <div class="row">
                                                    <!-- <div class="col">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" id="customControlInline">
                                                            <label class="form-label" class="form-check-label" for="customControlInline">Remember me</label>
                                                        </div>
                                                    </div> -->
                                                    <div class="col-7 d-none">
                                                        <div class="text-md-end mt-3 mt-md-0">
                                                            <a href="auth-recoverpw.html" class="text-muted"><i class="mdi mdi-lock"></i> Forgot your password?</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-grid">
                                                <div class="error-feedback" style="font-size:14px;text-align:center;" id="login_error">
                                                            Please enter proper Credentials
                                                </div>
                                                    <div id="login_sending_wrap" class="text-center text-muted small mb-2" style="display:none;"><span class="spinner-border spinner-border-sm align-middle me-2" role="status" aria-hidden="true"></span>Sending OTP to your email…</div>
                                                    <button class="btn btn-primary waves-effect waves-light" id="login" type="button">Submit</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="mt-5 text-center d-none">
                            <p class="text-white-50">Don't have an account ? <a href="auth-register.html" class="fw-medium text-primary"> Register </a> </p>
                            <p class="text-white-50">© <script>document.write(new Date().getFullYear())</script> Upzet. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesdesign</p>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
        </div>
        <!-- end Account pages -->

        <?php include('includes/footer_includes.php'); ?>

        <script>
            var loginOtpStep = false;
            var loginOtpSending = false;
            function adminLoginRefreshButton(){
                var $b = $('#login');
                if(loginOtpSending){
                    $b.prop('disabled', true).text('Sending…');
                    return;
                }
                $b.prop('disabled', false);
                $b.text('Submit');
            }
            function adminLoginSetError(msg){
                if(msg){
                    $('#login_error').text(msg).show();
                }else{
                    $('#login_error').hide();
                }
            }
            $(document).on("click","#login",function(){
                var email=$('#email').val().trim();
                var password=$('#password').val().trim();
                var otp=$('#login_otp').val().trim();
                if(!loginOtpStep && email==''){
                    if(email==''){
                        $('#email').addClass('invalid-div');
                        $('#email').removeClass('valid-div');
                        $('#email').closest('div').find('.error-feedback').show();
                    }else{
                        $('#email').addClass('valid-div');
                        $('#email').removeClass('invalid-div');
                        $('#email').closest('div').find('.error-feedback').hide();
                    }
                    $('#password').removeClass('invalid-div');
                    $('#password').closest('div').find('.error-feedback').hide();
                }
                else if(!loginOtpStep){
                    details={formName:'login_request_otp',email:email,password:password};
                    loginOtpSending = true;
                    $('#login_sending_wrap').show();
                    adminLoginRefreshButton();
                    adminLoginSetError('');
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        dataType:'json',
                        success:function(data){
                            if(data && data.success){
                                if(data.auto_login){
                                    window.location.href = data.redirect || "dashboard.php";
                                    return;
                                }
                                loginOtpStep = true;
                                $('#otp_wrap').show();
                                $('#email,#password').prop('readonly',true);
                                adminLoginSetError(data.message || 'OTP sent to your email.');
                            }else{
                                adminLoginSetError((data && data.message) ? data.message : 'Invalid email or password.');
                            }
                        },
                        error:function(){
                            adminLoginSetError('Unable to process login now.');
                        },
                        complete:function(){
                            loginOtpSending = false;
                            $('#login_sending_wrap').hide();
                            adminLoginRefreshButton();
                        }
                    });
                }else{
                    if(!/^\d{6}$/.test(otp)){
                        $('#login_otp').addClass('invalid-div');
                        $('#login_otp').closest('div').find('.error-feedback').show();
                        return;
                    }
                    details={formName:'login_verify_otp',otp:otp};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        dataType:'json',
                        success:function(data){
                            if(data && data.success){
                                var ut = parseInt(data.user_type,10);
                                if(ut===1 || ut===2){
                                    window.location.href="dashboard.php";
                                }else{
                                    window.location.href="student_docs.php";
                                }
                            }else{
                                adminLoginSetError((data && data.message) ? data.message : 'Invalid OTP.');
                            }
                        },
                        error:function(){
                            adminLoginSetError('Unable to verify OTP now.');
                        }
                    });
                }
            })
        </script>
    </body>
</html>
<?php } ?>