<?php
include('php/db.php');

function getClassDetails($class_code) {
    global $conn;

    $sql = "SELECT c.id, c.class_code, c.sem, c.AY,
                   p.name as program_name,
                   u.first_name, u.last_name 
            FROM classes c
            LEFT JOIN programs p ON c.program_id = p.id
            LEFT JOIN users u ON c.faculty_id = u.id
            WHERE c.class_code = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc(); 
}

function getSemesterText($sem) {
    switch($sem) {
        case 1:
            return "1st Semester";
        case 2:
            return "2nd Semester";
        case 3:
            return "Short Term";
        default:
            return "Unknown Semester";
    }
}

function getClassStudents($class_code) {
    global $conn;

    $sql = "SELECT u.id, u.first_name, u.last_name, u.email 
            FROM student_class sc
            JOIN users u ON sc.student_id = u.id
            WHERE sc.class_code = ?
            ORDER BY u.last_name, u.first_name";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    $stmt->close(); 
    return $students; 
}

function getClassTopics($class_code) {
    global $conn;
    
    $sql = "SELECT t.id, t.topic, 
            (SELECT COUNT(*) FROM topic_assessments ta WHERE ta.topic_id = t.id) as assessment_count
            FROM topics t
            WHERE t.class_code = ?
            ORDER BY t.id";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $class_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $topics = [];
    while ($row = $result->fetch_assoc()) {
        $topics[] = $row;
    }
    
    return $topics; 
}

function displayClassStudents($students) {
    if (empty($students)) {
        echo '<h1>No students found.</h1>';
        return;
    }
    ?>
    <div id="students-container">
        <div class="table-responsive">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th>#</th> 
                        <th>Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 1;
                    foreach ($students as $student): ?>
                        <tr data-student-id="<?php echo $student['id']; ?>">
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($student['last_name'] . ', ' . $student['first_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td>
                                <button class="del-btn" onclick="removeStudent(<?php echo $student['id']; ?>)">
                                    Remove
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

function displayClassTopics($topics) {
    if (empty($topics)) {
        echo '<h1>No topics found.</h1>';
        return;
    }
    ?>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Topic</th>
                    <th>Assessments</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topics as $topic): ?>
                    <tr data-topic-id="<?php echo $topic['id']; ?>">
                        <td>
                            <?php echo htmlspecialchars($topic['topic']); ?>
                        </td>
                        <td><?php echo $topic['assessment_count']; ?></td>
                        <td class="action-buttons">
                        <button class="view-btn" onclick="window.location.href='topic_assessments.php?topic_id=<?php echo htmlspecialchars($topic['id']); ?>'">
                                View Assessments
                            </button>
                            <button class="edit-btn" onclick="showAddAssessmentsModal(<?php echo $topic['id']; ?>)">
                                Add Assessment
                            </button>
                            <button class="del-btn" onclick="deleteTopic(<?php echo $topic['id']; ?>)">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>