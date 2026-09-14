@extends('layouts.app')

@section('title', 'Validator BT — Validasi Pra-BTel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-shield-check text-primary me-2"></i>Stage 4A : Validasi Pra-Buku Tanah Elektronik</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'validasi_btel', 'stageLabel' => 'Validator BT'])
@endsection