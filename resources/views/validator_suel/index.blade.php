@extends('layouts.app')

@section('title', 'Validator SU — Validasi Pra-SuEl')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-shield-shaded text-primary me-2"></i>Stage 4B : Validasi Pra-Surat Ukur Elektronik</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'validasi_suel', 'stageLabel' => 'Validator SU'])
@endsection