const express = require('express');
const mysql = require('mysql2/promise');
const session = require('express-session');
const app = express();
const port = 3000;

// Database connection configuration
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'crw-database'
};

// Middleware to parse URL-encoded bodies
app.use(express.urlencoded({ extended: true }));
app.use(session({
    secret: 'your_session_secret',
    resave: false,
    saveUninitialized: true
}));

// Function to get users
async function getUsers(req) {
    const connection = await mysql.createConnection(dbConfig);
    const loggedInUser  = req.session.user_id;

    let sql = "SELECT * FROM users WHERE users.id != ?";
    const params = [loggedInUser ];

    if (req.query.search && req.query.search.trim() !== '') {
        const search = req.query.search;
        sql += " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
        const searchTerm = `%${search}%`;
        params.push(searchTerm, searchTerm, searchTerm);
    }

    if (req.query.filter && req.query.filter !== 'default') {
        const filter = req.query.filter.toLowerCase();
        sql += " AND LOWER(user_type) = ?";
        params.push(filter.charAt(0).toUpperCase() + filter.slice(1));
    }

    if (req.query.sort && req.query.sort !== 'default') {
        switch (req.query.sort) {
            case 'lastName (A-Z)':
                sql += " ORDER BY last_name ASC";
                break;
            case 'lastName (Z-A)':
                sql += " ORDER BY last_name DESC";
                break;
            case 'firstName (A-Z)':
                sql += " ORDER BY first_name ASC";
                break;
            case 'firstName (Z-A)':
                sql += " ORDER BY first_name DESC";
                break;
        }
    }

    const [rows] = await connection.execute(sql, params);
    await connection.end();

    if (req.query.sort === 'desc') {
        return rows.reverse();
    }

    return rows;
}

// Route to display users
app.get('/users', async (req, res) => {
    const users = await getUsers(req);
    const currentSearch = req.query.search || '';
    const currentFilter = req.query.filter || 'default';
    const currentSort = req.query.sort || 'default';

    res.send(`
        <div class="heading"><h1>User Management</h1>
            <div class="filter-section">
                <form action="/users" method="get">
                    <input type="text" name="search" class="search-bar" 
                           placeholder="Search user..." value="${currentSearch}">

                    <select name="filter" id="sortnfilter">
                        <option value="default" ${currentFilter === 'default' ? 'selected' : ''}>
                            Filter by: Default
                        </option>
                        <option value="admin" ${currentFilter === 'admin' ? 'selected' : ''}>
                            Filter by: Type (Admin)
                        </option>
                        <option value="student" ${currentFilter === 'student' ? 'selected' : ''}>
                            Filter by: Type (Student)
                        </option>
                        <option value="faculty" ${currentFilter === 'faculty' ? 'selected' : ''}>
                            Filter by: Type (Faculty)
                        </option>
                    </select>
                    <select name="sort" id="sortnfilter">
                        <option value="default" ${currentSort === 'default' ? 'selected' : ''}>
                            Sort by: Default
                        </option>
                        <option value="lastName (A-Z)" ${currentSort === 'lastName (A-Z)' ? 'selected' : ''}>
                            Sort by: Last Name (A-Z)
                        </option>
                        <option value="lastName (Z-A)" ${currentSort === 'lastName (Z-A)' ? 'selected' : ''}>
                            Sort by: Last Name (Z-A)
                        </option>
                        <option value="firstName (A-Z)" ${currentSort === 'firstName (A-Z)' ? 'selected' : ''}>
                            Sort by: First Name (A-Z)
                        </option>
                        <option value="firstName (Z-A)" ${currentSort === 'firstName (Z-A)' ? 'selected' : ''}>
                            Sort by: First Name (Z-A)
                        </option>
                        <option value="asc" ${currentSort === 'asc' ? 'selected' ⬤