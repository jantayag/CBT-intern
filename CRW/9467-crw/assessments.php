<?php
include('php/session_management.php'); 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}
include "php/assessment-queries/get_assessments.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="styles/styles.css">
    <title>Assessment Manager</title>
</head>
<body>
<section id="sidebar">
    <?php include 'includes/sidebar.php' ?>
    </section>
    <section id="content">
    <?php include 'includes/nav.php' ?>
    <main id="main">
        <div class="heading">
        <h1>Assessment Management</h1>
        <div class="filter-section">
                    <form>
                        <input type="text" class="search-bar" placeholder="Search assessment...">
                    </form>
                    <div class="question-actions">
                        <button class="action-btn" onclick="showAssessmentCreator()">Create Assessment</button>
                    </div>
                </div>
        </div>
        <?php $assessments = getAssessments();
        displayAssessments($assessments); ?>
    </main>
    </section>
    <div class="modal" id="assessmentModal" style="display: none;">
    <div class="modal-content">
        <form method="post" id="assessmentForm">
            <h2 class="question-form-heading">Create New Assessment</h2>
            
            <div class="form-group">
                <label for="title">Assessment Title:</label>
                <textarea name="title" id="title" rows="2" required></textarea> 
            </div>
            <input type="hidden" name="assessment_id" id="assessment_id">
            <div class="form-actions">
                <input class="save-btn" type="submit" value="Save"/>
                <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
            </div>
        </form>
    </div>
</div>
    <script src="scripts/assessment_form.js"></script>
    <script src="scripts/pagination.js"></script>
</body>
</html>