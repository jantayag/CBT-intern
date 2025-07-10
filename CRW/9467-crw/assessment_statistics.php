<?php
include('php/session_management.php');
include('php/db.php');

// Get assessment ID
$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

// Fetch assessment title
$assessmentTitle = '';
$stmt = $conn->prepare("SELECT title FROM assessments WHERE id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$stmt->bind_result($assessmentTitle);
$stmt->fetch();
$stmt->close();

// Get total items for the assessment
$totalItems = 0;
$itemQuery = $conn->prepare("SELECT COUNT(*) FROM assessment_questions WHERE assessment_id = ?");
$itemQuery->bind_param("i", $assessment_id);
$itemQuery->execute();
$itemQuery->bind_result($totalItems);
$itemQuery->fetch();
$itemQuery->close();

// Fetch results
$results = [];
$sql = "
    SELECT u.id AS student_id, u.first_name, u.last_name, ar.score
    FROM assessment_results ar
    JOIN users u ON ar.student_id = u.id
    WHERE ar.assessment_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['percentage'] = $totalItems > 0 ? round(($row['score'] / $totalItems) * 100, 2) : 0;
    $results[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assessment Statistics</title>
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php'; ?>
    </section>

    <section id="content">
        <?php include 'includes/nav.php'; ?>
        
        <main id="main">
            <div class="heading2">
                <h1>Statistics for: <?php echo htmlspecialchars($assessmentTitle); ?></h1>
                <img id="return-btn" src="img/return-button.svg" alt="return to classes page" onclick="window.location.href='classes.php'">
                        
            </div>
            
                

            <?php if (empty($results)): ?>
                <div class="no-results">No student results found for this assessment.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Score</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): ?>
                                <tr onclick="window.location.href='view_assessment.php?assessment_id=<?php echo $assessment_id; ?>&student_id=<?php echo $row['student_id']; ?>'" style="cursor: pointer;">
                                    <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['score']) . ' / ' . $totalItems; ?></td>
                                    <td><?php echo htmlspecialchars($row['percentage']) . '%'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </section>
</body>
</html>
