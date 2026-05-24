<?php
include('includes/dbconnect.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] === '') {
    header('Location: student_login.php');
    exit;
}
$ut = @$_SESSION['user_type'];
if ($ut !== 0 && $ut !== 'student') {
    header('Location: dashboard.php');
    exit;
}

$student_user_id = intval($_SESSION['user_id']);
$student_name    = $_SESSION['user_name'] ?? '';

// Fetch student email
$student_email = '';
$suRes = mysqli_query($connection, "SELECT email, full_name FROM student_users WHERE id = $student_user_id LIMIT 1");
if ($suRes && mysqli_num_rows($suRes) > 0) {
    $su = mysqli_fetch_assoc($suRes);
    $student_email = $su['email'];
    $student_name  = $su['full_name'] ?: $student_name;
}

// Fetch previous feedbacks submitted by this student
$feedbacks = [];
$fbRes = mysqli_query($connection,
    "SELECT * FROM student_feedback WHERE student_user_id = $student_user_id ORDER BY created_at DESC"
);
if ($fbRes) {
    while ($row = mysqli_fetch_assoc($fbRes)) $feedbacks[] = $row;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Feedback – National College Australia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <?php include('includes/app_includes.php'); ?>
    <style>
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: flex-end; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 1.8rem; color: #ccc; cursor: pointer; padding: 0 2px; transition: color 0.15s; }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #ffc107; }
        .star-display { color: #ffc107; font-size: 1.1rem; }
        .star-display .empty { color: #ddd; }
    </style>
</head>
<body data-topbar="colored">
<div class="main-wrapper">
    <?php include('includes/header.php'); ?>
    <?php include('includes/sidebar.php'); ?>

    <div class="page-wrapper">
        <div class="content pb-0">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-12">
                        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                            <h4 class="mb-sm-0">Feedback</h4>
                            <div class="page-title-right">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item active">Feedback</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feedback Form -->
                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="ti ti-message-star me-2"></i>Share Your Feedback</h5>
                            </div>
                            <div class="card-body">
                                <form id="feedbackForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Category</label>
                                        <select class="form-select" name="category" id="fb_category" required>
                                            <option value="">-- Select Category --</option>
                                            <option value="Course Content">Course Content</option>
                                            <option value="Trainer/Assessor">Trainer / Assessor</option>
                                            <option value="Support Services">Support Services</option>
                                            <option value="Facilities">Facilities</option>
                                            <option value="Administration">Administration</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Overall Rating</label>
                                        <div class="star-rating" id="starRating">
                                            <input type="radio" name="rating" id="star5" value="5"><label for="star5" title="5 stars">&#9733;</label>
                                            <input type="radio" name="rating" id="star4" value="4"><label for="star4" title="4 stars">&#9733;</label>
                                            <input type="radio" name="rating" id="star3" value="3"><label for="star3" title="3 stars">&#9733;</label>
                                            <input type="radio" name="rating" id="star2" value="2"><label for="star2" title="2 stars">&#9733;</label>
                                            <input type="radio" name="rating" id="star1" value="1"><label for="star1" title="1 star">&#9733;</label>
                                        </div>
                                        <small class="text-muted">Click to rate (1 = Poor, 5 = Excellent)</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Subject</label>
                                        <input type="text" class="form-control" name="subject" id="fb_subject" placeholder="Brief subject of your feedback" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Your Feedback <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="message" id="fb_message" rows="4" placeholder="Please share your experience, suggestions, or concerns..." required></textarea>
                                    </div>

                                    <div id="fb_status" class="mb-2"></div>
                                    <button type="submit" class="btn btn-primary" id="fb_submit_btn">
                                        <i class="ti ti-send me-1"></i>Submit Feedback
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body text-center d-flex flex-column justify-content-center">
                                <i class="ti ti-heart-handshake fs-1 text-primary mb-3"></i>
                                <h5>Your Opinion Matters</h5>
                                <p class="text-muted small mb-0">Help us improve by sharing your honest feedback. All submissions are reviewed by our team and used to enhance the learning experience.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Previous Feedbacks -->
                <div class="row">
                    <div class="col-12">
                        <h5 class="mb-3">My Previous Feedback</h5>
                        <?php if (count($feedbacks) === 0): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-message-off fs-1 d-block mb-2"></i>
                            You haven't submitted any feedback yet.
                        </div>
                        <?php else: ?>
                        <div class="row">
                            <?php foreach ($feedbacks as $fb): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-primary me-1"><?php echo htmlspecialchars($fb['category']); ?></span>
                                                <strong><?php echo htmlspecialchars($fb['subject']); ?></strong>
                                            </div>
                                            <small class="text-muted text-nowrap"><?php echo date('d/m/Y', strtotime($fb['created_at'])); ?></small>
                                        </div>
                                        <div class="mb-2 star-display">
                                            <?php
                                            $r = intval($fb['rating']);
                                            for ($i = 1; $i <= 5; $i++) echo $i <= $r ? '&#9733;' : '<span class="empty">&#9733;</span>';
                                            ?>
                                        </div>
                                        <p class="mb-0 small" style="white-space:pre-wrap;"><?php echo htmlspecialchars($fb['message']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="rightbar-overlay"></div>
<?php include('includes/footer_includes.php'); ?>
<script>
$('#feedbackForm').on('submit', function(e){
    e.preventDefault();
    var $btn    = $('#fb_submit_btn');
    var $status = $('#fb_status');
    var category = $('#fb_category').val();
    var subject  = $('#fb_subject').val().trim();
    var message  = $('#fb_message').val().trim();
    var rating   = $('input[name="rating"]:checked').val() || 0;

    if (!category) { $status.html('<div class="alert alert-danger py-2">Please select a category.</div>'); return; }
    if (!subject)  { $status.html('<div class="alert alert-danger py-2">Please enter a subject.</div>'); return; }
    if (!message)  { $status.html('<div class="alert alert-danger py-2">Please enter your feedback.</div>'); return; }

    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Submitting…');
    $status.html('');

    $.ajax({
        url: 'includes/datacontrol',
        type: 'POST',
        data: { action: 'submit_student_feedback', category: category, subject: subject, message: message, rating: rating },
        dataType: 'json',
        success: function(res){
            if (res.success) {
                $status.html('<div class="alert alert-success py-2"><i class="ti ti-circle-check me-1"></i>Thank you! Your feedback has been submitted.</div>');
                $('#feedbackForm')[0].reset();
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                $status.html('<div class="alert alert-danger py-2">' + (res.message || 'Error submitting feedback.') + '</div>');
                $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i>Submit Feedback');
            }
        },
        error: function(){
            $status.html('<div class="alert alert-danger py-2">Request failed. Please try again.</div>');
            $btn.prop('disabled', false).html('<i class="ti ti-send me-1"></i>Submit Feedback');
        }
    });
});
</script>
</body>
</html>
