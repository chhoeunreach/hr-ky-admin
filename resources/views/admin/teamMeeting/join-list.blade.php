<div class="modal fade" id="meetingJoinList" tabindex="-1" aria-labelledby="meetingJoinListLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="meetingJoinListLabel">{{ __('index.meeting_join_list') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('index.close') }}"></button>
            </div>
            <div class="modal-body">
                <h6 class="join-list-meeting-title mb-1"></h6>
                <p class="text-muted join-list-summary mb-3"></p>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('index.employee') }}</th>
                            <th>{{ __('index.checked_in_at') }}</th>
                        </tr>
                        </thead>
                        <tbody class="join-list-rows">
                        <tr>
                            <td colspan="3" class="text-center text-muted">{{ __('index.loading') }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('index.close') }}</button>
            </div>
        </div>
    </div>
</div>
