<?php
include('php/session_management.php'); 
$student_id = $_SESSION['user_id'];
include('php/class-queries/get_topic_assessments.php');
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;
$topic = getTopicDetails($topic_id);
$assessments = getTopicAssessments($topic_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/tabs.css">
    <title>Assessments of Topic: <?php echo htmlspecialchars($topic['topic']); ?></title>
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php' ?>
    </section>
    <section id="content">
        <?php include 'includes/nav.php' ?>
        <main id="main">
            <div class="heading2">
                <h1><?php echo htmlspecialchars($topic['topic']); ?></h1> <!-- Assessments for Topic:  -->
                <img id="return-btn" src="img/return-button.svg" alt="return to class page" onclick="history.back()">
            </div>
            <section>
            <input type="hidden" id="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
                <?php displayTopicAssessments($assessments, $topic); ?>
            </section>
        </main>
    </section>
    <!-- edit assessment modal -->
    <div class="modal" id="assessmentModal" style="display: none;">
    <div class="modal-content">
        <form action="php/class-queries/edit_topic_assessment.php" method="post" id="assessmentForm">
            <h2 class="question-form-heading">Edit Evaluation Period</h2>

            <div class="form-group">
                <label for="evaluation_start">Start Date and Time:</label>
                <input type="datetime-local" name="evaluation_start" id="evaluation_start"> 
            </div>

            <div class="form-group">
                <label for="evaluation_end">End Date and Time:</label>
                <input type="datetime-local" name="evaluation_end" id="evaluation_end"> 
            </div>
            <div class="form-group">
                <label>Students Can View Results?</label>
                <div class="radio-buttons">
                    <label for="can_view_yes">Yes</label>
                    <input type="radio" id="can_view_yes" name="can_view" value="True" />
                    <label for="can_view_no">No</label>
                    <input type="radio" id="can_view_no" name="can_view" value="False" />
                </div>
            </div>
            <div class="form-actions">
                <input class="save-btn" type="submit" value="Save"/>
                <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>


    <script src="scripts/topic_assessments.js"></script>
</body>
</html>