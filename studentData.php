<?php include('includes/dbconnect.php'); ?>
<?php 
session_start();
if(@$_SESSION['user_type']==1){

    $qualifications=mysqli_query($connection,"SELECT * from qualifications where qualification_status!=1");
    $venue=mysqli_query($connection,"SELECT * from venue where venue_status!=1");
    $source=mysqli_query($connection,"SELECT * from source where source_status!=1");
    $courses=mysqli_query($connection,"SELECT * from courses where course_status!=1");

if(isset($_GET['check'])){
    $Updatestatus=1;
    $enrolId=base64_decode($_GET['check']);
    $queryRes=mysqli_fetch_array(mysqli_query($connection,"SELECT * from student_enrolment where st_enrol_status!=1 and st_unique_id='$enrolId'"));
    if($queryRes['st_unique_id']=="" ){
        header('Location:dashboard');
    }
    $selectQry=mysqli_query($connection,"SELECT * FROM `student_docs` WHERE `st_unique_id`='$enrolId'");
    $courses=mysqli_fetch_array(mysqli_query($connection,"SELECT * from courses where course_status!=1 AND course_id=".$queryRes['st_enrol_course']));


    $selectQryRowss=mysqli_num_rows($selectQry);
    if($selectQryRowss!=0){
    $selectQryRess=mysqli_fetch_array($selectQry);
    $thumbs=array();
    $imageArrays=json_decode($selectQryRess['st_doc_names']);
    for($i=0;$i<count($imageArrays);$i++){

        $imageFulls=explode('||',$imageArrays[$i])[0];
        array_push($thumbs,explode('||',$imageArrays[$i])[1]);
    
    }
    }

    $documents=mysqli_fetch_all(mysqli_query($connection,"SELECT document_name,document_shortcode FROM documents where document_shortcode!=1"),MYSQLI_ASSOC);

    // $selectinvoicesQry=mysqli_query($connection,"SELECT * FROM `invoices` WHERE `st_unique_id`='$enrolId'");


}else{
    $Updatestatus=0;
    $enrolId=0;
    header('Location:dashboard');
}

?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Student Data</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
        <meta content="Themesdesign" name="author" />

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
                                    <h4 class="mb-sm-0">Student Data</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item active"><a href="javascript: void(0);">Dashboard</a></li>
                                            <li class="breadcrumb-item">Student Data</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="needs-validation" id="formid" novalidate>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom01">Student ID</label>
                                                        <div class="">
                                                        <?php echo $queryRes['st_unique_id'];  ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom02">Student Name</label>
                                                        <div class="">
                                                        <?php echo $queryRes['st_name'];  ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom02">Email</label>
                                                        <div class="">
                                                        <a href="mailto:<?php echo $queryRes['st_email'];  ?>"><?php echo $queryRes['st_email'];  ?></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom02">Phone Number</label>
                                                        <div class="">
                                                        <a href="tel:<?php echo $queryRes['st_mobile'];  ?>" ><?php echo $queryRes['st_mobile'];  ?></a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom02">Enquirt ID</label>
                                                        <div class="">
                                                        <p><?php echo $queryRes['st_enquiry_id']!="" ? $queryRes['st_enquiry_id'] : "Not Found" ;  ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="validationCustom02">Course</label>
                                                        <div class="">
                                                        <p><?php echo $courses['course_sname'].'-'.$courses['course_name'];  ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                        </div>   
                        <!-- end row -->

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Invoices</h4>
                                        <table id="datatable" class="table table-striped table-bordered nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th scope="col-3">Invoice ID</th>
                                                    <th scope="col-2">Course</th>
                                                    <th scope="col-2">Fee</th>
                                                    <th scope="col-2">Amount Paid</th>
                                                    <th scope="col-2">Payment Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                        </div>   
                        <!-- end row -->
                        <div class="row align-items-baseline mb-2">
                            <div class="col-md-6">
                                <h5 class="mb-sm-0">Document Check List</h5>
                            </div>                            
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Uploaded</h4>
                                        <?php 
                                        
                                        for($i=0;$i<count($documents);$i++){
                                            if($documents[$i]['document_shortcode']==$thumbs[$i]){

                                        ?>
                                        <label class="btn btn-g-primary" for="btncheck11"><?php echo $documents[$i]['document_name'] ?> <i class="mdi mdi-check-bold"></i></label>
                                        <?php }
                                     } ?>
                                    </div>
                                    <div class="card-body">
                                        <h4 class="card-title mb-3">Missing</h4>
                                        <?php 
                                        
                                        for($i=0;$i<count($documents);$i++){
                                            if($documents[$i]['document_shortcode']!=$thumbs[$i]){

                                        ?>                                
                                        <label class="btn btn-danger" for="btncheck11"><?php echo $documents[$i]['document_name'] ?> <i class="mdi mdi-close-thick"></i></label>
                                        <?php } 
                                    } ?>                                        
                                    </div>
                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->
                        </div>   
                        <!-- end row -->
                        <div class="row align-items-baseline mb-2">
                            <div class="col-md-6">
                                <h5 class="mb-sm-0">Files</h5>
                            </div>                            
                        </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body row" id="docs_div">
                                    <?php 
                                    $selectQry=mysqli_query($connection,"SELECT * FROM `student_docs` WHERE `st_unique_id`='$enrolId'");
                                    $imageDiv='';
                                    $columnStart='';
                                    $selectQryRows=mysqli_num_rows($selectQry);
                                    if($selectQryRows!=0){
                                    $selectQryRes=mysqli_fetch_array($selectQry);
                                    $imageArray=json_decode($selectQryRes['st_doc_names']);

                                    echo "<div class='row mb-2'><div class='col-md-3'><strong>Created Date: </strong>".date('d-m-Y',strtotime($selectQryRes['created_date']))."</div>";

                                    if($selectQryRes['st_modified_date']!=''){
                                    echo "<div class='col-md-3'><strong>Modified Date: </strong>".date('d-m-Y',strtotime($selectQryRes['st_modified_date']))."</div>";
                                    }
                                    echo '</div>';

                                    for($i=0;$i<count($imageArray);$i++){
                                        $columnStart='<div class="col-md-2 text-center"><div class="mb-3">';
                                        $columnEnd='</div></div></div>';

                                        $imageFull=explode('||',$imageArray[$i])[0];
                                        $thumb=explode('||',$imageArray[$i])[1];

                                        $posSlash=strripos($imageFull,'/');
                                        $mainImage=substr($imageFull,$posSlash+1);
                                        $ext=explode('.',$mainImage)[1];

                                        $imageName=substr($mainImage,0,strripos($mainImage,'_')).'.'.$ext;
                                        $studentDocs=mysqli_fetch_array(mysqli_query($connection,"SELECT document_name from documents where document_shortcode='$thumb'"));

                                        $imageDiv.=$columnStart.'<label class="form-label" style="width:90%" for="validationCustom02">'.$studentDocs['document_name'].'</label><div class="shop-city"><a href="includes/uploads/'.$mainImage.'"><img src="assets/images/thumbnails/pdf.png" style="width:70%"></a>'.$columnEnd;
                                    }
                                    echo $imageDiv;
                                    }
                                    ?>
                                    </div>
                                </div>
                            </div>
                        </div>

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


                $(document).ready(function () {
                    $('#datatable').DataTable({lengthMenu: [5, 10, 20],language:{paginate:{previous:"<i class='mdi mdi-chevron-left'>",next:"<i class='mdi mdi-chevron-right'>"}},drawCallback:function(){$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
                    scrollX: true,
                        ajax: 'includes/datacontrol?name=singleinvoice&id=<?php echo $enrolId; ?>',
                            columns: [
                            { data: 'autoId' },                                    
                            { data: 'course' },                                    
                            { data: 'fee' },
                            { data: 'paid' },
                            { data: 'date' },
                        ],
                    });
                });

            function uplaodFiles(element){
                var files=$(element).prop('files');
                var formDatas=new FormData();
                var allowedFormats=['docx','doc','xlsx','xlx','pdf'];                
                var formatError=0;
                for (const file of files) {
                    if(!allowedFormats.includes(file.name.split('.')[1])){
                        formatError=1;
                    }
                    formDatas.append('fileUpload[]',file);
                }
                formDatas.append('formName','studentDocs');
                formDatas.append('enrollId','<?php echo $enrolId; ?>');
                if(formatError==0){
                $.ajax({
                    type:'post',
                    url:'includes/datacontrol',
                    data:formDatas,
                    contentType: false,
                    processData:false,
                    success:function(data){
                        var Images=JSON.parse(data);
                        var columnStart='<div class="col-md-2 text-center"><div class="mb-3">';
                        var columnEnd=' </div></div></div>';
                        $('#docs_div').html('');
                        for(i=0;i<Images.length;i++){
                            var pathImage=Images[i].split('||')[0];
                            var thumb=Images[i].split('||')[1];
                            var nameOnly=Images[i].split('||')[0].split('.')[0];
                            var Type=Images[i].split('||')[0].split('.')[1];                            
                                                        
                            var subs=(nameOnly.substring(nameOnly.lastIndexOf('/') + 1));
                            var imageName=(nameOnly.substring(nameOnly.lastIndexOf('/') + 1)).substring(0,subs.lastIndexOf('_'))+'.'+Type;
                            $('#docs_div').append(columnStart+'<label class="form-label" style="width:90%" for="validationCustom02">'+imageName+'</label><div class="shop-city"><a href="includes/uploads/'+subs+'"><img src="assets/images/thumbnails/'+thumb+'" style="width:70%">'+columnEnd);
                        }
                        $('#toast-text').html('Files Uploaded Successfully');
                        $('#borderedToast1Btn').trigger('click');
                    }
                })}else{
                    alert("Attachments with 'docx','doc','xlsx','xlx','pdf' formats are only allowed")
                }
            }

        </script>
    </body>
</html>
<?php } else{
    header('Location: student_enquiry.php');
    // echo "testss";
} ?>
