const express = require('express');
const path = require('path');
const bodyParser = require('body-parser');
const pool = require('./backend/dbconn'); 
const session = require('express-session');
const sidebar = require('./includes/sidebar');
const nav = require('./includes/nav');
const multer = require('multer');
const csv = require('csv-parser');
const fs = require('fs');
const bcrypt = require('bcrypt');
const upload = multer({ dest: 'uploads/' });
const app = express();
const port = 8888;
const bcrypt = require('bcrypt');

const SALT_ROUNDS = 10; 
app.use(express.static(path.join(__dirname)));
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

app.use(
    session({
        secret: 'xxx', 
        resave: false,
        saveUninitialized: true,
        cookie: { secure: false }, 
    })
);

app.use('/sidebar', sidebar);
app.use('/nav', nav);

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.post('/login', async (req, res) => {
    const { email, password } = req.body;

    try {
        const query = 'SELECT id, email, first_name, last_name, user_type, password FROM users WHERE email = ?';
        const [rows] = await pool.query(query, [email]);
        
        if (rows.length > 0) {
            const user = rows[0];
            //Kindly check bcrypt, because somewhat user.password!=password)
            //const passwordMatch = await bcrypt.compare(password, user.password);
            const passwordMatch = user.password === password;

            if (passwordMatch) {
                req.session.user = {
                    id: user.id,
                    email: user.email,
                    firstName: user.first_name,
                    lastName: user.last_name,
                    userType: user.user_type,
                };

                return res.redirect('/users.html');
            }
        }

        res.send(`<script>
            alert('Invalid credentials.');
            window.location.href='/';
        </script>`);
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).send(`<script>
            alert('An error occurred. Please try again later.');
            window.location.href='/';
        </script>`);
    }
});

app.get('/logout', (req, res) => {
    req.session.destroy((err) => {
        if (err) {
            console.error('Logout error:', err);
            return res.status(500).send(`<script>
                alert('An error occurred while logging out.');
                window.location.href='/';
            </script>`);
        }
        res.clearCookie('connect.sid', {
            path: '/',
            httpOnly: true,
        });

        res.redirect('/');
    });
});

app.get('/users.html', (req, res, next) => {
    if (!req.session.user) {
        return res.redirect('/');
    }
    next();
});

app.get('/api/users', async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ error: 'Not authenticated' });
    }

    try {
        let query = 'SELECT * FROM users WHERE id != ?';
        let params = [req.session.user.id];

        if (req.query.search) {
            query += ' AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)';
            const searchTerm = `%${req.query.search}%`;
            params.push(searchTerm, searchTerm, searchTerm);
        }

        if (req.query.filter && req.query.filter !== 'default') {
            query += ' AND LOWER(user_type) = ?';
            params.push(req.query.filter.charAt(0).toUpperCase() + req.query.filter.slice(1));
        }

        if (req.query.sort && req.query.sort !== 'default') {
            switch (req.query.sort) {
                case 'lastName (A-Z)':
                    query += ' ORDER BY last_name ASC';
                    break;
                case 'lastName (Z-A)':
                    query += ' ORDER BY last_name DESC';
                    break;
                case 'firstName (A-Z)':
                    query += ' ORDER BY first_name ASC';
                    break;
                case 'firstName (Z-A)':
                    query += ' ORDER BY first_name DESC';
                    break;
            }
        }

        const [users] = await pool.query(query, params);
        res.json(users);
    } catch (error) {
        console.error('Error fetching users:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

app.post('/api/users/add', async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { first_name, last_name, email, password, user_type } = req.body;

        // Validate incoming fields
        if (!first_name || !last_name || !email || !password || !user_type) {
            return res.status(400).json({
                success: false,
                message: 'Missing required fields (first_name, last_name, email, password, user_type)'
            });
        }

        // Check if the email already exists
        const [emailCheck] = await pool.query('SELECT id FROM users WHERE email = ?', [email]);
        
        if (emailCheck.length > 0) {
            return res.status(400).json({
                success: false,
                message: `Email ${email} is already in use.`
            });
        }

        // Validate password length (example: minimum 8 characters)
        if (password.length < 8) {
            return res.status(400).json({
                success: false,
                message: 'Password must be at least 8 characters long'
            });
        }
        // Instead of hashing the password, store it as plaintext
        const plaintextPassword = password;

        // Insert the new user into the database
        const [result] = await pool.query(
            'INSERT INTO users (email, first_name, last_name, password, user_type) VALUES (?, ?, ?, ?, ?)',
            [email, first_name, last_name, plaintextPassword, user_type]
        );

        // Send success response
        res.json({
            success: true,
            message: `User ${email} added successfully`
        });
    } catch (error) {
        console.error('Error adding user:', error);
        res.status(500).json({
            success: false,
            message: 'Error creating user: ' + error.message
        });
    }
});

