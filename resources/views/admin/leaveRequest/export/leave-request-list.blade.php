<table>
    <thead>
    <tr>
        <th style="text-align: center;"><b>ថ្ងៃខែឆ្នាំ</b></th>
        <th style="text-align: center;"><b>លេខសម្គាល់</b></th>
        <th style="text-align: center;"><b>ឈ្មោះ</b></th>
        <th style="text-align: center;"><b>ប្រភេទឈប់</b></th>
        <th style="text-align: center;"><b>Paid/Unpad</b></th>
        <th style="text-align: center;"><b>រយៈពេល (ថ្ងៃ)</b></th>
        <th style="text-align: center;"><b>ចាប់ពីថ្ងៃ</b></th>
        <th style="text-align: center;"><b>ដលថ្ងៃ</b></th>
        <th style="text-align: center;"><b>ផ្សេង</b></th>
        <th style="text-align: center;"><b>ស្ថានភាព</b></th>
        <th style="text-align: center;"><b>អនុញ្ញាតិដោយ</b></th>
        <th style="text-align: center;"><b>ផ្សេងៗ</b></th>
        <th style="text-align: center;"><b>ថ្ងៃខែស្នើរ</b></th>
        <th style="text-align: center;"><b>ថ្ងៃខែអនុញ្ញាតិ</b></th>
        <th style="text-align: center;"><b>ខែ-ឆ្នាំ</b></th>
        <th style="text-align: center;"><b>សម្គាល់-ខែ-ឆ្នាំ</b></th>
    </tr>
    </thead>
    <tbody>
    @forelse($leaveDetails as $leave)
        @php
            $requestedDate = $leave->leave_requested_date ? date('d-m-Y', strtotime($leave->leave_requested_date)) : '';
            $employeeCode = $leave->leaveRequestedBy?->employee_code ?? '';
            $username = $leave->leaveRequestedBy?->username ?? '';
            $employeeName = $leave->leaveRequestedBy?->name ?? 'N/A';
            $leaveTypeName = $leave->leaveType?->name ?? '';
            $isPaid = !is_null($leave->leaveType?->leave_allocated);
            $paidStatus = $leave->status === 'rejected' ? 0 : ($isPaid ? 1 : 2);
            $approvedBy = $leave->leaveRequestUpdatedBy?->name ?? '';
            $requestedAt = $leave->leave_requested_date ? date('Y-m-d H:i:s', strtotime($leave->leave_requested_date)) : '';
            $approvedAt = $leave->updated_at ? date('Y-m-d H:i:s', strtotime($leave->updated_at)) : '';
            $monthYear = $leave->leave_requested_date ? date('m-Y', strtotime($leave->leave_requested_date)) : '';
            $monthYearNote = $requestedDate && $employeeCode ? $requestedDate . '-' . $username : '';
        @endphp
        <tr>
            <td style="text-align: center;">{{ $requestedDate }}</td>
            <td style="text-align: center;">{{ $username }}</td>
            <td style="text-align: center;">{{ $employeeName }}</td>
            <td style="text-align: center;">{{ $leaveTypeName }}</td>
            <td style="text-align: center;">{{ $paidStatus }}</td>
            <td style="text-align: center;">{{ $leave->no_of_days }}</td>
            <td style="text-align: center;">{{ $leave->leave_from }}</td>
            <td style="text-align: center;">{{ $leave->leave_to }}</td>
            <td style="text-align: center;">{{ strip_tags($leave->reasons ?? '') }}</td>
            <td style="text-align: center;">{{ $leave->status }}</td>
            <td style="text-align: center;">{{ $approvedBy }}</td>
            <td style="text-align: center;">{{ strip_tags($leave->admin_remark ?? '') }}</td>
            <td style="text-align: center;">{{ $requestedAt }}</td>
            <td style="text-align: center;">{{ $approvedAt }}</td>
            <td style="text-align: center;">{{ $monthYear }}</td>
            <td style="text-align: center;">{{ $monthYearNote }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="16" style="text-align: center;">
                <b>{{ __('index.no_records_found') }}</b>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
