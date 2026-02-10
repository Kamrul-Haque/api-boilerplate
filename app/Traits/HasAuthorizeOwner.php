<?php

namespace App\Traits;

use App\Services\AccessControlService;

/**
 *  Adds Ownership or Permission Check to Model
 */
trait HasAuthorizeOwner
{
    /**
     * Authorize that the current user is either the creator of the model
     * or has the given permission.
     *
     * @param  string|null  $permission  Optional permission name to check
     */
    public function authorizeOwnerOrPermission(bool $withAdditionalCheck = true, ?string $permission = null): void
    {
        $additionalCheck = $withAdditionalCheck && method_exists($this, 'getAdditionalOwnerCheck')
            ? $this->getAdditionalOwnerCheck()
            : null;

        AccessControlService::authorizeOwnerOrPermission($this, $permission, $additionalCheck);
    }
}
