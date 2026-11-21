// Reference screens for the existing Flutter client. Add qr_flutter and
// mobile_scanner to that app's pubspec and pass its authenticated API client
// through the callbacks below.
import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:qr_flutter/qr_flutter.dart';

const _navy = Color(0xFF0F172A);
const _green = Color(0xFF10B981);
const _blue = Color(0xFF3B82F6);

/// Render the server-signed response from POST /api/v1/hr-queue/qr/generate.
class HrQrPassScreen extends StatelessWidget {
  const HrQrPassScreen({super.key, required this.payload, required this.expiresAt});
  final String payload;
  final DateTime expiresAt;

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: _navy,
        appBar: AppBar(title: const Text('QR Attendance'), backgroundColor: _navy),
        body: Center(
          child: Card(
            margin: const EdgeInsets.all(24),
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(mainAxisSize: MainAxisSize.min, children: [
                const Text('បង្កើត និងផ្ញើ QR', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold)),
                const SizedBox(height: 20),
                QrImageView(data: payload, size: 260),
                const SizedBox(height: 12),
                Text('Expires at ${TimeOfDay.fromDateTime(expiresAt).format(context)}'),
              ]),
            ),
          ),
        ),
      );
}

/// The callback posts the scanned JSON string to POST /api/v1/hr-queue/queue/scan.
class HrQueueScannerScreen extends StatefulWidget {
  const HrQueueScannerScreen({super.key, required this.onScan});
  final Future<void> Function(String payload) onScan;
  @override
  State<HrQueueScannerScreen> createState() => _HrQueueScannerScreenState();
}

/// Scans an employee's signed pass for attendance. Select check-in or check-out;
/// the server verifies that the selected action matches the employee's current
/// attendance state before recording it. The callback should attach GPS values.
class HrAttendanceScannerScreen extends StatefulWidget {
  const HrAttendanceScannerScreen({super.key, required this.onScan});
  final Future<void> Function(String payload, String action) onScan;
  @override
  State<HrAttendanceScannerScreen> createState() => _HrAttendanceScannerScreenState();
}

class _HrAttendanceScannerScreenState extends State<HrAttendanceScannerScreen> {
  final _controller = MobileScannerController();
  String _action = 'check_in';
  bool _busy = false;

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: _navy,
        appBar: AppBar(title: const Text('QR Attendance'), backgroundColor: _navy, actions: [
          IconButton(icon: const Icon(Icons.flash_on), onPressed: _controller.toggleTorch),
        ]),
        body: Column(children: [
          const SizedBox(height: 24),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'check_in', label: Text('ស្កេនចូល · IN'), icon: Icon(Icons.login)),
              ButtonSegment(value: 'check_out', label: Text('ស្កេនចេញ · OUT'), icon: Icon(Icons.logout)),
            ],
            selected: {_action},
            onSelectionChanged: (values) => setState(() => _action = values.first),
          ),
          const Spacer(),
          SizedBox(
            width: 300,
            height: 300,
            child: ClipRRect(
              borderRadius: BorderRadius.circular(24),
              child: MobileScanner(controller: _controller, onDetect: (capture) async {
                if (_busy || capture.barcodes.isEmpty) return;
                final payload = capture.barcodes.first.rawValue;
                if (payload == null) return;
                _busy = true;
                try {
                  await widget.onScan(payload, _action);
                } finally {
                  _busy = false;
                }
              }),
            ),
          ),
          const Spacer(),
          const Padding(padding: EdgeInsets.all(20), child: Text('Generate a fresh signed QR pass before each scan. GPS is required.', style: TextStyle(color: Colors.white70), textAlign: TextAlign.center)),
        ]),
      );

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}

class _HrQueueScannerScreenState extends State<HrQueueScannerScreen> {
  final _controller = MobileScannerController();
  bool _busy = false;

