<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Role;
use App\Models\UserProfile;
use App\Traits\Loggable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    use Loggable;

    public function register(array $data): array
    {
        DB::beginTransaction();

        try {
            // if (Account::where('email', $data['email'])->exists()) {
            //     throw ValidationException::withMessages([
            //         'email' => ['This email is already registered.']
            //     ]);
            // }

            if (!empty($data['phone']) && UserProfile::where('phone', $data['phone'])->exists()) {
                throw ValidationException::withMessages([
                    'phone' => ['This phone number is already registered.']
                ]);
            }

            $role = Role::firstOrCreate([
                'name' => 'user'
            ], [
                'description' => 'Default user role'
            ]);

            $account = Account::create([
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role_id' => $role->id,
                'email_verified_at' => null,
                'is_active' => true
            ]);

            $profile = UserProfile::create([
                'account_id' => $account->id,
                'name' => $data['name'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'district' => $data['district'] ?? null,
                'ward' => $data['ward'] ?? null,
                'avatar' => $data['avatar'] ?? null,
            ]);

            $account->load(['profile', 'role']);

            $token = JWTAuth::fromUser($account);

            DB::commit();

            return [
                'account' => $account,
                'profile' => $profile,
                'role' => $account->role,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ];
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Registration failed. Please try again.');
        }
    }

    public function login(array $credentials): array
    {
        try {
            $account = Account::where('email', $credentials['email'])->first();

            if (!$account || !Hash::check($credentials['password'], $account->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'],
                ]);
            }

            // if (!$account->is_active) {
            //     throw ValidationException::withMessages([
            //         'email' => ['Account is inactive. Contact admin.'],
            //     ]);
            // }

            $token = JWTAuth::attempt($credentials);

            if (!$token) {
                throw ValidationException::withMessages([
                    'email' => ['Login failed.'],
                ]);
            }

            $account->update(['last_login_at' => now()]);
            $account->load(['profile', 'role']);

            return [
                'account' => $account,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new \Exception('Login failed. Please try again.');
        }
    }

    public function logout(): void
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (\Exception $e) {
            throw new \Exception('Logout failed.');
        }
    }

    public function refresh(): array
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());

            return [
                'token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60
            ];
        } catch (\Exception $e) {
            throw new \Exception('Token refresh failed.');
        }
    }

    public function getCurrentUser(): array
    {
        try {
            $account = JWTAuth::parseToken()->authenticate();

            if (!$account) {
                throw new \Exception('User not found');
            }

            $account->loadMissing(['profile', 'role']);

            return [
                'account' => $account,
                'profile' => $account->profile,
                'role' => $account->role
            ];
        } catch (\Exception $e) {
            throw new \Exception('Could not retrieve user.');
        }
    }

    public function changePassword(array $data): bool
    {
        $account = JWTAuth::parseToken()->authenticate();

        if (!Hash::check($data['current_password'], $account->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.']
            ]);
        }

        $account->update([
            'password' => Hash::make($data['new_password'])
        ]);

        return true;
    }

    public function requestPasswordReset(string $email): bool
    {
        $account = Account::where('email', $email)->first();

        if ($account) {
            // TODO: Implement real email reset logic
        }

        return true;
    }
}
