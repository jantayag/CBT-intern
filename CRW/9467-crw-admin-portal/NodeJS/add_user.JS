const express = require('express');
const mysql = require('mysql2/promise');
const bodyParser = require('body-parser');

const app = express();
const port = 3000;

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(bodyParser.json());

// Database connection
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'crw-database'
};

// Function to get the next user ID
async function getNextUser Id(connection) {
    const [rows] = await connection.query("SELECT MAX(id) as max_id FROM users");
    return (rows[0].max_id === null) ? 1 : rows[0].max_id + 1;
}

// POST endpoint to add a user
app.post('/add_user', async (req, res) => {
    const { password, email, first_name, last_name, user_type } = req.body;

    // Sanitize inputs
    const sanitizedEmail = email.trim();
    const sanitizedFirstName = first_name.trim();
    const sanitizedLastName = last_name.trim();
    const sanitizedUser Type = user_type.trim();

    try {
        const connection = await mysql.createConnection(dbConfig);
        const userId = await getNextUser Id(connection);

        // Check if email already exists
        const [emailCheckRows] = await connection.execute("SELECT id FROM users WHERE email = ?", [sanitizedEmail]);

        if (emailCheckRows.length > 0) {
            return res.json({
                success: false,
                message: 'This email is already in use. Please use a different email.'
            });
        }

        // Insert new user
        const sql = "INSERT INTO users (id, password, email, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?, ?)";
        const [result] = await connection.execute(sql, [userId, password, sanitizedEmail, sanitizedFirstName, sanitizedLastName, sanitizedUser Type]);

        if (result.affectedRows > 0) {
            return res.json({
                success: true,
                user_id: userId,
                message: 'User  added successfully'
            });
        } else {
            return res.json({
                success: false,
                message: 'Error creating user.'
            });
        }
    } catch (error) {
        console.error("Error:", error);
        return res.json({
            success: false,
            message: 'Error creating user: ' + error.message
        });
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});