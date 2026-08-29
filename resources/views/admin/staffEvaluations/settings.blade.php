@extends('layouts.master')

@section('title', 'Evaluation Settings')
@section('action', 'Evaluation Settings')

@section('main-content')
    <section class="content">
        <div class="alert alert-info">
            These defaults guide the AI generator and printed forms. Formal editable settings can be expanded later without changing employee or evaluation history.
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">Default KNEAYERNG 100-Point Sections</h6></div>
                    <div class="card-body table-responsive">
                        <table class="table table-sm">
                            <thead><tr><th>Section</th><th>Default Weight</th></tr></thead>
                            <tbody>
                            @foreach($defaultSections as $section)
                                <tr><td>{{ $section['name'] }}</td><td>{{ $section['weight'] }}%</td></tr>
                            @endforeach
                            </tbody>
                            <tfoot><tr><th>Total</th><th>{{ collect($defaultSections)->sum('weight') }}%</th></tr></tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="card h-100">
                    <div class="card-header"><h6 class="mb-0">Flexible Options</h6></div>
                    <div class="card-body">
                        <h6>Question Types</h6>
                        <div class="mb-3">
                            @foreach($questionTypes as $type)
                                <span class="badge bg-light text-dark border mb-1">{{ $type }}</span>
                            @endforeach
                        </div>
                        <h6>Evaluation Types</h6>
                        <div>
                            @foreach($evaluationTypes as $type)
                                <span class="badge bg-light text-dark border mb-1">{{ $type }}</span>
                            @endforeach
                        </div>
                        <hr>
                        <p class="text-muted mb-0">AI suggestions are only suggestions. Promotion, warning, salary, transfer, and termination decisions remain human-controlled.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
