<?php 
session_start();
// print_r($_SESSION);
if(@$_SESSION['user_type']==1){
?>
<!doctype html>
<html lang="en">

    <head>
        
        <meta charset="utf-8" />
        <title>Attendance</title>
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
                                    <h4 class="mb-sm-0">Attendance</h4>

                                    <div class="page-title-right">
                                        <ol class="breadcrumb m-0">
                                            <li class="breadcrumb-item"><a href="javascript: void(0);">Forms</a></li>
                                            <li class="breadcrumb-item active">Attendance</li>
                                        </ol>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <!-- end page title -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="col-6">
                                            <h4 class="card-title">Dropzone</h4>
                                            <p class="card-title-desc"><b>Note:</b> Only Excel sheets are allowed to upload.</p>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a href="assets/docs/excel_sample.xlsx"><button type="button" class="btn btn-primary"><i class="mdi mdi-download"></i> Download Sample</button></a>
                                            </div>
                                        </div>
        
                                        <div>
                                            <input name="file" type="file" accept=".XLSX,.XLX,.CSV" id="upload_file" class="form-control d-none" onchange="uploadFile()">
                                            <form action="#" id="dropzone" class="dropzone">
                                                <div class="fallback text-center m-4" id="image-name-div" style="display:none"> 
                                                      <img src="assets/images/thumbnails/xlsx.png" style="width:15%" alt="">
                                                      <div id="image-name"></div>
                                                </div>
                                                <div class="dz-message needsclick text-center mt-5" id="dz-message">
                                                    <div class="mb-3">
                                                        <i class="display-4 text-muted mdi mdi-cloud-upload-outline"></i>
                                                    </div>                                                    
                                                    <h4>Drop files here to upload</h4>
                                                </div>
                                            </form>
                                        </div>
                                        <div id="attendaceTable" class="d-none">
                                            <table class="table table-bordered border-primary mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Student ID</th>
                                                        <th>Course Unit</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="table-body">
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-primary waves-effect waves-light" onclick="send_file()" id="send_file">Send Files</button>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>
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
var uniFile='';
function uploadFile(){
    var filesUp=$('#upload_file').prop('files')[0];   
    sendFile(filesUp);
}

function sendFile(Files){ 
    uniFile =Files;
    var allowedExt=['xlsx','xlx','csv'];
        if(allowedExt.includes(Files.name.split('.')[1])){
            $('#dz-message').hide();
            $('#image-name-div').show();
            $('#image-name').html(Files.name);
        }else{
            alert('Only SpreadSheet Files are allowed to Upload');
            uniFile='';
            $('#upload_file').val('');
        }
}

function send_file(){    
    var filesUp= uniFile =='' ? $('#upload_file').prop('files')[0] : uniFile;
    var formsData=new FormData();
    formsData.append('fileUpload',filesUp);
    formsData.append('formName','uploadExcel');
    if(filesUp!=undefined && filesUp!=''){
        $.ajax({
            type:'post',
            data:formsData,
            url:'includes/datacontrol',
            contentType: false,
            processData:false,
            success:function(data){
                $('#table-body').html(data);
                $('#myModalLabel').html('Uploaded Data');
                $('.modal-body').html($('#attendaceTable').html());
                $('#upload_file').val('');
                $('#dz-message').show();
                $('#image-name-div').hide();
                $('#image-name').html();
                $('#model_trigger').trigger('click');
            }
        })
    }else{
        alert("Please upload attendace Sheet to submit");
    }
}


$('#dropzone').on(
    'dragover',
    function(e) {
        e.preventDefault();
        e.stopPropagation();
    }
)
$('#dropzone').on(
    'dragenter',
    function(e) {
        e.preventDefault();
        e.stopPropagation();
    }
)

// $(document).on('click','#dropzone',function(){
//     tiggerFile();
// })

$('#dropzone').on({

    drop:function(e){
            e.preventDefault();
           e.stopPropagation();  
           sendFile(e.originalEvent.dataTransfer.files[0]);        
    },
    click:function(e){
            e.preventDefault();
           e.stopPropagation();  
        $('#upload_file').trigger('click');
    }
}

);
        </script>
    </body>
</html>
<?php }else{ 

header("Location: index.php");

}
?>