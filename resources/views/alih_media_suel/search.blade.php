@extends('layouts.app')

@section('title', 'Alih Media SU — Smart Search')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-search text-primary me-2"></i>Hasil Pencarian DB Admin</h4>
        </div>

    <div class="card card-custom">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">
                Hasil untuk "{{ $search ?? 'semua' }}"
                <span class="badge bg-primary rounded-pill ms-1">{{ $tikets->count() }}</span>
            </h6>
        </div>
        <div class="card-body p-2">
            @include('partials.search_results', ['stage' => 'alih_media_suel', 'stageLabel' => 'Alih Media SU'])
        </div>
    </div>
@endsection