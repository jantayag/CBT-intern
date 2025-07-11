const express = require('express');
const mysql = require('mysql2/promise');

const app = express();
const port = 3000;

// Middleware to parse JSON bodies
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Database connection configuration
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'crw-database'
};

// POST endpoint to delete a user
app.post('/delete_user', async (req, res) => {
    const { user_id } = req.body;

    if (!user_id) {
        return res.status(400).json({
            success: false,
            message: 'Invalid request method.'
        });
    }

    const connection = await mysql.createConnection(dbConfig);
    await connection.beginTransaction();

    try {
        const deleteUser Query = "DELETE FROM users WHERE id = ?";
        const [result] = await connection.execute(deleteUser Query, [user_id]);

        if (result.affectedRows > 0) {
            await connection.commit();
            return res.json({
                success: true,
                message: 'User  deleted successfully.'
            });
        } else {
            throw new Error('User  not found or could not be deleted.');
        }
    } catch (error) {
        await connection.rollback();
        return res.json({
            success: false,
            message: 'Error deleting user: ' + error.message
        });
    } finally {
        await connection.end();
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});