<?php include('php/session_management.php'); ?>
<?php
    include 'php/assessment-queries/get_assessment_details.php';
    if (!isset($_GET['assessment_id'])) {
        header('Location: assessments.php');
        exit();
    }

    $assessment_id = (int)$_GET['assessment_id'];
    $assessment = getAssessmentDetails($assessment_id);

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
    <title>Assessment: <?php echo htmlspecialchars($assessment['title']) . ' | ID #:' . htmlspecialchars($assessment['id']); ?></title>
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </section>
    <section id="content">
        <?php include 'includes/nav.php'; ?>
        <main id="main">
            <?php
                include "php/assessment-queries/assessment_questions.php";
                $questions = getAssessmentQuestions($assessment['id']);
                displayAssessmentQuestions($questions, $assessment);
            ?>
        </main>
    </section>

    

    <!-- add/edit question form -->
    <div class="modal" style="display: none;">
        <div class="modal-content">
            <form action="php/question-queries/add_question.php" method="post" id="questionForm" enctype="multipart/form-data">
                <h2 class="question-form-heading">Add New Question</h2>
                    <div class="form-group">
                        <label for="question_text">Question:</label>
                        <textarea name="question_text" id="question_text" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="difficulty">Difficulty:</label>
                        <select name="difficulty" id="difficulty">
                            <option value="">Select difficulty</option>
                            <option value="Easy">Easy</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                    </div>

                <div class="form-group">
                    <label for="points">Points:</label>
                    <input type="number" name="points" id="points" min="0" value="1" required />
                </div>

                    <div class="form-group">
                        <label for="type">Type:</label>
                        <select name="type" id="type">
                            <option value="">Select type</option>
                            <option value="alternate-response">Alternate-Response</option>
                            <option value="mc">Multiple-Choice</option>
                            <option value="identification">Identification</option>
                        </select>
                    </div>

            <!-- alternate-response -->
                    <div id="alternate-response" class="answer-container" style="display: none;">
                        <label>Answer:</label>
                        <input type="radio" name="answer" value="True" id="trueAnswer" />
                        <label for="trueAnswer">True</label>
                        <input type="radio" name="answer" value="False" id="falseAnswer" />
                        <label for="falseAnswer">False</label>
                    </div>

            <!-- multiple-choice -->
                    <div id="mc" class="answer-container" style="display: none;">
                        <label>Choices:</label>
                        <div id="choices-container">
                            <div class="choice">
                                <input type="text" name="choice[]" placeholder="Choice 1" />
                                <input type="radio" name="correctChoice" value="0" /> Correct
                                <button class="del-btn" type="button" onclick="removeChoice(this)">Remove</button>
                            </div>
                        </div>
                        <button class="view-btn" type="button" onclick="addChoice()">Add another choice</button>
                    </div>

            <!-- identification -->
                    <div id="identification" class="answer-container" style="display: none;">
                        <label for="identificationAnswer">Answer:</label>
                        <input type="text" name="identificationAnswer" id="identificationAnswer" />
                    </div>
                    <div class="form-group">
                        <div id="drag-drop-area" class="drag-drop-container">
                            <input type="file" id="csv-upload" name="csv-upload" accept=".csv" style="display: none;">
                            <p>Drag and Drop CSV File or Click to Select</p>
                            <small>CSV file should contain question, difficulty, type, answer, and other choices (for multiple-choice)</small>
                            <p id="file-name" class="file-name"></p>
                        </div>
                    </div>
                    <div class="form-actions">
                        <input class="view-btn" type="submit" value="Add" />
                        <input class="save-btn" type="submit" value="Save"/>
                        <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
                    </div>

                    <div class="form-group">
                        <label for="question_image">Image (optional):</label>
                        <input type="file" name="question_image" id="question_image" accept="image/*">
                        <p id="selected-image-name" style="font-size: 0.9rem; margin-top: 5px; color: #555;"></p>
                    </div>
            </form>
        </div>
    </div>

    <div id="questionSelectionModal" class="modal" style="display:none;">
  <div class="modal-content">
    <span class="close" onclick="closeQuestionSelection()">&times;</span>
    <h2>Select Questions to Add</h2>
    <form id="questionSelectionForm">
      <input type="hidden" id="assessment_id" name="assessment_id" value="">
      <!-- Populate question list dynamically here, or via AJAX -->
      <div id="question-list-container">
        <!-- example: checkboxes for available questions -->
      </div>
      <button type="submit">Add Selected Questions</button>
    </form>
  </div>
</div>

<script>
  function showQuestionSelection(assessmentId) {
    document.getElementById('assessment_id').value = assessmentId;
    document.getElementById('questionSelectionModal').style.display = 'block';

    // Optionally, load available questions here dynamically if needed
  }

  function closeQuestionSelection() {
    document.getElementById('questionSelectionModal').style.display = 'none';
  }

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('questionSelectionForm');
    if (!form) return;

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      const formData = new FormData(form);

      fetch('php/assessment-queries/add_assessment_questions.php', {
        method: 'POST',
        body: formData,
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert(data.message);
          closeQuestionSelection();
          location.reload();
        } else {
          alert('Error adding questions: ' + data.message);
        }
      })
      .catch(err => {
        console.error(err);
        alert('Error submitting form');
      });
    });
  });
</script>

    <div class="modal2" style="display: none;">
        <div class="modal-answers">
            <!-- where view answers will be displayed  -->
        </div>
    </div>

    <script src="scripts/question_form.js"></script>
    <script src="scripts/questions_pagination.js"></script>
    <script src="scripts/pagination.js"></script>
    <script src="scripts/csv.js"></script>
</body>
</html>
