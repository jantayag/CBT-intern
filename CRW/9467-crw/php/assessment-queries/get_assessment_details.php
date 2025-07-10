<?php
include('php/db.php');

function getAssessmentDetails($assessment_id) {
    global $conn;
    $sql = 'SELECT * FROM assessments WHERE id = ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getAssessmentQuestions($assessment_id) {
    global $conn;

    $sql = "SELECT q.id, q.question_text, q.difficulty, q.points, q.type
            FROM assessment_questions aq
            INNER JOIN questions q ON aq.question_id = q.id
            WHERE aq.assessment_id = ?";
    
    $params = [$assessment_id];
    $types = "i";
    
    if (isset($_GET['question-text']) && !empty($_GET['question-text'])) {
        $sql .= " AND q.question_text LIKE ?";
        $params[] = "%" . $_GET['question-text'] . "%";
        $types .= "s";
    }
    
    if (isset($_GET['filter']) && $_GET['filter'] !== 'default') {
        switch ($_GET['filter']) {
            case 'identification':
            case 'multiple-choice':
            case 'alternate-response':
                $sql .= " AND q.type = ?";
                $params[] = $_GET['filter'];
                $types .= "s";
                break;
            case 'easy':
            case 'intermediate':
            case 'advanced':
                $sql .= " AND q.difficulty = ?";
                $params[] = $_GET['filter'];
                $types .= "s";
                break;
        }
    }

    if (isset($_GET['sort']) && $_GET['sort'] !== 'default') {
        switch ($_GET['sort']) {
            case 'point-asc':
                $sql .= " ORDER BY q.points ASC";
                break;
            case 'point-desc':
                $sql .= " ORDER BY q.points DESC";
                break;
        }
    } 
    
    $stmt = $conn->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

function getCorrectAnswer($question_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT text FROM choices WHERE question_id = ? AND is_answer = 'Y' LIMIT 1");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $answer = $result->fetch_assoc();
    return $answer['text'] ?? 'N/A';
}

function displayAssessmentQuestions($questions, $assessment) {
    $currentSearch = htmlspecialchars($_GET['question-text'] ?? '');
    $currentFilter = $_GET['filter'] ?? 'default';
    $currentSort = $_GET['sort'] ?? 'default';
    ?>
    <div class="heading">
    <h1><?php  echo htmlspecialchars($assessment['title']); ?></h1>
        <div class="filter-section">
            <form action="" method="get">
                <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($assessment['id']); ?>">
                <input type="text" class="search-bar" name="question-text" 
                       placeholder="Search questions..." value="<?php echo $currentSearch; ?>">
                
                <select name="filter" id="sortnfilter">
                    <option value="default" <?php echo $currentFilter === 'default' ? 'selected' : ''; ?>>Filter by: Default</option>
                    <option value="identification" <?php echo $currentFilter === 'identification' ? 'selected' : ''; ?>>Filter by: Type (identification)</option>
                    <option value="multiple-choice" <?php echo $currentFilter === 'multiple-choice' ? 'selected' : ''; ?>>Filter by: Type (multiple-choice)</option>
                    <option value="alternate-response" <?php echo $currentFilter === 'alternate-response' ? 'selected' : ''; ?>>Filter by: Type (alternate-response)</option>
                    <option value="easy" <?php echo $currentFilter === 'easy' ? 'selected' : ''; ?>>Filter by: Difficulty (Easy)</option>
                    <option value="intermediate" <?php echo $currentFilter === 'intermediate' ? 'selected' : ''; ?>>Filter by: Difficulty (Intermediate)</option>
                    <option value="advanced" <?php echo $currentFilter === 'advanced' ? 'selected' : ''; ?>>Filter by: Difficulty (Advanced)</option>
                </select>
                <select name="sort" id="sortnfilter">
                    <option value="default" <?php echo $currentSort === 'default' ? 'selected' : ''; ?>>Sort by: Default</option>
                    <option value="point-asc" <?php echo $currentSort === 'point-asc' ? 'selected' : ''; ?>>Sort by: Points (ASC)</option>
                    <option value="point-desc" <?php echo $currentSort === 'point-desc' ? 'selected' : ''; ?>>Sort by: Points (DESC)</option>
                </select>
                <button type="submit" class="action-btn">Apply Filters</button>
            </form>
            <div class="question-actions">
                <button class="action-btn" onclick="showQuestionSelection(<?php echo $assessment['id']; ?>)">Add Questions</button>
            </div>
            <img id="return-btn-v2" src="img/return-button.svg" 
                 alt="return to previous page" 
                 onclick="handleReturn()">
        </div>
    </div>
    
    <?php if (!empty($questions)): ?>
        <div id="questions-container">
            <div class="table-responsive">
                <table id="questionsTable">
                    <thead>
                        <tr>
                           <th>ID</th>
                            <th>Question</th>
                            <th>Difficulty</th>
                            <th>Points</th>
                            <th>Type</th>
                            <th>Answer</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="questions-tbody">
                        <?php foreach ($questions as $question): ?>
                            <tr data-question-id="<?php echo $question['id']; ?>">
                                <td><?php echo htmlspecialchars($question['id']); ?></td>
                                <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                                <td><?php echo htmlspecialchars($question['difficulty']); ?></td>
                                <td><?php echo htmlspecialchars($question['points']); ?></td>
                                <td><?php echo htmlspecialchars($question['type']); ?></td>
                                <td><?php echo htmlspecialchars(getCorrectAnswer($question['id'])); ?></td>
                                <td>
                                    <?php
                                        $imagePath = isset($question['image']) ? $question['image'] : '';
                                        echo $imagePath ? 'Yes' : 'None';
                                    ?>
                                </td>
                                <td class="action-buttons">
                                    <button class="edit-btn" onclick="editQuestion(<?php echo $question['id']; ?>)">Edit</button>
                                    <button class="del-btn" onclick="deleteQuestion(<?php echo $question['id']; ?>)">Delete</button>
                                </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="no-questions">No questions found.</div>
    <?php endif; ?>
    
<?php

}
?>