const mysql = require('mysql2/promise');
const dbConfig = require('./dbconfig');

const pool = mysql.createPool(dbConfig);


async function setConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('Database connected successfully');
        connection.release();
    } catch (error) {
        console.error('Error connecting to the database:', error);
    }
}

setConnection();

module.exports = pool;