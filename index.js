// --- index.js ---

// Impor dotenv di awal untuk memuat variabel lingkungan
require('dotenv').config();

const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const app = express();
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const multer = require('multer');
const { check, validationResult } = require('express-validator');
const methodOverride = require('method-override');
const { v4: uuidv4 } = require('uuid');
const cloudinary = require('cloudinary').v2;
const { CloudinaryStorage } = require('multer-storage-cloudinary');
const url = require('url'); // Impor modul URL

// --- Konfigurasi Lingkungan dari .env ---
const port = process.env.PORT || 3000;
const secretKey = process.env.JWT_SECRET || 'SECRET_KEY_YANG_SANGAT_RAHASIA';

// --- Konfigurasi Koneksi Database ---
let dbConfig = {};
if (process.env.DATABASE_URL) {
    const dbUrl = url.parse(process.env.DATABASE_URL);
    const auth = dbUrl.auth.split(':');
    dbConfig = {
        host: dbUrl.hostname,
        user: auth[0],
        password: auth[1],
        database: dbUrl.pathname.substring(1),
        port: dbUrl.port,
        ssl: {
            rejectUnauthorized: false // SET KE false UNTUK MENGATASI SSL HANDSHAKE ERROR
        },
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0
    };
} else {
    // Fallback untuk lingkungan lokal
    dbConfig = {
        host: process.env.DATABASE_HOST,
        user: process.env.DATABASE_USER,
        password: process.env.DATABASE_PASSWORD,
        database: process.env.DATABASE_NAME,
        waitForConnections: true,
        connectionLimit: 10,
        queueLimit: 0
    };
}

const pool = mysql.createPool(dbConfig);

// Konfigurasi Cloudinary dari .env
cloudinary.config({
    cloud_name: process.env.CLOUDINARY_CLOUD_NAME,
    api_key: process.env.CLOUDINARY_API_KEY,
    api_secret: process.env.CLOUDINARY_API_SECRET
});

// Middleware
app.use(express.json());
app.use(cors());
app.use(methodOverride('_method'));

// Konfigurasi Multer untuk Cloudinary
const storage = new CloudinaryStorage({
    cloudinary: cloudinary,
    params: {
        folder: 'gajah_uploads', // Nama folder di Cloudinary
        allowedFormats: ['jpg', 'jpeg', 'png', 'pdf'],
        public_id: (req, file) => `${Date.now()}-${file.originalname}`
    },
});
const upload = multer({ storage: storage });

// Middleware Autentikasi JWT
const authenticateToken = (req, res, next) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (token == null) {
        return res.status(401).json({ message: 'Token tidak ditemukan.' });
    }

    jwt.verify(token, secretKey, (err, user) => {
        if (err) {
            return res.status(403).json({ message: 'Token tidak valid.' });
        }
        req.user = user;
        next();
    });
};

// Middleware untuk menangani hasil validasi
const handleValidationErrors = (req, res, next) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ errors: errors.array() });
    }
    next();
};

// Middleware untuk mengunggah dan memvalidasi file
const uploadAndValidate = (fieldName) => {
    return (req, res, next) => {
        upload.single(fieldName)(req, res, (err) => {
            if (err) {
                return res.status(500).json({ message: err.message });
            }
            if (!req.file && req.method === 'PATCH') {
                return next();
            }
            if (!req.file) {
                return res.status(400).json({ message: `Mohon lampirkan file ${fieldName}.` });
            }
            next();
        });
    };
};

// --- Rute API ---

// Rute Registrasi
app.post('/api/register', [
    check('name').notEmpty().withMessage('Nama harus diisi.'),
    check('email').isEmail().withMessage('Email tidak valid.'),
    check('password').isLength({ min: 6 }).withMessage('Password minimal 6 karakter.')
], handleValidationErrors, async (req, res) => {
    const { name, email, password } = req.body;
    try {
        const [existingUsers] = await pool.query('SELECT email FROM users WHERE email = ?', [email]);
        if (existingUsers.length > 0) {
            return res.status(409).json({ message: 'Email sudah terdaftar. Silakan gunakan email lain.' });
        }
        const salt = await bcrypt.genSalt(10);
        const hashedPassword = await bcrypt.hash(password, salt);
        const [result] = await pool.query('INSERT INTO users (name, email, password) VALUES (?, ?, ?)', [name, email, hashedPassword]);
        const newUser = { id: result.insertId, name: name, email: email };
        const token = jwt.sign({ id: newUser.id, email: newUser.email }, secretKey, { expiresIn: '1h' });
        res.status(201).json({ message: 'Registrasi berhasil!', token });
    } catch (err) {
        console.error('Error saat registrasi:', err);
        res.status(500).json({ error: 'Terjadi kesalahan server saat registrasi.' });
    }
});

