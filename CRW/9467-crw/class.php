<?php
include('php/session_management.php'); 

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

include('php/class-queries/get_class_details.php');

if (!isset($_GET['id'])) {
    header("Location: classes.php");
    exit();
}

$class_code = $_GET['id'];
$class = getClassDetails($class_code);

if (!$class) {
    header("Location: classes.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/tabs.css">
    <title>Class Details of <?php echo ' '.htmlspecialchars($class['class_code']) . " | " . htmlspecialchars($class['program_name']); ?></title>
</head>
<body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php' ?>
    </section>
    <section id="content">
        <?php include 'includes/nav.php' ?>
        <main id="main">
            <div class="heading2">
                <h1><?php echo htmlspecialchars($class['class_code']) . ' ' . htmlspecialchars($class['program_name']) . ' | ' . getSemesterText($class['sem']) . ", A.Y. " . htmlspecialchars($class['AY']); ?></h1>
                <img id="return-btn" src="img/return-button.svg" alt="return to classes page" onclick="window.location.href='classes.php'">
            </div>
            <input type="hidden" id="class_code" name="class_code" value="<?php echo isset($class_code) ? htmlspecialchars($class_code) : ''; ?>">
    <section>
    <div class="tabs-container">
        <div class="tabs-header">
            <div>
                <a class="tabs" data-tab="topics">Topics</a>
                <a class="tabs" data-tab="students">Students</a>
                <?php if ($_SESSION['user_type'] === 'Admin' || $_SESSION['user_type'] === 'Faculty'): ?>
                    <a class="tabs" data-tab="stats">Statistics</a>
                <?php endif; ?>
            </div>
            <button class="action-btn" onclick="showAddTopicModal()">Add Topic</button>
            <button class="action-btn" onclick="showAddStudentModal()">Add Student</button>
        </div>
        
        <div id="topics" class="tab-content">
            <?php                         
                $topics = getClassTopics($class_code);
                displayClassTopics($topics);
            ?>
        </div>
        
        <div id="students" class="tab-content">
            <?php
            $students = getClassStudents($class['class_code']);
            displayClassStudents($students);
            ?>
        </div>

        <?php if ($_SESSION['user_type'] === 'Admin' || $_SESSION['user_type'] === 'Faculty'): ?>
        <div id="stats" class="tab-content">
            <div class="stats-header">
                <p id="studentCount" class="student-count">Total Students: 0</p>
            </div>
            <div class="charts-header">
                <canvas id="topicChart"></canvas>
                <canvas id="performanceChart"></canvas>
                <canvas id="participationChart"></canvas>
            </div>
            <canvas id="assessmentChart"></canvas>
            <canvas id="questionChart"></canvas>
        </div>
        <?php endif; ?>
    </div>

                </div>    
            </section>
        </main>
    </section>
      <!-- add student form -->
        <div class="modal"  id="addStudentModal" style="display: none;">
            <div class="modal-content" >
                <h2 class="question-form-heading">Add Student/s</h2>

                <div class="form-group">
                    <label for="student-email">Student Email</label>
                    <input type="text" id="student-email" name="student-email" placeholder="Enter student email">
                </div>
                <div class="form-group">
                    <div id="drag-drop-area" class="drag-drop-container">
                        <input type="file" id="csv-upload" name="csv-upload" accept=".csv" style="display: none;">
                        <p>Drag and Drop CSV File or Click to Select</p>
                        <small>CSV file should contain email addresses</small>
                        <p id="file-name" class="file-name"></p>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="view-btn" onclick="addStudents()">Add</button>
                    <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
                </div>
            </div>
        </div>
       <!-- add topic form -->
       <div class="modal" style="display: none;">
            <div class="modal-content" >
                <h2 class="question-form-heading">Add Topic</h2>
                <div class="form-group">
                    <label for="topic">Topic:</label>
                    <textarea name="topic" id="class_code" rows="2" required></textarea>
                </div>
                <div class="form-actions">
                    <button class="view-btn" onclick="addTopic()">Add</button>
                    <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
                </div>
            </div>
    </div>
        <!-- add assessments form -->
        <div class="modal" id="addAssessmentModal" style="display: none;">
        <div class="modal-content">
            <h2>Add Assessments</h2>
            <form id="addAssessmentsForm">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date Created</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <button type="button" class="view-btn" onclick="addAssessments()">Add</button>
                    <button type="button" class="del-btn" onclick="cancelForm()">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script src="scripts/pagination.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="scripts/stats.js"></script>
    <script src="scripts/class_tabs.js"></script>
    <script src="scripts/csv.js"></script>
</body>
</html>

