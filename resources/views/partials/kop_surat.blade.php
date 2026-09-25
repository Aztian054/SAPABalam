{{-- Kop Surat Resmi — Kantor Pertanahan Kota Bandar Lampung (Kementerian ATR/BPN) --}}
@props(['subtitle' => null])

<style>
    .kop-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        padding-bottom: 6px;
        border-bottom: 3px double #222;
        margin-bottom: 14px;
        font-family: 'Times New Roman', Times, serif;
        color: #000;
    }
    .kop-logo { flex: 0 0 auto; }
    .kop-logo img { display: block; width: 84px; height: 84px; object-fit: contain; }
    .kop-text { flex: 1 1 auto; text-align: center; line-height: 1.2; }
    .kop-institusi { font-size: 11.5px; font-weight: 700; letter-spacing: 0.3px; }
    .kop-kantor { font-size: 15px; font-weight: 700; letter-spacing: 0.6px; }
    .kop-provinsi { font-size: 10px; font-weight: 700; letter-spacing: 2.5px; }
    .kop-alamat { font-size: 8.5px; margin-top: 3px; }
    .kop-subtitle {
        text-align: center;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin: -6px 0 14px;
    }
</style>

<div class="kop-wrapper">
    <div class="kop-logo">
        <img src="{{ asset('asset/images/logobpn2026.svg') }}" alt="Logo Kementerian ATR/BPN">
    </div>
    <div class="kop-text">
        <div class="kop-institusi">KEMENTERIAN AGRARIA DAN TATA RUANG/BADAN PERTANAHAN NASIONAL</div>
        <div class="kop-kantor">KANTOR PERTANAHAN KOTA BANDAR LAMPUNG</div>
        <div class="kop-provinsi">PROVINSI LAMPUNG</div>
        <div class="kop-alamat">Jln. Drs. Warsito No. 5, Bandar Lampung 35215 &nbsp;Telp. (0721) 486217/Fax. (0721) 480223 &nbsp;Email : kot-bandarlampung@atrbpn.go.id</div>
    </div>
</div>

@if($subtitle)
    <div class="kop-subtitle">{{ $subtitle }}</div>
@endif