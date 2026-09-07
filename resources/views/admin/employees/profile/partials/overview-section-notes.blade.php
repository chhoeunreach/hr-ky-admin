@php
    $notes = ($overviewNotes ?? collect())->where('section_key', $sectionKey)->values();
    $modalKey = str_replace('_', '-', $sectionKey);
    $sectionSamples = [
        'key_summary' => [
            'Total of 5 warnings recorded this month; 2 written and 1 final. / សរុបមានការព្រមាន ៥ ករណីក្នុងខែនេះ ក្នុងនោះ ២ ជាលាយលក្ខណ៍អក្សរ និង ១ ចុងក្រោយ។',
            'Most warnings relate to attendance (lateness). / ការព្រមានភាគច្រើនទាក់ទងនឹងវត្តមាន (ការមកយឺត)។',
            'No final warnings this period — improvement is noted. / គ្មានការព្រមានចុងក្រោយក្នុងរយៈពេលនេះ — កត់សម្គាល់ពីវឌ្ឍនភាព។',
        ],
        'attendance_summary' => [
            'Employee was absent 3 days this month without approval. / បុគ្គលិកអវត្តមាន ៣ ថ្ងៃក្នុងខែនេះ ដោយគ្មានការអនុញ្ញាត។',
            'Repeated lateness (5 times) within the grace period. / ការមកយឺតដដែលៗ (៥ ដង) ស្ថិតក្នុងរយៈពេលអនុគ្រោះ។',
            'Worked hours fell below standard by 20% this month. / ម៉ោងធ្វើការទាបជាងស្តង់ដារ ២០% ក្នុងខែនេះ។',
        ],
        'warning_status' => [
            '3 warnings remain open pending employee acknowledgment. / ការព្រមាន ៣ នៅតែបើក រង់ចាំការទទួលស្គាល់ពីបុគ្គលិក។',
            '1 warning escalated to the disciplinary committee. / ការព្រមាន ១ បានបញ្ជូនបន្តទៅគណៈកម្មការវិន័យ។',
            'All cancelled warnings were based on incorrect information. / ការព្រមានដែលលុបចោលទាំងអស់គឺដោយព័ត៌មានមិនត្រឹមត្រូវ។',
        ],
        'warning_type' => [
            'Most common type is written warning, followed by verbal. / ប្រភេទទូទៅបំផុតគឺការព្រមានជាលាយលក្ខណ៍អក្សរ បន្ទាប់មកការព្រមានមាត់។',
            'Suspension is used only for serious misconduct. / ការផ្អាកការងារប្រើសម្រាប់តែកំហុសធ្ងន់ធ្ងរប៉ុណ្ណោះ។',
            'Other category covers customer-complaint related records. / ប្រភេទផ្សេងៗ គ្របដណ្តប់ឯកសារទាក់ទងនឹងពាក្យបណ្តឹងអតិថិជន។',
        ],
        'violation_category' => [
            'Main violations are attendance/late and company policy. / កំហុសចម្បងគឺវត្តមាន/យឺត និងគោលការណ៍ក្រុមហ៊ុន។',
            'No work-performance violations recorded this period. / គ្មានកំហុសលទ្ធផលការងារចុះបញ្ជីក្នុងរយៈពេលនេះ។',
            'Safety-policy violation contributed to 2 of the records. / កំហុសសុវត្ថិភាពរួមចំណែកក្នុង ២ ក្នុងចំណោមឯកសារ។',
        ],
        'department_branch' => [
            'Sales department has the highest warning count this month. / ផ្នែកលក់មានចំនួនការព្រមានខ្ពស់បំផុតក្នុងខែនេះ។',
            'Phnom Penh branch reported 60% of the total warnings. / សាខាភ្នំពេញរាយការណ៍ ៦០% នៃការព្រមានសរុប។',
            'Branch comparison shows steady improvement in the last quarter. / ការប្រៀបធៀបសាខាបង្ហាញពីភាពប្រសើរឡើងនៅត្រីមាសចុងក្រោយ។',
        ],
        'repeated_employees' => [
            '2 employees received 3 or more warnings — consider a PIP. / បុគ្គលិក ២ នាក់បានទទួលការព្រមាន ៣ ដង ឬច្រើនជាង — គួរពិចារណាផែនការកែលម្អ (PIP)។',
            'Follow up with the supervisor on repeated offenders. / តាមដានជាមួយអ្នកគ្រប់គ្រងលើបុគ្គលិកមានពាក្យផ្ទួនៗ។',
            'Most repeated employees share the same supervisor. / បុគ្គលិកភាគច្រើនដែលមានពាក្យផ្ទួនៗ ស្ថិតក្រោមអ្នកគ្រប់គ្រងតែមួយ។',
        ],
        'upcoming_follow_up' => [
            'Follow-up scheduled for the 2 pending warnings next week. / គ្រោងតាមដានការព្រមានរង់ចាំ ២ ករណី នៅសប្តាហ៍ក្រោយ។',
            'HR to confirm employee acknowledgment by month end. / នាយកដ្ឋាន HR ត្រូវបញ្ជាក់ការទទួលស្គាល់ត្រឹមចុងខែ។',
            'Earliest follow-up date is 5 days from now. / កាលបរិច្ឆេទតាមដានជិតបំផុតគឺ ៥ ថ្ងៃពីពេលនេះ។',
        ],
        'recent_warnings' => [
            'Last warning issued on 2026-09-01 for repeated lateness. / ការព្រមានចុងក្រោយចេញនៅថ្ងៃ 2026-09-01 សម្រាប់ការមកយឺតដដែលៗ។',
            'Review recent warnings with the department head for root cause. / ពិនិត្យការព្រមានថ្មីៗជាមួយប្រធានផ្នែកដើម្បីរកមូលហេតុ។',
            'Recent records are pending final approval. / ឯកសារថ្មីៗកំពុងរង់ចាំការអនុម័តចុងក្រោយ។',
        ],
        'management_notes' => [
            'Overall disciplinary trend is improving; continue monitoring. / និន្នាការវិន័យទូទៅមានភាពប្រសើរឡើង; បន្តតាមដាន។',
            'Recommend a refresher training on company policy. / ផ្តល់អនុសាសន៍វគ្គបណ្តុះបណ្តាលឡើងវិញអំពីគោលការណ៍ក្រុមហ៊ុន។',
            'Committee to review escalated cases at the next meeting. / គណៈកម្មការនឹងពិនិត្យករណីដែលបញ្ជូនបន្តនៅកិច្ចប្រជុំបន្ទាប់។',
        ],
    ];
    $samples = $sectionSamples[$sectionKey] ?? [];
    $defaultPlaceholder = 'e.g. / ឧទាហរណ៍៖ ' . ($samples[0] ?? '');
