<?php
include('php/db.php');

function getAssessments() {
    global $conn;

    $sql = "SELECT id, title, date_created FROM assessments";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

function displayAssessments($assessments) {
    if (empty($assessments)) {
        echo "<div class='no-assessments'>No assessments found.</div>";
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
                        <th>Date Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="assessments-tbody">
                    <?php foreach ($assessments as $assessment): ?>
                        <tr id="assessment-<?php echo htmlspecialchars($assessment['id']); ?>">
                            <td><?php echo htmlspecialchars($assessment['id']); ?></td>
                            <td><?php echo htmlspecialchars($assessment['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($assessment['date_created'])); ?></td>
                            <td class="action-buttons">
                                <button class="view-btn" onclick="window.location.href='assessment.php?assessment_id=<?php echo htmlspecialchars($assessment['id']); ?>'">
                                    View
                                </button>
                                <button class="edit-btn" onclick="editAssessment(<?php echo htmlspecialchars($assessment['id']); ?>)">
                                    Edit
                                </button>
                                 <button class="view-btn" onclick="window.location.href='assessment_statistics.php?assessment_id=<?php echo htmlspecialchars($assessment['id']); ?>'">
                                    View Statistics
                                </button>
                                <button class="del-btn" onclick="deleteAssessment(<?php echo htmlspecialchars($assessment['id']); ?>)">
                                    Delete
                                </button>
                               
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
