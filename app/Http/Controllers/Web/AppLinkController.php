<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppLink;
use App\Repositories\AppLinkRepository;
use App\Requests\AppLink\AppLinkRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppLinkController extends Controller
{
    private $view = 'admin.appLink.';

    public function __construct(
        protected AppLinkRepository $appLinkRepo
    ) {}

    public function index(Request $request)
    {
        try {
            $filterParameters = [
                'link_type' => $request->link_type ?? null,
                'status' => $request->status ?? null,
                'search' => $request->search ?? null,
            ];

            $links = $this->appLinkRepo->getAllLinksPaginated($filterParameters);
            $linkTypes = AppLink::LINK_TYPES;

            return view($this->view . 'index', compact('links', 'filterParameters', 'linkTypes'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function create()
    {
        try {
            $linkTypes = AppLink::LINK_TYPES;
            return view($this->view . 'create', compact('linkTypes'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function store(AppLinkRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['status'] = $request->has('status') ? 1 : 0;
            $validatedData['order'] = $request->order ?? 0;

            $this->appLinkRepo->store($validatedData);
            return redirect()->route('admin.app-links.index')->with('success', __('message.link_added_successfully') ?? 'Link added successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $linkDetail = $this->appLinkRepo->findById($id);
            if (!$linkDetail) {
                return redirect()->route('admin.app-links.index')->with('danger', 'Link not found.');
            }
            $linkTypes = AppLink::LINK_TYPES;
            return view($this->view . 'edit', compact('linkDetail', 'linkTypes'));
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function update(AppLinkRequest $request, $id)
    {
        try {
            $link = $this->appLinkRepo->findById($id);
            if (!$link) {
                return redirect()->route('admin.app-links.index')->with('danger', 'Link not found.');
            }

            $validatedData = $request->validated();
            $validatedData['status'] = $request->has('status') ? 1 : 0;
            $validatedData['order'] = $request->order ?? 0;

            $this->appLinkRepo->update($link, $validatedData);
            return redirect()->route('admin.app-links.index')->with('success', __('message.link_updated_successfully') ?? 'Link updated successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage())->withInput();
        }
    }

    public function delete($id)
    {
        try {
            $link = $this->appLinkRepo->findById($id);
            if ($link) {
                $this->appLinkRepo->delete($link);
            }
            return redirect()->back()->with('success', __('message.link_deleted_successfully') ?? 'Link deleted successfully.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        try {
            $link = $this->appLinkRepo->findById($id);
            if ($link) {
                $this->appLinkRepo->toggleStatus($link);
                return redirect()->back()->with('success', 'Status updated successfully.');
            }
            return redirect()->back()->with('danger', 'Link not found.');
        } catch (Exception $exception) {
            return redirect()->back()->with('danger', $exception->getMessage());
        }
    }
}
