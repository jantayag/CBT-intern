<?php
include('php/db.php');  

function getTopicDetails($topic_id) {
    global $conn;

    $sql = "SELECT t.*, c.class_code 
            FROM topics t 
            LEFT JOIN classes c ON t.class_code = c.class_code 
            WHERE t.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

function getTopicAssessments($topic_id) {
    global $conn;
    
    $is_student = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'Student';
    $student_id = $is_student ? $_SESSION['user_id'] : null;

    $sql = "SELECT 
        a.id AS assessment_id, 
        a.title, 
        a.date_created,
        COALESCE(ta.is_published, 0) as is_published, 
        COALESCE(ta.can_view, 0) as can_view, 
        ta.evaluation_start, 
        ta.evaluation_end,
        ta.is_removed";

    if ($is_student) {
        $sql .= ", (SELECT COUNT(*) > 0 
                   FROM assessment_results ar 
                   WHERE ar.assessment_id = a.id 
                   AND ar.topic_id = ta.topic_id 
                   AND ar.student_id = ?) as has_results";
    }

    $sql .= " FROM assessments a 
              JOIN topic_assessments ta ON a.id = ta.assessment_id 
              WHERE ta.topic_id = ?";
              
    if (!$is_student) {
        $sql .= " AND (ta.is_removed = FALSE OR ta.is_removed IS NULL)";
    } else {
        $sql .= " AND (ta.is_removed = FALSE OR (ta.is_removed = TRUE AND EXISTS (
            SELECT 1 FROM assessment_results ar 
            WHERE ar.assessment_id = a.id 
            AND ar.topic_id = ta.topic_id 
            AND ar.student_id = ?
        )))";
    }
    
    $sql .= " ORDER BY a.date_created DESC";

    $stmt = $conn->prepare($sql);
    
    if ($is_student) {
        $stmt->bind_param('iii', $student_id, $topic_id, $student_id);
    } else {
        $stmt->bind_param('i', $topic_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    $assessments = [];
    while ($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }

    return $assessments;
}

function displayTopicAssessments($assessments, $topic) {
    if (empty($assessments)) {
        echo "<h1>No assessments found.</h1>";
        return;
    }
    ?>
    <div id="assessments-container">
        <div class="table-responsive">
            <table id="assessmentsTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Published</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>View Results</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assessments-tbody">
                    <?php foreach ($assessments as $assessment): ?>
                        <tr id="assessment-<?php echo htmlspecialchars($assessment['assessment_id']); ?>" 
                            data-can-view="<?php echo $assessment['can_view'] ? 'true' : 'false'; ?>"
                            data-removed="<?php echo isset($assessment['is_removed']) && $assessment['is_removed'] ? 'true' : 'false'; ?>"
                            <?php echo isset($assessment['is_removed']) && $assessment['is_removed'] ? 'class="removed-assessment"' : ''; ?>>
                            <td><?php echo htmlspecialchars($assessment['assessment_id']); ?></td>
                            <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                            <td class="is-published"><?php echo $assessment['is_published'] ? 'Yes' : 'No'; ?></td>
                            
                            <td class="evaluation-start">
                                <?php echo $assessment['evaluation_start'] 
                                    ? date('M d, Y h:i A', strtotime($assessment['evaluation_start'])) 
                                    : 'Not Set'; 
                                ?>
                            </td>
                            <td class="evaluation-end">
                                <?php echo $assessment['evaluation_end'] 
                                    ? date('M d, Y h:i A', strtotime($assessment['evaluation_end'])) 
                                    : 'Not Set'; 
                                ?>
                            </td>
                            <td class="can-view"><?php echo $assessment['can_view'] ? 'Yes' : 'No'; ?></td>
                            
                            <td class="action-buttons">
                                <button class="view-btn" onclick="window.location.href='assessment.php?assessment_id=<?php echo htmlspecialchars($assessment['assessment_id']); ?>'">
                                    View
                                </button>
                                <?php if (!isset($assessment['is_removed']) || !$assessment['is_removed']): ?>
                                    <button class="edit-btn" onclick="showEditTopicAssessmentModal(<?php echo htmlspecialchars($assessment['assessment_id']); ?>, <?php echo htmlspecialchars($topic['id']); ?>)">
                                        Publish
                                    </button>

                                    <button class="unpublish-btn" style="display: none;" onclick="unpublishAssessment(<?php echo $assessment['assessment_id']; ?>, <?php echo htmlspecialchars($topic['id']); ?>)">
                                        Unpublish
                                    </button>
                                    <button class="del-btn" onclick="removeAssessment(<?php echo htmlspecialchars($assessment['assessment_id']); ?>, <?php echo htmlspecialchars($topic['id']); ?>)">
                                        Remove
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>