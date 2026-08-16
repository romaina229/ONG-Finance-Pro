<?php

declare(strict_types=1);

namespace App\Services\Sync;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\ConnectionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SyncAuthorization
{
    public function __construct(private readonly ConnectionInterface $db) {}

    public function assertAuthorized(Authenticatable $user, string $organizationId, string $deviceId): void
    {
        $userId = (string) $user->getAuthIdentifier();

        $membership = $this->db->table('user_organizations')
            ->where('user_id', $userId)
            ->where('organization_id', $organizationId)
            ->where('status', 'active')
            ->exists();

        $deviceOwned = $this->db->table('devices')
            ->where('id', $deviceId)
            ->where('user_id', $userId)
            ->exists();

        if (!$membership || !$deviceOwned) {
            throw new AccessDeniedHttpException('User, organization or device is not authorized for synchronization.');
        }
    }
}
