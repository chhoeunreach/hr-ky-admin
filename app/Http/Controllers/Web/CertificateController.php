<?php

namespace App\Http\Controllers\Web;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Department;
use App\Models\User;
use App\Repositories\BranchRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\UserRepository;
use App\Requests\CertificateManagement\CertificateRequest;
use App\Services\CertificateManagement\CertificateService;
use App\Traits\CustomAuthorizesRequests;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use CustomAuthorizesRequests;

    private string $view = 'admin.certificateManagement.';

    public function __construct(
        protected CertificateService $certificateService,
        protected UserRepository $userRepository,
        protected DepartmentRepository $departmentRepository,
        protected BranchRepository $branchRepository,
        protected CompanyRepository $companyRepository
    ) {}

    /**
     * Display a listing of issued certificates.
     */
    public function index(Request $request)
    {
        $this->authorizeAccess('certificate_list');

        try {
            $filterParameters = [
                'search' => $request->search ?? null,
                'category' => $request->category ?? null,
                'branch_id' => $request->branch_id ?? null,
                'department_id' => $request->department_id ?? null,
                'employee_id' => $request->employee_id ?? null,
                'issue_date' => $request->issue_date ?? null,
            ];

            if (!auth('admin')->check() && auth()->check() && !AppHelper::canAccessAllBranches()) {
                $filterParameters['branch_id'] = AppHelper::employeeBranchFilter($request->input('branch_id'));
            }

            $select = ['*'];
            $with = ['template', 'user:id,name,english_name,employee_code,avatar', 'branch:id,name', 'departmentRel:id,dept_name'];
            $certificates = $this->certificateService->getAllCertificatesPaginated($filterParameters, $select, $with);

            $stats = $this->certificateService->getStatistics();
            $totalCertificates = $stats['total'];
            $totalTrainingHours = $stats['total_hours'];
            $leaderCount = $stats['leader_count'];
            $serviceCount = $stats['service_count'];

            $branches = Branch::select('id', 'name')->get();
            $departments = Department::select('id', 'dept_name')->get();

            return view($this->view . 'index', compact(
                'certificates',
                'filterParameters',
                'totalCertificates',
                'totalTrainingHours',
                'leaderCount',
                'serviceCount',
                'branches',
                'departments'
            ));
        } catch (Exception $e) {
            return redirect()->back()->with('danger', 'Unable to load certificates: ' . $e->getMessage());
        }
    }

    /**
     * Display listing of saved Layout Models / Templates (វិញ្ញាបនបត្រ គំរូ).
     */
    public function templates(Request $request)
    {
        $category = $request->query('category');
        $query = CertificateTemplate::orderBy('sort_order');
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }
        $templates = $query->get();

        $counts = [
            'all' => CertificateTemplate::count(),
            'leader' => CertificateTemplate::where('category', 'leader')->count(),
            'staff' => CertificateTemplate::where('category', 'staff')->count(),
            'service' => CertificateTemplate::where('category', 'service')->count(),
            'course' => CertificateTemplate::where('category', 'course')->count(),
            'custom' => CertificateTemplate::where('category', 'custom')->count(),
        ];

        return view($this->view . 'templates', compact('templates', 'counts', 'category'));
    }

    /**
     * Set a template as the default layout model.
     */
    public function setDefaultTemplate($id)
    {
        CertificateTemplate::query()->update(['is_default' => false]);
        CertificateTemplate::where('id', $id)->update(['is_default' => true]);
        $template = CertificateTemplate::findOrFail($id);

        return redirect()->back()->with('success', "កំណត់គំរូ [{$template->title}] ជាគំរូលំនាំដើម (Default Layout Model) ដោយជោគជ័យ។");
    }

    /**
     * Reset a template to its pristine factory default.
     */
    public function resetTemplate($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $ok = \Database\Seeders\CertificateTemplateSeeder::resetTemplateToDefault($template);

        if ($ok) {
            return redirect()->back()->with('success', "កំណត់គំរូ [{$template->title}] ឡើងវិញទៅលំនាំដើម (Reset to Default) ដោយជោគជ័យ។");
        }

        return redirect()->back()->with('error', "មិនអាចកំណត់គំរូនេះទៅលំនាំដើមបានទេ។");
    }

    /**
     * Dedicated Full A4 Preview page for a Layout Model.
     */
    public function previewTemplate(Request $request, $id)
    {
        $this->authorizeAccess('certificate_list');

        $template = CertificateTemplate::where('id', $id)->orWhere('template_code', $id)->firstOrFail();
        $certificate = $this->certificateService->buildPreviewCertificate([], null, $template);
        $templates = CertificateTemplate::where('is_active', true)->orderBy('sort_order')->get();

        // Embed / content-only mode: render ONLY the A4 certificate canvas
        // (no admin sidebar, no top navigation, no print customization toolbar).
        $embed = $request->boolean('embed') || $request->boolean('content_only');

        return view($this->view . 'template', [
            'certificate' => $certificate,
            'templates' => $templates,
            'activeTemplate' => $template,
            'isPreview' => $embed,
            'autoPrint' => $request->boolean('print') || $request->query('print') == '1',
            'isTemplatePreview' => true,
        ]);
    }

    /**
     * Store a newly created Layout Model / Template.
     */
    public function storeTemplate(Request $request)
    {
        $this->authorizeAccess('certificate_create');

        $validated = $request->validate([
            'title' => 'nullable|string|max:190',
            'khmer_title' => 'required|string|max:190',
            'category' => 'required|string|in:leader,staff,service,course,custom',
            'layout_template' => 'required|string|max:60',
            'certificate_title' => 'nullable|string|max:190',
            'khmer_certificate_title' => 'nullable|string|max:190',
            'description' => 'nullable|string|max:500',
            'khmer_description' => 'nullable|string|max:500',
            'training_hours' => 'nullable|integer|min:0|max:999',
            'version_tag' => 'nullable|string|max:20',
            'is_default' => 'nullable|boolean',
        ]);

        $title = !empty($validated['title']) ? $validated['title'] : $validated['khmer_title'];
        $khmerTitle = $validated['khmer_title'];

        // Clean unique code
        $baseCode = 'TMPL-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 8));
        if (strlen($baseCode) <= 5) {
            $baseCode = 'TMPL-' . strtoupper($validated['category']);
        }
        $code = $baseCode;
        $idx = 1;
        while (CertificateTemplate::where('template_code', $code)->exists()) {
            $code = $baseCode . '-' . $idx;
            $idx++;
        }

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            CertificateTemplate::query()->update(['is_default' => false]);
        }

        $layout = $validated['layout_template'];
        $certTitle = !empty($validated['certificate_title']) ? $validated['certificate_title'] : 'CERTIFICATE OF APPRECIATION';
        $khmerCertTitle = !empty($validated['khmer_certificate_title']) ? $validated['khmer_certificate_title'] : 'វិញ្ញាបនបត្រកោតសរសើរ និងផ្តល់ការតែងតាំង';
        $hours = (int) ($validated['training_hours'] ?? 45);

        // Standard styling according to layout
        $styling = [
            'primaryColor' => '#092653',
            'accentColor' => '#D4A32C',
            'bgColor' => '#FCFBF7',
            'borderStyle' => in_array($layout, ['classic', 'v4-royal']) ? 'classic-vintage' : 'navy-gold-ribbon',
            'dividerOrnament' => 'baroque-fleur',
            'dividerWidth' => 240,
            'showWatermark' => true,
            'watermarkOpacity' => 0.14,
            'watermarkType' => 'laurel',
            'logoOrnament' => 'classic-laurel',
            'titleFontSize' => 34,
            'recipientFontSize' => 42,
            'fieldStyles' => [
                'khmerTitle' => ['fontFamily' => 'khmer-moul', 'fontSize' => 24],
                'khmerRecipientName' => ['fontFamily' => 'khmer-moul', 'fontSize' => 30],
            ],
        ];

        $seal = [
            'enabled' => true,
            'style' => 'crown-laurel',
            'ribbonColor' => 'navy',
            'primaryText' => 'គ្នា​យើង',
            'secondaryText' => 'SEAL',
        ];

        $signatories = [
            [
                'id' => 's1',
                'name' => 'CHHOEUN REACH',
                'khmerName' => 'ឈឿន រាជ',
                'role' => 'IT Manager / Branch Manager',
                'khmerRole' => 'ប្រធានផ្នែកព័ត៌មានវិទ្យា / ប្រធានសាខា',
                'organization' => 'Admin • HR • System • Stock',
                'signatureStyle' => 'alex-brush',
            ],
            [
                'id' => 's2',
                'name' => 'SOPHEA MENG',
                'khmerName' => 'ម៉េង សោភា',
                'role' => 'HR Manager / HR Supervisor',
                'khmerRole' => 'ប្រធានផ្នែកធនធានមនុស្ស',
                'organization' => 'Human Resource Department',
                'signatureStyle' => 'great-vibes',
            ],
        ];

        $payload = [
            'title' => $title,
            'khmerTitle' => $khmerCertTitle,
            'certificateTitle' => $certTitle,
            'bannerText' => 'THIS CERTIFICATE IS PRESENTED TO',
            'recipientName' => 'KHUN SREYNI',
            'khmerRecipientName' => 'ឃុន ស្រីនី',
            'recipientTitle' => 'Team Leader · KNEAYERNG PHONE SHOP',
            'khmerRecipientTitle' => 'ប្រធានក្រុមឆ្នើម',
            'credentialId' => 'KY-CERT-' . date('Y') . '-' . rand(100, 999),
            'issueDate' => '15 November 2026',
            'khmerIssueDate' => 'ថ្ងៃទី ១៥ ខែ វិច្ឆិកា ឆ្នាំ ២០២៦',
            'digitalHr' => [
                'employeeId' => 'EMP-KY-TL-2026-02',
                'trainingHours' => $hours,
                'complianceStatus' => 'Verified & Accredited',
            ],
            'citation' => 'In recognition of your dedication, leadership skills, and official appointment as Team Leader at Kneayerng Phone Shop.',
            'khmerCitation' => 'ដើម្បីទទួលស្គាល់ការលះបង់ ជំនាញដឹកនាំ និងការតែងតាំងជាផ្លូវការជាប្រធានក្រុមនៅហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)។ ការប្ដេជ្ញាចិត្តរបស់អ្នកក្នុងការដឹកនាំសហការី គឺជាគំរូដ៏ប្រពៃសម្រាប់ក្រុមទាំងមូល។',
            'citationClosing' => 'Congratulations on this well-deserved achievement, and we wish you continued success in your new role.',
            'styling' => $styling,
            'seal' => $seal,
            'signatories' => $signatories,
        ];

        $template = CertificateTemplate::create([
            'template_code' => $code,
            'title' => $title,
            'khmer_title' => $khmerTitle,
            'category' => $validated['category'],
            'layout_template' => $layout,
            'version_tag' => !empty($validated['version_tag']) ? $validated['version_tag'] : 'CUSTOM',
            'language_mode' => 'bilingual',
            'theme' => 'navy-gold',
            'description' => $validated['description'] ?? 'Custom layout model for Kneayerng certificates.',
            'khmer_description' => $validated['khmer_description'] ?? 'គំរូវិញ្ញាបនបត្ររចនាសម្រាប់ហាងទូរស័ព្ទ គ្នាយើង។',
            'issuer_name' => 'KNEAYERNG PHONE SHOP',
            'issuer_khmer_name' => 'ហាងទូរស័ព្ទ គ្នាយើង (Kneayerng Phone Shop)',
            'department' => 'PHONE SHOP',
            'logo_url' => asset('assets/aistudio/unnamed_521b71dc.jpg'),
            'certificate_title' => $certTitle,
            'khmer_certificate_title' => $khmerCertTitle,
            'subtitle' => 'THIS CERTIFICATE IS PRESENTED TO',
            'khmer_subtitle' => 'វិញ្ញាបនបត្រនេះត្រូវបានប្រគល់ជូន',
            'authority_preamble' => 'KINGDOM OF CAMBODIA · NATION RELIGION KING',
            'khmer_preamble' => 'ព្រះរាជាណាចក្រកម្ពុជា · ជាតិ សាសនា ព្រះមហាក្សត្រ',
            'course_or_achievement_title' => 'Leadership Development',
            'khmer_course_or_achievement_title' => 'ការអភិវឌ្ឍភាពជាអ្នកដឹកនាំ',
            'citation' => $payload['citation'],
            'khmer_citation' => $payload['khmerCitation'],
            'citation_closing' => $payload['citationClosing'],
            'training_hours' => $hours,
            'hr_competency_track' => 'Leadership Development',
            'compliance_status' => 'Verified & Accredited',
            'signatories_json' => $signatories,
            'styling_json' => $styling,
            'seal_json' => $seal,
            'payload_json' => $payload,
            'is_default' => $isDefault,
            'is_active' => true,
            'sort_order' => (int) CertificateTemplate::max('sort_order') + 1,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.certificates.templates')
            ->with('success', "បានបន្ថែមគំរូ Layout [{$template->khmer_title}] ដោយជោគជ័យ (Layout Model created successfully).");
    }

    /**
     * Show full edit page for a Layout Model.
     */
    public function editTemplate($id)
    {
        $this->authorizeAccess('certificate_edit');
        $template = CertificateTemplate::findOrFail($id);
        $previewCert = $this->certificateService->buildPreviewCertificate([], null, $template);
        return view($this->view . 'editTemplate', compact('template', 'previewCert'));
    }

    /**
     * Update an existing Layout Model / Template with full customization.
     */
    public function updateTemplate(Request $request, $id)
    {
        $this->authorizeAccess('certificate_edit');

        $template = CertificateTemplate::findOrFail($id);

        $validated = $request->validate([
            // Tab 1: General & Layout
            'title' => 'nullable|string|max:190',
            'khmer_title' => 'required|string|max:190',
            'category' => 'required|string|in:leader,staff,service,course,custom',
            'layout_template' => 'required|string|max:60',
            'theme' => 'nullable|string|max:40',
            'language_mode' => 'nullable|string|max:30',
            'version_tag' => 'nullable|string|max:40',
            'training_hours' => 'nullable|integer|min:0|max:999',
            'hr_competency_track' => 'nullable|string|max:190',
            'compliance_status' => 'nullable|string|max:190',
            'description' => 'nullable|string|max:1000',
            'khmer_description' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',

            // Tab 2: Content & Text
            'issuer_name' => 'nullable|string|max:190',
            'issuer_khmer_name' => 'nullable|string|max:190',
            'department' => 'nullable|string|max:190',
            'logo_url' => 'nullable|string|max:500',
            'authority_preamble' => 'nullable|string|max:250',
            'khmer_preamble' => 'nullable|string|max:250',
            'certificate_title' => 'nullable|string|max:190',
            'khmer_certificate_title' => 'nullable|string|max:190',
            'banner_text' => 'nullable|string|max:190',
            'subtitle' => 'nullable|string|max:250',
            'khmer_subtitle' => 'nullable|string|max:250',
            'organization_sub_banner' => 'nullable|string|max:190',
            'show_organization_sub_banner' => 'nullable|boolean',
            'course_or_achievement_title' => 'nullable|string|max:250',
            'khmer_course_or_achievement_title' => 'nullable|string|max:250',
            'citation' => 'nullable|string|max:2000',
            'khmer_citation' => 'nullable|string|max:2000',
            'citation_closing' => 'nullable|string|max:500',

            // Tab 3: Style & Appearance
            'primary_color' => 'nullable|string|max:30',
            'accent_color' => 'nullable|string|max:30',
            'bg_color' => 'nullable|string|max:30',
            'border_style' => 'nullable|string|max:60',
            'divider_ornament' => 'nullable|string|max:60',
            'divider_width' => 'nullable|integer|min:80|max:600',
            'show_recipient_photo' => 'nullable|boolean',
            'recipient_photo_shape' => 'nullable|string|max:40',
            'show_watermark' => 'nullable|boolean',
            'watermark_opacity' => 'nullable|numeric|min:0|max:1',
            'show_storefront_watermark' => 'nullable|boolean',
            'show_digital_hours_badge' => 'nullable|boolean',
            'hanging_medal_style' => 'nullable|string|max:60',
            'show_qr_code' => 'nullable|boolean',
            'font_english_title' => 'nullable|string|max:60',
            'font_body' => 'nullable|string|max:60',
            'font_signature' => 'nullable|string|max:60',

            // Tab 4: Signatories
            'sig1_name' => 'nullable|string|max:120',
            'sig1_khmer_name' => 'nullable|string|max:120',
            'sig1_role' => 'nullable|string|max:150',
            'sig1_khmer_role' => 'nullable|string|max:150',
            'sig1_organization' => 'nullable|string|max:150',
            'sig1_style' => 'nullable|string|max:50',

            'sig2_name' => 'nullable|string|max:120',
            'sig2_khmer_name' => 'nullable|string|max:120',
            'sig2_role' => 'nullable|string|max:150',
            'sig2_khmer_role' => 'nullable|string|max:150',
            'sig2_organization' => 'nullable|string|max:150',
            'sig2_style' => 'nullable|string|max:50',

            // Seal
            'seal_enabled' => 'nullable|boolean',
            'seal_style' => 'nullable|string|max:60',
            'seal_ribbon_color' => 'nullable|string|max:30',
            'seal_primary_text' => 'nullable|string|max:120',
            'seal_secondary_text' => 'nullable|string|max:120',
        ]);

        $title = !empty($validated['title']) ? $validated['title'] : $validated['khmer_title'];
        $khmerTitle = $validated['khmer_title'];

        $isDefault = $request->boolean('is_default');
        if ($isDefault && !$template->is_default) {
            CertificateTemplate::query()->update(['is_default' => false]);
            $template->is_default = true;
        } elseif (!$isDefault && $template->is_default) {
            $hasOtherDefault = CertificateTemplate::where('id', '!=', $template->id)->where('is_default', true)->exists();
            if ($hasOtherDefault) {
                $template->is_default = false;
            }
        }

        // 1. General & Layout Columns
        $template->title = $title;
        $template->khmer_title = $khmerTitle;
        $template->category = $validated['category'];
        $template->layout_template = $validated['layout_template'];
        if (!empty($validated['theme'])) $template->theme = $validated['theme'];
        if (!empty($validated['language_mode'])) $template->language_mode = $validated['language_mode'];
        if (!empty($validated['version_tag'])) $template->version_tag = $validated['version_tag'];
        if (isset($validated['training_hours'])) $template->training_hours = (int) $validated['training_hours'];
        if (array_key_exists('hr_competency_track', $validated)) $template->hr_competency_track = $validated['hr_competency_track'];
        if (array_key_exists('compliance_status', $validated)) $template->compliance_status = $validated['compliance_status'];
        if (array_key_exists('description', $validated)) $template->description = $validated['description'];
        if (array_key_exists('khmer_description', $validated)) $template->khmer_description = $validated['khmer_description'];
        if ($request->has('is_active')) $template->is_active = $request->boolean('is_active');

        // 2. Issuer & Content & Text Columns
        if (array_key_exists('issuer_name', $validated)) $template->issuer_name = $validated['issuer_name'];
        if (array_key_exists('issuer_khmer_name', $validated)) $template->issuer_khmer_name = $validated['issuer_khmer_name'];
        if (array_key_exists('department', $validated)) $template->department = $validated['department'];
        if (array_key_exists('logo_url', $validated) && !empty($validated['logo_url'])) $template->logo_url = $validated['logo_url'];
        if (array_key_exists('authority_preamble', $validated)) $template->authority_preamble = $validated['authority_preamble'];
        if (array_key_exists('khmer_preamble', $validated)) $template->khmer_preamble = $validated['khmer_preamble'];
        if (array_key_exists('certificate_title', $validated)) $template->certificate_title = $validated['certificate_title'];
        if (array_key_exists('khmer_certificate_title', $validated)) $template->khmer_certificate_title = $validated['khmer_certificate_title'];
        if (array_key_exists('subtitle', $validated)) $template->subtitle = $validated['subtitle'];
        if (array_key_exists('khmer_subtitle', $validated)) $template->khmer_subtitle = $validated['khmer_subtitle'];
        if (array_key_exists('course_or_achievement_title', $validated)) $template->course_or_achievement_title = $validated['course_or_achievement_title'];
        if (array_key_exists('khmer_course_or_achievement_title', $validated)) $template->khmer_course_or_achievement_title = $validated['khmer_course_or_achievement_title'];
        if (array_key_exists('citation', $validated)) $template->citation = $validated['citation'];
        if (array_key_exists('khmer_citation', $validated)) $template->khmer_citation = $validated['khmer_citation'];
        if (array_key_exists('citation_closing', $validated)) $template->citation_closing = $validated['citation_closing'];

        // 3. Build & Sync Styling JSON
        $styling = is_array($template->styling_json) ? $template->styling_json : [];
        if (!empty($validated['primary_color'])) $styling['primaryColor'] = $validated['primary_color'];
        if (!empty($validated['accent_color'])) $styling['accentColor'] = $validated['accent_color'];
        if (!empty($validated['bg_color'])) $styling['bgColor'] = $validated['bg_color'];
        if (!empty($validated['border_style'])) $styling['borderStyle'] = $validated['border_style'];
        if (!empty($validated['divider_ornament'])) $styling['dividerOrnament'] = $validated['divider_ornament'];
        if (isset($validated['divider_width'])) $styling['dividerWidth'] = (int) $validated['divider_width'];
        if ($request->has('show_watermark')) $styling['showWatermark'] = $request->boolean('show_watermark');
        if (isset($validated['watermark_opacity'])) $styling['watermarkOpacity'] = (float) $validated['watermark_opacity'];
        if (!empty($validated['font_english_title'])) $styling['fontDisplay'] = $validated['font_english_title'];
        if (!empty($validated['font_body'])) $styling['fontBody'] = $validated['font_body'];
        if (!empty($validated['font_signature'])) $styling['fontSignature'] = $validated['font_signature'];
        $template->styling_json = $styling;

        // 4. Build & Sync Signatories JSON
        $signatories = is_array($template->signatories_json) ? $template->signatories_json : [];
        if (!isset($signatories[0])) $signatories[0] = [];
        if (!isset($signatories[1])) $signatories[1] = [];

        if (array_key_exists('sig1_name', $validated)) $signatories[0]['name'] = $validated['sig1_name'];
        if (array_key_exists('sig1_khmer_name', $validated)) $signatories[0]['khmerName'] = $validated['sig1_khmer_name'];
        if (array_key_exists('sig1_role', $validated)) $signatories[0]['role'] = $validated['sig1_role'];
        if (array_key_exists('sig1_khmer_role', $validated)) $signatories[0]['khmerRole'] = $validated['sig1_khmer_role'];
        if (array_key_exists('sig1_organization', $validated)) $signatories[0]['organization'] = $validated['sig1_organization'];
        if (array_key_exists('sig1_style', $validated)) $signatories[0]['signatureStyle'] = $validated['sig1_style'];

        if (array_key_exists('sig2_name', $validated)) $signatories[1]['name'] = $validated['sig2_name'];
        if (array_key_exists('sig2_khmer_name', $validated)) $signatories[1]['khmerName'] = $validated['sig2_khmer_name'];
        if (array_key_exists('sig2_role', $validated)) $signatories[1]['role'] = $validated['sig2_role'];
        if (array_key_exists('sig2_khmer_role', $validated)) $signatories[1]['khmerRole'] = $validated['sig2_khmer_role'];
        if (array_key_exists('sig2_organization', $validated)) $signatories[1]['organization'] = $validated['sig2_organization'];
        if (array_key_exists('sig2_style', $validated)) $signatories[1]['signatureStyle'] = $validated['sig2_style'];

        $template->signatories_json = array_values($signatories);

        // 5. Build & Sync Seal JSON
        $seal = is_array($template->seal_json) ? $template->seal_json : [];
        if (!empty($validated['seal_style'])) $seal['style'] = $validated['seal_style'];
        if (!empty($validated['seal_ribbon_color'])) $seal['ribbonColor'] = $validated['seal_ribbon_color'];
        if (array_key_exists('seal_primary_text', $validated)) $seal['primaryText'] = $validated['seal_primary_text'];
        if (array_key_exists('seal_secondary_text', $validated)) $seal['secondaryText'] = $validated['seal_secondary_text'];
        if ($request->has('seal_enabled')) {
            $seal['enabled'] = $request->boolean('seal_enabled');
        } else {
            $seal['enabled'] = true;
        }
        $template->seal_json = $seal;

        // 6. Build & Sync Comprehensive Payload JSON (for CertiCraft Studio & Previews)
        $p = is_array($template->payload_json) ? $template->payload_json : [];
        $p['id'] = $template->template_code ?: ('TMPL-' . $template->id);
        $p['title'] = $title;
        $p['templateTitle'] = $title;
        $p['templateKhmerTitle'] = $khmerTitle;
        $p['category'] = $validated['category'];
        $p['layoutTemplate'] = $validated['layout_template'];
        if (!empty($validated['theme'])) $p['theme'] = $validated['theme'];
        if (!empty($validated['language_mode'])) $p['languageMode'] = $validated['language_mode'];
        if (!empty($validated['version_tag'])) $p['versionTag'] = $validated['version_tag'];
        if (!empty($validated['certificate_title'])) $p['certificateTitle'] = $validated['certificate_title'];
        if (!empty($validated['khmer_certificate_title'])) $p['khmerTitle'] = $validated['khmer_certificate_title'];
        if (!empty($validated['banner_text'])) $p['bannerText'] = $validated['banner_text'];
        if (array_key_exists('subtitle', $validated)) $p['subtitle'] = $validated['subtitle'];
        if (array_key_exists('khmer_subtitle', $validated)) $p['khmerSubtitle'] = $validated['khmer_subtitle'];
        if (array_key_exists('organization_sub_banner', $validated)) $p['organizationSubBanner'] = $validated['organization_sub_banner'];
        if (array_key_exists('authority_preamble', $validated)) $p['authorityPreamble'] = $validated['authority_preamble'];
        if (array_key_exists('khmer_preamble', $validated)) $p['khmerPreamble'] = $validated['khmer_preamble'];
        if (array_key_exists('course_or_achievement_title', $validated)) $p['courseOrAchievementTitle'] = $validated['course_or_achievement_title'];
        if (array_key_exists('khmer_course_or_achievement_title', $validated)) $p['khmerCourseOrAchievementTitle'] = $validated['khmer_course_or_achievement_title'];
        if (array_key_exists('citation', $validated)) $p['citation'] = $validated['citation'];
        if (array_key_exists('khmer_citation', $validated)) $p['khmerCitation'] = $validated['khmer_citation'];
        if (array_key_exists('citation_closing', $validated)) $p['citationClosing'] = $validated['citation_closing'];

        if ($request->has('show_recipient_photo')) $p['showRecipientPhoto'] = $request->boolean('show_recipient_photo');
        if (!empty($validated['recipient_photo_shape'])) $p['recipientPhotoShape'] = $validated['recipient_photo_shape'];
        if (!empty($validated['hanging_medal_style'])) $p['hangingMedalStyle'] = $validated['hanging_medal_style'];
        if ($request->has('show_qr_code')) $p['showQrCode'] = $request->boolean('show_qr_code');

        if (!isset($p['visibility']) || !is_array($p['visibility'])) $p['visibility'] = [];
        if ($request->has('show_organization_sub_banner')) $p['visibility']['showOrganizationSubBanner'] = $request->boolean('show_organization_sub_banner');
        if ($request->has('show_storefront_watermark')) $p['visibility']['showStorefrontWatermark'] = $request->boolean('show_storefront_watermark');

        if (!isset($p['issuer']) || !is_array($p['issuer'])) $p['issuer'] = [];
        if (array_key_exists('issuer_name', $validated)) $p['issuer']['name'] = $validated['issuer_name'];
        if (array_key_exists('issuer_khmer_name', $validated)) $p['issuer']['khmerName'] = $validated['issuer_khmer_name'];
        if (array_key_exists('department', $validated)) $p['issuer']['department'] = $validated['department'];
        if (!empty($template->logo_url)) $p['issuer']['logoUrl'] = $template->logo_url;

        if (!isset($p['digitalHr']) || !is_array($p['digitalHr'])) $p['digitalHr'] = [];
        if (isset($validated['training_hours'])) $p['digitalHr']['trainingHours'] = (int) $validated['training_hours'];
        if (array_key_exists('compliance_status', $validated)) $p['digitalHr']['complianceStatus'] = $validated['compliance_status'];
        if (array_key_exists('hr_competency_track', $validated)) $p['digitalHr']['hrCompetencyTrack'] = $validated['hr_competency_track'];
        if ($request->has('show_digital_hours_badge')) $p['digitalHr']['showDigitalHoursBadge'] = $request->boolean('show_digital_hours_badge');

        $p['styling'] = $styling;
        $p['signatories'] = $signatories;
        $p['seal'] = $seal;
        $template->payload_json = $p;

        $template->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "បានកែសម្រួលគំរូ [{$template->khmer_title}] ដោយជោគជ័យ (Layout Model updated successfully).",
                'template' => $template,
            ]);
        }

        return redirect()->route('admin.certificates.templates')
            ->with('success', "បានកែសម្រួលគំរូ [{$template->khmer_title}] ដោយជោគជ័យពេញលេញ (Layout Model content, style & text updated successfully).");
    }

    /**
     * Delete a Layout Model / Template.
     */
    public function destroyTemplate($id)
    {
        $this->authorizeAccess('certificate_delete');

        $template = CertificateTemplate::findOrFail($id);
        $wasDefault = $template->is_default;
        $title = $template->khmer_title ?: $template->title;

        // Reassign default to another template if this was the default
        if ($wasDefault) {
            $next = CertificateTemplate::where('id', '!=', $id)->where('is_active', true)->first();
            if ($next) {
                $next->is_default = true;
                $next->save();
            }
        }

        // Unlink on issued certificates referencing this template
        Certificate::where('template_id', $id)->update(['template_id' => null]);

        $template->delete();

        return redirect()->route('admin.certificates.templates')
            ->with('success', "បានលុបគំរូ [{$title}] ដោយជោគជ័យ (Layout Model deleted successfully).");
    }

    /**
     * Show form to issue a new certificate.
     */
    public function create(Request $request)
    {
        $this->authorizeAccess('certificate_create');

        $users = User::select('id', 'name', 'english_name', 'employee_code', 'department_id', 'branch_id')
            ->where('status', 'verified')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get();

        $departments = Department::select('id', 'dept_name')->get();
        $branches = Branch::select('id', 'name')->get();

        $selectedEmployeeId = $request->query('employee_id');
        $selectedUser = $selectedEmployeeId ? User::find($selectedEmployeeId) : null;

        $templates = CertificateTemplate::where('is_active', true)->orderBy('sort_order')->get();
        $selectedTemplateId = $request->query('template_id') ?? $request->query('template');
        $selectedTemplate = null;
        if ($selectedTemplateId) {
            $selectedTemplate = $templates->firstWhere('id', $selectedTemplateId)
                ?? $templates->firstWhere('template_code', $selectedTemplateId);
        }
        if (!$selectedTemplate) {
            $selectedTemplate = $templates->firstWhere('is_default', true) ?? $templates->first();
        }

        $suggestedId = 'KY-LDR-' . date('Y') . '-' . str_pad(Certificate::count() + 1, 3, '0', STR_PAD_LEFT);

        return view($this->view . 'create', compact(
            'users',
            'departments',
            'branches',
            'suggestedId',
            'selectedUser',
            'templates',
            'selectedTemplate'
        ));
    }

    /**
     * Store a newly created certificate.
     */
    public function store(CertificateRequest $request)
    {
        $this->authorizeAccess('certificate_create');

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $validated['created_by'] = Auth::id();

            $certificate = $this->certificateService->saveCertificate($validated);

            DB::commit();

            return redirect()->route('admin.certificates.show', $certificate->credential_id)
                ->with('success', 'Certificate issued successfully: ' . $certificate->credential_id);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('danger', 'Error creating certificate: ' . $e->getMessage());
        }
    }

    /**
     * Show rendered certificate (HTML canvas / print layout).
     * Supports dynamically selecting and previewing Layout Models (វិញ្ញាបនបត្រ គំរូ).
     */
    public function show(Request $request, $id)
    {
        $templateId = $request->query('template_id') ?? $request->query('template');
        $layout = $request->query('layout');

        $certificate = $this->certificateService->resolveTemplateData($id);
        $templates = CertificateTemplate::where('is_active', true)->orderBy('sort_order')->get();

        // Match requested or saved Layout Model
        $activeTemplate = null;
        if ($templateId) {
            $activeTemplate = CertificateTemplate::where('id', $templateId)
                ->orWhere('template_code', $templateId)
                ->first();
        } elseif (!empty($certificate['template_id'])) {
            $activeTemplate = CertificateTemplate::find($certificate['template_id']);
        }

        if (!$activeTemplate && !empty($certificate['layoutTemplate'])) {
            $activeTemplate = CertificateTemplate::where('layout_template', $certificate['layoutTemplate'])->first();
        }

        if ($activeTemplate) {
            $certificate['template_id'] = $activeTemplate->id;
            $certificate['templateCode'] = $activeTemplate->template_code;
            $certificate['templateTitle'] = $activeTemplate->title;
            $certificate['templateKhmerTitle'] = $activeTemplate->khmer_title;
            $certificate['layoutTemplate'] = $activeTemplate->layout_template;
            if (!empty($activeTemplate->theme)) {
                $certificate['theme'] = $activeTemplate->theme;
            }
            if (!empty($activeTemplate->language_mode)) {
                $certificate['languageMode'] = $activeTemplate->language_mode;
            }
            if (!empty($activeTemplate->styling_json)) {
                $certificate['styling'] = $activeTemplate->styling_json;
            }
            if (!empty($activeTemplate->seal_json)) {
                $certificate['seal'] = $activeTemplate->seal_json;
            }
            if (!empty($activeTemplate->logo_url)) {
                $certificate['issuer']['logoUrl'] = $activeTemplate->logo_url;
            }
            $tp = is_array($activeTemplate->payload_json) ? $activeTemplate->payload_json : [];
            foreach ([
                'showRecipientPhoto', 'recipientPhotoPosition', 'recipientPhotoShape', 'recipientPhotoSize',
                'recipientPhotoZoom', 'recipientPhotoOffsetX', 'recipientPhotoOffsetY',
                'recipientPhotoFrameOffsetX', 'recipientPhotoFrameOffsetY',
                'hangingMedalStyle', 'hangingMedalScale', 'visibility',
            ] as $k) {
                if (isset($tp[$k])) {
                    $certificate[$k] = $tp[$k];
                }
            }
        } elseif ($layout) {
            $certificate['layoutTemplate'] = $layout;
        }

        return view($this->view . 'template', [
            'certificate' => $certificate,
            'templates' => $templates,
            'activeTemplate' => $activeTemplate,
            'isPreview' => false,
            'autoPrint' => $request->boolean('print') || $request->query('print') == '1',
        ]);
    }

    /**
     * Apply and persist a selected Layout Model (វិញ្ញាបនបត្រ គំរូ) onto a certificate record.
     */
    public function applyTemplate(Request $request, $id)
    {
        $this->authorizeAccess('certificate_edit');

        $templateId = $request->input('template_id');
        $template = CertificateTemplate::findOrFail($templateId);

        $record = Certificate::where('credential_id', $id)->orWhere('id', $id)->firstOrFail();
        $record->template_id = $template->id;
        $record->layout_template = $template->layout_template;
        if (!empty($template->theme)) {
            $record->theme = $template->theme;
        }

        if (is_array($record->payload_json)) {
            $p = $record->payload_json;
            $p['templateId'] = $template->id;
            $p['templateCode'] = $template->template_code;
            $p['layoutTemplate'] = $template->layout_template;
            $p['theme'] = $template->theme;
            $record->payload_json = $p;
        }

        $record->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'បានកំណត់វិញ្ញាបនបត្រគំរូដោយជោគជ័យ (Layout Model applied: ' . $template->title . ')',
                'template_id' => $template->id,
                'layout_template' => $template->layout_template,
            ]);
        }

        return redirect()->route('admin.certificates.show', [
            'certificate' => $record->credential_id ?: $id,
            'template_id' => $template->id,
        ])->with('success', 'បានផ្លាស់ប្តូរវិញ្ញាបនបត្រគំរូដោយជោគជ័យ៖ ' . $template->title);
    }

    /**
     * Live preview for Batch Issue (renders the same A4 template without saving).
     */
    public function batchPreview(Request $request)
    {
        $this->authorizeAccess('certificate_create');

        $template = $request->filled('template_id')
            ? CertificateTemplate::find($request->input('template_id'))
            : null;

        if (!$template && $request->filled('layout_template')) {
            $template = CertificateTemplate::where('layout_template', $request->input('layout_template'))->first();
        }

        if (!$template) {
            $template = CertificateTemplate::where('is_default', true)->first() ?? CertificateTemplate::first();
        }

        $user = $request->filled('employee_id')
            ? User::with('post')->find($request->input('employee_id'))
            : null;

        $certificate = $this->certificateService->buildPreviewCertificate(
            $request->only([
                'category',
                'layout_template',
                'certificate_title',
                'khmer_title',
                'subtitle',
                'khmer_subtitle',
                'course_or_achievement_title',
                'khmer_course_or_achievement_title',
                'citation',
                'khmer_citation',
                'training_hours',
                'compliance_status',
                'hr_competency_track',
                'issue_date',
                'khmer_issue_date',
                'organization_sub_banner',
                'show_organization_sub_banner',
                'hanging_medal_style',
                'seal_enabled',
                'seal_style',
                'seal_ribbon_color',
                'show_digital_hours_badge',
                'show_storefront_watermark',
                'show_watermark',
                'show_qr_code',
                'sig1_name',
                'sig1_khmer_name',
                'sig1_role',
                'sig1_khmer_role',
                'sig1_organization',
                'sig1_style',
                'sig2_name',
                'sig2_khmer_name',
                'sig2_role',
                'sig2_khmer_role',
                'sig2_organization',
                'sig2_style',
            ]),
            $user,
            $template
        );

        return view($this->view . 'template', [
            'certificate' => $certificate,
            'activeTemplate' => $template,
            'isPreview' => true,
            'autoPrint' => false,
        ]);
    }

    /**
     * Print all certificates from a saved batch in one document.
     */
    public function batchPrint(Request $request)
    {
        $this->authorizeAccess('certificate_list');

        $ids = array_filter((array) $request->query('ids', []));
        $certificates = [];
        foreach ($ids as $id) {
            try {
                $data = $this->certificateService->resolveTemplateData($id);
                if (!empty($data['recipientName'])) {
                    $certificates[] = $data;
                }
            } catch (\Throwable $e) {
                // Skip missing certificates so one bad ID does not break printing.
            }
        }

        return view($this->view . 'batchPrint', [
            'certificates' => $certificates,
            'autoPrint' => $request->boolean('print'),
        ]);
    }

    /**
     * Show form to edit certificate.
     */
    public function edit($id)
    {
        $this->authorizeAccess('certificate_edit');

        $certificate = $this->certificateService->findCertificateById($id);
        if (!$certificate) {
            return redirect()->route('admin.certificates.index')->with('danger', 'Certificate not found.');
        }

        $users = User::select('id', 'name', 'english_name', 'employee_code')->get();
        $departments = Department::select('id', 'dept_name')->get();
        $branches = Branch::select('id', 'name')->get();

        return view($this->view . 'edit', compact('certificate', 'users', 'departments', 'branches'));
    }

    /**
     * Update certificate.
     */
    public function update(CertificateRequest $request, $id)
    {
        $this->authorizeAccess('certificate_edit');

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $this->certificateService->updateCertificate($id, $validated);

            DB::commit();

            return redirect()->route('admin.certificates.index')->with('success', 'Certificate updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('danger', 'Error updating certificate: ' . $e->getMessage());
        }
    }

    /**
     * Delete certificate.
     */
    public function destroy($id)
    {
        $this->authorizeAccess('certificate_delete');

        try {
            DB::beginTransaction();

            $this->certificateService->deleteCertificate($id);

            DB::commit();

            return redirect()->route('admin.certificates.index')->with('success', 'Certificate removed successfully.');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('danger', 'Error deleting certificate: ' . $e->getMessage());
        }
    }

    /**
     * Export certificate to PDF using Laravel DomPDF.
     */
    public function downloadPdf(Request $request, $id)
    {
        $templateId = $request->query('template_id') ?? $request->query('template');
        $certificate = $this->certificateService->resolveTemplateData($id);

        if ($templateId) {
            $tmpl = CertificateTemplate::where('id', $templateId)->orWhere('template_code', $templateId)->first();
            if ($tmpl) {
                $certificate['template_id'] = $tmpl->id;
                $certificate['layoutTemplate'] = $tmpl->layout_template;
                if (!empty($tmpl->theme)) $certificate['theme'] = $tmpl->theme;
                if (!empty($tmpl->styling_json)) $certificate['styling'] = $tmpl->styling_json;
            }
        }

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView($this->view . 'template', [
                'certificate' => $certificate,
                'isPreview' => false,
                'autoPrint' => false,
            ])
                ->setPaper('a4', 'landscape')
                ->setOptions([
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'dpi' => 300,
                ]);

            return $pdf->download("Certificate_{$id}.pdf");
        }

        return view($this->view . 'template', [
            'certificate' => $certificate,
            'isPreview' => false,
            'autoPrint' => false,
        ]);
    }

    /**
     * Public QR verification endpoint.
     */
    public function verify(Request $request, $id)
    {
        $certificate = $this->certificateService->resolveTemplateData($id);
        $isValid = !empty($certificate['recipientName']);

        $verificationData = [
            'status' => 'authenticated',
            'valid' => $isValid,
            'credential_id' => $certificate['credentialId'] ?? $id,
            'recipient_name' => $certificate['recipientName'] ?? 'Unknown Recipient',
            'khmer_recipient_name' => $certificate['khmerRecipientName'] ?? null,
            'course_title' => $certificate['courseOrAchievementTitle'] ?? null,
            'khmer_course_title' => $certificate['khmerCourseOrAchievementTitle'] ?? null,
            'training_hours' => $certificate['digitalHr']['trainingHours'] ?? 0,
            'issuer' => $certificate['issuer']['name'] ?? 'គ្នា​យើង · Knea Yoeng',
            'issuer_khmer' => $certificate['issuer']['khmerName'] ?? 'ស្ថាប័ន គ្នា​យើង',
            'department' => $certificate['digitalHr']['department'] ?? null,
            'compliance_status' => $certificate['digitalHr']['complianceStatus'] ?? 'Verified & Accredited',
            'issue_date' => $certificate['issueDate'] ?? null,
            'khmer_issue_date' => $certificate['khmerIssueDate'] ?? null,
            'tamper_evident_hash' => hash('sha256', $id . ($certificate['recipientName'] ?? '') . 'KY_SECURE_SALT_2026'),
            'verified_at' => now()->toIso8601String(),
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json($verificationData);
        }

        return view($this->view . 'verify', ['verification' => $verificationData, 'certificate' => $certificate]);
    }

    /**
     * Launch CertiCraft Studio visual editor.
     * Serves as the central place to configure & create Layout Models (វិញ្ញាបនបត្រ គំរូ).
     */
    public function studio(Request $request)
    {
        $this->authorizeAccess('certificate_studio');

        $certCtrl = app(\App\Http\Controllers\CertificateController::class);
        $employees = $certCtrl->loadHrEmployees();
        $templates = CertificateTemplate::where('is_active', true)->orderBy('sort_order')->get();
        $selectedEmployee = null;
        $initialCert = null;

        // 1. Template selection via URL (e.g. ?template_id=1 or ?template=TMPL-V1-EXECUTIVE)
        if ($tmplId = ($request->query('template_id') ?? $request->query('template'))) {
            $selectedTemplate = $templates->firstWhere('id', $tmplId) ?? $templates->firstWhere('template_code', $tmplId);
            if ($selectedTemplate) {
                $initialCert = $selectedTemplate->toStudioPayload();
            }
        }

        // 2. Employee selection via URL
        if ($empId = $request->query('employee_id')) {
            $selectedEmployee = collect($employees)->first(function ($emp) use ($empId) {
                return (string) ($emp['employee_code'] ?? $emp['employeeId'] ?? $emp['id'] ?? '') === (string) $empId
                    || (string) ($emp['id'] ?? '') === (string) $empId;
            });

            if ($selectedEmployee) {
                if ($initialCert) {
                    $initialCert['recipientName'] = strtoupper($selectedEmployee['full_name'] ?? $selectedEmployee['name'] ?? 'Employee');
                    $initialCert['khmerRecipientName'] = $selectedEmployee['khmer_name'] ?? ($selectedEmployee['name'] ?? '');
                    $initialCert['recipientTitle'] = ($selectedEmployee['position'] ?? 'Staff Member') . ' · ' . ($selectedEmployee['branch'] ?? 'គ្នាយើង');
                    $initialCert['khmerRecipientTitle'] = $selectedEmployee['position'] ?? 'បុគ្គលិកឆ្នើម';
                    $initialCert['recipientPhotoUrl'] = $selectedEmployee['profile_photo'] ?? null;
                    if (isset($initialCert['digitalHr'])) {
                        $initialCert['digitalHr']['employeeId'] = $selectedEmployee['employee_code'] ?? $selectedEmployee['employeeId'] ?? '';
                        $initialCert['digitalHr']['department'] = $selectedEmployee['department'] ?? 'Store Operations';
                    }
                } else {
                    $initialCert = $certCtrl->buildCertificateForEmployee($selectedEmployee);
                }
            }
        }

        if (!$initialCert && ($selectedCertId = $request->query('id'))) {
            $initialCert = $this->certificateService->resolveTemplateData($selectedCertId);
        }

        // 3. Default to default layout model from database
        if (!$initialCert) {
            $defaultTmpl = $templates->firstWhere('is_default', true) ?? $templates->first();
            if ($defaultTmpl) {
                $initialCert = $defaultTmpl->toStudioPayload();
            }
        }

        if (!$initialCert) {
            $latestCert = Certificate::latest('id')->first();
            if ($latestCert) {
                $initialCert = $latestCert->toStudioPayload();
            } else {
                $initialCert = $this->certificateService->resolveTemplateData('KY-LDR-2026-001');
            }
        }

        return view($this->view . 'studio', [
            'initialCert' => $initialCert,
            'selectedEmployee' => $selectedEmployee,
            'employees' => $employees,
            'templates' => $templates,
        ]);
    }

    /**
     * Batch Issuance Form.
     */
    public function batchCreate()
    {
        $this->authorizeAccess('certificate_create');

        $branches = Branch::select('id', 'name')->get();
        $departments = Department::select('id', 'dept_name', 'branch_id')->get();
        $users = User::select('id', 'name', 'english_name', 'employee_code', 'department_id', 'branch_id')
            ->where('status', 'verified')
            ->orWhereNull('status')
            ->orderBy('name')
            ->get();

        $templates = CertificateTemplate::where('is_active', true)->orderBy('sort_order')->get();
        $selectedTemplate = $templates->firstWhere('is_default', true) ?? $templates->first();

        return view($this->view . 'batch', compact('branches', 'departments', 'users', 'templates', 'selectedTemplate'));
    }

    /**
     * Store Batch Certificates.
     */
    public function batchStore(Request $request)
    {
        $this->authorizeAccess('certificate_create');

        $validated = $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'template_id' => 'nullable|integer',
            'layout_template' => 'nullable|string|max:100',
            'category' => 'required|in:leader,staff,service,course,custom',
            'certificate_title' => 'required|string|max:255',
            'khmer_title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string',
            'khmer_subtitle' => 'nullable|string',
            'course_or_achievement_title' => 'required|string|max:255',
            'khmer_course_or_achievement_title' => 'nullable|string|max:255',
            'citation' => 'nullable|string',
            'khmer_citation' => 'nullable|string',
            'training_hours' => 'nullable|numeric|min:0',
            'hr_competency_track' => 'nullable|string',
            'compliance_status' => 'nullable|string',
            'issue_date' => 'required|string',
            'khmer_issue_date' => 'nullable|string',
            'organization_sub_banner' => 'nullable|string|max:255',
            'show_organization_sub_banner' => 'nullable|boolean',
            'hanging_medal_style' => 'nullable|string|max:50',
            'seal_enabled' => 'nullable|boolean',
            'seal_style' => 'nullable|string|max:50',
            'seal_ribbon_color' => 'nullable|string|max:30',
            'show_digital_hours_badge' => 'nullable|boolean',
            'show_storefront_watermark' => 'nullable|boolean',
            'show_watermark' => 'nullable|boolean',
            'show_qr_code' => 'nullable|boolean',
            'sig1_name' => 'nullable|string|max:190',
            'sig1_khmer_name' => 'nullable|string|max:190',
            'sig1_role' => 'nullable|string|max:190',
            'sig1_khmer_role' => 'nullable|string|max:190',
            'sig1_organization' => 'nullable|string|max:190',
            'sig1_style' => 'nullable|string|max:50',
            'sig2_name' => 'nullable|string|max:190',
            'sig2_khmer_name' => 'nullable|string|max:190',
            'sig2_role' => 'nullable|string|max:190',
            'sig2_khmer_role' => 'nullable|string|max:190',
            'sig2_organization' => 'nullable|string|max:190',
            'sig2_style' => 'nullable|string|max:50',
        ]);

        try {
            DB::beginTransaction();

            $employeeIds = $validated['employee_ids'];
            unset($validated['employee_ids']);
            $validated['created_by'] = Auth::id();

            $created = $this->certificateService->batchIssueCertificates($employeeIds, $validated);

            DB::commit();

            $credentialIds = collect($created)->pluck('credential_id')->filter()->values()->all();

            return redirect()->route('admin.certificates.index')
                ->with('success', 'Successfully batch issued ' . count($created) . ' certificates.')
                ->with('batch_issued_ids', $credentialIds);
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('danger', 'Error batch issuing certificates: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to get branch wise department & employee data for dependent select boxes.
     */
    public function getBranchCertificateData($branchId): JsonResponse
    {
        try {
            $departments = Department::where('branch_id', $branchId)->select('id', 'dept_name')->get();
            $employees = User::where('branch_id', $branchId)
                ->where(function ($q) {
                    $q->where('status', 'verified')->orWhereNull('status');
                })
                ->select('id', 'name', 'english_name', 'employee_code', 'department_id')
                ->get();

            return response()->json([
                'status' => true,
                'departments' => $departments,
                'employees' => $employees,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Safe authorization helper.
     */
    private function authorizeAccess(string $permission): void
    {
        if (Auth::guard('admin')->check()) {
            return;
        }

        $user = Auth::user();
        if ($user && ($user->role_id === 1 || $user->can($permission))) {
            return;
        }

        if ($user && method_exists($user, 'hasPermissionTo')) {
            $this->authorize($permission);
        }
    }
}
