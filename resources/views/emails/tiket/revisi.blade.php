@component('mail::message')
# Permohonan Perlu Perbaikan (Revisi)

Yth. **{{ $tiket->nama_pemohon }}**,

Berkas permohonan Anda **{{ $tiket->kode_tiket }}** telah **dikembalikan untuk diperbaiki** oleh Petugas Verifikator.

## Detail Tiket
- **Kode Tiket** : {{ $tiket->kode_tiket }}
- **Jenis Permohonan** : {{ $tiket->jenisPermohonan->nama ?? '-' }}
- **Tanggal Masuk** : {{ optional($tiket->tanggal_masuk)->format('d/m/Y') }}
- **Status** : **Revisi / Perlu Perbaikan**

@isset($catatan)
## Catatan Revisi / Perbaikan
{{ $catatan }}
@endisset

## Tindakan yang Perlu Dilakukan
Silakan login ke sistem Loket untuk melihat detail revisi dan mengajukan **perbaikan berkas** (Resubmit Revisi).

@component('mail::button', ['url' => $urlLoket])
Lihat Detail & Perbaiki Berkas
@endcomponent

Terima kasih atas kerjasamanya.

Hormat kami,
**{{ config('app.name', 'LOKET BPN') }}**
@endcomponent
