const express = require('express');
const mysql = require('mysql2/promise');
const app = express();
const port = 3000;

// Database connection configuration
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'crw-database'
};

// Middleware to parse JSON bodies
app.use(express.json());

// GET endpoint to retrieve a user by ID
app.get('/user', async (req, res) => {
    const userId = parseInt(req.query.id, 10);

    if (!userId) {
        return res.status(400).json({
            success: false,
            message: 'Invalid request.'
        });
    }

    const connection = await mysql.createConnection(dbConfig);

    try {
        const [rows] = await connection.execute(
            "SELECT id, first_name, last_name, email, password, user_type FROM users WHERE id = ?",
            [userId]
        );

        if (rows.length > 0) {
            return res.json({
                success: true,
                user: rows[0]
            });
        } else {
            return res.json({
                success: false,
                message: 'User  not found.'
            });
        }
    } catch (error) {
        return res.status(500).json({
            success: false,
            message: 'Database error: ' + error.message
        });
    } finally {
        await connection.end();
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});