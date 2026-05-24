<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']==1){
    $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Invoices</title>
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
                                    <h4 class="mb-sm-0">Invoice</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Invoice</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        </div>
                        <!-- end row -->
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title text-center">Student's Details</h4>
                                          
                                        <form id="invoiceForm">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Company Name</label>
                                                    <input type="text" class="form-control" name="company_name" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" name="address" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Phone</label>
                                                    <input type="text" class="form-control" name="phone" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Contact Person's Number</label>
                                                    <input type="text" class="form-control" name="contact_number" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Contact Person's Name</label>
                                                    <input type="text" class="form-control" name="contact_name" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Contact Person's Email</label>
                                                    <input type="email" class="form-control" name="contact_email" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Contact Person's Role</label>
                                                    <input type="text" class="form-control" name="contact_role" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Number of Students</label>
                                                    <input type="number" class="form-control" name="num_students" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Students' Names</label>
                                                    <textarea class="form-control" name="students_names" required></textarea>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Course Name</label>
                                                    <input type="text" class="form-control" name="course_name" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Total Amount</label>
                                                    <input type="number" class="form-control" name="total_amount" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Paid Amount</label>
                                                    <input type="number" class="form-control" name="paid_amount" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Date & Time of Payment</label>
                                                    <input type="datetime-local" class="form-control" name="date_time" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Mode of Payment</label>
                                                    <select class="form-control" name="payment_mode">
                                                        <option>EFTPOS</option>
                                                        <option>EFT</option>
                                                        <option>Cash</option>
                                                        <option>MOTO</option>
                                                        <option>Bank Deposit</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Balance Amount</label>
                                                    <input type="number" class="form-control" name="balance_amount" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Invoice Number</label>
                                                    <input type="text" class="form-control" name="invoice_number" required>
                                                </div>
                                            </div>
                                        </form>

                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->                            
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                    <div class="col-md-12 text-center">
                                        <button class="form-control btn btn-primary" type="button" style="width:30%;" id="invoice_submit" onclick="submitForm()">Submit</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
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
            function submitForm() {
                let formData = new FormData(document.getElementById("invoiceForm"));                
                    formData.append("formName", "invoice_submit_company");
                $.ajax({
                    type: 'POST',
                    url: 'includes/datacontrol',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert("PDF generated successfully! Invoice Number: " + response);
                    },
                    error: function() {
                        alert("Error generating PDF.");
                    }
                });
            }
        </script>
    </body>
</html>
<?php }else{ 

header("Location: index.php");

}
?>