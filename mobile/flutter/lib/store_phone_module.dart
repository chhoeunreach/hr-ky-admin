import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

const _navy = Color(0xFF0F172A);
const _blue = Color(0xFF2563EB);
const _green = Color(0xFF059669);
const _orange = Color(0xFFD97706);
const _cardBg = Color(0xFFF8FAFC);

/// Model for a Store Phone & SIM line
class StorePhoneModel {
  final int id;
  final String phoneNumber;
  final String operator;
  final String operatorName;
  final String simSlot;
  final String simSlotName;
  final String? purpose;
  final String? purposeName;
  final String? planType;
  final String? serialNumber;
  final String status;
  final String? branchName;
  final String? deviceName;
  final String? deviceCode;
  final String? custodianName;
  final String? assignedDate;
  final String? notes;

  StorePhoneModel({
    required this.id,
    required this.phoneNumber,
    required this.operator,
    required this.operatorName,
    required this.simSlot,
    required this.simSlotName,
    this.purpose,
    this.purposeName,
    this.planType,
    this.serialNumber,
    required this.status,
    this.branchName,
    this.deviceName,
    this.deviceCode,
    this.custodianName,
    this.assignedDate,
    this.notes,
  });

  factory StorePhoneModel.fromJson(Map<String, dynamic> json) => StorePhoneModel(
        id: json['id'] ?? 0,
        phoneNumber: json['phone_number'] ?? '',
        operator: json['operator'] ?? '',
        operatorName: json['operator_name'] ?? json['operator'] ?? '',
        simSlot: json['sim_slot'] ?? '',
        simSlotName: json['sim_slot_name'] ?? json['sim_slot'] ?? '',
        purpose: json['purpose'],
        purposeName: json['purpose_name'],
        planType: json['plan_type'],
        serialNumber: json['serial_number'],
        status: json['status'] ?? 'active',
        branchName: json['branch'] != null ? json['branch']['name'] : (json['branch_name']),
        deviceName: json['device'] != null ? json['device']['name'] : null,
        deviceCode: json['device'] != null ? json['device']['asset_code'] : null,
        custodianName: json['assigned_employee'] != null
            ? json['assigned_employee']['name']
            : json['custodian_name'],
        assignedDate: json['assigned_date'],
        notes: json['notes'],
      );
}

/// Store Phone & SIM Cards Directory Screen for mobile
class StorePhoneDirectoryScreen extends StatefulWidget {
  const StorePhoneDirectoryScreen({
    super.key,
    required this.baseUrl,
    required this.authToken,
  });

  final String baseUrl;
  final String authToken;

  @override
  State<StorePhoneDirectoryScreen> createState() => _StorePhoneDirectoryScreenState();
}

