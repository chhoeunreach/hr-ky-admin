<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Repositories\CompanyRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = 'admin/dashboard/';

    private UserRepository $userRepo;
    private CompanyRepository $companyRepo;

    public function __construct(UserRepository $userRepo,CompanyRepository $companyRepo)
    {
        $this->middleware('guest')->except('logout');
        $this->userRepo = $userRepo;
        $this->companyRepo = $companyRepo;
    }

    public function showAdminLoginForm(): View|Factory|Application|RedirectResponse
    {
        $select = ['logo','name'];
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        } else {
            $companyDetail = $this->companyRepo->getCompanyDetail($select);
            return view('auth.login',compact('companyDetail'));
        }
    }

    public function login(Request $request)
    {
        try {
            $this->validateLogin($request);

            $this->checkCredential($request);

            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
                $this->sendLockoutResponse($request);
            }
            if ($this->attemptLogin($request)) {
              return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
        } catch (Exception $e) {

            return redirect()->back()->with('danger', $e->getMessage())->withInput();
        }
    }

    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }

    public function username()
    {
        return 'email';
    }

    public function checkCredential($request)
    {
        $user = '';
        $select = ['id', 'name', 'email', 'username', 'password'];

        $userWithUserEmail = $this->userRepo->getUserByUserEmail($request->get('email'), $select);
        if ($userWithUserEmail) {
            $user = $userWithUserEmail;
            $request['login_type'] = 'email';
        }

        $userWithUserName = $this->userRepo->getUserByUserName($request->get('email'), $select);

        if ($userWithUserName) {
            $user = $userWithUserName;
            $request['login_type'] = 'username';
            $request['username'] = $request->get('email');
        }

        if (!$user) {
            return redirect()->back()->withInput()->withErrors(['username' => __('auth.username_not_found')]);
        }

        if (!Hash::check($request->get('password'), $user->password)) {
            return redirect()->back()->withInput()->withErrors(['password' => __('auth.invalid_credentials')]);
        }

    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->boolean('remember')
        );
    }

    protected function credentials(Request $request)
    {
        return $request->only($request->login_type, 'password');
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectTo);
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(route('admin.login'));
    }

}

