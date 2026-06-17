        <div class="accordion mb-3" id="enquiryMainAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingStudentEnquiry">
                    <button class="accordion-button fw-medium" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudentEnquiry" aria-expanded="true" aria-controls="collapseStudentEnquiry">
                        Student Enquiry
                    </button>
                </h2>
                <div id="collapseStudentEnquiry" class="accordion-collapse collapse show" aria-labelledby="headingStudentEnquiry" data-bs-parent="#enquiryMainAccordion">
                    <div class="accordion-body p-0">
        <form class="student_enquiry_form" id="student_enquiry_form">
            <input type="hidden" name="student_user_id" id="student_user_id" value="<?php echo $student_user_id; ?>">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body" id="student_enquiry_form_parent">
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Personal Details
                                        </button>
                                    </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="email_address">Email<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="email_address" placeholder="Email Address" value="<?php echo $queryRes['st_email']; ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Email Address
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_date">Date<span class="asterisk">*</span></label>
                                                        <input type="date" class="form-control" id="enquiry_date" value="<?php echo  $queryRes['st_enquiry_date']!='' ? date('Y-m-d',strtotime($queryRes['st_enquiry_date'])) : ''; ?>">
                                                        <div class="error-feedback">
                                                            Please select the Date
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="surname">Surname<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="surname" placeholder="Surname" value="<?php echo  $queryRes['st_surname']; ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Surname
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_for">Enquiring For<span class="asterisk">*</span></label>
                                                        <select name="enquiry_for" class="form-select" id="enquiry_for">
                                                        <?php  
                                                        $st_enquiry=['--select--','Self','Family Member'];
                                                        for($i=0;$i<count($st_enquiry);$i++){
                                                            $checked= $i==$queryRes['st_enquiry_for'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_enquiry[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>  
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="member_name">Name<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="member_name" placeholder="Name" value="<?php echo $queryRes['st_enquiry_for']==1 ? $queryRes['st_name'] : $queryRes['st_member_name']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6" id="student_name_wrap" style="display:<?php echo $queryRes['st_enquiry_for']==2 ? 'block' : 'none' ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="student_name">Student name / Family member name<span class="asterisk">*</span></label>
                                                        <input type="text" class="form-control" id="student_name" placeholder="Student name" value="<?php echo $queryRes['st_name']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Student name
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="course_type">Course Type</label>
                                                        <select name="course_type" class="form-select" id="course_type">
                                                        <?php  
                                                        $st_course_type=['--select--','Need exemption','Regular','Regular - Group','Short courses','Short course - Group'];
                                                        $selectedCourseType=$queryRes['st_course_type']!='' ? $queryRes['st_course_type'] : 0;
                                                        for($i=0;$i<count($st_course_type);$i++){
                                                            $checked= $i==$queryRes['st_course_type'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" data="'.$st_course_type[$i].'" '.$checked.'>'.$st_course_type[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>  
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_source">Enquiry Source</label>
                                                        <select name="enquiry_source" class="form-select" id="enquiry_source">
                                                        <?php
                                                        $st_enquiry_source = ['--select--','Website form','Phone call','Walk-in','Email','WhatsApp','Facebook / Instagram ads'];
                                                        $sel_source = isset($queryRes['st_enquiry_source']) ? (int)$queryRes['st_enquiry_source'] : 0;
                                                        for($i=0;$i<count($st_enquiry_source);$i++){
                                                            $ch = $i === $sel_source ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$ch.'>'.$st_enquiry_source[$i].'</option>';
                                                        }
                                                        if ($sel_source === 7) {
                                                            echo '<option value="7" selected>Agent / referral (legacy)</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3" id="enquiry_source_staff_wrap" style="display:<?php echo (in_array($sel_source, array(4, 5, 6), true)) ? 'block' : 'none'; ?>;">
                                                        <label class="form-label" for="enquiry_source_responsible_staff">Responsible staff</label>
                                                        <select class="form-select" id="enquiry_source_responsible_staff" name="enquiry_source_responsible_staff">
                                                            <option value="">--select--</option>
                                                            <?php
                                                            if (!isset($enquirySourceStaffUsers) && isset($connection)) {
                                                                $enquirySourceStaffUsers = mysqli_query($connection, "SELECT user_id, user_name FROM users WHERE user_status != 1 ORDER BY user_name");
                                                            }
                                                            $staff_sel = isset($queryRes['st_hearedby']) ? trim((string)$queryRes['st_hearedby']) : '';
                                                            if ($staff_sel === '' && isset($_SESSION['user_name'])) {
                                                                $staff_sel = trim((string)$_SESSION['user_name']);
                                                            }
                                                            if ($enquirySourceStaffUsers && mysqli_num_rows($enquirySourceStaffUsers) > 0) {
                                                                mysqli_data_seek($enquirySourceStaffUsers, 0);
                                                                while ($su = mysqli_fetch_array($enquirySourceStaffUsers)) {
                                                                    $nm = $su['user_name'];
                                                                    $os = ($staff_sel === $nm) ? 'selected' : '';
                                                                    echo '<option value="'.htmlspecialchars($nm, ENT_QUOTES, 'UTF-8').'" '.$os.'>'.htmlspecialchars($nm, ENT_QUOTES, 'UTF-8').'</option>';
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                        <div class="error-feedback" id="enquiry_source_staff_error" style="display:none;">Please select the responsible staff for this enquiry source.</div>
                                                    </div>
                                                </div>
                                                <?php if (!$is_student_portal): ?>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="enquiry_college">Received Enquiry for Which college</label>
                                                        <select name="enquiry_college" class="form-select" id="enquiry_college">
                                                        <?php
                                                        $st_enquiry_college = ['--select--','Apt Training College','Milton College','NCA','Power Education','Auz Training'];
                                                        $sel_college = isset($queryRes['st_enquiry_college']) ? (int)$queryRes['st_enquiry_college'] : 0;
                                                        for($i=0;$i<count($st_enquiry_college);$i++){
                                                            $ch = $i === $sel_college ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$ch.'>'.$st_enquiry_college[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                        
        </div>                        

                          <!-- Short Course - group Form -->

                <div class="row" id="short_grp_form" style="display:<?php echo $queryRes['st_course_type']==5 || $queryRes['st_course_type']==4 ? 'block' : 'none' ?>">
                    <div class="col-xl-12">
                        <div class="card">
                            <div class="card-body">
                                <b><p class="card-title">Short Course Group Form</p></b>
                                <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_org_name">Organisation Name</label>
                                    <input type="text" name="short_grp_org_name" class="form-control" id="short_grp_org_name" placeholder="Organisation Name"  value="<?php echo $short_grp['short_grp_org_name']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_org_type">Type of Organisation</label>
                                    <select name="short_grp_org_type" class="form-control" id="short_grp_org_type">
                                    <?php 
                                        $short_grp_org_type=['--select--','Job Agency','Employer','College'];
                                        for($i=0;$i<count($short_grp_org_type);$i++){
                                            $selected=$i==$short_grp['short_grp_org_type'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_org_type[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_campus">Training to be given</label>
                                    <select name="short_grp_campus" class="form-control" id="short_grp_campus">
                                    <?php 
                                        $short_grp_campus=['--select--','Off Campus','On Campus'];
                                        for($i=0;$i<count($short_grp_campus);$i++){
                                            $selected=$i==$short_grp['short_grp_campus'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_campus[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_date">Date Required</label>
                                    <input type="date" name="short_grp_date" class="form-control" id="short_grp_date" value="<?php echo $short_grp['short_grp_date']!='' ? date('Y-m-d',strtotime($short_grp['short_grp_date'])) : ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_num_std">Number of Students</label>
                                    <input type="tel" name="short_grp_num_std" class="form-control number-field" id="short_grp_num_std" value="<?php echo $short_grp['short_grp_num_std']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_ind_exp">Have they got Industry Experience ?</label>
                                    <select name="short_grp_ind_exp" class="form-control" id="short_grp_ind_exp">
                                    <?php 
                                        $short_grps_ind_exp=['--select--','Yes','No'];
                                        for($i=0;$i<count($short_grps_ind_exp);$i++){
                                            $selected=$i==$short_grp['short_grp_ind_exp'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grps_ind_exp[$i]."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_before">Have they done this Training Before ?</label>
                                    <select name="short_grp_before" class="form-control" id="short_grp_before">
                                    <?php 
                                        $short_grp_train_bef=['--select--','Yes','No'];
                                        for($i=0;$i<count($short_grp_train_bef);$i++){
                                            $selected=$i==$short_grp['short_grp_before'] ? 'selected' : '';
                                            echo "<option value='".$i."' ".$selected.">".$short_grp_train_bef[$i]."</option>";
                                        }

                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_type">How did they Contact us</label>
                                    <input type="text" name="short_grp_con_type" class="form-control" id="short_grp_con_type" value="<?php echo $short_grp['short_grp_con_type']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_num">Contact Number</label>
                                    <input type="tel" name="short_grp_con_num" class="form-control number-field" id="short_grp_con_num" placeholder="Contact Number" value="<?php echo $short_grp['short_grp_con_num']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_name">Contact Person Name</label>
                                    <input type="text" name="short_grp_con_name" class="form-control" id="short_grp_con_name" placeholder="Name" value="<?php echo $short_grp['short_grp_con_name']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="short_grp_con_email">Contact Person Email</label>
                                    <input type="email" name="short_grp_con_email" class="form-control" id="short_grp_con_email" placeholder="Email" value="<?php echo $short_grp['short_grp_con_email']; ?>">
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                                <div class="row" id="rpl_form" style="display:<?php echo (isset($queryRes['st_course_type']) && $queryRes['st_course_type']==1) ? 'block' : 'none' ?>">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Need exemption Form</p></b>
                                                <div class="row">

                                                <div class="col-md-6">
                                                    <label class="form-label" for="rpl_exp">Do they have Experience ?</label>
                                                    <select name="rpl_exp" class="form-control rpl_parent" id="rpl_exp">
                                                        <?php 
                                                        $rpl_exps=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps);$i++){
                                                            $selected=$i==$rpl_array['rpl_exp'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_in">Experienced In</label>
                                                    <select name="exp_in" class="form-control" id="exp_in">
                                                    <?php 
                                                        $rpl_exps_in=['--select--','Aged Care','Disability Care','Mental Health','Home Care and Hospitals'];
                                                        for($i=0;$i<count($rpl_exps_in);$i++){
                                                            $selected=$i==$rpl_array['exp_in'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_in[$i]."</option>";
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_name">Role/Designation</label>
                                                    <input type="text" name="exp_name" class="form-control" id="exp_name" placeholder="Role" value="<?php echo $rpl_array['exp_name']; ?>">
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_years">How Many Years & Months</label>
                                                    <input type="text" name="exp_years" class="form-control" id="exp_years" placeholder="Years" value="<?php echo $rpl_array['exp_years']; ?>">
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_docs">Do they have any Documents(payslips and job description / Contract Letter)</label>
                                                    <select name="exp_docs" class="form-control" id="exp_docs">
                                                    <?php 
                                                        $rpl_exps_doc=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps_doc);$i++){
                                                            $selected=$i==$rpl_array['exp_docs'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_doc[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_child" style="display:<?php echo $rpl_array['rpl_exp']==1 ? 'block' : 'none' ?>">
                                                    <label class="form-label" for="exp_prev">Any previous Qualification done ?</label>
                                                    <select name="exp_prev" class="form-control rpl_prev_parent" id="exp_prev">
                                                    <?php 
                                                        $rpl_exps_prev=['--select--','Yes','No'];
                                                        for($i=0;$i<count($rpl_exps_prev);$i++){
                                                            $selected=$i==$rpl_array['exp_prev'] ? 'selected' : '';
                                                            echo "<option value='".$i."' ".$selected.">".$rpl_exps_prev[$i]."</option>";
                                                        }

                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 rpl_prev_child">
                                                    <label class="form-label" for="exp_prev_name">Previous Qualification Name</label>
                                                    <input type="text" name="exp_prev_name" class="form-control" id="exp_prev_name" placeholder="Name" value="<?php echo $rpl_array['exp_prev_name']; ?>">
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="regular_grp_form" style="display:<?php echo $queryRes['st_course_type']==3 ? '' : 'none' ?>">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Regular Group Form</p></b>
                                                <div class="row">

                                                    <div class="col-md-6">
                                                        <label class="form-label" for="reg_grp_names">Enter the People Names</label>
                                                        <input type="text" id="reg_grp_names" class="form-control" name="reg_grp_names" value="<?php echo $reg_grp; ?>">
                                                        <div class="alert alert-primary d-flex align-items-center mt-2" role="alert">
                                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                                                        <div>
                                                        Multiple Names can be written with a Comma(,) in Between
                                                        </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">
                                            <div class="accordion" id="accordionExample">
                                                <div class="accordion-item">
                                                            <h2 class="accordion-header" id="headingTwo">
                                                                <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                                                                Address Details
                                                                </button>
                                                            </h2>                                                                
                                                    <div id="collapseTwo" class="accordion-collapse collapse show" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                                        <div class="accordion-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="contact_num">Mobile<span class="asterisk">*</span></label>
                                                                            <input type="text" class="form-control number-field" maxlength="10" id="contact_num" placeholder="Contact Number" value="<?php echo $queryRes['st_phno']; ?>" >
                                                                            <div class="error-feedback">
                                                                                Please enter the Contact Number
                                                                            </div>
                                                                            <div class="phone_error">
                                                                                Entered Number Already exist with Enquiry ID: <span id="phone_err_id"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="street_no">Street No / Name</label>
                                                                            <input type="text" class="form-control street_no" id="street_no" placeholder="Street No / Name" value="<?php echo $queryRes['st_street_details']; ?>" >
                                                                            <div class="error-feedback">
                                                                                Please enter the Street Details
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="suburb">Suburb</label>
                                                                            <input type="text" class="form-control suburb" id="suburb" placeholder="Suburb" value="<?php echo $queryRes['st_suburb']; ?>" >
                                                                            <div class="error-feedback">
                                                                                Please enter the Suburb
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="stu_state">State</label>
                                                                            <select name="stu_state" id="stu_state" class="form-control">
                                                                            <?php  
                                                                            $st_states=['--select--','NSW - New South Wales','VIC - Victoria','ACT - Australian Capital Territory','NT - Northern Territoy','WA - Western Australia','QLD - Queensland','SA - South Australia','TAS - Tasmania'];
                                                                            for($i=0;$i<count($st_states);$i++){
                                                                                $checked= $i==$queryRes['st_state'] ? 'selected' : '';
                                                                                echo '<option value="'.$i.'" '.$checked.'>'.$st_states[$i].'</option>';
                                                                            }
                                                                            ?>
                                                                            </select>
                                                                            <div class="error-feedback">
                                                                                Please enter the State
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="post_code">Post Code<span class="asterisk">*</span></label>
                                                                            <input type="tel" class="form-control number-field" maxlength="6" id="post_code" placeholder="Post Code" value="<?php echo $queryRes['st_post_code']; ?>" >
                                                                            <div class="error-feedback">
                                                                                Please enter the Post Code
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label" for="visit_before">Have you visited us before?<span class="asterisk">*</span></label>
                                                                            <select name="visit_before" class="form-select" id="visit_before">
                                                                            <?php  
                                                                            $st_visited=['--select--','Yes','No'];
                                                                            for($i=0;$i<count($st_visited);$i++){
                                                                                $checked= $i==$queryRes['st_visited'] ? 'selected' : '';
                                                                                echo '<option value="'.$i.'" '.$checked.'>'.$st_visited[$i].'</option>';
                                                                            }
                                                                            ?>
                                                                            </select>  
                                                                            <div class="error-feedback">
                                                                                Please select atleast one option
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">      
                                                <div class="accordion" id="accordionExample">
                                                    <div class="accordion-item">
                                                                <h2 class="accordion-header" id="headingThree">
                                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_counsel" aria-expanded="true" aria-controls="collapse_counsel">
                                                                    Training Dependencies
                                                                    </button>
                                                                </h2>
                                                                <div id="collapse_counsel" class="accordion-collapse collapse show" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                                                    <div class="accordion-body">
                                                                    <div class="row">
                                                            <div class="col-sm">
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="plan_to_start_date">When do you plan to start?</label>
                                                                        <input type="date" class="form-control" id="plan_to_start_date" value="<?php echo $queryRes['st_startplan_date']!='' ? date('Y-m-d',strtotime($queryRes['st_startplan_date'])) : '' ?>" >
                                                                        <div class="error-feedback">
                                                                            Please select the Plan to Start Date
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="refer_select">Have you been referred by someone?<span class="asterisk">*</span></label>
                                                                        <select name="refer_select" class="form-select refered" id="refer_select">
                                                                        <?php  
                                                                        $st_refered=['--select--','Yes','No'];
                                                                        for($i=0;$i<count($st_refered);$i++){
                                                                            $checked= $i==$queryRes['st_refered'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_refered[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 refered_field" style="display:<?php echo $queryRes['st_refered']==1 ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="referer_name">Please specify his / her name</label>
                                                                        <input type="text" class="form-control" id="referer_name" value="<?php echo $queryRes['st_refer_name']; ?>" placeholder="name1,name2,name3">
                                                                        <div class="alert alert-primary d-flex align-items-center mt-2" role="alert">
                                                                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="Info:"><use xlink:href="#info-fill"/></svg>
                                                                        <div>
                                                                        Multiple Names can be written with a Comma(,) in Between
                                                                        </div>
                                                                        </div>
                                                                        <div class="error-feedback">
                                                                            Please Enter his / her name
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 refered_field" style="display:<?php echo $queryRes['st_refered']==1 ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="refer_alumni">Is he / she an alumni<span class="asterisk">*</span></label>
                                                                        <select name="refer_alumni" class="form-select" id="refer_alumni">
                                                                        <?php  
                                                                        $st_refer_alumni=['--select--','Yes','No'];
                                                                        for($i=0;$i<count($st_refer_alumni);$i++){
                                                                            $checked= $i==$queryRes['st_refered'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_refer_alumni[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="visa_condition">Visa Condition</label>
                                                                        <select name="visa_condition" class="form-select" id="visa_condition">
                                                                        <?php 
                                                                        while($visaRes=mysqli_fetch_array($visaStatus)){
                                                                            if($visaRes['visa_id']==1){
                                                                                echo "<option value='0'>--select--</option><optgroup label='Subclass 500 main applicant'>";
                                                                            }
                                                                        ?>                                                                                                      
                                                                            <option value="<?php echo $visaRes['visa_id']; ?>" <?php echo $visaRes['visa_id']==$queryRes['st_visa_status'] ? 'selected' : ''; ?>><?php echo $visaRes['visa_status_name']; ?></option>
                                                                            <?php
                                                                        if($visaRes['visa_id']==4){
                                                                            echo '</optgroup>';
                                                                        }

                                                                        } ?>
                                                                        </select> 
                                                                        <div class="error-feedback">
                                                                            Please select a visa status
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12 visa_note" style="display:<?php echo (!empty($queryRes['st_visa_status']) && $queryRes['st_visa_status']==7) ? '' : 'none'; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="visa_note">Specify Visa Status</label>
                                                                        <input type="text" class="form-control" id="visa_note" value="<?php echo $queryRes['st_visa_note']; ?>" placeholder="Visa Note">
                                                                        <div class="error-feedback">
                                                                            Please Specify the Visa Condition
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                    <div><label for="visa_status_label">Visa Status</label></div>
                                                                    <div>
                                                                        <input class="form-check-input visa_status" type="radio" value="1" name="visa_status" id="visa_status1" <?php echo $queryRes['st_visa_condition']=='' ? 'checked' :  ( $queryRes['st_visa_condition']==1 ? 'checked' : '' ) ; ?>>
                                                                        <label class="form-check-label" for="visa_status1">
                                                                            Approved
                                                                        </label>
                                                                        <input class="form-check-input visa_status" type="radio" value="2" name="visa_status" id="visa_status2" <?php echo $queryRes['st_visa_condition']==2 ? 'checked' : ''; ?>>
                                                                        <label class="form-check-label" for="visa_status2" >
                                                                            Not Approved
                                                                        </label>
                                                                        <div class="error-feedback">
                                                                            Please select a visa status
                                                                        </div>
                                                                    </div>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-12">
                                                                    <div class="mb-3">
                                                                        <label class="form-label" for="shore">Are you Offshore or Onshore</label>
                                                                        <select name="shore" class="form-select" id="shore">
                                                                        <?php  
                                                                        $st_shore=['--select--','OffShore','OnShore'];
                                                                        for($i=0;$i<count($st_shore);$i++){
                                                                            $checked= $i==$queryRes['st_refered'] ? 'selected' : '';
                                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_shore[$i].'</option>';
                                                                        }
                                                                        ?>
                                                                        </select>                                                          
                                                                        <div class="error-feedback">
                                                                            Please select atleast one option
                                                                        </div>
                                                                </div>
                                                            </div>
                                                            </div> <!-- col-sm-close div -->

                                                                <div class="col-sm">

                                                                    <div class="col-md-12">
                                                                            <div class="mb-3">
                                                                                <label class="form-label" for="courses">Which Course are you interested in?<span class="asterisk">*</span></label>
                                                                                <?php 
                                                                                $counts=1;
                                                                                $_decoded_courses = !empty($queryRes['st_course']) ? json_decode($queryRes['st_course']) : null;
                                                                                $coursesSel = is_array($_decoded_courses) ? $_decoded_courses : [];
                                                                                while($coursesRes=mysqli_fetch_array($courses)){
                                                                                    if(in_array($counts,$coursesSel)){
                                                                                        $checked='checked';
                                                                                    }else{
                                                                                        $checked='';
                                                                                    }                                                            

                                                                                    echo '<div class="form-check"><input type="checkbox" class="courses_check form-check-input" id="course_check_'.$counts.'" '.$checked.' value="'.$counts.'">';
                                                                                    echo '<label for="course_check_'.$counts.'">'.$coursesRes["course_sname"].'-'.$coursesRes["course_name"].'</label></div>';
                                                                                    $counts++;
                                                                                }

                                                                                ?>
                                                                                <div class="courses_error">
                                                                                    Please select the Courses
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <div class="row">
                            <div class="col-xl-12">
                                <div class="card">
                                    <div class="card-body" id="student_enquiry_form_parent">
                                            <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="headingfour">
                                                    <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsefour" aria-expanded="true" aria-controls="collapsefour">
                                                    Additional Information
                                                    </button>
                                                </h2>
                                                <div id="collapsefour" class="accordion-collapse collapse show" aria-labelledby="headingfour" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body">
                                                    <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="ethnicity">Ethnicity</label>
                                                        <input type="text" class="form-control" id="ethnicity" placeholder="Ethnicity" value="<?php echo $queryRes['st_ethnicity']; ?>">
                                                        <div class="error-feedback">
                                                            Please enter the Ethnicity
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="payment_fee">Fees mentioned</label>
                                                        <input type="text" class="form-control" maxlength="255" id="payment_fee" placeholder="0.00" value="<?php echo $queryRes['st_fee']; ?>" >
                                                        <div class="error-feedback">
                                                            Please enter the Mentioned Fee
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php if (!$is_student_portal): ?>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="appointment_booked">Appointment booked for counseling or not?</label>
                                                        <select name="appointment_booked" class="form-select" id="appointment_booked">
                                                        <?php  
                                                        $st_appoint_book=['--select--','Yes','No'];
                                                        $selectedAppoint=$queryRes['st_appoint_book']=='' ? 0 : $queryRes['st_appoint_book'];
                                                        for($i=0;$i<count($st_appoint_book);$i++){
                                                            $checked= $i==$queryRes['st_appoint_book'] ? 'selected' : '';
                                                            echo '<option value="'.$i.'" '.$checked.'>'.$st_appoint_book[$i].'</option>';
                                                        }
                                                        ?>
                                                        </select>                                                          
                                                        <div class="error-feedback">
                                                            Please select atleast one option
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <?php if (!$is_student_portal): ?>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label" for="remarks">Remarks</label>
                                                        <?php
                                                        $st_remarks=['Seems to be interested to do course and need to contact asap','contacted and followed','Selected - Good with communication skills','Sent enrollement form online/ hard copies','Want to do the course asap','not interested much','Looking for government funding','Have done counselling before but wants to get more info','Counseling is done but enrolment is due','Have done the counselling before','Seems like having attitude','Want to book an appointment for counselling','Will callus back again','Planning to relocate to other state','Wants to get COE for visa purpose','Rejected - "Reasons mentioned in comments" or " ReCounseliing needed"'];
                                                        $_decoded_remarks = !empty($queryRes['st_remarks']) ? json_decode($queryRes['st_remarks']) : null;
                                                        $remarksSel = is_array($_decoded_remarks) ? $_decoded_remarks : [];
                                                        for($i=1;$i<count($st_remarks);$i++){
                                                            $checked = (!empty($remarksSel) && in_array($i,$remarksSel)) ? 'checked' : '';
                                                            echo '<div class="form-check"><input type="checkbox" class="remarks_check form-check-input" id="remark_check_'.$i.'" '.$checked.' value="'.$i.'">';
                                                            echo '<label for="remark_check_'.$i.'">'.htmlspecialchars($st_remarks[$i]).'</label></div>';
                                                        }
                                                        ?>
                                                        <div class="error-feedback">Please select atleast one option</div>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                         <label class="form-label" for="pref_comment">Any preferences or requirements or expectations regarding this course</label>
                                                        <input type="text" class="form-control" id="pref_comment" placeholder="Requirements" value="<?php echo $queryRes['st_pref_comments']; ?>">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                                <?php if (!$is_student_portal): ?>
                                <!-- appointment form (admin only) -->
                                <div class="row" id="appointment_form" style="display:<?php echo $queryRes['st_appoint_book']==1 ? 'block' : 'none' ?>"> 
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <b><p class="card-title">Appointment Form</p></b>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_time">Appointment Time</label>
                                                        <input type="datetime-local" name="slot_book_time" class="form-control" id="slot_book_time" value="<?php echo $slot_book['slot_book_time']!='' ? date('Y-m-d H:i',strtotime($slot_book['slot_book_time'])) : '' ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_purpose">Purpose of Appointment</label>
                                                        <input type="text" name="slot_book_purpose" class="form-control" id="slot_book_purpose" placeholder="Purpose" value="<?php echo $slot_book['slot_book_purpose']; ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_date">Booked On</label>
                                                        <input type="date" name="slot_book_date" class="form-control" id="slot_book_date" value="<?php echo $slot_book['slot_book_date']!='' ? date('Y-m-d',strtotime($slot_book['slot_book_date'])) : '' ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_by">Booking Made By</label>
                                                        <input type="text" name="slot_book_by" class="form-control" id="slot_book_by" placeholder="Booked By" value="<?php echo $slot_book['slot_book_by']; ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label" for="slot_book_link">Sent the Email for the Link ?</label>
                                                        <select name="slot_book_link" class="form-control" id="slot_book_link">
                                                        <?php 
                                                            $slot_booking_link=['--select--','Yes','No'];
                                                            for($i=0;$i<count($slot_booking_link);$i++){
                                                                $selected=$i==$slot_book['slot_book_link'] ? 'selected' : '';
                                                                echo "<option value='".$i."' ".$selected.">".$slot_booking_link[$i]."</option>";
                                                            }

                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="row">
                                   <div class="col-xl-12">
                                        <div class="card">
                                            <div class="card-body" id="student_enquiry_form_parent">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <?php if($eqId==0){ ?>
                                                        <button class="btn btn-primary" type="button" id="enquiry_form">Submit Enquiry</button>
                                                        <?php }else{ ?>
                                                        <button class="btn btn-primary" type="button" id="enquiry_form">Update Enquiry</button>
                                                        <?php } ?>
                                                        <input type="hidden" value="<?php echo $eqId; ?>" id="check_update">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                    </div></div></div>
        </div>
