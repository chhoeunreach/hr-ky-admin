import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:url_launcher/url_launcher.dart';

const _navy = Color(0xFF0F172A);
const _blue = Color(0xFF2563EB);
const _red = Color(0xFFDC2626);
const _amber = Color(0xFFD97706);
const _green = Color(0xFF16A34A);

class CompanyDocumentModel {
  final int id;
  final String title;
  final String? documentNumber;
  final String category;
  final String? validUntil;
  final int? daysUntilExpiry;
  final String? status;
  final String? fileUrl;
  final String? description;

  CompanyDocumentModel({
    required this.id,
    required this.title,
    this.documentNumber,
    required this.category,
    this.validUntil,
    this.daysUntilExpiry,
    this.status,
    this.fileUrl,
    this.description,
  });

  factory CompanyDocumentModel.fromJson(Map<String, dynamic> json) => CompanyDocumentModel(
        id: json['id'] ?? 0,
        title: json['title'] ?? '',
        documentNumber: json['document_number'],
        category: json['category'] ?? 'general',
        validUntil: json['valid_until'],
        daysUntilExpiry: json['days_until_expiry'],
        status: json['status'],
        fileUrl: json['file_url'] ?? json['attachment'],
        description: json['description'],
      );
}

class CompanyDocumentsScreen extends StatefulWidget {
  const CompanyDocumentsScreen({
    super.key,
    required this.baseUrl,
    required this.authToken,
  });

  final String baseUrl;
  final String authToken;

  @override
  State<CompanyDocumentsScreen> createState() => _CompanyDocumentsScreenState();
}

class _CompanyDocumentsScreenState extends State<CompanyDocumentsScreen> {
  List<CompanyDocumentModel> _documents = [];
  bool _isLoading = true;
  int _expiredCount = 0;
  int _expiringSoonCount = 0;
  String _selectedCategory = 'all';

  @override
  void initState() {
    super.initState();
    _fetchAlerts();
    _fetchDocuments();
  }

  Map<String, String> get _authHeaders => {
        'Accept': 'application/json',
        'Authorization': 'Bearer ${widget.authToken}',
      };

  Future<void> _fetchAlerts() async {
    try {
      final uri = Uri.parse('${widget.baseUrl}/api/v1/company-documents/alerts');
      final res = await http.get(uri, headers: _authHeaders);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final data = body['data'] ?? {};
        setState(() {
          _expiredCount = data['expired_count'] ?? 0;
          _expiringSoonCount = data['expiring_soon_count'] ?? 0;
        });
      }
    } catch (_) {}
  }

  Future<void> _fetchDocuments([String? category]) async {
    setState(() => _isLoading = true);
    try {
      final uri = Uri.parse('${widget.baseUrl}/api/v1/company-documents').replace(
        queryParameters: {
          if (category != null && category != 'all') 'category': category,
        },
      );
      final res = await http.get(uri, headers: _authHeaders);
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final list = (body['data'] as List? ?? [])
            .map((item) => CompanyDocumentModel.fromJson(item))
            .toList();
        setState(() {
          _documents = list;
          _isLoading = false;
        });
      } else {
        setState(() => _isLoading = false);
      }
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _openFile(String? url) async {
    if (url == null || url.isEmpty) return;
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('ឯកសារក្រុមហ៊ុន · Company Documents'),
        backgroundColor: _navy,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Expiration Alert Banner (if any)
          if (_expiredCount > 0 || _expiringSoonCount > 0)
            Container(
              margin: const EdgeInsets.all(12),
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: _expiredCount > 0 ? _red.withOpacity(0.08) : _amber.withOpacity(0.08),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: _expiredCount > 0 ? _red.withOpacity(0.3) : _amber.withOpacity(0.3),
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.warning_amber_rounded, color: _expiredCount > 0 ? _red : _amber),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _expiredCount > 0
                          ? 'មានឯកសារ $_expiredCount ផុតកំណត់សុពលភាព!'
                          : 'មានឯកសារ $_expiringSoonCount ជិតផុតកំណត់សុពលភាព!',
                      style: TextStyle(
                        color: _expiredCount > 0 ? _red : _amber,
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          // Category Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            child: Row(
              children: [
                _categoryChip('ទាំងអស់', 'all'),
                _categoryChip('ច្បាប់ & បទបញ្ជា', 'rules'),
                _categoryChip('អាជ្ញាប័ណ្ណ & ពន្ធ', 'licenses'),
                _categoryChip('កិច្ចសន្យា', 'contracts'),
                _categoryChip('ទូទៅ', 'general'),
              ],
            ),
          ),

          // Document List
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _documents.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.folder_open_outlined, size: 54, color: Colors.grey.shade400),
                            const SizedBox(height: 8),
                            const Text('មិនមានឯកសារនៅក្នុងផ្នែកនេះទេ', style: TextStyle(color: Colors.grey)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () async {
                          await _fetchAlerts();
                          await _fetchDocuments(_selectedCategory);
                        },
                        child: ListView.separated(
                          padding: const EdgeInsets.all(12),
                          itemCount: _documents.length,
                          separatorBuilder: (_, __) => const SizedBox(height: 10),
                          itemBuilder: (context, index) {
                            final doc = _documents[index];
                            final isExpired = (doc.daysUntilExpiry != null && doc.daysUntilExpiry! < 0);
                            final isExpiringSoon = (doc.daysUntilExpiry != null && doc.daysUntilExpiry! >= 0 && doc.daysUntilExpiry! <= 30);

                            return Card(
                              elevation: 1,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(10),
                                          decoration: BoxDecoration(
                                            color: _blue.withOpacity(0.1),
                                            borderRadius: BorderRadius.circular(10),
                                          ),
                                          child: const Icon(Icons.description_outlined, color: _blue, size: 24),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                doc.title,
                                                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15),
                                              ),
                                              if (doc.documentNumber != null) ...[
                                                const SizedBox(height: 2),
                                                Text(
                                                  'លេខកូដ: ${doc.documentNumber}',
                                                  style: const TextStyle(fontSize: 12, color: Colors.blueGrey),
                                                ),
                                              ],
                                            ],
                                          ),
                                        ),
                                        if (doc.fileUrl != null)
                                          IconButton(
                                            icon: const Icon(Icons.download_for_offline_outlined, color: _blue),
                                            onPressed: () => _openFile(doc.fileUrl),
                                            tooltip: 'ទាញយក / បើកឯកសារ',
                                          ),
                                      ],
                                    ),
                                    if (doc.validUntil != null) ...[
                                      const SizedBox(height: 8),
                                      Row(
                                        children: [
                                          Icon(
                                            Icons.calendar_today_outlined,
                                            size: 13,
                                            color: isExpired ? _red : (isExpiringSoon ? _amber : Colors.grey),
                                          ),
                                          const SizedBox(width: 4),
                                          Text(
                                            'សុពលភាពដល់: ${doc.validUntil}',
                                            style: TextStyle(
                                              fontSize: 12,
                                              color: isExpired ? _red : (isExpiringSoon ? _amber : Colors.grey.shade700),
                                              fontWeight: (isExpired || isExpiringSoon) ? FontWeight.bold : FontWeight.normal,
                                            ),
                                          ),
                                        ],
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _categoryChip(String label, String value) {
    final isSelected = _selectedCategory == value;
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
          setState(() => _selectedCategory = value);
          _fetchDocuments(value);
        },
      ),
    );
  }
}
