<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EmailUpdateRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Utils\ErrorType;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController extends Controller
{
    const NAME = 'name';
    const EMAIL = 'email';
    const PASSWORD = 'password';

    public function user()
    {
        $user = Auth::user();

        return jsend_success(new UserResource($user), JsonResponse::HTTP_OK);
    }

    public function login(LoginUserRequest $request)
    {
        $email = $request->input(self::EMAIL);
        $password = $request->input(self::PASSWORD);

        try {
            $user = User::where('email', '=', $email)->first();

            if (is_null($user)) {
                return jsend_fail(['message' => 'User does not exists.'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            if (!Auth::guard('user')->attempt(['email' => $email, 'password' => $password])) {
                return jsend_fail(['message' => 'Invalid Credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
            }

            config(['auth.guards.api.provider' => 'user']);
            $user = Auth::guard('user')->user();

            $tokenResult = $user->createToken('IO Token', ['user']);
            $access_token = $tokenResult->accessToken;
            $expiration = $tokenResult->token->expires_at->diffInSeconds(now());

            return jsend_success([
                'name' => $user->name,
                'email' => $user->email,
                'token_type' => 'Bearer',
                'access_token' => $access_token,
                'expires_in' => $expiration
            ], JsonResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Login Failed!', [
                'code' => $e->getCode(),
                'trace' => $e->getTrace(),
            ]);
            return jsend_error(['message' => 'Invalid Credentials']);
        }
    }

    public function register(RegisterUserRequest $request)
    {
        try {
            $user = new User();
            $user->name = $request->input(self::NAME);
            $user->email = $request->input(self::EMAIL);

            $user->password = Hash::make($request->input(self::PASSWORD));

            $user->save();
            return jsend_success(new UserResource($user), JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return jsend_error(__('api.saved-failed', ['model' => 'User']), $e->getCode(), ErrorType::SAVE_ERROR, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UpdateUserRequest $request, User $user)
    {

        try {
            $user->name = $request->input(self::NAME);

            $user->save();

            return jsend_success(new UserResource($user), JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return jsend_error(__('api.updated-failed', ['model' => 'User']), $e->getCode(), ErrorType::UPDATE_ERROR, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function emailUpdate(EmailUpdateRequest $request, User $user)
    {

        try {
            $user->email = $request->input(self::EMAIL);

            $user->save();

            return jsend_success(new UserResource($user), JsonResponse::HTTP_CREATED);
        } catch (Exception $e) {
            return jsend_error(__('api.updated-failed', ['model' => 'User']), $e->getCode(), ErrorType::UPDATE_ERROR, JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return jsend_success(['message' => 'Successfully Logout.'], JsonResponse::HTTP_ACCEPTED);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $current_password = $request->get('current_password');
        $new_password = $request->get('new_password');

        // Auth User
        $user = Auth::user();

        if (!(Hash::check($current_password, $user->password))) {
            // The passwords matches
            return jsend_fail(['message' => 'Your current password does not matches with the password.']);
        }

        if (strcmp($current_password, $new_password) == 0) {
            // Current password and new password same
            return jsend_fail(['message' => 'New Password cannot be same as your current password.']);
        }

        //Change Password
        $user->password = Hash::make($new_password);
        $user->plain_password = '';
        $user->save();

        return jsend_success(['message' => 'Password successfully changed!'], JsonResponse::HTTP_CREATED);
    }

    public function show(User $user)
    {
        return jsend_success(new UserResource($user), JsonResponse::HTTP_OK);
    }

    public function destroy(User $user)
    {

        try {
            $user->delete();

            return jsend_success(null, JsonResponse::HTTP_NO_CONTENT);
        } catch (ModelNotFoundException $exception) {
            return jsend_error(["error" => 'Data Not Found.'], JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