@endphp

<div class="employee-overview-notes mt-3">
    <div class="employee-overview-notes-header">
        <span><strong>More Information / ព័ត៌មានបន្ថែម — {{ $sectionLabel }}</strong></span>
        @can('employee.discipline.manage')
            <button type="button" class="btn btn-outline-primary btn-xs" data-bs-toggle="modal" data-bs-target="#overviewAddNoteModal{{ $modalKey }}">
                <i class="link-icon" data-feather="plus"></i> Add
            </button>
        @endcan
    </div>

    @forelse($notes as $note)
        <div class="employee-overview-note-item">
            <div class="employee-overview-note-text">{{ $note->content }}</div>
            <div class="employee-overview-note-meta">
                <small>Added by {{ $note->creator?->name ?: 'N/A' }} · {{ optional($note->created_at)->format('Y-m-d H:i') }}</small>
                @can('employee.discipline.manage')
                    <span>
                        <button type="button" class="btn btn-outline-primary btn-xs" data-bs-toggle="modal" data-bs-target="#overviewEditNoteModal{{ $note->id }}">
                            Edit
                        </button>
                        <form method="post" action="{{ route('admin.employees.profile.overview-notes.destroy', [$employee->id, $note->id]) }}" class="d-inline" onsubmit="return confirm('Delete this note?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-xs">Delete</button>
                        </form>
                    </span>
                @endcan
            </div>
        </div>
    @empty
        <small class="text-muted">No additional information added. / មិនទាន់មានព័ត៌មានបន្ថែមទេ។</small>
    @endforelse
</div>

@can('employee.discipline.manage')
    <div class="modal fade" id="overviewAddNoteModal{{ $modalKey }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="post" action="{{ route('admin.employees.profile.overview-notes.store', $employee->id) }}" class="modal-content">
                @csrf
                <input type="hidden" name="section_key" value="{{ $sectionKey }}">
                <div class="modal-header">
                    <h5 class="modal-title">Add More Information — {{ $sectionLabel }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($samples)
                        <label class="form-label text-muted small mb-1">Suggestions / ការណែនាំ</label>
                        <div class="employee-overview-note-suggestions mb-2">
                            @foreach($samples as $index => $sample)
                                <button type="button" class="btn btn-outline-info btn-xs" data-overview-note-sample="{{ $sample }}" onclick="fillOverviewNote('overviewNoteTextarea{{ $modalKey }}', this.dataset.overviewNoteSample)">
                                    Sample {{ $index + 1 }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <textarea id="overviewNoteTextarea{{ $modalKey }}" class="form-control" name="content" rows="4" required placeholder="{{ $defaultPlaceholder }}"></textarea>
                    <small class="text-muted">Click a suggestion to use it as a template, then edit before saving. / ចុចលើគំរូដើម្បីប្រើជាគំរូ ហើយកែសម្រួលមុនរក្សាទុក។</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Add Note</button>
                </div>
            </form>
        </div>
    </div>

    @foreach($notes as $note)
        <div class="modal fade" id="overviewEditNoteModal{{ $note->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="post" action="{{ route('admin.employees.profile.overview-notes.update', [$employee->id, $note->id]) }}" class="modal-content">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="section_key" value="{{ $note->section_key }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Update More Information — {{ $sectionLabel }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($samples)
                            <label class="form-label text-muted small mb-1">Suggestions / ការណែនាំ</label>
                            <div class="employee-overview-note-suggestions mb-2">
                                @foreach($samples as $index => $sample)
                                    <button type="button" class="btn btn-outline-info btn-xs" data-overview-note-sample="{{ $sample }}" onclick="fillOverviewNote('overviewEditNoteTextarea{{ $note->id }}', this.dataset.overviewNoteSample)">
                                        Sample {{ $index + 1 }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <textarea id="overviewEditNoteTextarea{{ $note->id }}" class="form-control" name="content" rows="4" required>{{ $note->content }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Note</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endcan