/**
 * ============================================================================
 * SIMPATI PUBLIC API - MOCK LOCAL SERVER (Port 3000)
 * ============================================================================
 * Server lokal mandiri untuk menjalankan API SIMPATI di laptop Anda
 * Menggunakan Node.js native tanpa dependensi tambahan.
 *
 * Cara Menjalankan:
 *   node simpati-server.js
 * ============================================================================
 */

import http from 'http';
import url from 'url';

// PORT & HOST bisa dioverride via env agar fleksibel di Windows/server.
// Contoh: PORT=3000 HOST=0.0.0.0 node simpati-server.js
// Bind default ke 0.0.0.0 agar bisa dijangkau via localhost MAUPUN 127.0.0.1
// (sebelumnya hanya listen() default sehingga rawan masalah resolusi IPv6 ::1 vs IPv4).
const PORT = parseInt(process.env.PORT || process.env.SIMPATI_PORT || '3000', 10);
const HOST = process.env.HOST || process.env.SIMPATI_HOST || '0.0.0.0';
const VALID_API_KEY = process.env.SIMPATI_API_KEY || 'si-ke-ren74_K9xM2pL8vR5wQ1zY4tN7bC0jF3hG6dS8aE1uW4iO9qX2zV5mP0';

// Master Data Pegawai BPS Sultra
const PEGAWAI_DATA = [
    {
        id: 1,
        nama_lengkap: "A. Ranuwirawan Rahim, S.Si., M.Si.",
        niplama: "19750101",
        nipbaru: "197501011998031001",
        email: "ranuwirawan@bps.go.id",
        nm_jabatan: "Kepala Bagian Umum",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_active: 1
    },
    {
        id: 2,
        nama_lengkap: "Budi Santoso, S.Stat.",
        niplama: "19880202",
        nipbaru: "198802022010121002",
        email: "budi.santoso@bps.go.id",
        nm_jabatan: "Statistisi Ahli Madya / Ketua Tim IPDS",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_active: 1
    },
    {
        id: 3,
        nama_lengkap: "Siti Aminah, S.Tr.Stat.",
        niplama: "19920303",
        nipbaru: "199203032015022001",
        email: "siti.aminah@bps.go.id",
        nm_jabatan: "Statistisi Ahli Muda / Ketua Tim Nerwilis",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_active: 1
    },
    {
        id: 4,
        nama_lengkap: "Muhammad Gibran Fitrah, S.Kom.",
        niplama: "19990404",
        nipbaru: "199904042022011001",
        email: "gibran.fitrah@bps.go.id",
        nm_jabatan: "Pranata Komputer Ahli Pertama",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_active: 1
    },
    {
        id: 5,
        nama_lengkap: "Dewi Sartika, S.E.",
        niplama: "19950505",
        nipbaru: "199505052018012002",
        email: "dewi.sartika@bps.go.id",
        nm_jabatan: "Pranata Keuangan APBN",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_active: 1
    },
    {
        id: 6,
        nama_lengkap: "Ahmad Fauzi, S.Si.",
        niplama: "19900606",
        nipbaru: "199006062014031001",
        email: "ahmad.fauzi@bps.go.id",
        nm_jabatan: "Statistisi Ahli Pertama",
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        is_pindahsatker: 0,
        is_active: 1
    },
    {
        id: 7,
        nama_lengkap: "Hendra Wijaya, S.E.",
        niplama: "19910707",
        nipbaru: "199107072016011002",
        email: "hendra.w@bps.go.id",
        nm_jabatan: "Statistisi Ahli Pertama",
        id_satker: "7471",
        nm_satker: "BPS Kota Kendari",
        satker_asal: "7400",
        is_pindahsatker: 1,
        catatan_mutasi: "Pindah SATKER dari BPS Provinsi Sulawesi Tenggara (7400) ke BPS Kota Kendari (7471)",
        is_active: 1
    }
];

