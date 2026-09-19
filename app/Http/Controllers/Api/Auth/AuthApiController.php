<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Schema;
use App\Repositories\UserRepository;
use App\Requests\User\UserLoginRequest;
use App\Services\Auth\AuthService;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Helpers\AppHelper;

class AuthApiController
{
    private AuthService $authService;
    private UserRepository $userRepo;

    public function __construct(AuthService $authService, UserRepository $userRepo)
    {
        $this->authService = $authService;
        $this->userRepo = $userRepo;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $validatedData = $request->validated();

            $data = $this->authService->checkCredential($validatedData);
            $user = $data['user'];
            $credentials = array(
                $data['credential']['login_type'] => $validatedData['username'],
                'password' => $validatedData['password']
            );

            if (!$this->getAttempt($credentials)) {
                throw new Exception(__('index.invalid_login_credentials'), 401);
            }


            $tokens = $user->createToken($this->deviceTokenName($validatedData))->accessToken;
            $validatedData['id'] = $user->id;
            $this->authService->updateUserLoginDetail($validatedData);

            if (isset($validatedData['latitude'], $validatedData['longitude'])) {
                $this->userRepo->setEmployeeLocation([
                    'employee_id' => $user->id,
                    'latitude' => $validatedData['latitude'],
                    'longitude' => $validatedData['longitude'],
                ]);
            }

            if (Schema::hasTable('user_locations')) {
                $rawUuid = (string) ($validatedData['uuid'] ?? ($user->uuid ?? ''));
                $deviceKey = hash('sha256', $rawUuid);
                $deviceName = $validatedData['device_name'] ?? null;
                if (!$deviceName) {
                    $deviceName = str_contains($rawUuid, ':')
                        ? trim(explode(':', $rawUuid, 2)[0])
                        : ucfirst((string) ($user->device_type ?? 'mobile')) . ' Device';
                }

                $locationData = [
                    'device_type' => $user->device_type,
                    'device_name' => $deviceName,
                ];

                if (isset($validatedData['latitude'], $validatedData['longitude'])) {
                    $locationData['latitude'] = $validatedData['latitude'];
                    $locationData['longitude'] = $validatedData['longitude'];
                    $locationData['accuracy'] = $validatedData['accuracy'] ?? 0;
                }
                if (isset($validatedData['battery_level'])) {
                    $locationData['battery_level'] = $validatedData['battery_level'];
                }

                foreach (['app_name', 'app_version', 'app_build', 'device_model', 'os_version'] as $col) {
                    if (Schema::hasColumn('user_locations', $col) && !empty($validatedData[$col])) {
                        $locationData[$col] = $validatedData[$col];
                    }
                }

                UserLocation::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'device_key' => $deviceKey,
                    ],
                    $locationData
                );
            }

            DB::commit();
            return AppHelper::sendSuccessResponse(
                __('index.authenticated'),
                [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'username' => $user->username,
                        'workspace_type' => $user->workspace_type,
                        'theme_mode' => $user->app_theme_mode ?: User::DEFAULT_THEME_MODE,
                        'avatar' => ($user->avatar) ? asset(User::AVATAR_UPLOAD_PATH.$user->avatar) : asset('assets/images/img.png'),
                    ],
                    'tokens' => $tokens,
                    'app_version' => AppHelper::getAppVersionCheckData($validatedData['app_version'] ?? null),
                ]
            );
        } catch (Exception $e) {
            DB::rollBack();
            if ($e instanceof ValidationException) {
                return AppHelper::sendErrorResponse($e->getMessage(), 422, $e->errors());
            }
            if ($e instanceof GuzzleException) {
                return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
            }
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    protected function getAttempt(array $credentials): bool
    {
        return auth()->attempt($credentials);
    }

    private function deviceTokenName(array $loginData): string
    {
        $platform = strtolower((string) ($loginData['device_type'] ?? 'unknown'));
        $rawUuid = (string) ($loginData['uuid'] ?? '');
        $deviceName = str_contains($rawUuid, ':')
            ? trim(explode(':', $rawUuid, 2)[0])
            : ucfirst($platform) . ' Device';

        return 'device-login:' . json_encode([
            'platform' => $platform,
            'device_name' => Str::limit($deviceName, 100, ''),
            'device_key' => hash('sha256', $rawUuid),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function logout()
    {
        try {
            $userDetail = $this->userRepo->findUserDetailById(getAuthUserCode());
            if(!$userDetail){
                throw new Exception(__('index.user_detail_not_found'),401);
            }

            $isAuthorizeLogin = AppHelper::isAuthorizeLogin();



            DB::beginTransaction();
                $userToken = $this->getToken();
                $update['logout_status'] = User::LOGOUT_STATUS[ $isAuthorizeLogin ? 'pending' : 'approve'];
                $update['uuid'] = null;
                $update['fcm_token'] = null;
                $update['online_status'] = User::OFFLINE;
                $this->userRepo->update($userDetail,$update);
                $userToken->revoke();
            DB::commit();
            return AppHelper::sendSuccessResponse(__('index.partial_logout_successful'));
        } catch (Exception $e) {
            DB::rollBack();
            return AppHelper::sendErrorResponse($e->getMessage(), $e->getCode());
        }
    }

    protected function getToken()
    {
        return Auth::user()->token();
    }


}
