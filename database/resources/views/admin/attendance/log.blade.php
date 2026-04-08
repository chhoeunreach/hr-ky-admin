@extends('layouts.master')

@section('title', __('index.attendance'))

@section('action', 'Attendance Log')


@section('main-content')

    <section class="content">


        @include('admin.section.flash_message')

        @include('admin.attendance.common.breadcrumb')

        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Attendance Logs</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                        <table id="dataTableExample" class="table">
                            <thead>
                            <tr>
                                <th>SN</th>
                                <th>{{ __('index.employee_name') }}</th>
                                <th class="text-center">Attendance Type</th>
                                <th class="text-center">Identifier</th>
                                <th class="text-center">{{ __('index.date') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($logData as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->user?->name }}</td>
                                        <td  class="text-center">{{ $log->attendance_type ?? 'N/A' }}</td>
                                        <td  class="text-center">{{ $log->identifier ?? 'N/A' }}</td>
                                        <td  class="text-center">{{ \App\Helpers\AttendanceHelper::formattedAttendanceDateTime(\App\Helpers\AppHelper::ifDateInBsEnabled(), $log->updated_at) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="100%">
                                            <p class="text-center"><b>{{ __('index.no_records_found') }}</b></p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                </div>
            </div>
        </div>


        <div class="modal fade" id="addslider" tabindex="-1" aria-labelledby="addslider" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <iframe id="iframeModalWindow" class="attendancelocation" height="500px" width="100%" src="" name="iframe_modal"></iframe>
                    </div>
                </div>
            </div>
        </div>

        @include('admin.attendance.common.edit-attendance-form')

        <!-- note for checkin and checkout -->
        <div id="noteModal" class="modal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Attendance Notes</h5>
                    </div>
                    <div class="modal-body">
                        <p><strong>Check-in Note:</strong> <span id="checkinNote"></span></p>
                        <p><strong>Check-out Note:</strong> <span id="checkoutNote"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@section('scripts')
    @include('admin.attendance.common.scripts')
    <script>
        $('#branch_id').change(function() {
            let selectedBranchId = $('#branch_id option:selected').val();

            let departmentId = "{{  $filterParameter['department_id'] ?? '' }}";
            console.log(departmentId);
            $('#department_id').empty();
            if (selectedBranchId) {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('admin/departments/get-All-Departments') }}" + '/' + selectedBranchId ,
                }).done(function(response) {
                    if(!departmentId){
                        $('#department_id').append('<option disabled  selected >{{ __('index.select_department') }}</option>');
                    }
                    response.data.forEach(function(data) {
                        $('#department_id').append('<option ' + ((data.id == departmentId) ? "selected" : '') + ' value="'+data.id+'" >'+data.dept_name+'</option>');
                    });
                });
            }
        }).trigger('change');


        document.addEventListener('DOMContentLoaded', function() {
            const noteModal = new bootstrap.Modal(document.getElementById('noteModal'));

            document.querySelectorAll('.noteLink').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    const checkinNote = this.getAttribute('data-checkin_note');
                    const checkoutNote = this.getAttribute('data-checkout_note');

                    document.getElementById('checkinNote').textContent = checkinNote || '';
                    document.getElementById('checkoutNote').textContent = checkoutNote || '';

                    noteModal.show();
                });
            });
        });
    </script>
@endsection

