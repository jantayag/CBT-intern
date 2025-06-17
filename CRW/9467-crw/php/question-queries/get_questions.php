<?php
include('php/db.php');
include('php/objects/question.php');

function getQuestions() {
    global $conn;
    
    $sql = "SELECT * FROM questions WHERE true";
    $params = array();
    $types = "";
    
    // searching
    if (isset($_GET['question-text']) && !empty($_GET['question-text'])) {
        $sql .= " AND question_text LIKE ?";
        $searchTerm = "%" . $_GET['question-text'] . "%";
        $params[] = $searchTerm;
        $types .= "s";
    }
    
    // filtering
    if (isset($_GET['filter']) && $_GET['filter'] !== 'default') {
        $filterValue = $_GET['filter'];
        
        // if filter by type
        if (in_array($filterValue, ['identification', 'multiple-choice', 'alternate-response'])) {
            $sql .= " AND type = ?";
            $params[] = $filterValue;
            $types .= "s";
        }
        // if filter bydifficulty
        elseif (in_array($filterValue, ['easy', 'intermediate', 'advanced'])) {
            $sql .= " AND LOWER(difficulty) = ?";
            $params[] = ucfirst($filterValue);
            $types .= "s";
        }
    }
    
    // sorting
    if (isset($_GET['sort']) && $_GET['sort'] !== 'default') {
        switch ($_GET['sort']) {
            case 'id-asc':
                $sql .= " ORDER BY id ASC";
                break;
            case 'id-desc':
                $sql .= " ORDER BY id DESC";
                break;
        }
    } else {
        $sql .= " ORDER BY id ASC"; 
    }
    

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $questions = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questions[] = new Question(
                $row['id'],
                $row['question_text'],
                $row['difficulty'],
                $row['points'],
                $row['type']
            );
        }
    }
    
    return $questions;
}

function displayQuestionsTable($questions) {
    $currentSearch = htmlspecialchars($_GET['question-text'] ?? '');
    $currentFilter = $_GET['filter'] ?? 'default';
    $currentSort = $_GET['sort'] ?? 'default';
    ?>
    <div class="heading">
        <h1>Question Pool Management</h1>
        <div class="filter-section">
            <form action="" method="get">
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
                    <option value="id-asc" <?php echo $currentSort === 'id-asc' ? 'selected' : ''; ?>>Sort by: ID (ASC)</option>
                    <option value="id-desc" <?php echo $currentSort === 'id-desc' ? 'selected' : ''; ?>>Sort by: ID (DESC)</option>
                </select>
                
                <button type="submit" class="action-btn">Apply Filters</button>
            </form>
            <div class="question-actions">
                <button class="action-btn" onclick="addQuestion()">Add Question</button>
            </div>
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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="questions-tbody">
                        <?php foreach ($questions as $question): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($question->getId()); ?></td>
                                <td><?php echo htmlspecialchars($question->getQuestionText()); ?></td>
                                <td><?php echo htmlspecialchars($question->getDifficulty()); ?></td>
                                <td><?php echo htmlspecialchars($question->getPoints()); ?></td>
                                <td><?php echo htmlspecialchars($question->getType()); ?></td>
                                <td class="action-buttons">
                                    <button class="view-btn" onclick="viewAnswers(<?php echo $question->getId(); ?>)">
                                        View Answer
                                    </button>
                                    <button class="edit-btn" onclick="editQuestion(<?php echo $question->getId(); ?>)">
                                        Edit
                                    </button>
                                    <button class="del-btn" onclick="deleteQuestion(<?php echo $question->getId(); ?>)">
                                        Delete
                                    </button>
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

$questions = getQuestions();
        displayQuestionsTable($questions);
?>