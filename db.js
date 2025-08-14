const mysql = require('mysql2/promise');
const dotenv = require('dotenv');

// Muat environment variables dari file .env
dotenv.config();

const databaseUrl = process.env.DATABASE_URL;

if (!databaseUrl) {
    console.error('DATABASE_URL is not set in environment variables.');
    process.exit(1);
}

const pool = mysql.createPool({
    uri: databaseUrl,
    waitForConnections: true,
    connectionLimit: 10, // Jumlah maksimum koneksi
    queueLimit: 0       // Tidak ada batasan antrian
});

// Cek koneksi ke database saat aplikasi dimulai
async function testConnection() {
    try {
        const connection = await pool.getConnection();
        console.log('Successfully connected to the database!');
        connection.release(); // Lepaskan koneksi
    } catch (error) {
        console.error('Failed to connect to the database:', error);
        // Hentikan aplikasi jika koneksi gagal
        process.exit(1);
    }
}

testConnection();

module.exports = pool;