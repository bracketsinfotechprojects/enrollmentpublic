<?php
include('includes/dbconnect.php');
session_start();

// Check authentication - Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '' || ($_SESSION['user_type'] != 1 && $_SESSION['user_type'] != 2)) {
    header('Location: student_login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>Invoice List – Admin</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />
        <?php include('includes/app_includes.php'); ?>
    </head>

    <body data-topbar="colored">

        <!-- Begin page -->
        <div class="main-wrapper">
            <?php include('includes/header.php'); ?>
            <?php include('includes/sidebar.php'); ?>

            <div class="page-wrapper">
                <div class="content">
                    <div class="container-fluid">

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                                    <h4 class="mb-sm-0">Invoice List</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                            <li class="breadcrumb-item"><a href="invoices_create.php">Invoices</a></li>
                                            <li class="breadcrumb-item active">Invoice List</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">All Invoices</h5>
                                        <a href="invoices_create.php" class="btn btn-primary btn-sm">
                                            <i class="mdi mdi-plus"></i> Create New Invoice
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <table id="datatable_invoices" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>Invoice #</th>
                                                    <th>Student Name</th>
                                                    <th>Student ID</th>
                                                    <th>Email</th>
                                                    <th>Total Amount</th>
                                                    <th>GST</th>
                                                    <th>Status</th>
                                                    <th>Created Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div> <!-- content -->
            </div> <!-- page-wrapper -->
        </div> <!-- main-wrapper -->

        <!-- Right bar overlay-->
        <div class="rightbar-overlay"></div>
        <?php include('includes/footer_includes.php'); ?>

        <script>
            $(document).ready(function () {
                $('#datatable_invoices').DataTable({
                    lengthMenu: [5, 10, 20, 50],
                    language: {
                        paginate: {
                            previous: "<i class='mdi mdi-chevron-left'>",
                            next: "<i class='mdi mdi-chevron-right'>"
                        }
                    },
                    drawCallback: function() {
                        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                    },
                    scrollX: true,
                    ajax: {
                        url: 'includes/datacontrol.php',
                        data: {name: 'invoices_list'},
                        type: 'GET',
                        dataSrc: 'data'
                    },
                    columns: [
                        {data: 'invoice_number'},
                        {data: 'student_name'},
                        {data: 'student_id'},
                        {data: 'email_address'},
                        {data: 'total_due', render: function(data) { return '$' + parseFloat(data || 0).toFixed(2); }},
                        {data: 'total_gst', render: function(data) { return '$' + parseFloat(data || 0).toFixed(2); }},
                        {
                            data: 'status',
                            render: function(data) {
                                const map = {paid: 'success', pending: 'warning', overdue: 'danger'};
                                const badge = map[data] || 'secondary';
                                return '<span class="badge badge-' + badge + '">' + (data ? data.charAt(0).toUpperCase() + data.slice(1) : '—') + '</span>';
                            }
                        },
                        {data: 'created_at'},
                        {
                            data: null,
                            render: function(data, type, row) {
                                return `<a href="invoice_view.php?id=${row.id}" class="btn btn-sm btn-info">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteInvoice(${row.id})">
                                            <i class="ti ti-trash"></i>
                                        </button>`;
                            }
                        }
                    ]
                });
            });

            function deleteInvoice(invoiceId) {
                if (confirm('Are you sure you want to delete this invoice?')) {
                    alert('Delete invoice functionality to be implemented. Invoice ID: ' + invoiceId);
                }
            }
        </script>
    </body>
</html>