  @override
  Widget build(BuildContext context) => Scaffold(
        backgroundColor: _navy,
        appBar: AppBar(
          title: const Text('Scan to join queue'),
          backgroundColor: _navy,
          actions: [IconButton(icon: const Icon(Icons.flash_on), onPressed: _controller.toggleTorch)],
        ),
        body: Center(
          child: Container(
            width: 300,
            height: 300,
            clipBehavior: Clip.antiAlias,
            decoration: BoxDecoration(border: Border.all(color: _green, width: 3), borderRadius: BorderRadius.circular(24)),
            child: MobileScanner(
              controller: _controller,
              onDetect: (capture) async {
                if (_busy || capture.barcodes.isEmpty) return;
                final payload = capture.barcodes.first.rawValue;
                if (payload == null) return;
                _busy = true;
                try {
                  await widget.onScan(payload);
                } finally {
                  _busy = false;
                }
              },
            ),
          ),
        ),
      );

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }
}

class HrQueueTicketCard extends StatelessWidget {
  const HrQueueTicketCard({super.key, required this.number, required this.peopleAhead, required this.status});
  final String number;
  final int peopleAhead;
  final String status;

  @override
  Widget build(BuildContext context) => Card(
        color: _navy,
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            const Text('លេខសំបុត្រ · YOUR TICKET', style: TextStyle(color: Colors.white70)),
            const SizedBox(height: 8),
            Text(number, style: const TextStyle(color: _blue, fontSize: 52, fontWeight: FontWeight.w900)),
            Text('$peopleAhead people ahead', style: const TextStyle(color: Colors.white)),
            const SizedBox(height: 12),
            Chip(label: Text(status.toUpperCase()), backgroundColor: status == 'calling' || status == 'serving' ? _green : const Color(0xFFF59E0B)),
          ]),
        ),
      );
}

class SalaryItem {
  const SalaryItem(this.label, this.amount);
  final String label;
  final double amount;
}

/// Pass the server's payroll/breakdown values; this widget does not calculate or
/// invent payroll. onConfirm posts ticket_id, payment_method, and transaction_ref.
class SalaryBreakdownScreen extends StatefulWidget {
  const SalaryBreakdownScreen({
    super.key,
    required this.basicSalary,
    required this.deductions,
    required this.bonuses,
    required this.netSalary,
    required this.onConfirm,
    this.alreadyPaid = false,
  });
  final double basicSalary;
  final List<SalaryItem> deductions;
  final List<SalaryItem> bonuses;
  final double netSalary;
  final Future<void> Function() onConfirm;
  final bool alreadyPaid;

  @override
  State<SalaryBreakdownScreen> createState() => _SalaryBreakdownScreenState();
}

class _SalaryBreakdownScreenState extends State<SalaryBreakdownScreen> {
  bool _busy = false;
  bool _paid = false;

  @override
  Widget build(BuildContext context) {
    if (widget.alreadyPaid || _paid) {
      return Scaffold(
        backgroundColor: _navy,
        body: Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
          const Icon(Icons.check_circle, size: 104, color: _green),
          const SizedBox(height: 18),
          const Text('បើកប្រាក់ខែជោគជ័យ', style: TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold)),
          const SizedBox(height: 12),
          const Chip(label: Text('បានបើកប្រាក់ខែរួចរាល់ · PAID')),
        ])),
      );
    }
    return Scaffold(
      appBar: AppBar(title: const Text('Salary Breakdown')),
      body: ListView(padding: const EdgeInsets.all(20), children: [
        _row('ប្រាក់ខែមូលដ្ឋាន', widget.basicSalary, Colors.black87),
        ...widget.deductions.map((x) => _row(x.label, -x.amount.abs(), Colors.deepOrange)),
        ...widget.bonuses.map((x) => _row(x.label, x.amount.abs(), _green)),
        const Divider(),
        _row('ប្រាក់ខែទទួលបានពិតប្រាកដ', widget.netSalary, _navy, bold: true),
        const SizedBox(height: 24),
        FilledButton(
          onPressed: _busy ? null : () async {
            setState(() => _busy = true);
            try {
              await widget.onConfirm();
              if (mounted) setState(() => _paid = true);
            } finally {
              if (mounted) setState(() => _busy = false);
            }
          },
          child: Text(_busy ? 'Processing…' : 'បើកប្រាក់ខែ · Confirm payout'),
        ),
      ]),
    );
  }

  Widget _row(String label, double amount, Color color, {bool bold = false}) => ListTile(
        title: Text(label, style: TextStyle(fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
        trailing: Text('${amount < 0 ? '-' : ''}\$${amount.abs().toStringAsFixed(2)}', style: TextStyle(color: color, fontWeight: FontWeight.bold)),
      );
}
