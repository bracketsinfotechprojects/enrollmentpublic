<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Course Extensions</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <!-- App favicon -->
        <link rel="shortcut icon" href="assets/images/favicon.ico">
        <?php include('includes/app_includes.php'); ?>
        <style>
            .asterisk {
                color: red;
            }
            /* Pagination prev/next and page numbers visible */
            .dataTables_paginate .page-link,
            .dataTables_paginate .page-item.previous .page-link,
            .dataTables_paginate .page-item.next .page-link {
                color: #405189 !important;
            }
            .dataTables_paginate .page-item.disabled .page-link {
                color: #878a99 !important;
            }
            .dataTables_paginate .page-item.active .page-link {
                color: #fff !important;
            }
            .dataTables_paginate .page-link i {
                color: inherit;
            }
        </style>
    </head>

    <body>

    <div id="loader-container" style="display:none;">
        <div class="loader"></div>
    </div>

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
                                    <h4 class="mb-sm-0">Course Extensions</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Course Extensions</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-4">Course Extension Requests</h4>  
                                        <table id="datatable" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Reference ID</th>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Contact Number</th>
                                                    <th>Course Code</th>
                                                    <th>Course Title</th>
                                                    <th>Reason</th>
                                                    <th>Enrolment Date</th>
                                                    <th>Status</th>
                                                    <th>Submitted Date</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- end card-body -->
                                </div>
                            </div>
                        </div>
                        <!-- end row -->

                    </div> <!-- container-fluid -->
                </div>
            </div>

        </div>
        <!-- END layout-wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>
        
        <!-- Office Use Only Modal -->
        <div class="modal fade" id="officeUseModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">OFFICE USE ONLY - Course Extension</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="office_use_form">
                        <div class="modal-body">
                            <input type="hidden" name="formName" value="process_extension">
                            <input type="hidden" name="extension_id" id="office_extension_id">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">NCA has reviewed the application, and is able to offer an extension of course duration <span class="asterisk">*</span></label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="extension_approved" id="extension_yes" value="Yes" required>
                                                <label class="form-check-label" for="extension_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="extension_approved" id="extension_no" value="No" required>
                                                <label class="form-check-label" for="extension_no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Application approved by</label>
                                        <input type="text" class="form-control" id="application_approved_by" name="application_approved_by" placeholder="Enter name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Initial</label>
                                        <input type="text" class="form-control" id="approval_initial" name="approval_initial" placeholder="Enter initial">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Approval Date</label>
                                        <input type="date" class="form-control" id="approval_date" name="approval_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Rollover fee, if applicable ($)</label>
                                        <input type="number" step="0.01" class="form-control" id="rollover_fee" name="rollover_fee" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Comments</label>
                                        <textarea class="form-control" id="office_comments" name="office_comments" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Process Extension</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                $('#datatable').DataTable({
                    lengthMenu: [10, 25, 50, 100],
                    language:{
                        paginate:{
                            previous: "<i class='ti ti-chevron-left'></i>",
                            next: "<i class='ti ti-chevron-right'></i>"
                        }
                    },
                    drawCallback:function(){
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                    },
                    scrollX: true,
                    responsive: false,
                    ajax: 'includes/datacontrol?name=courseExtensions',
                    columns: [
                        { data: 'reference_id' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'contact_number' },
                        { data: 'course_code' },
                        { data: 'course_title' },
                        { data: 'reason' },
                        { data: 'enrolment_date' },
                        { data: 'status' },
                        { data: 'submitted_date' },
                        { data: 'action' }
                    ],
                });

                // Handle accept button click
                $(document).on('click', '.btn-accept', function(){
                    var extensionId = $(this).data('id');
                    $('#office_extension_id').val(extensionId);
                    $('#officeUseModal').modal('show');
                });

                // Handle office use form submission
                $('#office_use_form').on('submit', function(e){
                    e.preventDefault();
                    
                    // Show loader
                    $('#loader-container').css('display','flex');
                    $('#officeUseModal').find('.modal-content').css('opacity','0.5');
                    
                    var formData = new FormData(this);
                    
                    $.ajax({
                        type: 'POST',
                        url: 'includes/datacontrol',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response){
                            // Hide loader
                            $('#loader-container').hide();
                            $('#officeUseModal').find('.modal-content').css('opacity','');
                            
                            // Trim response to handle whitespace
                            response = $.trim(response);
                            
                            console.log('Response received:', response);
                            
                            if(response == '1' || response === '1'){
                                // Reset form
                                $('#office_use_form')[0].reset();
                                
                                // Close modal
                                var modalElement = document.getElementById('officeUseModal');
                                var modalInstance = bootstrap.Modal.getInstance(modalElement);
                                if(modalInstance){
                                    modalInstance.hide();
                                } else {
                                    $('#officeUseModal').modal('hide');
                                }
                                
                                // Show success message using Bootstrap modal
                                setTimeout(function(){
                                    $('#myModalLabel').html('Success!');
                                    $('.modal-body').html(
                                        '<div class="text-center mb-4">' +
                                        '<div class="mb-3"><i class="ti ti-check-circle" style="font-size: 64px; color: #0ac074;"></i></div>' +
                                        '<h4 class="text-success mb-3">Success!</h4>' +
                                        '<p class="mb-0">Extension processed successfully.<br>Email sent to student.</p>' +
                                        '</div>'
                                    );
                                    $('#model_trigger').trigger('click');
                                    
                                    // Reload page when modal is closed
                                    $('#myModal').on('hidden.bs.modal', function () {
                                        $(this).off('hidden.bs.modal'); // Remove listener
                                        location.reload();
                                    });
                                }, 400);
                            } else {
                                Swal.fire({
                                    position: 'center',
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Cannot process extension. Please try again.',
                                    confirmButtonColor: '#dc3545'
                                });
                            }
                        },
                        error: function(xhr, status, error){
                            // Hide loader
                            $('#loader-container').hide();
                            $('#officeUseModal').find('.modal-content').css('opacity','');
                            
                            Swal.fire({
                                position: 'center',
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred: ' + error,
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                });
            });
        </script>
    </body>
</html>
<?php } else {
    header("Location: index.php");
}
?>
