<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}
include "php/class-queries/get_classes.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="stylesheet" href="styles/card.css">
    <title>Classes</title>
</head>
<body>
      <section id="sidebar">
        <?php include 'includes/sidebar.php' ?>
    </section>
    <section id="content">
        <?php include 'includes/nav.php' ?>
        <main id="main">
            <?php 
            $pageHeading = ($_SESSION['user_type'] === 'Faculty' || $_SESSION['user_type'] === 'Admin') ? 'Class Management' : $_SESSION['first_name'] ."'s Classes"; 
            ?>
            <div class="heading"><h1><?php echo $pageHeading; ?></h1>
                <?php if ($_SESSION['user_type'] === 'Faculty' || $_SESSION['user_type'] === 'Admin'): ?>
                <div class="filter-section">
                <form method="GET" action="classes.php">
                    <input type="text" class="search-bar" name="search" placeholder="Search class..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <select name="semester" id="sortnfilter">
                        <option value="">Filter by: Default</option>
                        <option value="1" <?php if (isset($_GET['semester']) && $_GET['semester'] == '1') echo 'selected'; ?>>Filter by: 1st Semester</option>
                        <option value="2" <?php if (isset($_GET['semester']) && $_GET['semester'] == '2') echo 'selected'; ?>>Filter by: 2nd Semester</option>
                        <option value="3" <?php if (isset($_GET['semester']) && $_GET['semester'] == '3') echo 'selected'; ?>>Filter by: Short Term</option>
                    </select>
                    <button type="submit" class="action-btn">Apply Filters</button>
                </form>
                    <div class="question-actions">
                        <button class="action-btn" onclick="createClass()">Create Class</button>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <section class="card-section">
                <?php
                $classes = getClasses();
                displayClasses($classes);
                ?>
            </section>
        </main>
    </section>

     <?php if ($_SESSION['user_type'] === 'Faculty'|| $_SESSION['user_type'] === 'Admin'): ?>
    <div class="modal" style="display: none;">
        <div class="modal-content" >
            <form action="php/class-queries/create_class.php" method="post" id="classForm">
                <h2 class="question-form-heading">Create New Class</h2>
                    <div class="form-group">
                        <label for="class_code">Class Code:</label>
                        <textarea name="class_code" id="class_code" rows="2" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="program">Program:</label>
                        <select name="program" id="program" required>
                            <option value="">Select program</option>
                            <!-- fetch all programs from the database and set it as an option -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="semester">Semester:</label>
                        <select name="type" id="type" required>
                            <option value="">Select semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">Short Term</option>
                        </select>
                    </div>

                    <div class="form-group">
                    <label for="start_year">Academic Year:</label>
                    <div>
                        <select name="start_year" id="start_year" onchange="updateEndYear()" required>
                            <option value="">Select Start Year</option>
                            <?php
                                $currentYear = date("Y");
                                for ($year = $currentYear - 10; $year <= $currentYear + 10; $year++) {
                                    echo "<option value='$year'>$year</option>";
                                }
                            ?>
                        </select>
                        -
                        <select name="end_year" id="end_year" required>
                            <option value="">Select End Year</option>
                            <?php
                                for ($year = $currentYear - 10; $year <= $currentYear + 10; $year++) {
                                    echo "<option value='$year'>$year</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>

                    <div class="form-actions">
                        <input class="view-btn" type="submit" value="Create" />
                        <input class="save-btn" type="submit" value="Save"/>
                        <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
                    </div>
            </form>
        </div>
    </div>
    <script src="scripts/class_form.js"></script>
    <script src="scripts/del_class.js"></script>
    <?php endif; ?>
</body>
</html>