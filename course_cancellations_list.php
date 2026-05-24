<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']!=''){
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Course Cancellations</title>
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
                                    <h4 class="mb-sm-0">Course Cancellations</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Course Cancellations</li>
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
                                        <h4 class="card-title mb-4">Course Cancellation Requests</h4>  
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
                                                    <th>Effective Date</th>
                                                    <th>Cooling Off</th>
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
                        <h5 class="modal-title">OFFICE USE ONLY - Course Cancellation</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="office_use_form">
                        <div class="modal-body">
                            <input type="hidden" name="formName" value="process_cancellation">
                            <input type="hidden" name="cancellation_id" id="office_cancellation_id">
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label class="form-label">Refund to be issued / Direct debit payment cancelled <span class="asterisk">*</span></label>
                                        <div class="d-flex gap-4">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="refund_to_be_issued" id="refund_yes" value="Yes" required>
                                                <label class="form-check-label" for="refund_yes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="refund_to_be_issued" id="refund_no" value="No" required>
                                                <label class="form-check-label" for="refund_no">No</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Refund / Direct debit cancellation approved by the PEO</label>
                                        <input type="text" class="form-control" id="refund_approved_by" name="refund_approved_by" placeholder="Enter name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Approval Date</label>
                                        <input type="date" class="form-control" id="refund_approved_date" name="refund_approved_date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Refund Amount ($)</label>
                                        <input type="number" step="0.01" class="form-control" id="refund_amount" name="refund_amount" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Date forwarded to finance</label>
                                        <input type="date" class="form-control" id="date_forwarded_to_finance" name="date_forwarded_to_finance">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Finance Initial</label>
                                        <input type="text" class="form-control" id="finance_initial" name="finance_initial" placeholder="Enter initial">
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
                            <button type="submit" class="btn btn-primary">Process Cancellation</button>
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
                    ajax: 'includes/datacontrol?name=courseCancellations',
                    columns: [
                        { data: 'reference_id' },
                        { data: 'name' },
                        { data: 'email' },
                        { data: 'contact_number' },
                        { data: 'course_code' },
                        { data: 'course_title' },
                        { data: 'reason' },
                        { data: 'effective_date' },
                        { data: 'cooling_off' },
                        { data: 'status' },
                        { data: 'submitted_date' },
                        { data: 'action' }
                    ],
                });

                // Handle accept button click
                $(document).on('click', '.btn-accept', function(){
                    var cancellationId = $(this).data('id');
                    $('#office_cancellation_id').val(cancellationId);
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
                                        '<p class="mb-0">Cancellation processed successfully.<br>Email sent to student.</p>' +
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
                                    text: 'Cannot process cancellation. Please try again.',
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
