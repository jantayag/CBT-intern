<?php include('php/session_management.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="styles/styles.css" />
	<link rel="stylesheet" href="styles/tabs.css" />
	<title>User Management</title>
	<style>
		#sidebar {
			position: fixed;
			top: 0;
			left: 0;
			width: 280px;
			height: 100vh;
			background: #fff;
			box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
			display: flex;
			flex-direction: column;
			justify-content: space-between;
			align-items: center;
			padding: 20px 0;
			z-index: 1000;
		}

		.brand img {
			height: 80px;
			width: auto;
			margin-bottom: 30px;
		}

		.side-menu {
			list-style: none;
			padding: 0;
			margin: 0;
			width: 100%;
			text-align: center;
		}

		.side-menu li {
			margin: 10px 0;
		}

		.side-menu li a {
			display: inline-block;
			width: 80%;
			padding: 12px;
			background-color: #004990;
			color: #fff;
			text-decoration: none;
			border-radius: 6px;
			transition: background 0.2s ease;
		}

		.side-menu li a:hover {
			background-color: #003366;
		}

		.bottom-logos {
			display: flex;
			justify-content: center;
			align-items: center;
			gap: 20px;
			padding: 20px 0;
		}

		.bottom-logos img {
			height: 50px;
			width: auto;
		}

		#content {
			margin-left: 280px;
			padding: 20px;
		}
		.logs-section {
    margin-top: 40px;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
}

.logs-section h2 {
    margin-bottom: 15px;
}

#logsTable {
    width: 100%;
    border-collapse: collapse;
}

#logsTable th, #logsTable td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
}

	</style>
</head>
<body>
	<!-- SIDEBAR -->
	<section id="sidebar">
		<div class="brand">
			<a href="classes.php">
				<?php
				if (isset($_SESSION['user_type'])) {
					if ($_SESSION['user_type'] === 'Admin') {
						echo '<img src="img/admin.png" alt="Admin Logo">';
					} elseif ($_SESSION['user_type'] === 'Faculty') {
						echo '<img src="img/faculty.png" alt="Faculty Logo">';
					} elseif ($_SESSION['user_type'] === 'Student') {
						echo '<img src="img/student.png" alt="Student Logo">';
					}
				}
				?>
			</a>
		</div>

		<ul class="side-menu">
			<li><a href="classes.php">Home</a></li>
			<?php if ($_SESSION['user_type'] === 'Admin' || $_SESSION['user_type'] === 'Faculty'): ?>
				<li><a href="questions.php">Questions</a></li>
				<li><a href="assessments.php">Assessments</a></li>
			<?php endif; ?>
			<?php if ($_SESSION['user_type'] === 'Admin'): ?>
				<li><a href="users.php">Users</a></li>
			<?php endif; ?>
		</ul>

		<div class="bottom-logos">
			<img src="img/accountancy.png" alt="Accountancy Logo">
			<img src="img/slu.png" alt="SLU Logo">
		</div>
	</section>

	<!-- CONTENT -->
	<section id="content">
		<?php include 'includes/nav.php'; ?>

	  <main id="main">
	     <?php include('php/user-queries/get_users.php'); ?>
	     <?php include('php/user-queries/get_logs.php'); ?>
      </main>

	</section>

	<!-- MODAL -->
	<div class="modal" id="usersModal">
		<div class="modal-content">
		    <form action="php/user-queries/add_user.php" method="post" id="userForm" enctype="multipart/form-data">
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
					<input type="password" name="password" id="password" minlength="3" />
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
						<input type="file" id="csv-upload" name="csv-upload" accept=".csv" style="display: none;" />
						<p>Drag and Drop CSV File or Click to Select</p>
						<p id="file-name" class="file-name"></p>
					</div>
				</div>

				<div class="form-actions">
					<input class="view-btn" type="submit" value="Add" />
					<input class="save-btn" type="submit" value="Save" />
					<button class="del-btn" type="button" onclick="cancelForm()">Cancel</button>
				</div>
			</form>
		</div>
	</div>

	<!-- JS -->
	<script src="scripts/user_form.js"></script>
	<script src="scripts/pagination.js"></script>
	<script src="scripts/users_pagination.js"></script>
	<script src="scripts/csv.js"></script>
</body>
</html>
