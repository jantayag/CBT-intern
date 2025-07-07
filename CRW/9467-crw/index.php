<!DOCTYPE html>
<html lang="en">
    <!-- This project was created by CRW in compliance to their course in Web Technologies.
         Team:
         - Alcido, Andrei
         - Del Carpio, Marc
         - Dela Cruz, Perry
         - Doria, Francelle
         - Flordeliz, Ron
         - Galamay, Windsor 
    In collaboration of myReach team (Computer Science 2025 Interns)
         - Rizza Montevirgen
         - Jan Adrian Tayag-->

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="styles/login.css">    
    <title>Login Page</title>
</head>
<body class="loginBody">
    <div class="credentials">
        <div class="logo">
            <img src="img/myreach.png" alt="logo" width="350" height="250">
        </div>
        <form id="loginForm" action="php/login.php" method="post"  autocomplete="off">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter your Email"  autocomplete="off" required>
        
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your Password"  autocomplete="off" required>

            <div class="login">
                <button type="submit">Login</button>
            </div>
        </form>
    </div>

</body>
</html>