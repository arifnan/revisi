@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold text-dark">Responses for Form: {{ $form->title }}</h1>
        <div>
            <button id="showQuestions" class="btn btn-outline-primary me-2">
                <i class="bi bi-list-check"></i> Pertanyaan
            </button>
            <button id="showResponses" class="btn btn-primary">
                <i class="bi bi-card-checklist"></i> Responses
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Panel Pertanyaan -->
        <div id="questionsPanel" class="col-md-4 d-none">
            <div class"card shadow border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-question-circle"></i> Daftar Pertanyaan</h5>
                </div>
                <div class="card-body overflow-auto" style="max-height: 70vh;">
                    <ol class="list-group list-group-numbered">
                        @foreach($form->questions as $question)
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">{{ $question->question_text }}</div>
                                <small class="text-muted">{{ $question->question_type }}</small>
                            </div>
                        </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>

        <!-- Panel Responses -->
        <div id="responsesPanel" class="{{ $form->questions->isEmpty() ? 'col-md-12' : 'col-md-8' }}">
            <div class="card shadow border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Detail Responses</h5>
                </div>
                <div class="card-body">
                    @if($responses->isEmpty())
                        <div class="alert alert-info">Belum ada responses untuk form ini.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    @foreach($form->questions as $question)
                                        <th>{{ $question->question_text }}</th>
                                    @endforeach
                                    <th>Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($responses as $index => $response)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @foreach($form->questions as $question)
                                        <td>
                                            @if($question->question_type === 'file_upload')
                                                @if($response->answers->where('question_id', $question->id)->first()->file_path ?? false)
                                                    <a href="{{ Storage::url($response->answers->where('question_id', $question->id)->first()->file_path) }}" 
                                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                                        Lihat File
                                                    </a>
                                                @else
                                                    -
                                                @endif
                                            @else
                                                {{ $response->answers->where('question_id', $question->id)->first()->answer ?? '-' }}
                                            @endif
                                        </td>
                                    @endforeach
                                    <td>{{ $response->created_at->format('d M Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .list-group-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    .card {
        border-radius: 0.5rem;
    }
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
</style>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionsPanel = document.getElementById('questionsPanel');
    const responsesPanel = document.getElementById('responsesPanel');
    const showQuestionsBtn = document.getElementById('showQuestions');
    const showResponsesBtn = document.getElementById('showResponses');

    // Toggle panels
    function togglePanels(showQuestions) {
        if(showQuestions) {
            questionsPanel.classList.remove('d-none');
            questionsPanel.classList.add('col-md-4');
            responsesPanel.classList.remove('col-md-12');
            responsesPanel.classList.add('col-md-8');
            showQuestionsBtn.classList.add('btn-primary');
            showQuestionsBtn.classList.remove('btn-outline-primary');
            showResponsesBtn.classList.remove('btn-primary');
            showResponsesBtn.classList.add('btn-outline-primary');
        } else {
            questionsPanel.classList.add('d-none');
            questionsPanel.classList.remove('col-md-4');
            responsesPanel.classList.remove('col-md-8');
            responsesPanel.classList.add('col-md-12');
            showQuestionsBtn.classList.remove('btn-primary');
            showQuestionsBtn.classList.add('btn-outline-primary');
            showResponsesBtn.classList.add('btn-primary');
            showResponsesBtn.classList.remove('btn-outline-primary');
        }
    }

    // Button events
    showQuestionsBtn.addEventListener('click', () => togglePanels(true));
    showResponsesBtn.addEventListener('click', () => togglePanels(false));

    // Right click for questions
    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        togglePanels(true);
    });

    // Left click for responses
    responsesPanel.addEventListener('click', function() {
        togglePanels(false);
    });
});
</script>
@endsection
