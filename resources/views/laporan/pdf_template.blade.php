<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Service Completion Receipt - {{ $kunjungan->nomor }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 10px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #002266; /* Diubah ke warna Corporate Blue */
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            color: #002266; /* Diubah ke warna Corporate Blue */
            text-transform: uppercase;
        }
        .subtitle {
            font-size: 9px;
            color: #475569;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            background-color: #f0f4ff; /* Background biru sangat muda */
            color: #002266;
            padding: 6px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th, .data-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #002266; /* Header tabel jadi Corporate Blue */
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .signature-box {
            text-align: center;
            width: 45%;
            vertical-align: top;
        }
        .sig-label {
            height: 30px;
            font-size: 10px;
        }
        .sig-area {
            height: 70px;
            line-height: 70px;
        }
        .sign-img {
            height: 60px;
            vertical-align: middle;
        }
        .sig-name {
            border-top: 1px solid #002266;
            display: inline-block;
            width: 80%;
            padding-top: 3px;
            font-weight: bold;
            color: #1e293b;
            font-size: 10px;
        }
        .footer-note {
            margin-top: 25px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen -->
    <table class="header-table">
        <tr>
            <!-- INI BAGIAN LOGONYA -->
            <td style="width: 15%; text-align: left; vertical-align: middle;">
                <img src="{{ public_path('images/logo-masit.png') }}" alt="Logo MAS-IT" style="max-height: 55px; object-fit: contain;">
            </td>
            <!-- INFORMASI PERUSAHAAN -->
            <td style="width: 45%; vertical-align: middle;">
                <div class="title">PT MAS-IT SOLUSI INTEGRASI</div>
                <div class="subtitle">IT Infrastructure, Networking & Security Management Solutions</div>
                <div class="subtitle">Website: mas-it.id | Email: support@mas-it.id</div>
            </td>
            <!-- DETAIL DOKUMEN -->
            <td style="width: 40%; text-align: right; vertical-align: middle;">
                <div style="font-size: 12px; font-weight: bold; color: #003399;">BUKTI PENYELESAIAN PEKERJAAN</div>
                <div style="font-size: 10px; font-family: monospace; color: #334155;">No: {{ $kunjungan->nomor }}</div>
                <div style="font-size: 9px; color: #64748b;">Tanggal Cetak: {{ date('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-title">SERVICE COMPLETION RECEIPT</div>

    <!-- Informasi Kunjungan & Customer -->
    <table class="info-table">
        <tr>
            <td style="width: 20%; font-weight: bold; color: #002266;">Perusahaan (Klien)</td>
            <td style="width: 30%;">: {{ $kunjungan->customer->nama_perusahaan ?? '-' }}</td>
            <td style="width: 20%; font-weight: bold; color: #002266;">Engineer Bertugas</td>
            <td style="width: 30%;">: {{ $kunjungan->engineer->user->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #002266;">Person In Charge (PIC)</td>
            <td>: {{ $kunjungan->customer->pic ?? '-' }} ({{ $kunjungan->customer->telepon ?? '-' }})</td>
            <td style="font-weight: bold; color: #002266;">Kontak Engineer</td>
            <td>: {{ $kunjungan->engineer->kontak ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #002266;">Jadwal Pelaksanaan</td>
            <td>: {{ $kunjungan->tanggal }} ({{ $kunjungan->waktu }})</td>
            <td style="font-weight: bold; color: #002266;">Status Pekerjaan</td>
            <td>: <strong>{{ strtoupper($kunjungan->status) }}</strong></td>
        </tr>
        <tr>
            <td style="font-weight: bold; color: #002266;">Lokasi Pekerjaan</td>
            <td colspan="3">: {{ $kunjungan->lokasi }}</td>
        </tr>
    </table>

    <!-- Ringkasan Pekerjaan & Waktu Aktual -->
    @php
        $gpsIn = ($kunjungan->check_in_latitude && $kunjungan->check_in_longitude)
            ? $kunjungan->check_in_latitude.', '.$kunjungan->check_in_longitude
            : ($aktivitas->lokasi ?? '-');
        $gpsOut = ($kunjungan->check_out_latitude && $kunjungan->check_out_longitude)
            ? $kunjungan->check_out_latitude.', '.$kunjungan->check_out_longitude
            : '-';
    @endphp
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%;">Item Pekerjaan</th>
                <th style="width: 35%;">Deskripsi & Catatan Akhir</th>
                <th style="width: 20%;">Waktu Mulai (GPS)</th>
                <th style="width: 20%;">Waktu Selesai (GPS)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; color: #002266;">{{ $kunjungan->pekerjaan }}</td>
                <td>{{ $aktivitas->catatan ?? 'Pekerjaan telah diselesaikan sesuai dengan instruksi kerja.' }}</td>
                <td>
                    {{ $aktivitas->waktu_mulai ? date('d/m/Y H:i', strtotime($aktivitas->waktu_mulai)) : '-' }}<br>
                    <small style="color: #003399; font-size: 8px;">GPS: {{ $gpsIn }}</small>
                </td>
                <td>
                    {{ $aktivitas->waktu_selesai ? date('d/m/Y H:i', strtotime($aktivitas->waktu_selesai)) : '-' }}<br>
                    <small style="color: #003399; font-size: 8px;">GPS: {{ $gpsOut }}</small>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Kolom Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td class="signature-box">
                <div class="sig-label">
                    <div style="color: #475569;">Dikerjakan Oleh,</div>
                    <div style="font-weight: bold; color: #002266;">Engineer MAS-IT</div>
                </div>
                <div class="sig-area">
                    @if($bukti && $bukti->tanda_tangan_engineer)
                        <img src="{{ $bukti->tanda_tangan_engineer }}" class="sign-img" alt="Engineer Signature">
                    @endif
                </div>
                <div class="sig-name">
                    {{ $kunjungan->engineer->user->nama ?? 'Engineer' }}
                </div>
            </td>
            <td style="width: 10%;"></td>
            <td class="signature-box">
                <div class="sig-label">
                    <div style="color: #475569;">Disetujui & Diverifikasi Oleh,</div>
                    <div style="font-weight: bold; color: #002266;">Customer / Klien</div>
                </div>
                <div class="sig-area">
                    @if($bukti && $bukti->tanda_tangan_customer)
                        <img src="{{ $bukti->tanda_tangan_customer }}" class="sign-img" alt="Digital Signature">
                    @endif
                </div>
                <div class="sig-name">
                    {{ $kunjungan->customer->pic ?? 'Customer PIC' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat dan diverifikasi secara digital melalui Sistem Manajemen Kunjungan PT MAS-IT Solusi Integrasi.<br>
        Segala bentuk perubahan data setelah tanda tangan terverifikasi dianggap tidak sah.
    </div>

</body>
</html>