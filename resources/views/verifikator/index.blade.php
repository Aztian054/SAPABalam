@extends('layouts.app')

@section('title', 'Verifikator — Verifikasi Berkas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-clipboard-check-fill text-primary me-2"></i>Stage 2 : Verifikasi Berkas</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'verifikasi', 'stageLabel' => 'Verifikator'])
@endsection