// Master Data Tim Kerja BPS
const TIMS_DATA = [
    {
        id: 10,
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        nm_tim: "Integrasi Pengolahan dan Diseminasi Statistik (IPDS)",
        deskripsi: "Pengelolaan TI, sistem integrasi, dan diseminasi data BPS",
        anggota_nips: [
            { niplama: "19880202", jabatan_dalam_tim: "Ketua Tim" },
            { niplama: "19990404", jabatan_dalam_tim: "Anggota" }
        ]
    },
    {
        id: 20,
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        nm_tim: "Neraca Wilayah dan Analisis Statistik (Nerwilis)",
        deskripsi: "Penyusunan PDRB dan analisis statistik ekonomi",
        anggota_nips: [
            { niplama: "19920303", jabatan_dalam_tim: "Ketua Tim" },
            { niplama: "19900606", jabatan_dalam_tim: "Anggota" },
            { niplama: "19990404", jabatan_dalam_tim: "Anggota Tim Analis TI" } // Multi-tim (Gibran ada di IPDS dan Nerwilis)
        ]
    },
    {
        id: 30,
        id_satker: "7400",
        nm_satker: "BPS Provinsi Sulawesi Tenggara",
        nm_tim: "Bagian Umum & Keuangan",
        deskripsi: "Tata usaha, kepegawaian, perlengkapan BMN, dan keuangan",
        anggota_nips: [
            { niplama: "19750101", jabatan_dalam_tim: "Penanggung Jawab" },
            { niplama: "19950505", jabatan_dalam_tim: "Anggota" }
        ]
    }
];

// Helper: Response JSON
function sendJSON(res, statusCode, data) {
    res.writeHead(statusCode, {
        'Content-Type': 'application/json; charset=utf-8',
        'Access-Control-Allow-Origin': '*',
        'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
        'Access-Control-Allow-Headers': 'Content-Type, x-api-key, Authorization, Accept'
    });
    res.end(JSON.stringify(data, null, 2));
}

