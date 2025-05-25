<?php

namespace App\Traits;

use App\Models\Account;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;

trait ValidationTrait
{
    /**
     * Check if email exists
     * 
     * @param string $email
     * @param int|null $excludeAccountId
     * @return JsonResponse|null
     */
    protected function checkEmailExists(string $email, ?int $excludeAccountId = null): ?JsonResponse
    {
        $query = Account::where('email', $email);
        
        if ($excludeAccountId) {
            $query->where('id', '!=', $excludeAccountId);
        }

        if ($query->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This email is already in use by another account.'
            ], 400);
        }

        return null;
    }

    /**
     * Check if phone number exists
     * 
     * @param string $phone
     * @param int|null $excludeAccountId
     * @return JsonResponse|null
     */
    protected function checkPhoneExists(string $phone, ?int $excludeAccountId = null): ?JsonResponse
    {
        $query = UserProfile::where('phone', $phone);
        
        if ($excludeAccountId) {
            $query->where('account_id', '!=', $excludeAccountId);
        }

        if ($query->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This phone number is already in use by another account.'
            ], 400);
        }

        return null;
    }
} 