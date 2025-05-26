<?php

namespace App\Services;

use App\Models\Account;
use App\Traits\Loggable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserService
{
    use Loggable;

    /**
     * Get all users with their profiles
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsers()
    {
        try {
            return Account::with(['profile', 'role'])->get();
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'getAllUsers']);
            throw $e;
        }
    }

    /**
     * Get specific user data
     *
     * @param Account $account
     * @return array
     */
    public function getUser(Account $account)
    {
        try {
            return [
                'account' => $account,
                'profile' => $account->profile
            ];
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'getUser', 'user_id' => $account->id]);
            throw $e;
        }
    }

    /**
     * Update user information
     *
     * @param Account $account
     * @param array $data
     * @return array
     */
    public function updateUser(Account $account, array $data)
    {
        try {
            Log::info('Updating user', [
                'action' => 'updateUser',
                'user_id' => $account->id,
                'requester_id' => Auth::id(),
                'input_data' => $data
            ]);
            /** @var Account $currentUser */
            $currentUser = Auth::user();

            // Load role relationship if not loaded
            if ($currentUser && !$currentUser->relationLoaded('role')) {
                $currentUser->load('role');
            }

            // Kiểm tra quyền: chỉ admin hoặc chính user đó mới được cập nhật
            if (!$currentUser?->isAdmin() && Auth::id() !== $account->id) {
                $this->logError(new ValidationException(null, null, [
                    'permission' => ['You do not have permission to update this user.']
                ]), [
                    'action' => 'updateUser',
                    'user_id' => $account->id,
                    'requester_id' => Auth::id()
                ]);
                throw ValidationException::withMessages([
                    'permission' => ['You do not have permission to update this user.'],
                ]);
            }

            // Cập nhật thông tin account
            $account->update([
                'email' => $data['email'] ?? $account->email,
                'role' => $data['role'] ?? $account->role,
            ]);

            // Cập nhật thông tin profile
            if ($account->profile) {
                $account->profile->update([
                    'name' => $data['name'] ?? $account->profile->name,
                    'phone' => $data['phone'] ?? $account->profile->phone,
                    'address' => $data['address'] ?? $account->profile->address,
                    'city' => $data['city'] ?? $account->profile->city,
                    'district' => $data['district'] ?? $account->profile->district,
                    'ward' => $data['ward'] ?? $account->profile->ward,
                    'date_of_birth' => $data['date_of_birth'] ?? $account->profile->date_of_birth,
                    'gender' => $data['gender'] ?? $account->profile->gender,
                    'avatar' => $data['avatar'] ?? $account->profile->avatar,
                ]);
            } else {
                // Tạo profile mới nếu chưa có
                $account->profile()->create([
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'ward' => $data['ward'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                ]);
            }

            // Refresh relationship để lấy dữ liệu mới nhất
            $account->refresh();
            $account->loadMissing('profile');

            return [
                'account' => $account,
                'profile' => $account->profile
            ];
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'updateUser',
                'user_id' => $account->id,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete user
     *
     * @param Account $account
     * @return void
     */
    public function deleteUser(Account $account)
    {
        try {
            /** @var Account $currentUser */
            $currentUser = Auth::user();

            // Load role relationship if not loaded
            if ($currentUser && !$currentUser->relationLoaded('role')) {
                $currentUser->load('role');
            }

            // Kiểm tra quyền: chỉ admin hoặc chính user đó mới được xóa
            if (!$currentUser?->isAdmin() && Auth::id() !== $account->id) {
                throw ValidationException::withMessages([
                    'permission' => ['You do not have permission to delete this user.'],
                ]);
            }

            $account->delete();
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'deleteUser', 'user_id' => $account->id]);
            throw $e;
        }
    }

    /**
     * Get current authenticated user profile
     *
     * @return array
     */
    public function getCurrentUserProfile()
    {
        try {
            /** @var Account $account */
            $account = Auth::user();
            if (!$account) {
                throw new \Exception('User not found');
            }

            // Load profile relationship if not already loaded
            if (!$account->relationLoaded('profile')) {
                $account->loadMissing('profile');
            }

            return [
                'account' => $account,
                'profile' => $account->profile
            ];
        } catch (\Exception $e) {
            $this->logError($e, ['action' => 'getCurrentUserProfile', 'user_id' => Auth::id()]);
            throw $e;
        }
    }

    /**
     * Create new user account
     *
     * @param array $data
     * @return array
     */
    public function createUser(array $data)
    {
        try {
            /** @var Account $currentUser */
            $currentUser = Auth::user();

            // Load role relationship if not loaded
            if ($currentUser && !$currentUser->relationLoaded('role')) {
                $currentUser->load('role');
            }

            // Chỉ admin mới được tạo user mới
            if (!$currentUser?->isAdmin()) {
                throw ValidationException::withMessages([
                    'permission' => ['You do not have permission to create users.'],
                ]);
            }

            $account = Account::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
                'role' => $data['role'] ?? 'user',
            ]);

            // Tạo profile nếu có dữ liệu
            if (isset($data['phone']) || isset($data['address']) || isset($data['city'])) {
                $account->profile()->create([
                    'phone' => $data['phone'] ?? null,
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'district' => $data['district'] ?? null,
                    'ward' => $data['ward'] ?? null,
                    'date_of_birth' => $data['date_of_birth'] ?? null,
                    'gender' => $data['gender'] ?? null,
                    'avatar' => $data['avatar'] ?? null,
                ]);
            }

            $account->loadMissing('profile');

            return [
                'account' => $account,
                'profile' => $account->profile
            ];
        } catch (\Exception $e) {
            $this->logError($e, [
                'action' => 'createUser',
                'data' => $data
            ]);
            throw $e;
        }
    }


}
