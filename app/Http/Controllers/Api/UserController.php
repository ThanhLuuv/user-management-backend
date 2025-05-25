<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\UserService;
use App\Traits\ValidationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class UserController extends Controller
{
    use ValidationTrait;

    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $users = $this->userService->getAllUsers();
            return response()->json([
                'status' => 'success',
                'message' => 'Users retrieved successfully',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Account $account)
    {
        try {
            $this->authorize('view', $account);

            $data = $this->userService->getUser($account);
            return response()->json([
                'status' => 'success',
                'message' => 'User retrieved successfully',
                'data' => $data
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        try {
            /** @var Account|null $user */
            $user = Auth::user();

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:accounts,email',
                'password' => 'required|string|min:6|confirmed',
                'phone' => 'nullable|string|max:20|unique:profiles,phone',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'ward' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'avatar' => 'nullable|url|max:255',
            ];

            if ($user && $user->isAdmin()) {
                $rules['role'] = 'sometimes|in:admin,user';
            }

            $validatedData = $request->validate($rules);

            $data = $this->userService->createUser($validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $data
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, Account $account)
    {
        try {
            $this->authorize('update', $account);

            /** @var Account|null $user */
            $user = Auth::user();

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'ward' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'avatar' => 'nullable|url|max:255',
            ];

            if ($user && $user->isAdmin()) {
                $rules['role'] = 'sometimes|in:admin,user';
            }

            $validatedData = $request->validate($rules);

            // Additional email/phone uniqueness checks here if needed
            if ($this->checkEmailExists($request->email, $account->id)) {
                throw ValidationException::withMessages([
                    'email' => ['Email already exists']
                ]);
            }

            if ($request->has('phone') && $request->phone) {
                if ($this->checkPhoneExists($request->phone, $account->id)) {
                    throw ValidationException::withMessages([
                        'phone' => ['Phone number already exists']
                    ]);
                }
            }

            $data = $this->userService->updateUser($account, $validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $data
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied'
            ], 403);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Account $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Account $account)
    {
        try {
            $this->authorize('delete', $account);

            $this->userService->deleteUser($account);

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ], 200);
        } catch (AuthorizationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Permission denied'
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        try {
            $data = $this->userService->getCurrentUserProfile();

            return response()->json([
                'status' => 'success',
                'message' => 'Profile retrieved successfully',
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function updateProfile(Request $request)
    {
        try {
            /** @var Account|null $account */
            $account = Auth::user();

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string',
                'city' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'ward' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|in:male,female,other',
                'avatar' => 'nullable|url|max:255',
            ];

            if ($account && $account->isAdmin()) {
                $rules['role'] = 'sometimes|in:admin,user';
            }

            $validatedData = $request->validate($rules);

            // Same uniqueness checks for email and phone if necessary
            if ($this->checkEmailExists($request->email, $account->id)) {
                throw ValidationException::withMessages([
                    'email' => ['Email already exists']
                ]);
            }

            if ($request->has('phone') && $request->phone) {
                if ($this->checkPhoneExists($request->phone, $account->id)) {
                    throw ValidationException::withMessages([
                        'phone' => ['Phone number already exists']
                    ]);
                }
            }

            $data = $this->userService->updateUser($account, $validatedData);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $data
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Your existing helper validation methods (checkEmailExists, checkPhoneExists, etc.) here

    private function checkEmailExists(string $email, int $excludeUserId = null): bool
    {
        $query = Account::where('email', $email);
        if ($excludeUserId) {
            $query->where('id', '<>', $excludeUserId);
        }
        return $query->exists();
    }

    private function checkPhoneExists(string $phone, int $excludeUserId = null): bool
    {
        $query = \App\Models\UserProfile::where('phone', $phone);
        if ($excludeUserId) {
            $query->where('account_id', '<>', $excludeUserId);
        }
        return $query->exists();
    }
}
