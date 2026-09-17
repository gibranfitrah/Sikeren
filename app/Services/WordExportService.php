<?php

namespace App\Services;

use ZipArchive;
use Carbon\Carbon;
use App\Task;
use App\User;
use App\penugasan;
use Illuminate\Support\Facades\DB;

class WordExportService
{
    /**
     * Escape string for XML in Word OpenXML
     */
    protected static function escapeXml($text)
    {
        return htmlspecialchars((string)$text, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    /**
     * Buat template dasar paket OpenXML DOCX
     */
    protected static function createDocxPackage($documentXml)
    {
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
    <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>';

        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
    <w:docDefaults>
        <w:rPrDefault>
            <w:rPr>
                <w:rFonts w:ascii="Arial" w:hAnsi="Arial" w:cs="Arial"/>
                <w:sz w:val="22"/>
                <w:szCs w:val="22"/>
                <w:lang w:val="id-ID"/>
            </w:rPr>
        </w:rPrDefault>
        <w:pPrDefault>
            <w:pPr>
                <w:spacing w:line="260" w:lineRule="auto" w:after="100"/>
            </w:pPr>
        </w:pPrDefault>
    </w:docDefaults>
</w:styles>';

        $tempFile = tempnam(sys_get_temp_dir(), 'sikeren_docx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Gagal membuat file DOCX.");
        }

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);
        $zip->addFromString('word/styles.xml', $stylesXml);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->close();

        return $tempFile;
    }

    /**
     * Generate Surat Undangan / Penugasan Resmi Kegiatan (.docx)
     */
    public static function generateSuratKegiatan(Task $task)
    {
        Carbon::setLocale('id');

        $judulKegiatan = self::escapeXml($task->text);
        $agenda = self::escapeXml($task->agenda ?? $task->text);
        $tim = self::escapeXml($task->tim ?? 'BPS Provinsi Sulawesi Tenggara');
        $tempat = self::escapeXml($task->tempat ?? 'Aula Kantor BPS Provinsi Sulawesi Tenggara');
        
        $startDate = $task->start_date ? Carbon::parse($task->start_date)->translatedFormat('l, d F Y') : '-';
        $endDate = $task->date_akhir ? Carbon::parse($task->date_akhir)->translatedFormat('l, d F Y') : $startDate;
        
        $rentangTanggal = ($task->date_akhir && $task->date_akhir != $task->start_date)
            ? "{$startDate} s.d {$endDate}"
            : $startDate;

        $jamMulai = $task->start_jam ? substr($task->start_jam, 0, 5) : '09:00';
        $jamSelesai = $task->end_jam ? substr($task->end_jam, 0, 5) : 'Selesai';
        $waktu = "Pukul {$jamMulai} - {$jamSelesai} WITA";

        $pjNama = $task->penanggung_jawab ?? ($task->pemimpin ?? 'Ketua Tim / Penanggung Jawab');
        
        // Cari NIP PJ jika ada
        $pjUser = User::where('nama_lengkap', 'LIKE', '%' . $pjNama . '%')->first();
        $pjNip = $pjUser ? ($pjUser->nipbaru ?: $pjUser->niplama) : '-';
        $pjNamaXml = self::escapeXml($pjNama);
        $pjNipXml = self::escapeXml($pjNip);

        $nomorSurat = "B-" . str_pad($task->id, 4, '0', STR_PAD_LEFT) . "/74000/KS.300/" . date('Y');
        $tanggalSurat = Carbon::parse($task->start_date ?? now())->translatedFormat('d F Y');

        // Ambil daftar peserta yang ditugaskan
        $pesertaQuery = penugasan::where('id_kegiatan', $task->id)->get();
        $pesertaRowsXml = '';
        $no = 1;

        if ($pesertaQuery->count() > 0) {
            foreach ($pesertaQuery as $p) {
                $pNama = self::escapeXml($p->peserta ?: '-');
                $pNip = self::escapeXml($p->niplama ?: '-');
                $pStatus = self::escapeXml($p->status_kehadiran ?: 'Ditugaskan');

                $pesertaRowsXml .= '
                <w:tr>
                    <w:tc>
                        <w:tcPr><w:tcW w:w="600" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                        <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:t>' . $no++ . '</w:t></w:r></w:p>
                    </w:tc>
                    <w:tc>
                        <w:tcPr><w:tcW w:w="4200" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                        <w:p><w:pPr><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . $pNama . '</w:t></w:r></w:p>
                    </w:tc>
                    <w:tc>
                        <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                        <w:p><w:pPr><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:t>' . $pNip . '</w:t></w:r></w:p>
                    </w:tc>
                    <w:tc>
                        <w:tcPr><w:tcW w:w="1800" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                        <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:t>' . $pStatus . '</w:t></w:r></w:p>
                    </w:tc>
                </w:tr>';
            }
        } else {
            $pesertaRowsXml = '
            <w:tr>
                <w:tc>
                    <w:tcPr><w:gridSpan w:val="4"/><w:tcW w:w="9200" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:i/></w:rPr><w:t>Daftar penugasan peserta mengacu pada anggota tim kerja.</w:t></w:r></w:p>
                </w:tc>
            </w:tr>';
        }

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
            xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <w:body>
        <!-- KOP SURAT RESMI BPS SULTRA -->
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="20" w:line="220" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="26"/>
                </w:rPr>
                <w:t>BADAN PUSAT STATISTIK</w:t>
            </w:r>
        </w:p>
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="20" w:line="220" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:b/>
                    <w:sz w:val="24"/>
                </w:rPr>
                <w:t>PROVINSI SULAWESI TENGGARA</w:t>
            </w:r>
        </w:p>
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="60" w:line="200" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:sz w:val="18"/>
                    <w:color w:val="444444"/>
                </w:rPr>
                <w:t>Jl. Made Sabara No. 19, Kendari 93111 • Telp (0401) 3121572 • Faks (0401) 3121573</w:t>
            </w:r>
        </w:p>
        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:pBdr>
                    <w:bottom w:val="double" w:sz="12" w:space="4" w:color="000000"/>
                </w:pBdr>
                <w:spacing w:after="200" w:line="200" w:lineRule="auto"/>
            </w:pPr>
            <w:r>
                <w:rPr>
                    <w:rFonts w:ascii="Arial" w:hAnsi="Arial"/>
                    <w:sz w:val="18"/>
                    <w:color w:val="444444"/>
                </w:rPr>
                <w:t>Email: bps7400@bps.go.id • Laman: sultra.bps.go.id</w:t>
            </w:r>
        </w:p>

        <!-- TANGGAL DAN NOMOR SURAT -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/>
                    <w:insideH w:val="none"/><w:insideV w:val="none"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="5400" w:type="dxa"/></w:tcPr>
                    <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Nomor    : ' . $nomorSurat . '</w:t></w:r></w:p>
                    <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Sifat    : Penting / Biasa</w:t></w:r></w:p>
                    <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Lampiran : 1 (Satu) Berkas</w:t></w:r></w:p>
                    <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Hal      : Undangan / Penugasan Pelaksanaan ' . $judulKegiatan . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="3800" w:type="dxa"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="right"/><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Kendari, ' . $tanggalSurat . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <!-- TUJUAN SURAT -->
        <w:p><w:pPr><w:spacing w:before="120" w:after="40" w:line="240"/></w:pPr>
            <w:r><w:t>Yth.</w:t></w:r>
        </w:p>
        <w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr>
            <w:r><w:rPr><w:b/></w:rPr><w:t>Daftar Pegawai / Anggota Tim Terlampir</w:t></w:r>
        </w:p>
        <w:p><w:pPr><w:spacing w:after="160" w:line="240"/></w:pPr>
            <w:r><w:t>di Tempat</w:t></w:r>
        </w:p>

        <!-- PARAGRAF PEMBUKA -->
        <w:p><w:pPr><w:jc w:val="both"/><w:spacing w:after="140" w:line="276"/></w:pPr>
            <w:r><w:t xml:space="preserve">        Dalam rangka mendukung kelancaran pelaksanaan tugas dan program kerja pada Tim Kerja </w:t></w:r>
            <w:r><w:rPr><w:b/></w:rPr><w:t>' . $tim . '</w:t></w:r>
            <w:r><w:t xml:space="preserve">, bersama ini kami mengundang / menugaskan Bapak/Ibu untuk hadir dan berpartisipasi aktif pada kegiatan </w:t></w:r>
            <w:r><w:rPr><w:b/></w:rPr><w:t>' . $judulKegiatan . '</w:t></w:r>
            <w:r><w:t xml:space="preserve">, yang akan diselenggarakan dengan rincian jadwal sebagai berikut:</w:t></w:r>
        </w:p>

        <!-- TABEL RINCIAN KEGIATAN -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="8800" w:type="dxa"/>
                <w:tblInd w:w="400" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/>
                    <w:insideH w:val="none"/><w:insideV w:val="none"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="2200" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Hari, Tanggal</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>:</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="6300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . $rentangTanggal . '</w:t></w:r></w:p></w:tc>
            </w:tr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="2200" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Waktu / Pukul</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>:</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="6300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>' . $waktu . '</w:t></w:r></w:p></w:tc>
            </w:tr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="2200" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Tempat / Lokasi</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>:</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="6300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . $tempat . '</w:t></w:r></w:p></w:tc>
            </w:tr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="2200" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Agenda Pembahasan</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>:</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="6300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>' . $agenda . '</w:t></w:r></w:p></w:tc>
            </w:tr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="2200" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Penanggung Jawab</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>:</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="6300" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:spacing w:after="40" w:line="240"/></w:pPr><w:r><w:t>' . $pjNamaXml . '</w:t></w:r></w:p></w:tc>
            </w:tr>
        </w:tbl>

        <!-- PARAGRAF PENUTUP -->
        <w:p><w:pPr><w:jc w:val="both"/><w:spacing w:before="140" w:after="180" w:line="276"/></w:pPr>
            <w:r><w:t xml:space="preserve">        Mengingat pentingnya agenda tersebut, diharapkan kehadiran Bapak/Ibu tepat pada waktunya. Demikian surat undangan / penugasan ini disampaikan, atas perhatian dan kerja sama yang baik diucapkan terima kasih.</w:t></w:r>
        </w:p>

        <!-- TANDA TANGAN -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="none"/><w:left w:val="none"/><w:bottom w:val="none"/><w:right w:val="none"/>
                    <w:insideH w:val="none"/><w:insideV w:val="none"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="5000" w:type="dxa"/></w:tcPr><w:p/></w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Penanggung Jawab / Ketua Tim,</w:t></w:r></w:p>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="550" w:line="220"/></w:pPr><w:r><w:rPr><w:i/></w:rPr><w:t>(Ditandatangani Secara Elektronik)</w:t></w:r></w:p>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="20" w:line="220"/></w:pPr><w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t>' . $pjNamaXml . '</w:t></w:r></w:p>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>NIP. ' . $pjNipXml . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
        </w:tbl>

        <!-- PAGE BREAK UNTUK LAMPIRAN DAFTAR PESERTA -->
        <w:p><w:r><w:br w:type="page"/></w:r></w:p>

        <!-- HEADER LAMPIRAN -->
        <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Lampiran Surat Nomor : ' . $nomorSurat . '</w:t></w:r></w:p>
        <w:p><w:pPr><w:spacing w:after="30" w:line="220"/></w:pPr><w:r><w:t>Tanggal Pelaksanaan   : ' . $rentangTanggal . '</w:t></w:r></w:p>
        <w:p><w:pPr><w:spacing w:after="160" w:line="220"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>Perihal               : Daftar Peserta / Anggota Tim ' . $judulKegiatan . '</w:t></w:r></w:p>

        <w:p>
            <w:pPr>
                <w:jc w:val="center"/>
                <w:spacing w:after="160" w:line="240"/>
            </w:pPr>
            <w:r>
                <w:rPr><w:b/><w:sz w:val="24"/></w:rPr>
                <w:t>DAFTAR PENUGASAN PESERTA KEGIATAN</w:t>
            </w:r>
        </w:p>

        <!-- TABEL DAFTAR PESERTA -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="9200" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="2563EB"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>NO</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="4200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="2563EB"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>NAMA LENGKAP PEGAWAI</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="2600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="2563EB"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>NIP PEGAWAI</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1800" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="2563EB"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>STATUS</w:t></w:r></w:p>
                </w:tc>
            </w:tr>
            ' . $pesertaRowsXml . '
        </w:tbl>

        <w:sectPr>
            <w:pgSz w:w="12240" w:h="15840"/>
            <w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440" w:header="720" w:footer="720" w:gutter="0"/>
        </w:sectPr>
    </w:body>
</w:document>';

        return self::createDocxPackage($documentXml);
    }

    /**
     * Generate Rekapitulasi Daftar Agenda Kegiatan & Rapat (.docx)
     */
    public static function generateDaftarAgenda($kegiatans)
    {
        Carbon::setLocale('id');

        $tanggalCetak = Carbon::now()->translatedFormat('d F Y, H:i') . ' WITA';
        $totalKegiatan = count($kegiatans);
        $totalSelesai = $kegiatans->where('status', 'Selesai')->count();
        $totalBerjalan = $totalKegiatan - $totalSelesai;

        $rowsXml = '';
        $no = 1;

        foreach ($kegiatans as $k) {
            $nama = self::escapeXml($k->text);
            $jenis = self::escapeXml($k->jenis ?? 'Kegiatan');
            $tim = self::escapeXml($k->tim ?? '-');
            $pj = self::escapeXml($k->penanggung_jawab ?? ($k->pemimpin ?? '-'));
            $tempat = self::escapeXml($k->tempat ?? 'Kantor BPS');
            
            $start = $k->start_date ? Carbon::parse($k->start_date)->format('d/m/Y') : '-';
            $jam = $k->start_jam ? substr($k->start_jam, 0, 5) : '';
            $jadwal = $start . ($jam ? " ({$jam})" : '');
            
            $status = self::escapeXml($k->status ?? 'Sedang Berjalan');

            $rowsXml .= '
            <w:tr>
                <w:tc>
                    <w:tcPr><w:tcW w:w="500" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $no++ . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="3000" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:rPr><w:b/></w:rPr><w:t>' . $nama . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1200" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $jenis . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1400" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $tim . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1400" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $jadwal . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1600" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $pj . '</w:t></w:r></w:p>
                </w:tc>
                <w:tc>
                    <w:tcPr><w:tcW w:w="1200" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:t>' . $status . '</w:t></w:r></w:p>
                </w:tc>
            </w:tr>';
        }

        if (empty($rowsXml)) {
            $rowsXml = '
            <w:tr>
                <w:tc>
                    <w:tcPr><w:gridSpan w:val="7"/><w:tcW w:w="10300" w:type="dxa"/><w:vAlign w:val="center"/></w:tcPr>
                    <w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="220"/></w:pPr><w:r><w:rPr><w:i/></w:rPr><w:t>Tidak ada data kegiatan terdaftar.</w:t></w:r></w:p>
                </w:tc>
            </w:tr>';
        }

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
            xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <w:body>
        <!-- KOP REKAPITULASI AGENDA -->
        <w:p>
            <w:pPr><w:jc w:val="center"/><w:spacing w:after="30" w:line="220"/></w:pPr>
            <w:r><w:rPr><w:b/><w:sz w:val="26"/></w:rPr><w:t>BADAN PUSAT STATISTIK PROVINSI SULAWESI TENGGARA</w:t></w:r>
        </w:p>
        <w:p>
            <w:pPr><w:jc w:val="center"/><w:spacing w:after="60" w:line="220"/></w:pPr>
            <w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="2563EB"/></w:rPr><w:t>REKAPITULASI DAFTAR AGENDA KEGIATAN &amp; RAPAT KERJA</w:t></w:r>
        </w:p>
        <w:p>
            <w:pPr><w:jc w:val="center"/><w:pBdr><w:bottom w:val="single" w:sz="8" w:space="4" w:color="000000"/></w:pBdr><w:spacing w:after="160" w:line="200"/></w:pPr>
            <w:r><w:rPr><w:sz w:val="18"/><w:color w:val="666666"/></w:rPr><w:t>Dicetak melalui Sistem Manajemen Kegiatan (SIKEREN) pada: ' . $tanggalCetak . '</w:t></w:r>
        </w:p>

        <!-- KPI SUMMARY -->
        <w:p><w:pPr><w:spacing w:after="120" w:line="240"/></w:pPr>
            <w:r><w:rPr><w:b/></w:rPr><w:t>Total Agenda: ' . $totalKegiatan . ' Kegiatan | Berjalan: ' . $totalBerjalan . ' | Selesai: ' . $totalSelesai . '</w:t></w:r>
        </w:p>

        <!-- TABEL REKAP DAFTAR AGENDA -->
        <w:tbl>
            <w:tblPr>
                <w:tblW w:w="10300" w:type="dxa"/>
                <w:tblBorders>
                    <w:top w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:left w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:bottom w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:right w:val="single" w:sz="6" w:space="0" w:color="CCCCCC"/>
                    <w:insideH w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                    <w:insideV w:val="single" w:sz="4" w:space="0" w:color="E0E0E0"/>
                </w:tblBorders>
            </w:tblPr>
            <w:tr>
                <w:tc><w:tcPr><w:tcW w:w="500" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>NO</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="3000" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>AGENDA / KEGIATAN</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="1200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>JENIS</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="1400" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>TIM KERJA</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="1400" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>JADWAL</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="1600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>PENANGGUNG JAWAB</w:t></w:r></w:p></w:tc>
                <w:tc><w:tcPr><w:tcW w:w="1200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="1E40AF"/><w:vAlign w:val="center"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="0" w:line="240"/></w:pPr><w:r><w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr><w:t>STATUS</w:t></w:r></w:p></w:tc>
            </w:tr>
            ' . $rowsXml . '
        </w:tbl>

        <w:sectPr>
            <w:pgSz w:w="15840" w:h="12240" w:orient="landscape"/>
            <w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080" w:header="720" w:footer="720" w:gutter="0"/>
        </w:sectPr>
    </w:body>
</w:document>';

        return self::createDocxPackage($documentXml);
    }
}
