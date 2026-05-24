        <script>

            var datacontrolUrl = window.location.origin + '<?php echo rtrim(dirname($_SERVER['PHP_SELF']), "/\\"); ?>/includes/datacontrol.php';
            var checkPhone=0;
            function PhoneCheck(number){

                return new Promise(function (resolve, reject) {

                    var check_update=$('#check_update').val();
                    var oldenquiryFor='<?php echo $queryRes['st_enquiry_for']; ?>';
                    var oldNumber='<?php echo $queryRes['st_phno']; ?>';

                    var memberName=$('#member_name').val();     
                    var enquiryFor=$('#enquiry_for').val();                

                $.ajax({
                    type:'post',
                    data:{number:number,formName:'phoneNumberCheck',oldNumber:oldNumber,memberName:memberName,enquiryFor:enquiryFor,check_update:check_update,oldenquiryFor:oldenquiryFor},
                    url: datacontrolUrl,
                    success:function(datas){
                        resolve(datas);
                    },
                    error: function (xhr, status, error) {
                        reject(new Error(status + ': ' + error));
                    }

                })

            });

            }

                        // Usage with async/await
            async function getData(number) {
            try {
                const data = await PhoneCheck(number);
                return data;

                // You can perform further operations with 'data' here
            } catch (error) {
                console.error(error);
            }
            }

            $(document).ready(function(){            

                $('.refered').on("change",function(){
                    var value=$(this).val();
                    if( value==0 || value==2 ){
                        $('.refered_field').hide();
                    }else{
                        $('.refered_field').show();
                    }                 
                })
                $('.rpl_parent').on("change",function(){
                    var value=$(this).val();
                    if( value==0 || value==2 ){
                        $('.rpl_child').hide();
                    }else{
                        $('.rpl_child').show();
                    }                 
                })
                $('#visa_condition').on("change",function(){
                    var value=$(this).val();
                    if( value==7 ){
                        $('.visa_note').show();
                    }else{
                        $('.visa_note').hide();
                    }                 
                })
              /*  $('#hear_about').on("change",function(){
                    var value=$(this).val();                    
                    if( value.includes('9') ){
                        $('.hear_about_child').show();
                    }else{
                        $('.hear_about_child').hide();
                    }                 
                }) */

                $('#course_type').on("change",function(){
                    var value=$(this).val();
                    if( value==1 ){
                        $('#rpl_form').show();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').hide();
                    }else if(value==5 || value==4){       
                        $('#rpl_form').hide();
                        $('#short_grp_form').show(); 
                        $('#regular_grp_form').hide();
                    }else if(value==3){                        
                        $('#rpl_form').hide();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').show();
                    }else{
                        $('#rpl_form').hide();
                        $('#short_grp_form').hide();
                        $('#regular_grp_form').hide();
                    }
                })
                $('.rpl_prev_parent').on("change",function(){
                    var value=$(this).val();
                    if( value==2 || value==0){
                        $('.rpl_prev_child').hide();
                    }else{                                            
                        $('.rpl_prev_child').show();
                    }
                })
                if($('#appointment_booked').length){
                    $('#appointment_booked').on("change",function(){
                        var value=$(this).val();
                        if( value==1){
                            $('#appointment_form').show();
                        }else{
                            $('#appointment_form').hide();
                        }
                    });
                }

                $('#enquiry_for').on('change',function(){
                    var value=$(this).val();
                    if(value==1){
                        $('#student_name_wrap').hide();
                    }else if(value==2){
                        $('#student_name_wrap').show();
                    }
                });
                function toggleEnquirySourceStaffField(){
                    var v = parseInt($('#enquiry_source').val(), 10) || 0;
                    if (v === 4 || v === 5 || v === 6) {
                        $('#enquiry_source_staff_wrap').show();
                    } else {
                        $('#enquiry_source_staff_wrap').hide();
                        $('#enquiry_source_staff_error').hide();
                    }
                }
                $('#enquiry_source').on('change', toggleEnquirySourceStaffField);
                $('#enquiry_source_responsible_staff').on('change', function(){
                    $('#enquiry_source_staff_error').hide();
                    $(this).removeClass('invalid-div');
                });
                toggleEnquirySourceStaffField();
            })

            $(document).on('click','#enquiry_form',async() =>{
                var enquiryForVal = ($('#enquiry_for').val()||'0').toString();
                var studentName = enquiryForVal === '1' ? $('#member_name').val().trim() : $('#student_name').val().trim();
                var contactName=$('#contact_num').val().trim();
                var emailAddress=$('#email_address').val().trim();
                var enquiryDate=$('#enquiry_date').val();

                var surname=$('#surname').val();
                var suburb=$('#suburb').val();
                var stuState=$('#stu_state').val() == 0 ? '' : $('#stu_state').val();
                var postCode=$('#post_code').val();
                var visit_before=$('#visit_before').val()==0 ? '' :$('#visit_before').val();
                var hear_about='';
                var hearedby='';
                var esSrc = parseInt($('#enquiry_source').val(), 10) || 0;
                if (esSrc === 4 || esSrc === 5 || esSrc === 6) {
                    hearedby = ($('#enquiry_source_responsible_staff').val() || '').trim();
                }
                var staffSourceInvalid = (esSrc === 4 || esSrc === 5 || esSrc === 6) && hearedby === '';
                var plan_to_start_date=$('#plan_to_start_date').val();
                var refer_select=$('#refer_select').val();
                var referer_name=$('#referer_name').val();
                var refer_alumni=$('#refer_alumni').val();
                var shore=$('#shore').val();
                var comments=$('#comments').length ? $('#comments').val() : '';
                var remarks=[];
                var appointment_booked=$('#appointment_booked').length ? $('#appointment_booked').val() : 0;
                var memberName=$('#member_name').val().trim();

                $('.remarks_check:checkbox:checked').each(function() {
                    remarks.push(this.value);
                });           
                     
                var streetDetails=$('#street_no').val();
                var ethnicity=$('#ethnicity').val();                
                var prefComment=$('#pref_comment').val();                
                var enquiryFor=$('#enquiry_for').val()==0 ? '' : $('#enquiry_for').val();
                var courseType=$('#course_type').val();

                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                // var courses=$('#courses').val()==0 ? '' : $('#courses').val();
                var courses=[];

                $('.courses_check:checkbox:checked').each(function() {
                    courses.push(this.value);
                });

                var payment=$('#payment_fee').val().trim();
                var visaStatus=$('#visa_condition').val();
                var visaNote=$('#visa_note').val();
                var visaCondition=$('.visa_status').val();

                var reg_grp_names=$('#reg_grp_names').val();

                if(visaStatus==7 && visaNote=='' ){
                    visaNoteStatus=1;
                }else{
                    visaNoteStatus=0;
                }

                if(refer_select==0){
                    refer_select_error=0;
                }else if(refer_select==1){

                    if(refer_alumni==0){
                        refer_select_error=0;
                    }else{
                        refer_select_error=1;
                    }

                }else{
                    refer_select_error=1;
                }

                var hear_about_error=1;
                
                // checkPhone=0;            
                // var error_ph=await getData(contactName).split('||')[0];
                var enquiryIdRec=await getData(contactName);                
                if(enquiryIdRec.split('||')[0]==1 || ( contactName=='' || contactName.length!=10 ) ){
                    var phoneChecks=1;
                }else{
                    var phoneChecks=0;
                }

                if(studentName==''|| phoneChecks==1 ||emailAddress==''|| (emailAddress!='' && !emailAddress.match(emailregexp)==true ) ||courses.length==0|| enquiryDate=='' || refer_select_error==0 || surname=='' || enquiryFor==''|| postCode=='' || visit_before=='' || memberName=='' || visaNoteStatus==1 || staffSourceInvalid){

                    if(refer_select_error==0){
                        if(refer_select==0){
                            $('#refer_select').addClass('invalid-div');
                            $('#refer_select').removeClass('valid-div');
                            $('#refer_select').closest('div').find('.error-feedback').show();
                        }else if(refer_select==1){

                            if(refer_alumni==0){
                                $('#refer_alumni').addClass('invalid-div');
                                $('#refer_alumni').removeClass('valid-div');
                                $('#refer_alumni').closest('div').find('.error-feedback').show();
                            }else{
                                $('#refer_alumni').addClass('valid-div');
                                $('#refer_alumni').removeClass('invalid-div');
                                $('#refer_alumni').closest('div').find('.error-feedback').hide();
                            }

                        }else{
                            $('#refer_select').addClass('valid-div');
                            $('#refer_select').removeClass('invalid-div');
                            $('#refer_select').closest('div').find('.error-feedback').hide();
                        }
                    }

                    if(studentName==''){
                        if(enquiryForVal==='1'){
                            $('#member_name').addClass('invalid-div');
                            $('#member_name').removeClass('valid-div');
                            $('#member_name').closest('div').find('.error-feedback').show();
                        }else{
                            $('#student_name').addClass('invalid-div');
                            $('#student_name').removeClass('valid-div');
                            $('#student_name').closest('div').find('.error-feedback').show();
                        }
                    }else{
                        $('#member_name').addClass('valid-div');
                        $('#member_name').removeClass('invalid-div');
                        $('#member_name').closest('div').find('.error-feedback').hide();
                        $('#student_name').addClass('valid-div');
                        $('#student_name').removeClass('invalid-div');
                        $('#student_name').closest('div').find('.error-feedback').hide();
                    }

                    if(contactName=='' || contactName.length!=10 ){
                        $('#contact_num').addClass('invalid-div');
                        $('#contact_num').removeClass('valid-div');
                        $('#contact_num').closest('div').find('.error-feedback').show();
                    }else if(enquiryIdRec.split('||')[0]==1){
                        $('#contact_num').addClass('invalid-div');
                        $('#contact_num').removeClass('valid-div');                        
                        $('#contact_num').closest('div').find('.error-feedback').hide();     
                        $('#contact_num').closest('div').find('.phone_error').show();
                        $('#contact_num').closest('div').find('#phone_err_id').html(enquiryIdRec.split('||')[1]);
                    }else{
                        $('#contact_num').addClass('valid-div');
                        $('#contact_num').removeClass('invalid-div');
                        $('#contact_num').closest('div').find('.error-feedback').hide();
                        $('#contact_num').closest('div').find('.phone_error').hide();
                    }
                    if(memberName=='' ){
                        $('#member_name').addClass('invalid-div');
                        $('#member_name').removeClass('valid-div');
                        $('#member_name').closest('div').find('.error-feedback').show();
                    }else{
                        $('#member_name').addClass('valid-div');
                        $('#member_name').removeClass('invalid-div');
                        $('#member_name').closest('div').find('.error-feedback').hide();
                    }
                    if(emailAddress=='' || (emailAddress!='' && (!emailAddress.match(emailregexp)==true))){
                        $('#email_address').addClass('invalid-div');
                        $('#email_address').removeClass('valid-div');
                        $('#email_address').closest('div').find('.error-feedback').show();
                    }else{
                        $('#email_address').addClass('valid-div');
                        $('#email_address').removeClass('invalid-div');
                        $('#email_address').closest('div').find('.error-feedback').hide();
                    }
                    if(courses.length==0){
                        // $('#courses').addClass('invalid-div');
                        // $('#courses').removeClass('valid-div');
                        $('.courses_error').show();
                    }else{
                        // $('#courses').addClass('valid-div');
                        // $('#courses').removeClass('invalid-div');
                        $('.courses_error').hide();
                    }
                    if(visaNoteStatus==1){
                        $('#visa_note').addClass('invalid-div');
                        $('#visa_note').removeClass('valid-div');
                        $('#visa_note').closest('div').find('.error-feedback').show();
                    }else{
                        $('#visa_note').addClass('valid-div');
                        $('#visa_note').removeClass('invalid-div');
                        $('#visa_note').closest('div').find('.error-feedback').hide();
                    }
                    if(enquiryDate==''){
                        $('#enquiry_date').addClass('invalid-div');
                        $('#enquiry_date').removeClass('valid-div');
                        $('#enquiry_date').closest('div').find('.error-feedback').show();
                    }else{
                        $('#enquiry_date').addClass('valid-div');
                        $('#enquiry_date').removeClass('invalid-div');
                        $('#enquiry_date').closest('div').find('.error-feedback').hide();
                    }

                    if(surname==''){
                        $('#surname').addClass('invalid-div');
                        $('#surname').removeClass('valid-div');
                        $('#surname').closest('div').find('.error-feedback').show();
                    }else{
                        $('#surname').addClass('valid-div');
                        $('#surname').removeClass('invalid-div');
                        $('#surname').closest('div').find('.error-feedback').hide();
                    }

                    if(enquiryFor==''){
                        $('#enquiry_for').addClass('invalid-div');
                        $('#enquiry_for').removeClass('valid-div');
                        $('#enquiry_for').closest('div').find('.error-feedback').show();
                    }else{
                        $('#enquiry_for').addClass('valid-div');
                        $('#enquiry_for').removeClass('invalid-div');
                        $('#enquiry_for').closest('div').find('.error-feedback').hide();
                    }

                    if(postCode==''){
                        $('#post_code').addClass('invalid-div');
                        $('#post_code').removeClass('valid-div');
                        $('#post_code').closest('div').find('.error-feedback').show();
                    }else{
                        $('#post_code').addClass('valid-div');
                        $('#post_code').removeClass('invalid-div');
                        $('#post_code').closest('div').find('.error-feedback').hide();
                    }

                    if(visit_before==''){
                        $('#visit_before').addClass('invalid-div');
                        $('#visit_before').removeClass('valid-div');
                        $('#visit_before').closest('div').find('.error-feedback').show();
                    }else{
                        $('#visit_before').addClass('valid-div');
                        $('#visit_before').removeClass('invalid-div');
                        $('#visit_before').closest('div').find('.error-feedback').hide();
                    }

                    if(staffSourceInvalid){
                        $('#enquiry_source_staff_error').show();
                        $('#enquiry_source_responsible_staff').addClass('invalid-div');
                        $('#enquiry_source_responsible_staff').removeClass('valid-div');
                    }else{
                        $('#enquiry_source_staff_error').hide();
                        $('#enquiry_source_responsible_staff').removeClass('invalid-div');
                        $('#enquiry_source_responsible_staff').addClass('valid-div');
                    }

                    // console.log($('.error-feedback:visible'));
                    // $('.collapse').collapse();

                    $('.error-feedback:visible').parent('.accordion-button').trigger('click');
                    // if($('.error-feedback:visible').css('display')!='none'){

                    // }

                }else{
                    var checkId=$("#check_update").val();
                    var forms=true;
                    var appointForm=true;

                    if(courseType==1){
                        forms= submitRpl();
                    }else if(courseType==5 || courseType==4){
                        forms= submitShortGroup();
                    }else if(courseType==3){
                        if(reg_grp_names==''){
                            $('#reg_grp_names').addClass('invalid-div');
                             $('#reg_grp_names').removeClass('valid-div');
                            return false;
                        }else{
                            $('#reg_grp_names').addClass('valid-div');
                             $('#reg_grp_names').removeClass('invalid-div');
                        }
                    }

                    if(appointment_booked==1){
                        appointForm= submitSlot();
                    }

                    if(forms && appointForm ){

                    $('#loader-container').css('display','flex');
                    $('#student_enquiry_form').css('opacity','0.1');

                    courses=courses.filter(item => item !== '0');
                    remarks=remarks.filter(item => item !== '0');
                    
                    details={formName:'student_enquiry',studentName:studentName,contactName:contactName,emailAddress:emailAddress,courses:courses,payment:payment,checkId:checkId,visaStatus:visaStatus,surname:surname,enquiryDate:enquiryDate,suburb:suburb,stuState:stuState,postCode:postCode,visit_before:visit_before,hear_about:hear_about,hearedby:hearedby,memberName:memberName,plan_to_start_date:plan_to_start_date,refer_select:refer_select,referer_name:referer_name,refer_alumni:refer_alumni,visaNote:visaNote,prefComment:prefComment,student_user_id:$('#student_user_id').val(),comments:comments,appointment_booked:appointment_booked,visaCondition:visaCondition,remarks:remarks,reg_grp_names:reg_grp_names,streetDetails:streetDetails,enquiryFor:enquiryFor,courseType:courseType,shore:shore,ethnicity:ethnicity,enquiry_source:$('#enquiry_source').val()||0,location:($('#location').val()||'').trim(),enquiry_college:($('#enquiry_college').length ? $('#enquiry_college').val() : 0)||0,rpl_arrays:JSON.stringify(rpl_array),short_grps:JSON.stringify(short_grp),slot_books:JSON.stringify(slot_book),admin_id:"<?php echo $_SESSION['user_id']; ?>",formId:<?php echo $form_id; ?>,rpl_status:'<?php echo $rpl_status; ?>',short_grp_status:'<?php echo $short_grp_status; ?>',reg_grp_status:'<?php echo $reg_grp_status; ?>',slot_book_status:'<?php echo $slot_book_status; ?>'};
                    $.ajax({
                        type:'post',
                        url: datacontrolUrl,
                        data:details,
                        success:function(data){
                            if(data==0){
                                $('#toast-text2').html('Cannot add record. Please try again later');
                                var t2 = document.getElementById('borderedToast2');
                                if (t2 && window.bootstrap) { new bootstrap.Toast(t2).show(); } else { $('#borderedToast2Btn').trigger('click'); }
                            }else if(data==='enquiry_source_staff_required' || data=='enquiry_source_staff_required'){
                                $('#toast-text2').html('Please select responsible staff for Email, WhatsApp, or Facebook / Instagram ads.');
                                var t2 = document.getElementById('borderedToast2');
                                if (t2 && window.bootstrap) { new bootstrap.Toast(t2).show(); } else { $('#borderedToast2Btn').trigger('click'); }
                                $('#enquiry_source_staff_error').show();
                                $('#enquiry_source_responsible_staff').addClass('invalid-div');
                                $('#loader-container').hide();
                                $('#student_enquiry_form').css('opacity','');
                            }else if(data==2){
                                document.getElementById('student_enquiry_form').reset();
                                $('#toast-text').html('Record Updated Successfully');
                                var t1 = document.getElementById('borderedToast1');
                                if (t1 && window.bootstrap) { new bootstrap.Toast(t1).show(); } else { $('#borderedToast1Btn').trigger('click'); }
                                setTimeout(() => {location.reload();}, 500); 
                            }else{
                                document.getElementById('student_enquiry_form').reset();
                                $('#toast-text').html('New Enquiry Added');
                                var t1 = document.getElementById('borderedToast1');
                                if (t1 && window.bootstrap) { new bootstrap.Toast(t1).show(); } else { $('#borderedToast1Btn').trigger('click'); }
                                $('#myModalLabel').html('Enquiry ID Created:');
                                $('.modal-body').html(data);
                                $('#model_trigger').trigger('click');
                                setTimeout(() => {location.reload();}, 500); 
                            }
                        },
                        error:function(xhr, status, error){
                            $('#toast-text2').html('Server request failed: ' + status + ' - ' + error);
                            var t2 = document.getElementById('borderedToast2');
                            if (t2 && window.bootstrap) { new bootstrap.Toast(t2).show(); } else { $('#borderedToast2Btn').trigger('click'); }
                            console.error('AJAX error', status, error, xhr.responseText);
                        },
                        complete:function(){
                            $('#loader-container').hide();
                            $('#student_enquiry_form').css('opacity','');
                        }
                    })
                    }
                }

            })


            /* function genQR(){                

                // $.ajax({
                //     url:'includes/datacontrol',
                //     data:{admin_id:"<?php echo $_SESSION['user_id']; ?>",formName:'create_qr'},
                //     type:'post',
                //     success:function(data){
                        
                        var qrcodeContainer = document.getElementById('qrcode');
                        var updatedURL = removeLastSegmentFromURL(window.location.href)+'/common_enquiry.php';
            */

            var rpl_array = <?php echo $rpl_arrays; ?>;
            var short_grp = <?php echo $short_grps; ?>;
            var slot_book = <?php echo $slot_books; ?>;

            function submitRpl(){
               var rpl_exp=$('#rpl_exp').val()==0 ? '' : $('#rpl_exp').val();
               var exp_in=$('#exp_in').val()==0 ? '' : $('#exp_in').val();
               var exp_docs=$('#exp_docs').val()==0 ? '' : $('#exp_docs').val();
               var exp_prev=$('#exp_prev').val()==0 ? '' : $('#exp_prev').val();
               var exp_name=$('#exp_name').val();
               var exp_years=$('#exp_years').val();
               var exp_prev_name=$('#exp_prev_name').val();


               if(rpl_exp=='' || ( rpl_exp!='' && rpl_exp==1 ) && ( exp_in=='' ||  exp_docs=='' || exp_prev=='' || exp_name=='' || exp_years=='' ) || ( rpl_exp!='' && rpl_exp==1 ) && ( exp_prev==1 && exp_prev_name=='' ) ) {



                    if(rpl_exp==''){
                            $('#rpl_exp').addClass('invalid-div');
                            $('#rpl_exp').removeClass('valid-div');
                    }else{
                            $('#rpl_exp').addClass('valid-div');
                            $('#rpl_exp').removeClass('invalid-div');
                    }

                    if(rpl_exp!='' && ( exp_in=='' ||  exp_docs=='' || exp_prev=='' || exp_name=='' || exp_years=='' )){


                        if(exp_in==''){
                            $('#exp_in').addClass('invalid-div');
                            $('#exp_in').removeClass('valid-div');
                        }else{
                                $('#exp_in').addClass('valid-div');
                                $('#exp_in').removeClass('invalid-div');
                        }

                        if(exp_docs==''){
                            $('#exp_docs').addClass('invalid-div');
                            $('#exp_docs').removeClass('valid-div');
                        }else{
                            $('#exp_docs').addClass('valid-div');
                            $('#exp_docs').removeClass('invalid-div');
                        }

                        if(exp_prev==''){
                            $('#exp_prev').addClass('invalid-div');
                            $('#exp_prev').removeClass('valid-div');
                        }else{
                            $('#exp_prev').addClass('valid-div');
                            $('#exp_prev').removeClass('invalid-div');
                        }

                        if(exp_name==''){
                            $('#exp_name').addClass('invalid-div');
                            $('#exp_name').removeClass('valid-div');
                        }else{
                            $('#exp_name').addClass('valid-div');
                            $('#exp_name').removeClass('invalid-div');
                        }

                        if(exp_years==''){
                            $('#exp_years').addClass('invalid-div');
                            $('#exp_years').removeClass('valid-div');
                        }else{
                            $('#exp_years').addClass('valid-div');
                            $('#exp_years').removeClass('invalid-div');
                        }

                    }

                    if( exp_prev==1 && exp_prev_name=='' ){

                        if(exp_prev_name==''){
                            $('#exp_prev_name').addClass('invalid-div');
                            $('#exp_prev_name').removeClass('valid-div');
                        }else{
                            $('#exp_prev_name').addClass('valid-div');
                            $('#exp_prev_name').removeClass('invalid-div');
                        }

                    }

                    return false;

                }else{

                    rpl_array={"rpl_exp":rpl_exp,"exp_in":exp_in,"exp_docs":exp_docs,"exp_prev":exp_prev,"exp_name":exp_name,"exp_years":exp_years,"exp_prev_name":exp_prev_name};
                    return true;
                    
                }

            }

            function submitShortGroup(){
                var short_grp_org_name=$('#short_grp_org_name').val();
                var short_grp_date=$('#short_grp_date').val();
                var short_grp_num_std=$('#short_grp_num_std').val();
                var short_grp_ind_exp=$('#short_grp_ind_exp').val()==0 ? '' : $('#short_grp_ind_exp').val();
                var short_grp_con_type=$('#short_grp_con_type').val();
                var short_grp_con_num=$('#short_grp_con_num').val();
                var short_grp_con_name=$('#short_grp_con_name').val();
                var short_grp_con_email=$('#short_grp_con_email').val();
                var emailregexp = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var short_grp_org_type=$('#short_grp_org_type').val()==0 ? '' : $('#short_grp_org_type').val();
                var short_grp_campus=$('#short_grp_campus').val()==0 ? '' : $('#short_grp_campus').val();
                var short_grp_before=$('#short_grp_before').val()==0 ? '' : $('#short_grp_before').val();

                short_grp={"short_grp_org_name":short_grp_org_name,"short_grp_org_type":short_grp_org_type,"short_grp_campus":short_grp_campus,"short_grp_date":short_grp_date,"short_grp_num_std":short_grp_num_std,"short_grp_ind_exp":short_grp_ind_exp,"short_grp_con_type":short_grp_con_type,"short_grp_con_num":short_grp_con_num,"short_grp_con_name":short_grp_con_name, "short_grp_con_email":short_grp_con_email,"short_grp_before":short_grp_before};
                return true;
            // }

            }

            function submitSlot(){
                var slot_book_time=$('#slot_book_time').val(); 
                var slot_book_purpose=$('#slot_book_purpose').val(); 
                var slot_book_date=$('#slot_book_date').val(); 
                var slot_book_by=$('#slot_book_by').val(); 
                var slot_book_link=$('#slot_book_link').val()==0 ? '' : $('#slot_book_link').val(); 

                if(slot_book_time=='' || slot_book_purpose=='' || slot_book_date=='' || slot_book_by=='' || slot_book_link==''){

                    if(slot_book_time==''){

                        $('#slot_book_time').addClass('invalid-div');
                        $('#slot_book_time').removeClass('valid-div');
                    }else{
                        $('#slot_book_time').addClass('valid-div');
                        $('#slot_book_time').removeClass('invalid-div');

                    }
                    if(slot_book_purpose==''){

                        $('#slot_book_purpose').addClass('invalid-div');
                        $('#slot_book_purpose').removeClass('valid-div');
                    }else{
                        $('#slot_book_purpose').addClass('valid-div');
                        $('#slot_book_purpose').removeClass('invalid-div');

                    }
                    if(slot_book_date==''){

                        $('#slot_book_date').addClass('invalid-div');
                        $('#slot_book_date').removeClass('valid-div');
                    }else{
                        $('#slot_book_date').addClass('valid-div');
                        $('#slot_book_date').removeClass('invalid-div');

                    }
                    if(slot_book_by==''){

                        $('#slot_book_by').addClass('invalid-div');
                        $('#slot_book_by').removeClass('valid-div');
                    }else{
                        $('#slot_book_by').addClass('valid-div');
                        $('#slot_book_by').removeClass('invalid-div');

                    }
                    if(slot_book_link==''){

                        $('#slot_book_link').addClass('invalid-div');
                        $('#slot_book_link').removeClass('valid-div');
                    }else{
                        $('#slot_book_link').addClass('valid-div');
                        $('#slot_book_link').removeClass('invalid-div');

                    }

                    return false;

                }else{

                    slot_book={"slot_book_time":slot_book_time,"slot_book_purpose":slot_book_purpose,"slot_book_date":slot_book_date,"slot_book_by":slot_book_by,"slot_book_link":slot_book_link};
                    return true;

                }

            }

        </script>