app.post('/api/users/edit', async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { user_id, first_name, last_name, email, password, user_type } = req.body;
        
        if (!user_id || !email || !first_name || !last_name || !user_type) {
            return res.status(400).json({
                success: false,
                message: 'Missing required fields (user_id, first_name, last_name, email, user_type)'
            });
        }

        let queryParams;
        let updateQuery;

        if (password) {
            // Check if the password is valid (for example, check minimum length)
            if (password.length < 8) {
                return res.status(400).json({
                    success: false,
                    message: 'Password must be at least 8 characters long'
                });
            }
            const hashedPassword = await bcrypt.hash(password, SALT_ROUNDS);
            updateQuery = 'UPDATE users SET password = ?, email = ?, first_name = ?, last_name = ?, user_type = ? WHERE id = ?';
            queryParams = [hashedPassword, email, first_name, last_name, user_type, user_id];
        } else {
            updateQuery = 'UPDATE users SET email = ?, first_name = ?, last_name = ?, user_type = ? WHERE id = ?';
            queryParams = [email, first_name, last_name, user_type, user_id];
        }

        const [result] = await pool.query(updateQuery, queryParams);

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        res.json({
            success: true,
            message: `User ${email} updated successfully`
        });
    } catch (error) {
        console.error('Error editing user:', error);
        res.status(500).json({
            success: false,
            message: 'Error updating user: ' + error.message
        });
    }
});

app.post('/api/users/delete', async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { user_id } = req.body;
        const [result] = await pool.query('DELETE FROM users WHERE id = ?', [user_id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ 
                success: false, 
                message: 'User not found' 
            });
        }

        res.json({ 
            success: true, 
            message: 'User deleted successfully' 
        });
    } catch (error) {
        console.error('Error deleting user:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Error deleting user: ' + error.message 
        });
    }
});

app.get('/api/users/details', async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    try {
        const { id } = req.query;

        const [users] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);

        if (users.length === 0) {
            return res.status(404).json({ 
                success: false, 
                message: 'User not found' 
            });
        }

        res.json({ 
            success: true, 
            user: users[0] 
        });
    } catch (error) {
        console.error('Error fetching user details:', error);
        res.status(500).json({ 
            success: false, 
            message: 'Error fetching user details: ' + error.message 
        });
    }
});

app.post('/api/users/csv', upload.single('csv-upload'), async (req, res) => {
    if (!req.session.user) {
        return res.status(401).json({ success: false, message: 'Not authenticated' });
    }

    if (!req.file) {
        return res.status(400).json({ success: false, message: 'No file uploaded' });
    }

    const requiredHeaders = ['password', 'email', 'first_name', 'last_name', 'user_type'];
    const results = [];
    const errors = [];

    try {
        const fileStream = fs.createReadStream(req.file.path);
        const parsedData = [];

        let isFirstRow = true;

        await new Promise((resolve, reject) => {
            fileStream
                .pipe(csv())
                .on('data', (data) => {
                    if (isFirstRow) {
                        const headers = Object.keys(data);
                        if (!requiredHeaders.every((header) => headers.includes(header))) {
                            return reject(
                                new Error(
                                    `Invalid headers. Expected: ${requiredHeaders.join(', ')}. Found: ${headers.join(', ')}`
                                )
                            );
                        }
                        isFirstRow = false;
                    } else {
                        parsedData.push(data);
                    }
                })
                .on('end', resolve)
                .on('error', reject);
        });

        for (const data of parsedData) {
            try {
                if (!data.password || !data.email || !data.first_name || !data.last_name || !data.user_type) {
                    errors.push(`Invalid data: ${JSON.stringify(data)}`);
                    continue;
                }

                const [emailCheck] = await pool.query('SELECT id FROM users WHERE email = ?', [data.email]);
                if (emailCheck.length > 0) {
                    errors.push(`Email ${data.email} is already in use`);
                    continue;
                }

                const hashedPassword = await bcrypt.hash(data.password, SALT_ROUNDS);

                const [maxIdResult] = await pool.query('SELECT MAX(id) as max_id FROM users');
                const userId = (maxIdResult[0].max_id || 0) + 1;

                await pool.query(
                    'INSERT INTO users (id, password, email, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?, ?)',
                    [userId, hashedPassword, data.email, data.first_name, data.last_name, data.user_type]
                );

                results.push({
                    email: data.email,
                    userId: userId,
                    success: true,
                });
            } catch (insertError) {
                errors.push(`Error inserting user: ${insertError.message}`);
            }
        }

        fs.unlinkSync(req.file.path);

        const success = results.length > 0;
        const message = success
            ? `${results.length} user(s) added successfully`
            : 'No users were added';

        res.json({ success, message, results, errors: errors.length > 0 ? errors : undefined });
    } catch (error) {
        console.error('CSV Upload Error:', error);
        if (req.file.path) fs.unlinkSync(req.file.path);
        res.status(400).json({ success: false, message: error.message });
    }
});

app.listen(port, () => {
    console.log(`Server running at http://localhost:${port}`);
});
