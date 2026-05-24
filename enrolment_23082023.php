<?php 
session_start();
if(@$_SESSION['user_type']!=''){
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

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="student_enrol_form" id="student_enrol_form">
                                        <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="qualifications">Qualification</label>
                                                            <select name="qualifications" class="form-control" id="qualifications">
                                                                <option value="0">--select--</option>
                                                                <option value="1">Masters Degree</option>
                                                                <option value="2">Bachelors Degree</option>
                                                                <option value="3">MCA</option>
                                                            </select>
                                                        <div class="error-feedback">
                                                            Please select qualification
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="venue_name">Venue</label>
                                                        <select name="venue_name" class="form-control" id="venue_name">
                                                                <option value="0">--select--</option>
                                                                <option value="1">Adeladie</option>
                                                                <option value="2">New Jersey</option>
                                                                <option value="3">Australia</option>
                                                            </select>
                                                        <div class="error-feedback">
                                                            Please Select a Venue
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="given_name">Given Name</label>
                                                        <input type="text" class="form-control" id="given_name" placeholder="Given Name" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Given name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="name_main">I am</label>
                                                        <input type="text" class="form-control" id="name_main" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Name
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="middle_name">Middle Name</label>
                                                        <input type="text" class="form-control" id="middle_name" placeholder="Middle Name" value="" >
                                                        <div class="error-feedback">
                                                            Please enter the Middle Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="source">How did you hear about Milton College</label>
                                                        <select name="source" class="form-control" id="source">
                                                            <option value="0">--select--</option>
                                                            <option value="1">Friends</option>
                                                            <option value="2">Google</option>
                                                            <option value="3">Website</option>
                                                        </select>
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-primary" id="enrolment_check" type="button" >Submit Form</button>
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
            $(document).on('click','#enrolment_check',function(){
                var given_name=$('#given_name').val().trim();
                var name_main=$('#name_main').val().trim();
                var qualifications=$('#qualifications').val()==0 ? '' : $('#qualifications').val();
                var source=$('#source').val()==0 ? '' : $('#source').val();
                var middle_name=$('#middle_name').val().trim();
                var venue=$('#venue_name').val()==0 ? '' : $('#venue_name').val();

                if(qualifications==''|| given_name=='' ||name_main=='' ||source==''||middle_name==''||venue==''){
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
                    console.log(venue);
                    details={formName:'student_enrol',qualifications:qualifications,venues:venue,middle_name:middle_name,source:source,name_main:name_main,given_name:given_name};
                    $.ajax({
                        type:'post',
                        url:'includes/datacontrol',
                        data:details,
                        success:function(data){
                            if(data==0){
                            document.getElementById('student_enrol_form').reset();
                            $('#toast-text').html('New Record added Successfully');
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