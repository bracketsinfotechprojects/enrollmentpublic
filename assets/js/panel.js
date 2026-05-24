$(document).on('input','.number-field',function(e){
    $(this).val($(this).val().replace(/[^0-9]/gi, ''));
})
$(document).on('focus','input,select',function(e){
    $(this).removeClass('invalid-div');
    $(this).closest('div').find('.error-feedback').hide();
})


function studetnLookup(){
    var selected=$('#lookup_select').val();

    // Get the full URL of the current page
var currentUrl = window.location.href;

// Split the URL by '/'
var urlComponents = currentUrl.split('/');

// Get the last component (page name)
var lastPageName = urlComponents[urlComponents.length - 1];
    if(lastPageName=='enrolment.php'){
        forms='lookupLoad2';
    }else{
        forms='lookupLoad';
    }
    $.ajax({
        type:'post',
        url:'includes/datacontrol',
        data:{selected:selected,formName:forms,lastPageName:lastPageName},
        success:function(data){
            $('#lookup_select_data').html(data);
            select2_start();

            $('.select2').select2({
                dropdownParent: $('#myModal2')
            });
        }
    })
}

function select2_start(){                
    if($('.select2').length>0){                        
     $('.select2').select2().on('select2:select', function (e) {                                                
        var selected=$('#lookup_select').val();


            // Get the full URL of the current page
var currentUrl = window.location.href;

// Split the URL by '/'
var urlComponents = currentUrl.split('/');

// Get the last component (page name)
var lastPageName = urlComponents[urlComponents.length - 1];

if(lastPageName=='enrolment.php'){
    forms='lookupdata2';
}else{
    forms='lookupdata';
}

        $.ajax({
            type:'post',
            data:{formName:forms,selected:selected,values:$(this).val()},
            url:'includes/datacontrol',
            success:function(jsondata){
                var data=JSON.parse(jsondata);

                if(lastPageName=='enrolment.php'){
                    $('#close_lookup').trigger('click');
                    $('#given_name').val(data.st_name);
                    $('#courses').val(data.st_course).change();
                    $('#mobile_num').val(data.st_phno).change();
                    $('#email').val(data.st_email).change();
                    $('#enquiry_id').val(data.st_enquiry_id);
                }else{
                    $('#close_lookup').trigger('click');
                    $('#student_name').val(data.st_given_name+' '+data.st_middle_name);
                    $('#course_name').val(data.st_enrol_course).change();
                    $('#enrol_id').val(data.st_unique_id);
                }

            }
        })

     });
    }
}
function SuccessPop(Message,timing){
    Swal.fire({
        position: 'center',
        icon: 'success',
        title: Message,
        showConfirmButton: true,
        timer: timing
    })
}

function ErrorPop(Message,timing){
    Swal.fire({
        position: 'center',
        icon: 'error',
        title: 'Oops...',
        text: Message,
        timer:timing
        // footer: '<a href="">Why do I have this issue?</a>'
      })
} 

// function DeletePop(){
//     Swal.fire({
//         icon: 'warning',
//         title: 'Are you Sure ?',
//         html:'<input type="text" id="delete_note" class="delete_note form-control" placeholder="Note" maxlength="150">',
//         // text: Message,
//         showCancelButton:!0,
//         confirmButtonColor:"#1cbb8c",
//         cancelButtonColor:"#ff3d60",
//         confirmButtonText:"Add Note & delete!"
//         // timer:timing
//         // footer: '<a href="">Why do I have this issue?</a>'
//       }).then(function(t){
//         if(t.isConfirmed){
//             deleteFun()
//         }
//       })
// } 