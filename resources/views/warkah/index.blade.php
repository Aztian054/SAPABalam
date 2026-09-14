@extends('layouts.app')

@section('title', 'Warkah — Pencarian & Data Warkah')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-archive-fill text-primary me-2"></i>Stage 3 : Pencarian & Data Warkah</h4>
    </div>

    @include('partials.stage_index', ['stage' => 'warkah', 'stageLabel' => 'Warkah'])
@endsection