// Create Server
const server = http.createServer((req, res) => {
    // Handle CORS Preflight
    if (req.method === 'OPTIONS') {
        res.writeHead(204, {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers': 'Content-Type, x-api-key, Authorization, Accept'
        });
        return res.end();
    }

    const parsedUrl = url.parse(req.url, true);
    const pathname = parsedUrl.pathname.replace(/\/+$/, '') || '/';
    const query = parsedUrl.query;

    console.log(`[${new Date().toLocaleTimeString()}] ${req.method} ${pathname}`);

    // Health check tanpa API key agar Laravel / browser bisa cek "dijangkau atau tidak"
    // GET / , GET /health , GET /api/public/health
    if (req.method === 'GET' && (pathname === '/' || pathname === '/health' || pathname === '/api/public/health')) {
        return sendJSON(res, 200, {
            status: 'ok',
            service: 'SIMPATI Public API (Mock)',
            port: PORT,
            time: new Date().toISOString(),
            endpoints: [
                'GET /api/public/pegawai',
                'GET /api/public/pegawai/{niplama}',
                'GET /api/public/tim-kerja',
            ],
        });
    }

    // Check API Key
    const apiKey = req.headers['x-api-key'];
    if (!apiKey || apiKey !== VALID_API_KEY) {
        return sendJSON(res, 401, {
            error: "Unauthorized: API key tidak valid atau tidak disertakan pada header 'x-api-key'",
            code: 401
        });
    }

    // 1. GET /api/public/pegawai
    if (pathname === '/api/public/pegawai' && req.method === 'GET') {
        let result = PEGAWAI_DATA.filter(p => p.is_active === 1);
        if (query.id_satker) {
            result = result.filter(p => String(p.id_satker) === String(query.id_satker));
        }

        const formatted = result.map(p => ({
            id: p.id,
            nama_lengkap: p.nama_lengkap,
            niplama: p.niplama,
            nipbaru: p.nipbaru,
            email: p.email,
            nm_jabatan: p.nm_jabatan,
            id_satker: p.id_satker,
            nm_satker: p.nm_satker,
            is_pindahsatker: p.is_pindahsatker || 0,
            satker_asal: p.satker_asal || null,
            catatan_mutasi: p.catatan_mutasi || null
        }));

        return sendJSON(res, 200, { data: formatted });
    }

    // 2. GET /api/public/pegawai/:niplama
    const matchPegawaiNip = pathname.match(/^\/api\/public\/pegawai\/([^\/]+)$/);
    if (matchPegawaiNip && req.method === 'GET') {
        const niplama = decodeURIComponent(matchPegawaiNip[1]);
        const pegawai = PEGAWAI_DATA.find(p => String(p.niplama) === String(niplama) || String(p.nipbaru) === String(niplama));

        if (!pegawai) {
            return sendJSON(res, 404, { error: "Not Found", code: 404, message: `Pegawai dengan NIP ${niplama} tidak ditemukan.` });
        }

        // Cari tim-tim yang diikuti oleh pegawai ini
        const userTims = TIMS_DATA.filter(t => t.anggota_nips.some(a => a.niplama === pegawai.niplama)).map(t => ({
            id: t.id,
            id_satker: t.id_satker,
            nm_satker: t.nm_satker,
            nm_tim: t.nm_tim,
            deskripsi: t.deskripsi
        }));

        return sendJSON(res, 200, {
            data: {
                id: pegawai.id,
                nama_lengkap: pegawai.nama_lengkap,
                niplama: pegawai.niplama,
                nipbaru: pegawai.nipbaru,
                email: pegawai.email,
                nm_jabatan: pegawai.nm_jabatan,
                id_satker: pegawai.id_satker,
                nm_satker: pegawai.nm_satker,
                tims: userTims
            }
        });
    }

    // 3. GET /api/public/tim-kerja
    if (pathname === '/api/public/tim-kerja' && req.method === 'GET') {
        let result = TIMS_DATA;
        if (query.id_satker) {
            result = result.filter(t => String(t.id_satker) === String(query.id_satker));
        }
        if (query.nm_tim) {
            const search = String(query.nm_tim).toLowerCase();
            result = result.filter(t => t.nm_tim.toLowerCase().includes(search));
        }

        const formattedTims = result.map(t => {
            const anggotaList = t.anggota_nips.map(a => {
                const p = PEGAWAI_DATA.find(x => x.niplama === a.niplama) || {};
                return {
                    id: p.id || 0,
                    nama_lengkap: p.nama_lengkap || a.niplama,
                    niplama: p.niplama || a.niplama,
                    nipbaru: p.nipbaru || '',
                    email: p.email || '',
                    nm_jabatan: p.nm_jabatan || '',
                    jabatan_dalam_tim: a.jabatan_dalam_tim
                };
            });

            return {
                id: t.id,
                id_satker: t.id_satker,
                nm_satker: t.nm_satker,
                nm_tim: t.nm_tim,
                deskripsi: t.deskripsi,
                anggota: anggotaList
            };
        });

        return sendJSON(res, 200, { tims: formattedTims });
    }

    // 404 Route Not Found
    return sendJSON(res, 404, {
        error: "Not Found",
        code: 404,
        available_endpoints: [
            "GET /api/public/pegawai",
            "GET /api/public/pegawai/{niplama}",
            "GET /api/public/tim-kerja"
        ]
    });
});

server.on('error', (err) => {
    if (err.code === 'EADDRINUSE') {
        console.error("==========================================================");
        console.error(`✗ Port ${PORT} sudah dipakai aplikasi lain.`);
        console.error(`  Solusi: hentikan proses lama atau jalankan dengan port lain, contoh:`);
        console.error(`    PORT=3001 node simpati-server.js`);
        console.error(`  Lalu sesuaikan SIMPATI_API_BASE_URL di .env SIKEREN.`);
        console.error("==========================================================");
        process.exit(1);
    }
    console.error('✗ Server error:', err);
    process.exit(1);
});

server.listen(PORT, HOST, () => {
    console.log("==========================================================");
    console.log(`✓ SIMPATI Public API Server BERHASIL AKTIF di http://${HOST === '0.0.0.0' ? '127.0.0.1' : HOST}:${PORT}`);
    console.log(`  (bind ${HOST}:${PORT} — bisa diakses via localhost & 127.0.0.1)`);
    console.log(`✓ API Key: ${VALID_API_KEY}`);
    console.log("==========================================================");
    console.log("Endpoint Siap Digunakan:");
    console.log(` - GET http://127.0.0.1:${PORT}/api/public/pegawai`);
    console.log(` - GET http://127.0.0.1:${PORT}/api/public/pegawai/{niplama}`);
    console.log(` - GET http://127.0.0.1:${PORT}/api/public/tim-kerja`);
    console.log(` - GET http://127.0.0.1:${PORT}/health (tanpa API key)`);
    console.log("==========================================================");
});
