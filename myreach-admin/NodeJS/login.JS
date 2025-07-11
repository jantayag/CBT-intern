const express = require('express');
const session = require('express-session');
const mysql = require('mysql2/promise');
const bodyParser = require('body-parser');

const app = express();
const port = 3000;

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(session({
    secret: 'your_secret_key', // Change this to a secure random string
    resave: false,
    saveUninitialized: true,
    cookie: { secure: false } // Set to true if using HTTPS
}));

// Database connection
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'crw-database'
};

app.post('/login', async (req, res) => {
    const email = req.body.email;
    const password = req.body.password;

    try {
        const connection = await mysql.createConnection(dbConfig);
        
        const [rows] = await connection.execute("SELECT id, password, email, first_name, last_name, user_type FROM users WHERE email = ?", [email]);

        if (rows.length > 0) {
            const user = rows[0];

            // Here you should use a secure password comparison method, e.g., bcrypt
            if (password === user.password) {
                req.session.user_id = user.id;
                req.session.email = user.email;
                req.session.first_name = user.first_name;
                req.session.last_name = user.last_name;
                req.session.user_type = user.user_type;
                req.session.last_activity = Date.now();

                return res.redirect('../classes.php');
            }
        }

        res.send("<script>alert('Invalid credentials.'); window.location.href='../index.php';</script>");
    } catch (error) {
        console.error("Login error:", error);
        res.send("<script>alert('An error occurred. Please try again later.'); window.location.href='../index.php';</script>");
    }
});

// Redirect if not POST
app.get('/login', (req, res) => {
    res.redirect('../index.php');
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});