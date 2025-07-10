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
            <img id="return-btn" src="img/return-button.svg" alt="return to topic_assessments page" onclick="history.back()">'">

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
            $student_answer = $student_responses[$question_id]['answer_text'] ?? '';
            $is_correct = $student_responses[$question_id]['is_correct'] ?? false;
            $question_points = getQuestionPoints($question_id, $assessment_id);
            $correct_answer = '';
            
            if ($question_type === 'mc') {
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
                    <?php 
                        $earned = $is_correct ? $question_points : 0;
                        echo "{$earned} / {$question_points} pt" . ($question_points > 1 ? 's' : '');
                    ?>
                </div>
            </div>
            <?php if (!empty($question['image_path'])): ?>
                        <div class="question-image">
                            <img src="<?php echo htmlspecialchars($question['image_path']); ?>" alt="Question Image" style="max-width:100%; margin-top:10px; border-radius:8px;">
                        </div>
                    <?php endif; ?>

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
                
                <?php elseif ($question_type === 'mc'): ?>
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
        
            <h1 class="adal">MyReach</h1>
        </div>
    </form>
    <script src="scripts/assessment_results.js"></script>
</body>
</html>