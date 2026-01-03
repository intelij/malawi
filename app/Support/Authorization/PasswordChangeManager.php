<?php

namespace App\Support\Authorization;

use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Auth\Authenticatable;

class PasswordChangeManager
{
    public function __construct(private readonly CacheManager $cache)
    {
    }

    private function enabled(Authenticatable $user): bool
    {
        return setting('password-change.enabled') && $user->force_password_change;
    }

    public function isPasswordChangeForcedForUser(Authenticatable $user): bool
    {
        return $this->enabled($user) && $this->cache->has($this->cacheKey($user));
    }

    public function forcePasswordChange(Authenticatable $user): bool
    {
        if (!$this->enabled($user)) {
            return false;
        }

        $this->cache->put($this->cacheKey($user), true);

        return true;
    }

    public function liftPasswordChangeRequest(Authenticatable $user): void
    {
        $this->cache->forget($this->cacheKey($user));
    }

    private function cacheKey(Authenticatable $user): string
    {
        return sprintf('user_%s_force_password_change', $user->getAuthIdentifier());
    }
}
