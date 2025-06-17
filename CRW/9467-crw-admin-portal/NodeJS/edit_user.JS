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

// POST endpoint to update a user
app.post('/update_user', async (req, res) => {
    const { user_id, first_name, last_name, user_type, email, password } = req.body;

    if (!user_id) {
        return res.status(400).json({
            success: false,
            message: 'User  ID is required.'
        });
    }

    // Sanitize input
    const sanitizedFirstName = first_name ? first_name.trim() : '';
    const sanitizedLastName = last_name ? last_name.trim() : '';
    const sanitizedUser Type = user_type ? user_type.trim() : '';
    const sanitizedEmail = email ? email.trim() : '';
    const sanitizedPassword = password ? password.trim() : '';

    const connection = await mysql.createConnection(dbConfig);
    await connection.beginTransaction();

    try {
        const updateUser Query = `
            UPDATE users 
            SET password = ?, email = ?, first_name = ?, last_name = ?, user_type = ? 
            WHERE id = ?
        `;
        const [result] = await connection.execute(updateUser Query, [
            sanitizedPassword,
            sanitizedEmail,
            sanitizedFirstName,
            sanitizedLastName,
            sanitizedUser Type,
            user_id
        ]);

        if (result.affectedRows > 0) {
            await connection.commit();
            return res.json({
                success: true,
                user_id: user_id,
                message: 'Changes made successfully'
            });
        } else {
            throw new Error('Error updating user: User not found or no changes made.');
        }
    } catch (error) {
        await connection.rollback();
        return res.json({
            success: false,
            message: 'Error updating user: ' + error.message
        });
    } finally {
        await connection.end();
    }
});

// Start the server
app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});