@extends('layouts.app')

@section('title', 'Alih Media SU — Pra-SuEl')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-spreadsheet-fill text-primary me-2"></i>Stage 5B : Alih Media Pra-Surat Ukur Elektronik</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'alih_media_suel', 'stageLabel' => 'Alih Media SU'])
@endsection