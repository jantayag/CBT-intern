<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

include 'php/assessment-queries/get_assessment_details.php';
$assessment = null;
$assessment_id = null;
if (isset($_GET['assessment_id'])) {
    $assessment_id = (int)$_GET['assessment_id'];
    $assessment = getAssessmentDetails($assessment_id);
}

if (!$assessment) {
    header('Location: assessments.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Assessment Details of <?php echo htmlspecialchars($assessment['title']) . ' | ID #:' . htmlspecialchars($assessment['id']); ?></title>
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php' ?>
    </section>
    <section id="content">
        <?php include 'includes/nav.php' ?>
        <main id="main">
            <?php
                $questions = getAssessmentQuestions($assessment['id']);
                displayAssessmentQuestions($questions, $assessment);
            ?>
        </main>
    </section>
    <div class="modal2" style="display: none;">
        <div class="modal-answers">
            <!-- where view answers will be displayed  -->
        </div>
    </div>
    <?php include('php/assessment-queries/assessment_questions.php'); ?>
    <script src="scripts/assessment_questions.js"></script>
    <script src="scripts/questions_pagination.js"></script>
    <script src="scripts/pagination.js"></script>
</body>
</html>