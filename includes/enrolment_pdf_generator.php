<?php
/**
 * Generate Enrolment Form PDF matching National College Australia (NCA) layout.
 * Design: section headers with light blue-green bar, NCA logo, bordered grid, footers, Office Use Only in red box.
 */
require_once __DIR__ . '/../vendor/tecnickcom/tcpdf/tcpdf.php';

function enrolment_generate_pdf($data, $savePath) {
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('National College Australia');
    $pdf->SetAuthor('National College Australia');
    $pdf->SetTitle('Enrolment Form');
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 18);
    $pdf->SetFont('helvetica', '', 9);

    // Light blue-green for section headers (#D4EDDA ≈ RGB 212,237,218)
    $headerR = 212; $headerG = 237; $headerB = 218;
    $redR = 200; $redG = 0; $redB = 0;

    $val = function($key, $default = '') use ($data) {
        return isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null ? $data[$key] : $default;
    };

    $cb = function($checked) use ($pdf) {
        $x = $pdf->GetX(); $y = $pdf->GetY();
        $pdf->Rect($x, $y, 3.2, 3.2);
        if ($checked) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY($x, $y - 0.3);
            $pdf->Cell(3.2, 3.5, 'X', 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 9);
        }
        $pdf->SetXY($x + 3.8, $y);
    };

    $cbOnly = function($checked) use ($pdf) {
        $x = $pdf->GetX(); $y = $pdf->GetY();
        $pdf->Rect($x, $y, 3.2, 3.2);
        if ($checked) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetXY($x, $y - 0.3);
            $pdf->Cell(3.2, 3.5, 'X', 0, 0, 'C');
            $pdf->SetFont('helvetica', '', 9);
        }
        $pdf->SetXY($x + 3.8, $y);
    };

    $sectionBar = function($title) use ($pdf, $headerR, $headerG, $headerB) {
        $pdf->SetFillColor($headerR, $headerG, $headerB);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 7, $title, 1, 1, 'L', true);
        $pdf->SetFont('helvetica', '', 9);
    };

    $drawHeaderLogo = function() use ($pdf) {
        $pdf->SetXY(12, 12);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(100, 8, 'ENROLMENT FORM', 0, 0, 'L');
        $lx = 125; $ly = 10;
        $pdf->SetFillColor(0, 120, 130);
        $pdf->Rect($lx, $ly, 72, 14, 'F');
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetXY($lx, $ly + 1);
        $pdf->Cell(72, 6, 'NCA', 0, 0, 'C');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetXY($lx, $ly + 7);
        $pdf->Cell(72, 5, 'NATIONAL COLLEGE AUSTRALIA', 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 9);
        return 24;
    };

    $drawFooter = function($pageNum) use ($pdf) {
        $pdf->SetY(-15);
        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'National College Australia_RTO:91000', 0, 0, 'L');
        $pdf->Cell(0, 5, 'Enrolment Form_V1.0_August 2025', 0, 0, 'C');
        $pdf->Cell(0, 5, $pageNum . ' | Page', 0, 1, 'R');
    };

    // ---------- PAGE 1 ----------
    $pdf->AddPage();
    $startY = $drawHeaderLogo();
    $pdf->SetXY(12, $startY);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(35, 5, 'Qualification Code & Title:', 0, 0);
    $pdf->Cell(0, 5, $val('qualification_code_title') ?: $val('courses_display'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('STUDENT DETAILS');
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->MultiCell(0, 3.5, 'Unique Student Identifier (USI) is a 10 Digit Unique identification allocated to each individual user. If you yet do not have a USI, please refer to the USI section of the form for information on how to apply. You must write your name, exactly as written in your personal identity document you choose to use for applying for a USI.', 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(38, 5, 'Unique Student Identifier (USI):', 0, 0);
    $pdf->Cell(0, 5, $val('usi_id'), 0, 1);
    $pdf->Cell(38, 5, 'First Name:', 0, 0);
    $pdf->Cell(60, 5, $val('given_name'), 0, 0);
    $pdf->Cell(25, 5, 'Age Declaration:', 0, 0);
    $cbOnly((int)$val('age_declaration_18') === 1);
    $pdf->Cell(0, 5, 'I am at least 18 years of age', 0, 1);
    $pdf->Cell(38, 5, 'Last Name:', 0, 0);
    $pdf->Cell(0, 5, $val('surname'), 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'NCA does not recommend enrolling students under 18 years of age. Please contact our admin staff if you have any questions. All information is collected as per Student Identifiers Act 2014 & Privacy Act 1988.', 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(38, 5, 'Date of Birth (DD/MM/YYYY):', 0, 0);
    $pdf->Cell(35, 5, $val('dob'), 0, 0);
    $pdf->Cell(15, 5, 'Gender:', 0, 0);
    $cb((int)$val('gender_check') === 1); $pdf->Cell(15, 5, 'Male', 0, 0);
    $cb((int)$val('gender_check') === 2); $pdf->Cell(15, 5, 'Female', 0, 0);
    $cb((int)$val('gender_check') === 3); $pdf->Cell(0, 5, 'Other', 0, 1);
    $pdf->Ln(1);

    $sectionBar('Address Details');
    $pdf->Cell(42, 5, 'House and Street Number:', 0, 0);
    $pdf->Cell(0, 5, $val('street_details'), 0, 1);
    $pdf->Cell(42, 5, 'Suburb:', 0, 0);
    $pdf->Cell(40, 5, $val('sub_urb'), 0, 0);
    $pdf->Cell(15, 5, 'State:', 0, 0);
    $pdf->Cell(25, 5, $val('stu_state'), 0, 0);
    $pdf->Cell(18, 5, 'Post Code:', 0, 0);
    $pdf->Cell(0, 5, $val('post_code'), 0, 1);
    $pdf->Cell(0, 4, 'Postal Address (If Any):', 0, 1);
    $cbOnly((int)$val('postal_same_as_above') === 1); $pdf->Cell(32, 5, 'Same as Above', 0, 0);
    $cbOnly((int)$val('postal_same_as_above') === 0); $pdf->Cell(0, 5, 'Enter postal address below', 0, 1);
    $pdf->MultiCell(0, 5, $val('postal_address'), 0, 'L');
    $pdf->Ln(1);

    $pdf->Cell(35, 5, 'Mobile Number:', 0, 0);
    $pdf->Cell(45, 5, $val('mobile_num'), 0, 0);
    $pdf->Cell(22, 5, 'Work Phone:', 0, 0);
    $pdf->Cell(45, 5, $val('work_phone'), 0, 0);
    $pdf->Cell(22, 5, 'Home Phone:', 0, 0);
    $pdf->Cell(0, 5, $val('home_phone'), 0, 1);
    $pdf->Cell(35, 5, 'Email Address:', 0, 0);
    $pdf->Cell(0, 5, $val('emailAddress'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('Education and Training Details');
    $pdf->MultiCell(0, 4, 'Are you able to read, write, and understand English?', 0, 'L');
    $cb((int)$val('english_read_write') === 1); $pdf->Cell(12, 5, 'Yes', 0, 0);
    $cb((int)$val('english_read_write') === 2); $pdf->Cell(12, 5, 'No', 0, 1);
    $pdf->Ln(1);

    $sectionBar('Emergency Contact (Name & Relation)');
    $pdf->Cell(55, 5, 'Emergency Contact (Name & Relation):', 0, 0);
    $pdf->Cell(0, 5, $val('em_full_name') . ' / ' . $val('em_relation'), 0, 1);
    $pdf->Cell(40, 5, 'Mobile Number:', 0, 0);
    $pdf->Cell(0, 5, $val('em_mobile_num'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('Language and Cultural Diversity');
    $pdf->Cell(32, 5, 'Country of Birth:', 0, 0);
    $pdf->Cell(50, 5, $val('birth_country'), 0, 0);
    $pdf->Cell(22, 5, 'City of Birth:', 0, 0);
    $pdf->Cell(0, 5, $val('city_of_birth'), 0, 1);
    $pdf->MultiCell(0, 4, 'Do you speak a language other than English?', 0, 'L');
    $cb((int)$val('lan_spoken') === 2); $pdf->Cell(12, 5, 'No', 0, 0);
    $cb((int)$val('lan_spoken') === 1); $pdf->Cell(12, 5, 'Yes', 0, 0);
    $pdf->Cell(35, 5, 'Language Spoken (at Home):', 0, 0);
    $pdf->Cell(0, 5, $val('lan_spoken_other'), 0, 1);
    $pdf->MultiCell(0, 4, 'Are you Aboriginal and/or Torres Strait Islander? (please tick relevant)', 0, 'L');
    $cb((int)$val('origin') === 1); $pdf->Cell(12, 5, 'No', 0, 0);
    $cb((int)$val('origin') === 2); $pdf->Cell(35, 5, 'Yes, Aboriginal', 0, 0);
    $cb((int)$val('origin') === 3); $pdf->Cell(0, 5, 'Yes, Torres Strait Islander', 0, 1);
    $pdf->Ln(1);

    $sectionBar('Disability: Please see Disability Supplement section');
    $pdf->MultiCell(0, 4, 'Do you live with any disability, impairment, or long-term condition physical/mental disability that may affect your participation in the course?', 0, 'L');
    $cb((int)$val('disability') === 1); $pdf->Cell(45, 5, 'Yes (if yes, tick relevant)', 0, 0);
    $cb((int)$val('disability') === 2); $pdf->Cell(15, 5, 'No', 0, 1);
    $dt = $val('st_disability_type');
    if (is_string($dt)) $dt = @json_decode($dt, true);
    if (!is_array($dt)) $dt = array();
    $disLabels = array('Hearing/Deaf', 'Physical', 'Intellect', 'Medical Condition', 'Mental Illness', 'Acquired brain impairment', 'Learning', 'Vision', 'Other');
    $pdf->Cell(2, 5, '', 0, 0);
    foreach ($disLabels as $i => $l) {
        $cb(in_array((string)$i, $dt));
        $pdf->Cell(28, 5, $l, 0, 0);
    }
    $pdf->Ln(5);
    $pdf->Cell(2, 5, '', 0, 0);
    $pdf->Cell(0, 5, 'Other: ' . $val('disability_type_other'), 0, 1);
    $pdf->Ln(1);

    $pdf->MultiCell(0, 4, 'What is your highest school level COMPLETED? (tick one only)', 0, 'L');
    $hs = (int)$val('highest_school');
    $hsLabels = array(1=>'Year 12 or equivalent', 2=>'Year 11 or equivalent', 3=>'Year 10 or equivalent', 4=>'Year 9 or equivalent', 5=>'Year 8 or below', 6=>'Never attended school');
    foreach ($hsLabels as $i => $l) {
        $cb($hs === $i);
        $pdf->Cell(32, 5, $l, 0, 0);
    }
    $pdf->Ln(5);
    $pdf->MultiCell(0, 4, 'Are you still enrolled in secondary or senior secondary education?', 0, 'L');
    $cb((int)$val('sec_school') === 1); $pdf->Cell(12, 5, 'Yes', 0, 0);
    $cb((int)$val('sec_school') === 2); $pdf->Cell(12, 5, 'No', 0, 1);
    $pdf->Cell(55, 5, 'In which YEAR did you complete the above school level?', 0, 0);
    $pdf->Cell(0, 5, $val('year_completed_school'), 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->MultiCell(0, 4, 'Mode of Delivery (See Course Outline for delivery mode and available durations)', 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $md = $val('mode_delivery');
    $cb($md === 'Classroom'); $pdf->Cell(25, 5, 'Classroom', 0, 0);
    $cb($md === 'Online'); $pdf->Cell(28, 5, 'Online (Virtual)', 0, 0);
    $cb($md === 'Blended'); $pdf->Cell(22, 5, 'Blended', 0, 0);
    $cb($md === 'Workplace'); $pdf->Cell(0, 5, 'Workplace Based', 0, 1);
    $pdf->Cell(45, 5, 'Choose A Qualification:', 0, 0);
    $pdf->Cell(0, 5, $val('courses_display'), 0, 1);

    $drawFooter('1');

    // ---------- PAGE 2 ----------
    $pdf->AddPage();
    $startY = $drawHeaderLogo();
    $pdf->SetXY(12, $startY);

    $sectionBar('Have you successfully completed any of the following qualifications? (Tick most relevant)');
    $qCol1 = array('qual_cert1'=>'Certificate I', 'qual_cert2'=>'Certificate II', 'qual_cert3'=>'Certificate III (Trade Cert)');
    $qCol2 = array('qual_cert4'=>'Certificate IV (or advanced certificate/technician)', 'qual_diploma'=>'Diploma (or associate diploma)', 'qual_adv_diploma'=>'Advanced Diploma/Associate Degree');
    $qCol3 = array('qual_bachelor'=>"Bachelor's degree or Higher", 'qual_other'=>'Other education (not listed above)', 'qual_none'=>'None');
    $qw = 58;
    $qStartY = $pdf->GetY();
    $qK1 = array_keys($qCol1); $qK2 = array_keys($qCol2); $qK3 = array_keys($qCol3);
    for ($row = 0; $row < 3; $row++) {
        $y = $qStartY + $row * 5.5;
        $pdf->SetXY(12, $y);
        $k1 = $qK1[$row]; $l1 = $qCol1[$k1];
        $cbOnly((int)$val($k1) === 1);
        $pdf->Cell($qw - 4, 5, $l1, 0, 0);
        $pdf->SetXY(12 + $qw, $y);
        $k2 = $qK2[$row]; $l2 = $qCol2[$k2];
        $cbOnly((int)$val($k2) === 1);
        $pdf->Cell($qw - 4, 5, $l2, 0, 0);
        $pdf->SetXY(12 + $qw * 2, $y);
        $k3 = $qK3[$row]; $l3 = $qCol3[$k3];
        $cbOnly((int)$val($k3) === 1);
        $pdf->Cell($qw - 4, 5, $l3, 0, 0);
    }
    $pdf->SetXY(12, $qStartY + 16.5);
    $pdf->Ln(18);
    $pdf->Cell(0, 4, 'Attained:', 0, 1);
    $qa = $val('qualification_attained');
    $cb($qa === 'Australia'); $pdf->Cell(45, 5, 'Attained in Australia', 0, 0);
    $cb($qa === 'Equivalent'); $pdf->Cell(45, 5, 'Australian Equivalent', 0, 0);
    $cb($qa === 'International'); $pdf->Cell(0, 5, 'International', 0, 1);
    $pdf->Ln(2);

    $sectionBar('Employment Details (If your employment is not related to this course of study, tick most relevant):');
    $pdf->Cell(0, 4, 'Employment Status:', 0, 1);
    $emp = (int)$val('emp_status');
    $empLabels = array(1=>'Full-time employee', 2=>'Part-time employee', 3=>'Self-employed - not employing others', 4=>'Self-employed - employing others', 5=>'Employed - unpaid worker in a family business', 6=>'Unemployed - seeking full-time work', 7=>'Unemployed - seeking part-time work', 8=>'Unemployed - not seeking employment');
    $ew = 58;
    $empStartY = $pdf->GetY();
    $empKeys = array_keys($empLabels);
    for ($row = 0; $row < 3; $row++) {
        for ($col = 0; $col < 3; $col++) {
            $idx = $row * 3 + $col;
            if ($idx >= count($empLabels)) break;
            $v = $empKeys[$idx];
            $l = $empLabels[$v];
            $pdf->SetXY(12 + $col * $ew, $empStartY + $row * 5.5);
            $cb($emp === $v);
            $pdf->Cell($ew - 5, 5, $l, 0, 0);
        }
    }
    $pdf->SetXY(12, $empStartY + 17);
    $pdf->Ln(5);
    $pdf->Cell(50, 5, 'Industry of Work (Refer ANZSCO codes online):', 0, 0);
    $pdf->Cell(0, 5, $val('industry_of_work'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('Reason for Enrolling in this Course of Study:');
    $pdf->MultiCell(0, 4, 'Of the following categories, which BEST describes your main reason for undertaking this course?', 0, 'L');
    $studyLabels = array(1=>'To get a job', 2=>'To get a better job or promotion', 3=>'It was a requirement for my job', 4=>'I wanted extra skills for my job', 5=>'To start my own business', 6=>'To get into another course of study', 7=>'To try for a different career', 8=>'To develop my existing business', 9=>'For personal interest or self-development', 10=>'To get skills for community/voluntary work', 11=>'Other reasons');
    $sr = (int)$val('study_reason');
    $sw = 58;
    $studyStartY = $pdf->GetY();
    $studyKeys = array_keys($studyLabels);
    for ($row = 0; $row < 4; $row++) {
        for ($col = 0; $col < 3; $col++) {
            $idx = $row * 3 + $col;
            if ($idx >= count($studyLabels)) break;
            $v = $studyKeys[$idx];
            $l = $studyLabels[$v];
            $pdf->SetXY(12 + $col * $sw, $studyStartY + $row * 5.5);
            $cb($sr === $v);
            $pdf->Cell($sw - 5, 5, $l, 0, 0);
        }
    }
    $pdf->SetXY(12, $studyStartY + 22);
    $pdf->Ln(5);
    $pdf->Cell(0, 5, 'Other reasons: ' . $val('study_reason_other'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('Course Enrolment Details: (See Course Outline for delivery mode and available durations)');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Do you want to apply for Credit Transfer (CT) / Recognise Prior Learning?', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'A candidate is required to fill additional form with details for CT/RPL application.', 0, 'L');
    $pdf->SetFont('helvetica', '', 9);
    $cb((int)$val('cred_tansf') === 1); $pdf->Cell(12, 5, 'Yes', 0, 0);
    $cb((int)$val('cred_tansf') === 2); $pdf->Cell(12, 5, 'No', 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Which BEST describes your main reason for undertaking this course? Enter Text Below', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(0, 6, $val('study_reason_other'), 0, 1);
    $pdf->Ln(1);

    $sectionBar('Additional Information: (please answer all questions)');
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Do you have access to a computer and the internet?', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $cb((int)$val('computer_access') === 1); $pdf->Cell(12, 5, 'Yes', 0, 0);
    $cb((int)$val('computer_access') === 2); $pdf->Cell(12, 5, 'No', 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'What level of computer literacy do you have?', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $cbOnly($val('computer_literacy') === 'Excellent'); $pdf->Cell(22, 5, 'Excellent', 0, 0);
    $cbOnly($val('computer_literacy') === 'Good'); $pdf->Cell(15, 5, 'Good', 0, 0);
    $cbOnly($val('computer_literacy') === 'Basic'); $pdf->Cell(15, 5, 'Basic', 0, 0);
    $cbOnly($val('computer_literacy') === 'Poor'); $pdf->Cell(15, 5, 'Poor', 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'How do you rate your numeracy skills?', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $cbOnly($val('numeracy_skills') === 'Excellent'); $pdf->Cell(22, 5, 'Excellent', 0, 0);
    $cbOnly($val('numeracy_skills') === 'Good'); $pdf->Cell(15, 5, 'Good', 0, 0);
    $cbOnly($val('numeracy_skills') === 'Basic'); $pdf->Cell(15, 5, 'Basic', 0, 0);
    $cbOnly($val('numeracy_skills') === 'Poor'); $pdf->Cell(15, 5, 'Poor', 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Do you require additional support?', 0, 1);
    $pdf->SetFont('helvetica', '', 9);
    $cb((int)$val('additional_support') === 1); $pdf->Cell(12, 5, 'No', 0, 0);
    $cb((int)$val('additional_support') === 2); $pdf->Cell(0, 5, 'Yes (please specify: ' . $val('additional_support_specify') . ')', 0, 1);
    $pdf->Ln(1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(0, 5, 'IMPORTANT NOTE:', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->MultiCell(0, 3.5, 'National College Australia, RTO 91000 will provide access to additional support services where required, as described in the Additional Support Policy and Procedures. However, where a student is unable to meet minimum course entry requirements such as corresponding Learning, Literacy and Numeracy Skills and/or Physical Fitness requirements of a course, college reserves the right to defer/terminate enrolment. If you are in doubt, please ask our admin staff.', 0, 'L');

    $drawFooter('2');

    // ---------- PAGE 3 ----------
    $pdf->AddPage();
    $startY = $drawHeaderLogo();
    $pdf->SetXY(12, $startY);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(0, 6, 'UNIQUE STUDENT IDENTIFIER', 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'From 1 January 2015, National College Australia, RTO 91000 can be prevented from issuing you with a nationally recognised VET qualification or statement of attainment when you complete your course if you do not have a Unique Student Identifier (USI). In addition, we are required to include your USI in the data we submit to NCVER. If you have not yet obtained a USI you can apply for it directly at https://www.usi.gov.au/your-usi/create-usi on the computer or mobile device.', 0, 'L');
    $pdf->MultiCell(0, 3.5, "If you don't have a USI number, you can apply for one by going to the USI website: www.usi.gov.au and follow the steps here: https://www.usi.gov.au/your-usi/create-usi For more details, please refer to \"Unique Student Identifier (USI)\"", 0, 'L');
    $pdf->MultiCell(0, 3.5, 'I understand that my results will be uploaded into USI records as per company policy and information will be found online:', 0, 'L');
    $cbOnly((int)$val('usi_declaration') === 1);
    $pdf->Cell(0, 5, 'Yes, I understand and declare', 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(0, 6, 'PRIVACY NOTICE', 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'The NCVER will collect, hold, use, and disclose your personal information in accordance with the law, including the Privacy Act 1988 (Cth) (Privacy Act) and the NCVER Act. Your personal information may be used and disclosed by NCVER for purposes that include populating authenticated VET transcripts; administration of VET; facilitation of statistics and research relating to education, including surveys and data linkage; and understanding the VET market.', 0, 'L');
    $pdf->MultiCell(0, 3.5, 'For more information about how the NCVER will handle your personal information please refer to the NCVER\'s Privacy Policy at www.ncver.edu.au/privacy.', 0, 'L');
    $cbOnly((int)$val('privacy_declaration') === 1);
    $pdf->Cell(0, 5, 'Yes, I understand and declare', 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(0, 0, 128);
    $pdf->Cell(0, 6, 'REFUND POLICY', 0, 1);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'Details of the RTO Fees and Charges / Refund Policy and Refund Policy, Student Handbook can be found on our website.', 0, 'L');
    $cbOnly((int)$val('refund_declaration') === 1);
    $pdf->Cell(0, 5, 'Yes, I understand and declare', 0, 1);
    $pdf->Ln(3);

    $boxY = $pdf->GetY();
    $pdf->SetDrawColor($redR, $redG, $redB);
    $pdf->SetTextColor($redR, $redG, $redB);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Rect(12, $boxY, 186, 38);
    $pdf->SetXY(12, $boxY + 1);
    $pdf->Cell(0, 6, 'Office Use Only:', 0, 1, 'L', false, '', 0, false, 'B');
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Cell(28, 5, 'Student ID #:', 0, 0);
    $pdf->Cell(50, 5, $val('office_student_id'), 0, 0);
    $pdf->Cell(52, 5, 'Enrolment Coordinator/Admin Name:', 0, 0);
    $pdf->Cell(0, 5, $val('office_coordinator_name'), 0, 1);
    $pdf->Ln(2);
    $pdf->Cell(2, 5, '', 0, 0);
    $cbOnly((int)$val('office_invoice_provided') === 1); $pdf->Cell(32, 5, 'Invoice Provided', 0, 0);
    $cbOnly((int)$val('office_receipt_collected') === 1); $pdf->Cell(32, 5, 'Receipt Collected', 0, 0);
    $cbOnly((int)$val('office_lms_access') === 1); $pdf->Cell(32, 5, 'LMS Access Granted', 0, 0);
    $pdf->Ln(5);
    $pdf->Cell(2, 5, '', 0, 0);
    $cbOnly((int)$val('office_resources_access') === 1); $pdf->Cell(32, 5, 'Resources Access', 0, 0);
    $cbOnly((int)$val('office_uploaded_sms') === 1); $pdf->Cell(32, 5, 'Uploaded into SMS', 0, 0);
    $cbOnly((int)$val('office_welcome_pack_sent') === 1); $pdf->Cell(0, 5, 'Welcome Pack Sent:', 0, 1);
    $pdf->SetDrawColor(0, 0, 0);

    $drawFooter('3');

    // ---------- PAGE 4 ----------
    $pdf->AddPage();
    $startY = $drawHeaderLogo();
    $pdf->SetXY(12, $startY);

    $sectionBar('Candidate Declaration:');
    $cbOnly((int)$val('candidate_declaration') === 1);
    $pdf->Cell(0, 5, 'Yes, I understand and declare.', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $bullets = array(
        'Have read the student Handbook including the Privacy Policy, Fee Administration and Refund Policy, and other policies and procedures prior to enrolling.',
        'By signing this enrolment application, I agree to allow and collect National College Australia for Language, Literacy (including digital), Numeracy test, progression, assessment status, and other course information on a periodic basis, during and/or after my enrolment period.',
        'Give my consent to National College Australia to release my name, date of birth, contact details and statistical information, including my USI, to the relevant Federal government bodies for the purpose of auditing, regulation of training, obtaining feedback and as statistical information.',
        'Agree to participate in all mandatory course requirements satisfactorily which include assessments, work placement, practical workshops and be deemed competent before release of a final certificate.',
        'May receive a student survey which may be run by a government department or an NCVER employee, agent, third-party contractor or another authorised agency. Please note you may opt out of the survey at the time of being contacted.',
        'By consent, Photographs may be requested during work placement or during practical demonstrations for the purpose of presenting to the authorised body to demonstrate administration of VET, research relating to education, including surveys and data linkage.',
        'Confirm all the details provided in this form including provision of study rights are true and are presented with the intention of attaining a qualification in accordance with the law, Privacy and NCVER ACT.',
        'Assure that I have been informed about the training, assessment and support services to be provided and on my rights and obligations as a student at National College Australia'
    );
    foreach ($bullets as $b) {
        $pdf->Cell(4, 4, chr(149), 0, 0);
        $pdf->MultiCell(0, 4, $b, 0, 'L');
    }
    $pdf->SetFont('helvetica', '', 9);
    $pdf->Ln(2);
    $pdf->Cell(45, 5, 'Full Name of the Candidate:', 0, 0);
    $pdf->Cell(50, 5, $val('candidate_full_name'), 0, 0);
    $pdf->Cell(25, 5, 'Date:', 0, 0);
    $pdf->Cell(0, 5, $val('candidate_date'), 0, 1);
    $pdf->Cell(45, 5, 'Signature of the Candidate:', 0, 1);
    $pdf->Cell(0, 8, $val('candidate_signature'), 0, 1);
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Disability Categories:', 0, 1);
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(0, 5, 'Introduction:', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->MultiCell(0, 3.5, 'The purpose of the Disability Categorization is to provide additional information to assist with answering the disability question. Disability in this context does not include short-term disabling health conditions such as a fractured leg, influenza, or corrected physical conditions such as impaired vision managed by wearing glasses or lenses.', 0, 'L');
    $pdf->Ln(1);
    $defs = array(
        "'11 - Hearing/deaf'" => 'Hearing impairment is used to refer to a person who has an acquired mild, moderate, severe or profound hearing loss after learning to speak, communicates orally and maximises residual hearing with the assistance of amplification.',
        "'12 - Physical'" => 'A physical disability affects the mobility or dexterity of a person and may include a total or partial loss of a part of the body.',
        "'13 - Intellectual'" => 'In general, the term "intellectual disability" is used to refer to low general intellectual functioning and difficulties in adaptive behaviour, both of which conditions were manifested before the person reached the age of 18.',
        "'14 - Learning'" => 'A general term that refers to a heterogeneous group of disorders manifested by significant difficulties in the acquisition and use of listening, speaking, reading, writing, reasoning, or mathematical abilities.',
        "'15 - Mental illness'" => 'Mental illness refers to a cluster of psychological and physiological symptoms that cause a person suffering or distress and which represent a departure from a person\'s usual pattern and level of functioning.',
        "'16 - Acquired brain impairment'" => 'Acquired brain impairment is injury to the brain that results in deterioration in cognitive, physical, emotional or independent functioning.',
        "'17 - Vision'" => 'This covers a partial loss of sight causing difficulties in seeing, up to and including blindness.',
        "'18 - Medical condition'" => 'Medical condition is a temporary or permanent condition that may be hereditary, genetically acquired or of unknown origin.',
        "'19 - Other'" => 'A disability, impairment or long-term condition which is not suitably described by one or several disability types in combination.'
    );
    foreach ($defs as $title => $desc) {
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->Cell(0, 4, $title, 0, 1);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->MultiCell(0, 3.5, $desc, 0, 'L');
        $pdf->Ln(0.5);
    }

    $drawFooter('4');

    $pdf->Output($savePath, 'F');
    return true;
}
