<?php
include('php/session_management.php');

include('php/student-queries/get_assessment_results.php');
include('php/student-queries/get_question_points.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/form.css">
    <title>Assessment Results: <?php echo htmlspecialchars($assessment['title']); ?></title>
</head>
<body>
    <form>
        <div class="timer-container">
            <h3 class="time-limit">Score:
            <?php echo $score; ?> / <?php echo $total_score; ?>
            (<?php echo round(($score / $total_score) * 100, 1); ?>%)
            </h3>
            <img id="return-btn" src="img/return-button.svg" alt="return to topic_assessments page" onclick="window.location.href='topic_assessments.php?topic_id=<?php echo htmlspecialchars($topic_id); ?>'">

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
            $student_answer = $student_responses[$question_id]['answer_text'] ?? '';
            $is_correct = $student_responses[$question_id]['is_correct'] ?? false;
            $question_points = getQuestionPoints($question_id, $assessment_id);
            $correct_answer = '';
            
            if ($question_type === 'multiple-choice') {
                foreach ($choices as $choice) {
                    if ($choice['is_answer'] === 'Y') {
                        $correct_answer = $choice['text'];
                        break;
                    }
                }
            } elseif ($question_type === 'identification') {
                $correct_answer = getIdentificationAns($question_id);
            } else if ($question_type === 'alternate-response') {
                foreach ($choices as $choice) {
                    if ($choice['is_answer'] === 'Y') {
                        $correct_answer = $choice['text'];
                        break;
                    }
                }
            }
            ?>
            
            <div class="<?php echo $question_type; ?> <?php echo $is_correct ? 'correct-answer' : 'wrong-answer'; ?>">
            <div class="question-header">
                <h3><?php echo ($index + 1) . ". " . htmlspecialchars($question['question_text']); ?></h3>
                <div class="question-points">
                    <?php echo $question_points; ?> pts
                </div>
            </div>
                <?php if ($question_type === 'identification'): ?>
                    <input type="text" value="<?php echo htmlspecialchars($student_answer); ?>" readonly />
                    <?php if (!$is_correct): ?>
                        <div class="correct-choice">Correct answer: <?php echo htmlspecialchars($correct_answer); ?></div>
                    <?php endif; ?>
                
                <?php elseif ($question_type === 'alternate-response'): ?>
                    <label class="disabled">
                        <input type="radio" <?php echo $student_answer === 'True' ? 'checked' : ''; ?> disabled /> True
                    </label>
                    <label class="disabled">
                        <input type="radio" <?php echo $student_answer === 'False' ? 'checked' : ''; ?> disabled /> False
                    </label>
                    <?php if (!$is_correct): ?>
                        <div class="correct-choice">Correct answer: <?php echo htmlspecialchars($correct_answer); ?></div>
                    <?php endif; ?>
                
                <?php elseif ($question_type === 'multiple-choice'): ?>
                    <?php foreach ($choices as $choice): ?>
                        <label class="disabled <?php 
                            if ($choice['is_answer'] === 'Y') {
                                echo 'correct-choice'; 
                            } elseif ($student_answer == $choice['id']) {
                                echo 'wrong-choice'; 
                            }
                        ?>">
                            <input type="radio" 
                                <?php echo $student_answer == $choice['id'] ? 'checked' : ''; ?> 
                                disabled />
                            <?php echo htmlspecialchars($choice['text']); ?>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="policy">
            <p>This web application is created by CRW for their course in 9467-IT-312 | Web Technologies taught by Kasima Mendoza and Brittany Baldovino.</p>
            <h1 class="adal">Adal</h1>
        </div>
    </form>
    <script src="scripts/assessment_results.js"></script>
</body>
</html>