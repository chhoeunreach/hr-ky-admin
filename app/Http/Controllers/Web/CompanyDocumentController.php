<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Company;
use App\Models\CompanyDocument;
use App\Models\CompanyDocumentCategory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class CompanyDocumentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('list_company_document');
        $categories = CompanyDocumentCategory::where('is_active', true)->orderBy('order')->get();
        $branches = Branch::select('id', 'name')->orderBy('name')->get();
        $company = Company::first();

        $query = CompanyDocument::with(['category', 'branch', 'creator']);

        // Filter: Category
        if ($request->filled('category')) {
            $query->where('category_code', $request->category);
        }

        // Filter: Sub Type (e.g. national_id, passport, family_book, residence_book)
        if ($request->filled('doc_sub_type')) {
            $query->where('doc_sub_type', $request->doc_sub_type);
        }

        // Filter: Branch
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

        // Filter: Year (issue_date or expiry_date)
        if ($request->filled('year')) {
            $year = $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('issue_date', $year)
                  ->orWhereYear('expiry_date', $year);
            });
        }

        // Filter: Status
        $today = Carbon::today();
        if ($request->filled('status')) {
            if ($request->status === 'expired') {
                $query->whereNotNull('expiry_date')->whereDate('expiry_date', '<', $today);
            } elseif ($request->status === 'expiring_soon') {
                $query->whereNotNull('expiry_date')
                      ->whereDate('expiry_date', '>=', $today)
                      ->whereRaw('DATEDIFF(expiry_date, ?) <= alert_before_days', [$today->toDateString()]);
            } elseif ($request->status === 'valid') {
                $query->where(function ($q) use ($today) {
                    $q->whereNull('expiry_date')
                      ->orWhere(function ($sub) use ($today) {
                          $sub->whereDate('expiry_date', '>=', $today)
                              ->whereRaw('DATEDIFF(expiry_date, ?) > alert_before_days', [$today->toDateString()]);
                      });
                });
            }
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        if ($sort === 'expiry_date') {
            $query->orderByRaw('expiry_date IS NULL, expiry_date ' . $direction);
        } else {
            $query->orderBy($sort, $direction);
        }

        $documents = $query->paginate(20)->withQueryString();

        // Metrics computation
        $allDocs = CompanyDocument::all();
        $totalCount = $allDocs->count();
        $masterCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_MASTER)->count();
        $taxCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_TAX)->count();
        $businessCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_BUSINESS_LICENSE)->count();
        $nssfCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_NSSF)->count();
        $contractCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_CONTRACT)->count();
        $utilityCount = $allDocs->where('category_code', CompanyDocument::CATEGORY_UTILITY)->count();

        $expiredCount = $allDocs->filter(function ($doc) use ($today) {
            return $doc->expiry_date && Carbon::parse($doc->expiry_date)->startOfDay()->isPast();
        })->count();

        $expiringSoonCount = $allDocs->filter(function ($doc) use ($today) {
            if (!$doc->expiry_date) return false;
            $exp = Carbon::parse($doc->expiry_date)->startOfDay();
            $alertDays = $doc->alert_before_days ?: 30;
            return $exp->greaterThanOrEqualTo($today) && $exp->lessThanOrEqualTo($today->copy()->addDays($alertDays));
        })->count();

        $totalCostUsd = $allDocs->where('currency', 'USD')->sum('cost_amount');
        $totalCostKhr = $allDocs->where('currency', 'KHR')->sum('cost_amount');

        return view('admin.company-document.index', compact(
            'documents',
            'categories',
            'branches',
            'company',
            'totalCount',
            'masterCount',
            'taxCount',
            'businessCount',
            'nssfCount',
            'contractCount',
            'utilityCount',
            'expiredCount',
            'expiringSoonCount',
            'totalCostUsd',
            'totalCostKhr'
        ));
    }

    public function store(Request $request)
    {
        $this->authorize('create_company_document');
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
            'currency' => ['required', 'in:USD,KHR'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
            'confidentiality_level' => ['required', 'in:public,internal,restricted,confidential'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            $fileSize = $file->getSize();

            $uploadDir = public_path(CompanyDocument::UPLOAD_PATH . $validated['category_code']);
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
            $file->move($uploadDir, $fileName);

            $filePath = CompanyDocument::UPLOAD_PATH . $validated['category_code'] . '/' . $fileName;

            // Determine file type category
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

            $document = CompanyDocument::create([
                'category_code' => $validated['category_code'],
                'doc_sub_type' => $validated['doc_sub_type'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'company_id' => Company::first()?->id,
                'doc_number' => $validated['doc_number'] ?? null,
                'title' => $validated['title'],
                'holder_name' => $validated['holder_name'] ?? null,
                'issuing_authority' => $validated['issuing_authority'] ?? null,
                'issue_date' => $validated['issue_date'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'alert_before_days' => $validated['alert_before_days'] ?: 30,
                'cost_amount' => $validated['cost_amount'] ?? null,
                'currency' => $validated['currency'],
                'receipt_number' => $validated['receipt_number'] ?? null,
                'file_path' => $filePath,
                'file_name' => $originalName,
                'file_type' => $fileType,
                'file_size' => $fileSize,
                'confidentiality_level' => $validated['confidentiality_level'],
                'description' => $validated['description'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('index.document_saved_success'),
                    'data' => $document,
                ]);
            }

            return redirect()->route('admin.company-documents.index', ['category' => $validated['category_code']])
                ->with('success', __('index.document_saved_success'));
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->withInput()->with('danger', $e->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize('list_company_document');
        $document = CompanyDocument::with(['category', 'branch', 'creator', 'updater'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $document,
            'category_name' => $document->category ? $document->category->name : ucfirst($document->category_code),
            'calculated_status' => $document->calculated_status,
            'days_remaining' => $document->days_remaining,
            'formatted_cost' => $document->formatted_cost,
            'file_url' => asset($document->file_path),
            'is_pdf' => $document->is_pdf,
            'is_image' => $document->is_image,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('edit_company_document');
        $document = CompanyDocument::findOrFail($id);

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
            'currency' => ['required', 'in:USD,KHR'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'file' => ['nullable', 'file', 'max:25600', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
            'confidentiality_level' => ['required', 'in:public,internal,restricted,confidential'],
            'description' => ['nullable', 'string'],
        ]);

        try {
            if ($request->hasFile('file')) {
                // Remove old file
                if ($document->file_path && File::exists(public_path($document->file_path))) {
                    File::delete(public_path($document->file_path));
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = strtolower($file->getClientOriginalExtension());
                $fileSize = $file->getSize();

                $uploadDir = public_path(CompanyDocument::UPLOAD_PATH . $validated['category_code']);
                if (!File::isDirectory($uploadDir)) {
                    File::makeDirectory($uploadDir, 0755, true, true);
                }

                $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                $file->move($uploadDir, $fileName);

                $validated['file_path'] = CompanyDocument::UPLOAD_PATH . $validated['category_code'] . '/' . $fileName;
                $validated['file_name'] = $originalName;
                $validated['file_size'] = $fileSize;

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
                $validated['file_type'] = $fileType;
            }

            $validated['updated_by'] = auth()->id();
            $document->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => __('index.document_updated_success'),
                    'data' => $document,
                ]);
            }

            return redirect()->route('admin.company-documents.index', ['category' => $document->category_code])
                ->with('success', __('index.document_updated_success'));
        } catch (Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->withInput()->with('danger', $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->authorize('delete_company_document');
        try {
            $document = CompanyDocument::findOrFail($id);

            if ($document->file_path && File::exists(public_path($document->file_path))) {
                File::delete(public_path($document->file_path));
            }

            $document->delete();

            return response()->json([
                'success' => true,
                'message' => __('index.document_deleted_success'),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function download($id)
    {
        $this->authorize('list_company_document');
        $document = CompanyDocument::findOrFail($id);
        $fullPath = public_path($document->file_path);

        if (!File::exists($fullPath)) {
            abort(404, 'រកមិនឃើញឯកសារ (File not found on server)');
        }

        return Response::download($fullPath, $document->file_name ?: basename($fullPath));
    }

    public function preview($id)
    {
        $this->authorize('list_company_document');
        $document = CompanyDocument::findOrFail($id);
        $fullPath = public_path($document->file_path);

        if (!File::exists($fullPath)) {
            abort(404, 'រកមិនឃើញឯកសារ (File not found on server)');
        }

        $mimeType = File::mimeType($fullPath);

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . ($document->file_name ?: basename($fullPath)) . '"',
        ]);
    }

    public function expiringAlerts(Request $request)
    {
        $this->authorize('list_company_document');
        $days = (int) $request->get('days', 30);
        $today = Carbon::today();
        $targetDate = $today->copy()->addDays($days);

        $expiringDocs = CompanyDocument::with('category', 'branch')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today)
            ->whereDate('expiry_date', '<=', $targetDate)
            ->orderBy('expiry_date', 'asc')
            ->get();

        $expiredDocs = CompanyDocument::with('category', 'branch')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today)
            ->orderBy('expiry_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'today' => $today->toDateString(),
            'expiring_soon' => $expiringDocs,
            'expired' => $expiredDocs,
            'total_alerts' => $expiringDocs->count() + $expiredDocs->count(),
        ]);
    }
}
