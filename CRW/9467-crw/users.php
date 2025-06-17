<?php include('php/session_management.php'); ?>
   <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="styles/styles.css">
        <link rel="stylesheet" href="styles/tabs.css">
        <title>User Management</title>
    </head>
    <body>
    <section id="sidebar">
        <?php include 'includes/sidebar.php' ?>
        </section>
        <section id="content">
        <?php include 'includes/nav.php' ?>
        <main id="main">
            <?php include('php/user-queries/get_users.php'); ?>
        </main>
        </section>

        <div class="modal" id="usersModal">
            <div class="modal-content">
                <form action="php/user-queries/add_user.php" method="post" id="userForm">
                    <h2 class="question-form-heading">Create User</h2>
                    
                    <div class="form-group">
                        <label for="first_name">First Name:</label>
                        <textarea name="first_name" id="first_name" rows="1"></textarea> 
                        <label for="last_name">Last Name:</label>
                        <textarea name="last_name" id="last_name" rows="1"></textarea> 
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <textarea name="email" id="email" rows="1"></textarea>
                        <label for="password">Password:</label> 
                        <input type="password" name="password" id="password" minlength="3">
                    </div>

                    <div class="form-group">
                        <label for="user_type">Type:</label>
                        <select name="user_type" id="user_type">
                            <option value="">Select type</option>
                            <option value="Admin">Admin</option>
                            <option value="Faculty">Faculty</option>
                            <option value="Student">Student</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div id="drag-drop-area" class="drag-drop-container">
                            <input type="file" id="csv-upload" name="csv-upload" accept=".csv" style="display: none;">
                            <p>Drag and Drop CSV File or Click to Select</p>
                            <small>CSV file should contain email addresses</small>
                            <p id="file-name" class="file-name"></p>
                        </div>
                    </div>
                    <div class="form-group">
                    </div>
                    <div class="form-actions">
                        <input class="view-btn" type="submit" value="Add"/>
                        <input class="save-btn" type="submit" value="Save"/>
                        <button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <script src="scripts/user_form.js"></script>
        <script src="scripts/pagination.js"></script>
        <script src="scripts/users_pagination.js"></script>
        <script src="scripts/csv.js"></script>
    </body>
    </html>