class _StorePhoneDirectoryScreenState extends State<StorePhoneDirectoryScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  List<StorePhoneModel> _allNumbers = [];
  List<StorePhoneModel> _mySims = [];
  bool _isLoadingAll = true;
  bool _isLoadingMySims = true;
  String _selectedOperator = 'all';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchDirectory();
    _fetchMySims();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  Map<String, String> get _authHeaders => {
        'Accept': 'application/json',
        'Authorization': 'Bearer ${widget.authToken}',
      };

  Future<void> _fetchDirectory([String? query]) async {
    setState(() => _isLoadingAll = true);
    try {
      final uri = Uri.parse('${widget.baseUrl}/api/store-phone-numbers').replace(
        queryParameters: {
          if (query != null && query.isNotEmpty) 'search': query,
          if (_selectedOperator != 'all') 'operator': _selectedOperator,
        },
      );
      final response = await http.get(uri, headers: _authHeaders);
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        final list = (body['data'] as List? ?? [])
            .map((item) => StorePhoneModel.fromJson(item))
            .toList();
        setState(() {
          _allNumbers = list;
          _isLoadingAll = false;
        });
      } else {
        setState(() => _isLoadingAll = false);
      }
    } catch (_) {
      setState(() => _isLoadingAll = false);
    }
  }

  Future<void> _fetchMySims() async {
    setState(() => _isLoadingMySims = true);
    try {
      final uri = Uri.parse('${widget.baseUrl}/api/my-sim-cards');
      final response = await http.get(uri, headers: _authHeaders);
      if (response.statusCode == 200) {
        final body = jsonDecode(response.body);
        final list = (body['data'] as List? ?? [])
            .map((item) => StorePhoneModel.fromJson(item))
            .toList();
        setState(() {
          _mySims = list;
          _isLoadingMySims = false;
        });
      } else {
        setState(() => _isLoadingMySims = false);
      }
    } catch (_) {
      setState(() => _isLoadingMySims = false);
    }
  }

  Future<void> _callNumber(String phone) async {
    final clean = phone.replaceAll(RegExp(r'[^0-9+]'), '');
    final uri = Uri.parse('tel:$clean');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri);
    }
  }

  Color _operatorColor(String op) {
    switch (op.toLowerCase()) {
      case 'smart':
        return _green;
      case 'cellcard':
        return _orange;
      case 'metfone':
        return Colors.red;
      case 'seatel':
        return Colors.teal;
      default:
        return Colors.blueGrey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('លេខទូរសព្ទ & ស៊ីម · Store Phones'),
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.blueAccent,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white60,
          tabs: [
            Tab(
              icon: const Icon(Icons.sim_card_outlined),
              text: 'ស៊ីមរបស់ខ្ញុំ (${_mySims.length})',
            ),
            const Tab(
              icon: Icon(Icons.contacts_outlined),
              text: 'បញ្ជីលេខទូរសព្ទហាង',
            ),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          // Tab 1: My SIM Cards
          _buildMySimsTab(),
          // Tab 2: All Store Numbers Directory
          _buildDirectoryTab(),
        ],
      ),
    );
  }

  Widget _buildMySimsTab() {
    if (_isLoadingMySims) {
      return const Center(child: CircularProgressIndicator());
    }
    if (_mySims.isEmpty) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.sim_card_outlined, size: 64, color: Colors.grey.shade400),
            const SizedBox(height: 12),
            const Text(
              'មិនមានស៊ីមកាតត្រូវបានប្រគល់ជូនអ្នកនៅឡើយទេ',
              style: TextStyle(fontSize: 15, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _fetchMySims,
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: _mySims.length,
        separatorBuilder: (_, __) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          final sim = _mySims[index];
          final opColor = _operatorColor(sim.operator);
          return Card(
            elevation: 1.5,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            child: Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border(left: BorderSide(color: opColor, width: 4)),
              ),
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: opColor.withOpacity(0.12),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Text(
                          sim.operatorName,
                          style: TextStyle(color: opColor, fontWeight: FontWeight.bold, fontSize: 12),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade200,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          sim.simSlotName,
                          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                        ),
                      ),
                      const Spacer(),
                      IconButton(
                        icon: const Icon(Icons.phone, color: _green),
                        onPressed: () => _callNumber(sim.phoneNumber),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    sim.phoneNumber,
                    style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, letterSpacing: 0.5),
                  ),
                  if (sim.purposeName != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      'គោលបំណង: ${sim.purposeName}',
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                    ),
                  ],
                  if (sim.deviceName != null) ...[
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.smartphone, size: 14, color: Colors.blueGrey),
                        const SizedBox(width: 4),
                        Text(
                          'លើទូរសព្ទ: ${sim.deviceName} ${sim.deviceCode != null ? "(${sim.deviceCode})" : ""}',
                          style: const TextStyle(fontSize: 12, color: Colors.blueGrey),
                        ),
                      ],
                    ),
                  ],
                  if (sim.serialNumber != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      'ICCID: ${sim.serialNumber}',
                      style: TextStyle(fontFamily: 'monospace', fontSize: 11, color: Colors.grey.shade500),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildDirectoryTab() {
    return Column(
      children: [
        // Search & Filter header
        Container(
          color: Colors.white,
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'ស្វែងរកលេខ, បុគ្គលិក, សាខា...',
                  prefixIcon: const Icon(Icons.search),
                  suffixIcon: _searchController.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear),
                          onPressed: () {
                            _searchController.clear();
                            _fetchDirectory('');
                          },
                        )
                      : null,
                  filled: true,
                  fillColor: _cardBg,
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(10),
                    borderSide: BorderSide.none,
                  ),
                ),
                onSubmitted: (val) => _fetchDirectory(val),
              ),
              const SizedBox(height: 8),
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: Row(
                  children: [
                    _filterChip('ទាំងអស់', 'all'),
                    _filterChip('Smart', 'smart'),
                    _filterChip('Cellcard', 'cellcard'),
                    _filterChip('Metfone', 'metfone'),
                    _filterChip('Seatel', 'seatel'),
                  ],
                ),
              ),
            ],
          ),
        ),
        // List of store phone numbers
        Expanded(
          child: _isLoadingAll
              ? const Center(child: CircularProgressIndicator())
              : _allNumbers.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.phone_disabled_outlined, size: 48, color: Colors.grey.shade400),
                          const SizedBox(height: 8),
                          const Text('រកមិនឃើញលេខទូរសព្ទទេ', style: TextStyle(color: Colors.grey)),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: () => _fetchDirectory(_searchController.text),
                      child: ListView.separated(
                        padding: const EdgeInsets.all(12),
                        itemCount: _allNumbers.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemBuilder: (context, index) {
                          final phone = _allNumbers[index];
                          final opColor = _operatorColor(phone.operator);
                          return ListTile(
                            tileColor: Colors.white,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                              side: BorderSide(color: Colors.grey.shade200),
                            ),
                            leading: CircleAvatar(
                              backgroundColor: opColor.withOpacity(0.12),
                              child: Icon(Icons.phone, color: opColor, size: 20),
                            ),
                            title: Text(
                              phone.phoneNumber,
                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                            ),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                if (phone.custodianName != null)
                                  Text('អ្នកកាន់: ${phone.custodianName}', style: const TextStyle(fontSize: 12))
                                else
                                  const Text('មិនទាន់ប្រគល់ (Unassigned)', style: TextStyle(fontSize: 11, color: Colors.amber)),
                                if (phone.purposeName != null || phone.branchName != null)
                                  Text(
                                    '${phone.purposeName ?? ""} ${phone.branchName != null ? "• ${phone.branchName}" : ""}',
                                    style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                                  ),
                              ],
                            ),
                            trailing: IconButton(
                              icon: const Icon(Icons.call, color: _green),
                              onPressed: () => _callNumber(phone.phoneNumber),
                            ),
                            onTap: () {
                              Clipboard.setData(ClipboardData(text: phone.phoneNumber));
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(content: Text('បានចម្លងលេខ: ${phone.phoneNumber}')),
                              );
                            },
                          );
                        },
                      ),
                    ),
        ),
      ],
    );
  }

  Widget _filterChip(String label, String value) {
    final isSelected = _selectedOperator == value;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: FilterChip(
        label: Text(label),
        selected: isSelected,
        selectedColor: _blue.withOpacity(0.15),
        labelStyle: TextStyle(
          color: isSelected ? _blue : Colors.black87,
          fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
          fontSize: 12,
        ),
        onSelected: (val) {
          setState(() => _selectedOperator = value);
          _fetchDirectory(_searchController.text);
        },
      ),
    );
  }
}
