@extends('layouts.app')

@section('title', 'Alih Media BT — Pra-BTel')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-diff-fill text-primary me-2"></i>Stage 5A : Alih Media Pra-Buku Tanah Elektronik</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'alih_media_btel', 'stageLabel' => 'Alih Media BT'])
@endsection