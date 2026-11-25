<?php

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\CompanyDocumentCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CompanyDocumentApiController extends Controller
{
    /**
     * Get list of company documents with filters & pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = CompanyDocument::with(['category', 'branch', 'creator', 'attachments']);

            // Filter: Category
            if ($request->filled('category')) {
                $query->where('category_code', $request->category);
            }

            // Filter: Sub Type
            if ($request->filled('doc_sub_type')) {
                $query->where('doc_sub_type', $request->doc_sub_type);
            }

            // Filter: Branch (if specified or restrict by user branch if not superadmin)
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Filter: Search
            if ($request->filled('search')) {
                $search = trim($request->search);
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('holder_name', 'like', "%{$search}%")
                      ->orWhere('doc_number', 'like', "%{$search}%")
                      ->orWhere('issuing_authority', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter: Status (valid, expiring_soon, expired, archived)
            $today = Carbon::today();
            $todayStr = $today->toDateString();
            $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
            $diffExpr = $isSqlite ? '(julianday(expiry_date) - julianday(?))' : 'DATEDIFF(expiry_date, ?)';

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === CompanyDocument::STATUS_EXPIRED) {
                    $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
                } elseif ($status === CompanyDocument::STATUS_EXPIRING_SOON) {
                    $query->whereNotNull('expiry_date')
                          ->whereDate('expiry_date', '>=', $today)
                          ->whereRaw("{$diffExpr} <= alert_before_days", [$todayStr]);
                } elseif ($status === CompanyDocument::STATUS_VALID) {
                    $query->where(function ($q) use ($today, $diffExpr, $todayStr) {
                        $q->whereNull('expiry_date')
                          ->orWhere(function ($sub) use ($today, $diffExpr, $todayStr) {
                              $sub->whereDate('expiry_date', '>=', $today)
                                  ->whereRaw("{$diffExpr} > alert_before_days", [$todayStr]);
                          });
                    });
                } elseif ($status === CompanyDocument::STATUS_ARCHIVED) {
                    $query->where('status', CompanyDocument::STATUS_ARCHIVED);
                }
            }

            // Sorting
            $sort = $request->get('sort', 'created_at');
            $direction = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
            if ($sort === 'expiry_date') {
                $query->orderByRaw('expiry_date IS NULL, expiry_date ' . $direction);
            } else {
                $query->orderBy($sort, $direction);
            }

            $perPage = (int) $request->get('per_page', 15);
            $paginated = $query->paginate($perPage);

            $transformedData = $paginated->getCollection()->map(function ($doc) {
                return $this->transformDocument($doc);
            });

            // Summary metrics
            $allDocs = CompanyDocument::all();
            $expiredCount = $allDocs->filter(function ($doc) use ($today) {
                return $doc->expiry_date && Carbon::parse($doc->expiry_date)->startOfDay()->isPast();
            })->count();

            $expiringSoonCount = $allDocs->filter(function ($doc) use ($today) {
                if (!$doc->expiry_date) return false;
                $exp = Carbon::parse($doc->expiry_date)->startOfDay();
                $alertDays = $doc->alert_before_days ?: 30;
                return $exp->greaterThanOrEqualTo($today) && $exp->lessThanOrEqualTo($today->copy()->addDays($alertDays));
            })->count();

            $summary = [
                'total_count' => $allDocs->count(),
                'master_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_MASTER)->count(),
                'tax_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_TAX)->count(),
                'business_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_BUSINESS_LICENSE)->count(),
                'nssf_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_NSSF)->count(),
                'contract_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_CONTRACT)->count(),
                'utility_count' => $allDocs->where('category_code', CompanyDocument::CATEGORY_UTILITY)->count(),
                'expired_count' => $expiredCount,
                'expiring_soon_count' => $expiringSoonCount,
                'total_cost_usd' => (float) $allDocs->where('currency', 'USD')->sum('cost_amount'),
                'total_cost_khr' => (float) $allDocs->where('currency', 'KHR')->sum('cost_amount'),
            ];

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => $transformedData,
                'summary' => $summary,
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'has_more' => $paginated->hasMorePages(),
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get metadata for dropdowns (categories, sub-types, branches)
     */
    public function meta(Request $request): JsonResponse
    {
        try {
            $categories = CompanyDocumentCategory::where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(function ($cat) {
                    return [
                        'code' => $cat->code,
                        'name_km' => $cat->name_km,
                        'name_en' => $cat->name_en,
                        'name' => app()->getLocale() === 'km' ? $cat->name_km : ($cat->name_en ?: $cat->name_km),
                        'icon' => $cat->icon,
                        'color' => $cat->color,
                        'order' => $cat->order,
                    ];
                });

            $subTypes = [
                CompanyDocument::CATEGORY_MASTER => [
                    ['code' => CompanyDocument::SUB_TYPE_NATIONAL_ID, 'name' => __('index.doc_type_national_id')],
                    ['code' => CompanyDocument::SUB_TYPE_PASSPORT, 'name' => __('index.doc_type_passport')],
                    ['code' => CompanyDocument::SUB_TYPE_FAMILY_BOOK, 'name' => __('index.doc_type_family_book')],
                    ['code' => CompanyDocument::SUB_TYPE_RESIDENCE_BOOK, 'name' => __('index.doc_type_residence_book')],
                    ['code' => CompanyDocument::SUB_TYPE_OTHER_MASTER, 'name' => __('index.doc_type_other_master')],
                ],
                CompanyDocument::CATEGORY_UTILITY => [
                    ['code' => CompanyDocument::SUB_TYPE_RENT, 'name' => __('index.doc_type_rent')],
                    ['code' => CompanyDocument::SUB_TYPE_ELECTRICITY, 'name' => __('index.doc_type_electricity')],
                    ['code' => CompanyDocument::SUB_TYPE_WATER, 'name' => __('index.doc_type_water')],
                    ['code' => CompanyDocument::SUB_TYPE_INTERNET, 'name' => __('index.doc_type_internet')],
                    ['code' => CompanyDocument::SUB_TYPE_WASTE, 'name' => __('index.doc_type_waste')],
                ],
            ];

            $branches = \Illuminate\Support\Facades\Schema::hasTable('branches')
                ? Branch::select('id', 'name')->orderBy('name')->get()
                : collect();

            $meta = [
                'categories' => $categories,
                'sub_types' => $subTypes,
                'branches' => $branches,
                'currencies' => ['USD', 'KHR'],
                'confidentiality_levels' => [
                    ['code' => 'public', 'name' => 'Public'],
                    ['code' => 'internal', 'name' => 'Internal'],
                    ['code' => 'restricted', 'name' => 'Restricted'],
                    ['code' => 'confidential', 'name' => 'Confidential'],
                ],
            ];

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => $meta,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get expiring and expired alerts for quick banner display
     */
    public function alerts(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();
            $todayStr = $today->toDateString();
            $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
            $diffExpr = $isSqlite ? '(julianday(expiry_date) - julianday(?))' : 'DATEDIFF(expiry_date, ?)';

            $expiringDocs = CompanyDocument::with(['category', 'branch', 'attachments'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '>=', $today)
                ->whereRaw("{$diffExpr} <= alert_before_days", [$todayStr])
                ->orderBy('expiry_date', 'asc')
                ->get()
                ->map(fn($doc) => $this->transformDocument($doc));

            $expiredDocs = CompanyDocument::with(['category', 'branch', 'attachments'])
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<', $today)
                ->orderBy('expiry_date', 'desc')
                ->get()
                ->map(fn($doc) => $this->transformDocument($doc));

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => [
                    'expiring_soon' => $expiringDocs,
                    'expired' => $expiredDocs,
                    'expiring_soon_count' => $expiringDocs->count(),
                    'expired_count' => $expiredDocs->count(),
                ],
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get single document details
     */
    public function show($id): JsonResponse
    {
        try {
            $document = CompanyDocument::with(['category', 'branch', 'creator', 'updater', 'attachments'])->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => __('index.data_found'),
                'status_code' => 200,
                'data' => $this->transformDocument($document),
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 404);
        }
    }

    /**
     * Store a new document (uploaded from Flutter camera or file picker)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'category_code' => ['required', 'string', 'exists:company_document_categories,code'],
                'doc_sub_type' => ['nullable', 'string', 'max:50'],
                'title' => ['required', 'string', 'max:255'],
                'holder_name' => ['nullable', 'string', 'max:255'],
                'doc_number' => ['nullable', 'string', 'max:100'],
                'branch_id' => ['nullable', 'exists:branches,id'],
                'issuing_authority' => ['nullable', 'string', 'max:255'],
                'issue_date' => ['nullable', 'date'],
                'expiry_date' => ['nullable', 'date'],
                'alert_before_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'cost_amount' => ['nullable', 'numeric', 'min:0'],
                'currency' => ['nullable', 'in:USD,KHR'],
                'receipt_number' => ['nullable', 'string', 'max:100'],
                'file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
                'files' => ['nullable', 'array'],
                'files.*' => ['file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
                'confidentiality_level' => ['nullable', 'in:public,internal,restricted,confidential'],
                'description' => ['nullable', 'string'],
            ]);

            $uploadedFiles = [];
            if ($request->hasFile('files')) {
                $filesInput = $request->file('files');
                $uploadedFiles = is_array($filesInput) ? $filesInput : [$filesInput];
            } elseif ($request->hasFile('file')) {
                $uploadedFiles = [$request->file('file')];
            }

            if (empty($uploadedFiles)) {
                return AppHelper::sendErrorResponse('សូមជ្រើសរើសឯកសារយ៉ាងតិចមួយ (Please select at least one document file).', 422);
            }

            $uploadDir = public_path(CompanyDocument::UPLOAD_PATH . $validated['category_code']);
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $savedAttachments = [];
            $primaryFilePath = '';
            $primaryFileName = '';
            $primaryFileType = 'other';
            $primaryFileSize = 0;

            foreach ($uploadedFiles as $index => $uploadedFile) {
                if (!$uploadedFile || !$uploadedFile->isValid()) continue;

                $originalName = $uploadedFile->getClientOriginalName();
                $extension = strtolower($uploadedFile->getClientOriginalExtension());
                $fileSize = $uploadedFile->getSize();
                $fileName = time() . '_' . $index . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $uploadedFile->move($uploadDir, $fileName);
                $filePath = CompanyDocument::UPLOAD_PATH . $validated['category_code'] . '/' . $fileName;

                $fileType = 'other';
                if ($extension === 'pdf') {
                    $fileType = 'pdf';
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    $fileType = 'image';
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $fileType = 'word';
                } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                    $fileType = 'excel';
                }

                if ($index === 0) {
                    $primaryFilePath = $filePath;
                    $primaryFileName = $originalName;
                    $primaryFileType = $fileType;
                    $primaryFileSize = $fileSize;
                }

                $savedAttachments[] = [
                    'file_path' => $filePath,
                    'file_name' => $originalName,
                    'file_type' => $fileType,
                    'file_size' => $fileSize,
                    'sort_order' => $index,
                ];
            }

            $document = CompanyDocument::create([
                'category_code' => $validated['category_code'],
                'doc_sub_type' => $validated['doc_sub_type'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'company_id' => \Illuminate\Support\Facades\Schema::hasTable('companies') ? Company::first()?->id : null,
                'doc_number' => $validated['doc_number'] ?? null,
                'title' => $validated['title'],
                'holder_name' => $validated['holder_name'] ?? null,
                'issuing_authority' => $validated['issuing_authority'] ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'alert_before_days' => $validated['alert_before_days'] ?: 30,
                'cost_amount' => $validated['cost_amount'] ?? null,
                'currency' => $validated['currency'] ?? 'USD',
                'receipt_number' => $validated['receipt_number'] ?? null,
                'file_path' => $primaryFilePath,
                'file_name' => $primaryFileName,
                'file_type' => $primaryFileType,
                'file_size' => $primaryFileSize,
                'confidentiality_level' => $validated['confidentiality_level'] ?? 'internal',
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($savedAttachments as $attData) {
                $document->attachments()->create($attData);
            }

            $document->load(['category', 'branch', 'creator', 'attachments']);

            return response()->json([
                'status' => true,
                'message' => __('index.document_saved_success'),
                'status_code' => 201,
                'data' => $this->transformDocument($document),
            ], 201);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update an existing document
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $document = CompanyDocument::findOrFail($id);

            $validated = $request->validate([
                'category_code' => ['sometimes', 'required', 'string', 'exists:company_document_categories,code'],
                'doc_sub_type' => ['nullable', 'string', 'max:50'],
                'title' => ['sometimes', 'required', 'string', 'max:255'],
                'holder_name' => ['nullable', 'string', 'max:255'],
                'doc_number' => ['nullable', 'string', 'max:100'],
                'branch_id' => ['nullable', 'exists:branches,id'],
                'issuing_authority' => ['nullable', 'string', 'max:255'],
                'issue_date' => ['nullable', 'date'],
                'expiry_date' => ['nullable', 'date'],
                'alert_before_days' => ['nullable', 'integer', 'min:1', 'max:365'],
                'cost_amount' => ['nullable', 'numeric', 'min:0'],
                'currency' => ['nullable', 'in:USD,KHR'],
                'receipt_number' => ['nullable', 'string', 'max:100'],
                'file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
                'confidentiality_level' => ['nullable', 'in:public,internal,restricted,confidential'],
                'description' => ['nullable', 'string'],
            ]);

            // Replace file if new file uploaded
            if ($request->hasFile('file')) {
                // Delete old file
                if ($document->file_path && File::exists(public_path($document->file_path))) {
                    File::delete(public_path($document->file_path));
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());
                $fileSize = $file->getSize();

                $category = $validated['category_code'] ?? $document->category_code;
                $uploadDir = public_path(CompanyDocument::UPLOAD_PATH . $category);
                if (!File::isDirectory($uploadDir)) {
                    File::makeDirectory($uploadDir, 0755, true, true);
                }

                $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $file->move($uploadDir, $fileName);

                $filePath = CompanyDocument::UPLOAD_PATH . $category . '/' . $fileName;

                $fileType = 'other';
                if ($extension === 'pdf') {
                    $fileType = 'pdf';
                } elseif (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                    $fileType = 'image';
                } elseif (in_array($extension, ['doc', 'docx'])) {
                    $fileType = 'word';
                } elseif (in_array($extension, ['xls', 'xlsx', 'csv'])) {
                    $fileType = 'excel';
                }

                $document->file_path = $filePath;
                $document->file_name = $originalName;
                $document->file_type = $fileType;
                $document->file_size = $fileSize;
            }

            // Fill other attributes
            $fields = [
                'category_code', 'doc_sub_type', 'branch_id', 'doc_number', 'title',
                'holder_name', 'issuing_authority', 'issue_date', 'expiry_date',
                'alert_before_days', 'cost_amount', 'currency', 'receipt_number',
                'confidentiality_level', 'description',
            ];

            foreach ($fields as $field) {
                if (array_key_exists($field, $validated)) {
                    $document->{$field} = $validated[$field];
                }
            }

            $document->updated_by = auth()->id();
            $document->save();
            $document->load(['category', 'branch', 'creator', 'updater']);

            return response()->json([
                'status' => true,
                'message' => __('index.document_updated_success'),
                'status_code' => 200,
                'data' => $this->transformDocument($document),
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete document
     */
    public function destroy($id): JsonResponse
    {
        try {
            $document = CompanyDocument::findOrFail($id);

            // Delete file
            if ($document->file_path && File::exists(public_path($document->file_path))) {
                File::delete(public_path($document->file_path));
            }

            $document->delete();

            return response()->json([
                'status' => true,
                'message' => __('index.document_deleted_success'),
                'status_code' => 200,
            ]);
        } catch (Exception $e) {
            return AppHelper::sendErrorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Transform CompanyDocument model into a clean mobile-friendly array
     */
    protected function transformDocument(CompanyDocument $doc): array
    {
        $attachments = [];
        if ($doc->relationLoaded('attachments') && $doc->attachments->isNotEmpty()) {
            $attachments = $doc->attachments->map(fn($att) => [
                'id' => $att->id,
                'file_url' => $att->file_url,
                'file_name' => $att->file_name,
                'file_type' => $att->file_type,
                'file_size' => (int) ($att->file_size ?: 0),
                'is_pdf' => (bool) $att->is_pdf,
                'is_image' => (bool) $att->is_image,
            ])->values()->toArray();
        } elseif ($doc->file_path) {
            $attachments = [
                [
                    'id' => 0,
                    'file_url' => $doc->file_url,
                    'file_name' => $doc->file_name,
                    'file_type' => $doc->file_type,
                    'file_size' => (int) ($doc->file_size ?: 0),
                    'is_pdf' => (bool) $doc->is_pdf,
                    'is_image' => (bool) $doc->is_image,
                ]
            ];
        }

        return [
            'id' => $doc->id,
            'category_code' => $doc->category_code,
            'category_name' => $doc->category ? $doc->category->name : ucfirst($doc->category_code),
            'category_icon' => $doc->category?->icon ?? 'file-text',
            'category_color' => $doc->category?->color ?? '#0f766e',
            'doc_sub_type' => $doc->doc_sub_type,
            'branch_id' => $doc->branch_id,
            'branch_name' => $doc->branch?->name ?? __('index.head_office_all_branches'),
            'doc_number' => $doc->doc_number,
            'title' => $doc->title,
            'holder_name' => $doc->holder_name,
            'issuing_authority' => $doc->issuing_authority,
            'issue_date' => $doc->issue_date ? Carbon::parse($doc->issue_date)->format('Y-m-d') : null,
            'expiry_date' => $doc->expiry_date ? Carbon::parse($doc->expiry_date)->format('Y-m-d') : null,
            'alert_before_days' => (int) ($doc->alert_before_days ?: 30),
            'status' => $doc->status,
            'calculated_status' => $doc->calculated_status,
            'days_remaining' => $doc->days_remaining,
            'cost_amount' => $doc->cost_amount ? (float) $doc->cost_amount : null,
            'currency' => $doc->currency ?? 'USD',
            'formatted_cost' => $doc->formatted_cost,
            'receipt_number' => $doc->receipt_number,
            'file_url' => $doc->file_url,
            'file_name' => $doc->file_name,
            'file_type' => $doc->file_type,
            'file_size' => $doc->file_size ? (int) $doc->file_size : 0,
            'is_pdf' => (bool) $doc->is_pdf,
            'is_image' => (bool) $doc->is_image,
            'attachments' => $attachments,
            'attachments_count' => count($attachments),
            'confidentiality_level' => $doc->confidentiality_level ?? 'internal',
            'description' => $doc->description,
            'created_at' => $doc->created_at ? $doc->created_at->toIso8601String() : null,
            'updated_at' => $doc->updated_at ? $doc->updated_at->toIso8601String() : null,
            'created_by' => $doc->creator?->name,
        ];
    }
}
