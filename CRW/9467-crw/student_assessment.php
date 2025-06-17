<?php
include('php/session_management.php'); 
$student_id = $_SESSION['user_id'];
if (!isset($student_id)) {
    header("Location: index.php");
    exit();
}
include('php/student-queries/get_student_assessment_details.php');
include('php/student-queries/get_question_points.php');
$topic_id = $_GET['topic_id'] ?? 1;
$assessment_id = $_GET['assessment_id'] ?? 1; 
$assessment = getAssessmentDetails($assessment_id);
if (!$assessment) {
    die("Assessment not found.");
}
$questions = getAssessmentQuestions($assessment_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/form.css">
    <title><?php echo htmlspecialchars($assessment['title']); ?></title>
</head>
<body>
    <form action="php/student-queries/submit_assessment.php" method="POST">
    <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment_id); ?>">
    <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
    <input type="hidden" name="topic_id" value="<?php echo htmlspecialchars($topic_id); ?>">
        <div id="timer-container">
            <h3 class="time-limit"><span id="time-remaining">Loading...</span></h3>
        </div>
        <img src="img/form_bg.jpg" alt="header-img">
        <div class="main">
            <h1><?php echo htmlspecialchars($assessment['title']); ?></h1>
            <hr/>
            <p>
                At Adal, we prioritize the security and confidentiality of your information. Any data you provide during this assessment,
                including your responses, will only be used for evaluation and improvement of your learning experience.
                Your data will not be shared with third parties, and all submissions are securely stored in compliance with data protection standards. 
                Rest assured, your privacy is our priority.
            </p>
        </div>

        <?php foreach ($questions as $index => $question): ?>
            <?php 
            $question_id = $question['id'];
            $question_type = $question['type'];
            $choices = getChoices($question_id, $question_type);
            $question_points = getQuestionPoints($question_id, $assessment_id);
            ?>
            
            <!-- identification -->
            <?php if ($question_type === 'identification'): ?>
                <div class="identification">
                    <div class="question-header">
                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?> <span style="color: red;">*</span></h3>
                        <div class="question-points">
                            <?php echo $question_points; ?> pts
                        </div>
                    </div>
                    <input type="text" name="answers[<?php echo $question_id; ?>]" required />
                </div>
            
            <!-- alt-response (T or F) -->
            <?php elseif ($question_type === 'alternate-response'): ?>
                <div class="alt-response">
                    <div class="question-header">
                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?> <span style="color: red;">*</span></h3>
                        <div class="question-points">
                            <?php echo $question_points; ?> pts
                        </div>
                    </div>
                    <label>
                        <input type="radio" name="answers[<?php echo $question_id; ?>]" value="True" required /> True
                    </label>
                    <label>
                        <input type="radio" name="answers[<?php echo $question_id; ?>]" value="False" required /> False
                    </label>
                </div>
            
            <!-- multiple-choice -->
            <?php elseif ($question_type === 'multiple-choice'): ?>
                <div class="mc">
                    <div class="question-header">
                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?> <span style="color: red;">*</span></h3>
                        <div class="question-points">
                            <?php echo $question_points; ?> pts
                        </div>
                    </div>
                    <?php foreach ($choices as $choice): ?>
                        <label>
                            <input type="radio" name="answers[<?php echo $question_id; ?>]" value="<?php echo htmlspecialchars($choice['id']); ?>" required />
                            <?php echo htmlspecialchars($choice['text']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="submit">
            <button type="submit">Submit</button>
        </div>
        <p class="policy">
            This web application is created by CRW for their course in 9467-IT-312 | Web Technologies taught by Kasima Mendoza and Brittany Baldovino.
            <h1 class="adal">Adal</h1>
        </p>
    </form>
    <script src="scripts/answer_assessment.js"></script>
</body>
</html>