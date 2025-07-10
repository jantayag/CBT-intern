<?php
include('php/session_management.php'); 
include('php/db.php');

$student_id = $_SESSION['user_id'];  // ✅ must come before it's used

if (!isset($student_id)) {
    header("Location: index.php");
    exit();
}

$assessment_id = $_GET['assessment_id'] ?? null;

if ($_SESSION['user_type'] === 'Student' && $assessment_id) {
    $stmt = $conn->prepare("SELECT 1 FROM assessment_results WHERE student_id = ? AND assessment_id = ?");
    $stmt->bind_param("ii", $student_id, $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        die("Query error: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        header("Location: answered.php");
        exit;
    }
}

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
        <div class="main">
            <h1><?php echo htmlspecialchars($assessment['title']); ?></h1>
            <hr/>
            <p>
                We prioritize the security and confidentiality of your information. Any data you provide during this assessment,
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
                            <?php echo $question_points; ?> pt/s
                        </div>
                    </div>
                    <?php if (!empty($question['image_path'])): ?>
                        <div class="question-image">
                            <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question Image" style="max-width:100%; margin-top:10px; border-radius:8px;">
                        </div>
                    <?php endif; ?>

                    <input type="text" name="answers[<?php echo $question_id; ?>]" required />
                </div>
            
            <!-- alt-response (T or F) -->
            <?php elseif ($question_type === 'alternate-response'): ?>
                <div class="alt-response">
                    <div class="question-header">
                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?> <span style="color: red;">*</span></h3>
                        <div class="question-points">
                            <?php echo $question_points; ?> pt/s
                        </div>
                    </div>
                    <<?php if (!empty($question['image_path'])): ?>
                        <div class="question-image">
                            <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question Image" style="max-width:100%; margin-top:10px; border-radius:8px;">
                        </div>
                    <?php endif; ?>

                    <label>
                        <input type="radio" name="answers[<?php echo $question_id; ?>]" value="True" required /> True
                    </label>
                    <label>
                        <input type="radio" name="answers[<?php echo $question_id; ?>]" value="False" required /> False
                    </label>
                </div>
            
            <!-- multiple-choice -->
            <?php elseif ($question_type === 'mc'): ?>
                <div class="mc">
                    <div class="question-header">
                        <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?> <span style="color: red;">*</span></h3>
                        <div class="question-points">
                            <?php echo $question_points; ?> pt/s
                        </div>
                    </div>
                    <?php if (!empty($question['image_path'])): ?>
                        <div class="question-image">
                            <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question Image" style="max-width:100%; margin-top:10px; border-radius:8px;">
                        </div>
                    <?php endif; ?>

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
            <h1 class="adal">MyReach</h1>
        </p>
    </form>
    <script src="scripts/answer_assessment.js"></script>
</body>
</html>