// Rute Login
app.post('/api/login', [
    check('email').isEmail().withMessage('Email tidak valid.'),
    check('password').notEmpty().withMessage('Password harus diisi.')
], handleValidationErrors, async (req, res) => {
    const { email, password } = req.body;
    try {
        const [rows] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);
        const user = rows[0];
        if (!user) {
            return res.status(401).json({ message: 'Email atau password salah.' });
        }
        const passwordMatch = await bcrypt.compare(password, user.password);
        if (!passwordMatch) {
            return res.status(401).json({ message: 'Email atau password salah.' });
        }
        const token = jwt.sign({ id: user.id, email: user.email }, secretKey, { expiresIn: '1h' });
        res.status(200).json({ message: 'Login berhasil!', token });
    } catch (err) {
        console.error('Error saat login:', err);
        res.status(500).json({ error: 'Terjadi kesalahan server saat login.' });
    }
});

// Rute Ambil Biodata Pengguna
app.get('/api/user', authenticateToken, async (req, res) => {
    try {
        const { id } = req.user;
        const [rows] = await pool.query('SELECT * FROM users WHERE id = ?', [id]);
        const user = rows[0];
        if (!user) {
            return res.status(404).json({ message: 'Pengguna tidak ditemukan.' });
        }
        res.status(200).json(user);
    } catch (err) {
        console.error('Error saat mengambil data pengguna:', err);
        res.status(500).json({ error: 'Terjadi kesalahan server saat mengambil data.' });
    }
});

// Rute MENYIMPAN Biodata Baru (POST)
app.post(
    '/api/profile/biodata',
    authenticateToken,
    uploadAndValidate('identity_card'),
    [
        check('full_name').notEmpty().withMessage('Nama lengkap harus diisi.'),
        check('nik').isLength({ min: 16, max: 16 }).withMessage('NIK harus 16 digit.'),
        check('date_of_birth').isISO8601().toDate().withMessage('Format tanggal lahir tidak valid (YYYY-MM-DD).'),
        check('place_of_birth').notEmpty().withMessage('Tempat lahir harus diisi.'),
        check('sub_district').notEmpty().withMessage('Kecamatan harus diisi.'),
        check('regency').notEmpty().withMessage('Kabupaten/Kota harus diisi.'),
        check('full_address').notEmpty().withMessage('Alamat harus diisi.'),
        check('gender').isIn(['Laki-laki', 'Perempuan']).withMessage('Jenis kelamin tidak valid.')
    ],
    handleValidationErrors,
    async (req, res) => {
        const { full_name, nik, date_of_birth, place_of_birth, sub_district, regency, full_address, gender } = req.body;
        const { id } = req.user;
        const identity_card_path = req.file ? req.file.path : null;
        try {
            const [existingBiodata] = await pool.query('SELECT nik FROM users WHERE id = ? AND nik IS NOT NULL', [id]);
            if (existingBiodata.length > 0) {
                return res.status(409).json({ message: 'Biodata sudah ada. Gunakan PATCH untuk memperbarui.' });
            }
            const [existingNik] = await pool.query('SELECT id FROM users WHERE nik = ?', [nik]);
            if (existingNik.length > 0) {
                return res.status(409).json({ message: 'NIK sudah terdaftar di akun lain.' });
            }
            const updateQuery = 'UPDATE users SET full_name = ?, nik = ?, date_of_birth = ?, place_of_birth = ?, sub_district = ?, regency = ?, full_address = ?, gender = ?, identity_card_path = ? WHERE id = ?';
            const updateValues = [full_name, nik, date_of_birth, place_of_birth, sub_district, regency, full_address, gender, identity_card_path, id];
            await pool.query(updateQuery, updateValues);
            res.status(201).json({ message: 'Biodata berhasil disimpan.' });
        } catch (err) {
            console.error('Error saat menyimpan biodata:', err);
            res.status(500).json({ error: 'Terjadi kesalahan server saat menyimpan biodata.' });
        }
    }
);

