<?php

namespace App\Policies;

use App\Models\Account;
use Illuminate\Auth\Access\HandlesAuthorization;

class AccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the account.
     */
    public function view(Account $user, Account $account): bool
    {
        // Admin hoặc chính chủ
        return $user->id === $account->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the account.
     */
    public function update(Account $user, Account $account): bool
    {
        // Admin hoặc chính chủ
        return $user->id === $account->id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the account.
     */
    public function delete(Account $user, Account $account): bool
    {
        // Chỉ admin được phép, không cho phép admin tự xóa mình
        return $user->isAdmin() && $user->id !== $account->id;
    }

    /**
     * Determine whether the user can view any accounts (index).
     */
    public function viewAny(Account $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create an account.
     */
    public function create(Account $user): bool
    {
        return $user->isAdmin();
    }
}
