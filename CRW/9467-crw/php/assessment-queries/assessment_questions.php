<?php
include('php/db.php');
include('php/objects/question.php');

function getQuestions($assessment_id) {
    global $conn;
    $sql = "SELECT * FROM `questions` WHERE 
            `id` NOT IN (SELECT `question_id` FROM `assessment_questions` WHERE `assessment_id` = ?)";
    
    $params = [$assessment_id];
    $types = "i"; 

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

function displayQuestionSelectionTable($questions) {
    ?>   
    <div class="modal" id="questionSelectionModal">
        <div class="modal-content wide-modal">
            <h2>Select Questions for Assessment</h2>
            <div class="filter-section">
                <form id="filterForm">
                    <input type="text" class="search-bar" name="qa-text" 
                        placeholder="Search questions..." value="">
                    
                    <select name="filter" id="sortnfilter">
                        <option value="default">Filter by: Default</option>
                        <option value="identification">Filter by: Type (identification)</option>
                        <option value="multiple-choice">Filter by: Type (multiple-choice)</option>
                        <option value="alternate-response">Filter by: Type (alternate-response)</option>
                        <option value="easy">Filter by: Difficulty (Easy)</option>
                        <option value="intermediate">Filter by: Difficulty (Intermediate)</option>
                        <option value="advanced">Filter by: Difficulty (Advanced)</option>
                    </select>
                    
                    <select name="sort" id="sortnfilter">
                        <option value="default">Sort by: Default</option>
                        <option value="id-asc">Sort by: ID (ASC)</option>
                        <option value="id-desc">Sort by: ID (DESC)</option>
                    </select>
                    
                    <button type="submit" class="action-btn">Apply Filters</button>
                </form>
            </div>
            <form id="questionSelectionForm" method="post">
                <input type="hidden" name="assessment_id" id="assessment_id" value="">
                <div id="qa-container">
                    <div class="qatable-responsive">
                        <table id="qaTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Question</th>
                                    <th>Difficulty</th>
                                    <th>Points</th>
                                    <th>Type</th>
                                    <th>Select</th>
                                </tr>
                            </thead>
                            <tbody id="qa-tbody">
                                <?php foreach ($questions as $question): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($question->getId()); ?></td>
                                        <td><?php echo htmlspecialchars($question->getQuestionText()); ?></td>
                                        <td><?php echo htmlspecialchars($question->getDifficulty()); ?></td>
                                        <td><?php echo htmlspecialchars($question->getPoints()); ?></td>
                                        <td><?php echo htmlspecialchars($question->getType()); ?></td>
                                        <td>
                                            <input type="checkbox" name="selected_questions[]" 
                                                value="<?php echo $question->getId(); ?>" 
                                                class="question-checkbox">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>                      
                <div class="form-actions">
                    <button type="submit" class="save-btn">Confirm</button>
                    <button type="button" class="del-btn" onclick="closeQuestionSelection()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}
$questions = getQuestions($assessment_id);
displayQuestionSelectionTable($questions);
?>