// Rute MEMPERBARUI Biodata (PATCH)
app.patch(
    '/api/profile/biodata',
    authenticateToken,
    upload.single('identity_card'),
    [
        check('full_name').optional().notEmpty().withMessage('Nama lengkap harus diisi.'),
        check('nik').optional().isLength({ min: 16, max: 16 }).withMessage('NIK harus 16 digit.'),
        check('date_of_birth').optional().isISO8601().toDate().withMessage('Format tanggal lahir tidak valid (YYYY-MM-DD).'),
        check('place_of_birth').optional().notEmpty().withMessage('Tempat lahir harus diisi.'),
        check('sub_district').optional().notEmpty().withMessage('Kecamatan harus diisi.'),
        check('regency').optional().notEmpty().withMessage('Kabupaten/Kota harus diisi.'),
        check('full_address').optional().notEmpty().withMessage('Alamat harus diisi.'),
        check('gender').optional().isIn(['Laki-laki', 'Perempuan']).withMessage('Jenis kelamin tidak valid.')
    ],
    handleValidationErrors,
    async (req, res) => {
        const { full_name, nik, date_of_birth, place_of_birth, sub_district, regency, full_address, gender } = req.body;
        const { id } = req.user;
        const identity_card_path = req.file ? req.file.path : null;
        try {
            if (nik) {
                const [existingNik] = await pool.query('SELECT id FROM users WHERE nik = ? AND id != ?', [nik, id]);
                if (existingNik.length > 0) {
                    return res.status(409).json({ message: 'NIK sudah terdaftar di akun lain.' });
                }
            }
            const updates = {};
            const updateValues = [];
            if (full_name !== undefined) updates.full_name = full_name;
            if (nik !== undefined) updates.nik = nik;
            if (date_of_birth !== undefined) updates.date_of_birth = date_of_birth;
            if (place_of_birth !== undefined) updates.place_of_birth = place_of_birth;
            if (sub_district !== undefined) updates.sub_district = sub_district;
            if (regency !== undefined) updates.regency = regency;
            if (full_address !== undefined) updates.full_address = full_address;
            if (gender !== undefined) updates.gender = gender;
            if (identity_card_path) updates.identity_card_path = identity_card_path;
            if (Object.keys(updates).length === 0) {
                return res.status(400).json({ message: 'Tidak ada field yang dikirim untuk diperbarui.' });
            }
            const updateKeys = Object.keys(updates).map(key => `${key} = ?`);
            updateValues.push(...Object.values(updates));
            const updateQuery = `UPDATE users SET ${updateKeys.join(', ')} WHERE id = ?`;
            updateValues.push(id);
            await pool.query(updateQuery, updateValues);
            res.status(200).json({ message: 'Biodata berhasil diperbarui.' });
        } catch (err) {
            console.error('Error saat memperbarui biodata:', err);
            res.status(500).json({ error: 'Terjadi kesalahan server saat memperbarui biodata.' });
        }
    }
);

// Rute Ambil Semua Kursus
app.get('/api/courses', authenticateToken, async (req, res) => {
    try {
        const [courses] = await pool.query('SELECT * FROM courses');
        res.json(courses);
    } catch (err) {
        console.error('Error saat mengambil data:', err);
        res.status(500).json({ error: 'Terjadi kesalahan saat mengambil data.' });
    }
});

// Rute Ambil Kursus Berdasarkan Slug
app.get('/api/courses/:slug', authenticateToken, async (req, res) => {
    const { slug } = req.params;
    try {
        const [rows] = await pool.query('SELECT * FROM courses WHERE slug = ? LIMIT 1', [slug]);
        const course = rows[0];
        if (!course) {
            return res.status(404).json({ message: 'Kursus tidak ditemukan.' });
        }
        res.status(200).json(course);
    } catch (err) {
        console.error('Error saat mengambil data kursus:', err);
        res.status(500).json({ error: 'Terjadi kesalahan server saat mengambil data.' });
    }
});

// Rute Ambil Kursus Saya
app.get('/api/my-courses', authenticateToken, async (req, res) => {
    const { id } = req.user;
    try {
        const [courses] = await pool.query(
            'SELECT c.*, e.id AS enrollment_id, e.status FROM courses c INNER JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ?',
            [id]
        );
        if (courses.length === 0) {
            return res.status(404).json({ message: 'Anda belum mendaftar di kursus manapun.' });
        }
        res.status(200).json(courses);
    } catch (err) {
        console.error('Error saat mengambil data kursus:', err);
        res.status(500).json({ error: 'Terjadi kesalahan server saat mengambil data kursus.' });
    }
});

// Rute Pendaftaran Kursus dengan Validasi
app.post(
    '/api/courses/:courseId/enroll',
    authenticateToken,
    uploadAndValidate('cv'),
    [
        check('reason').notEmpty().withMessage('Alasan harus diisi.')
    ],
    handleValidationErrors,
    async (req, res) => {
        const { courseId } = req.params;
        const { id: userId } = req.user;
        const { reason } = req.body;
        const cvPath = req.file ? req.file.path : null;
        try {
            const [existingEnrollment] = await pool.query(
                'SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?',
                [userId, courseId]
            );
            if (existingEnrollment.length > 0) {
                return res.status(409).json({ message: 'Anda sudah mendaftar di kursus ini.' });
            }
            const enrollmentUuid = uuidv4();
            await pool.query(
                'INSERT INTO enrollments (uuid, user_id, course_id, reason, cv_path, status) VALUES (?, ?, ?, ?, ?, ?)',
                [enrollmentUuid, userId, courseId, reason, cvPath, 'pending']
            );
            res.status(201).json({ message: 'Pendaftaran berhasil! Mohon tunggu konfirmasi.' });
        } catch (err) {
            console.error('Error saat pendaftaran kursus:', err);
            res.status(500).json({ error: 'Terjadi kesalahan server saat pendaftaran kursus.' });
        }
    }
);

// Rute Home
app.get('/', (req, res) => {
    res.send('Aplikasi Express terhubung ke MySQL!');
});

// Jalankan Server
app.listen(port, () => {
    console.log(`Server berjalan di http://localhost:${port}`);
});