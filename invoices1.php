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
                                          
                                        <form id="paymentForm">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Given Name</label>
                                                    <input type="text" class="form-control" name="given_name" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Surname</label>
                                                    <input type="text" class="form-control" name="surname" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" class="form-control" name="address" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Phone No</label>
                                                    <input type="text" class="form-control" name="phone" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Email Address</label>
                                                    <input type="email" class="form-control" name="email" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Course Doing</label>
                                                    <input type="text" class="form-control" name="course" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Total Fees</label>
                                                    <input type="number" class="form-control" name="totalFees" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Payment Done</label>
                                                    <input type="number" class="form-control" name="paymentDone" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Date Paid</label>
                                                    <input type="date" class="form-control" name="datePaid" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Remaining Due</label>
                                                    <input type="number" class="form-control" name="remainingDue" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Comments</label>
                                                    <input type="text" class="form-control" name="comments">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Instalment 1 Paid Amount</label>
                                                    <input type="number" class="form-control" name="instalmentPaid">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Date & Time</label>
                                                    <input type="datetime-local" class="form-control" name="dateTime">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Who Took Payment</label>
                                                    <input type="text" class="form-control" name="whoTookPayment">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Mode of Payment</label>
                                                    <select class="form-control" name="paymentMode">
                                                        <option>EFTPOS</option>
                                                        <option>EFT</option>
                                                        <option>Cash</option>
                                                        <option>MOTO</option>
                                                        <option>Bank Deposit</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Funds Received?</label>
                                                    <select class="form-control" name="fundsReceived">
                                                        <option>Yes</option>
                                                        <option>No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Who Checked & When</label>
                                                    <input type="text" class="form-control" name="whoChecked">
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Receipt Emailed?</label>
                                                    <select class="form-control" name="receiptEmailed">
                                                        <option>Yes</option>
                                                        <option>No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <input type="hidden" name="formName" id="formName" value="invoice_submit">
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
        <?php include('includes/footer_includes.php'); ?>
        <script>
            function submitForm() {
                let formData = new FormData(document.getElementById("paymentForm"));
                
                $.ajax({
                    type: 'POST',
                    url: 'includes/datacontrol',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert("PDF generated successfully!");
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