<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

/**
 * Automatically records an AuditLog row for every create/update/delete made
 * by an authenticated admin — satisfies the "audit logs" security
 * requirement without scattering manual logging calls through every admin
 * controller. Customer-initiated changes (no admin guard session) are not
 * logged here, since those already flow through validated, policy-checked
 * API endpoints.
 */
trait LogsAuditActivity
{
    private const array AUDIT_EXCLUDED_KEYS = ['password', 'remember_token'];

    public static function bootLogsAuditActivity(): void
    {
        static::created(function ($model) {
            static::recordAudit('created', $model, [], $model->getAttributes());
        });

        static::updated(function ($model) {
            if (empty($model->getChanges())) {
                return;
            }
            static::recordAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::recordAudit('deleted', $model, $model->getOriginal(), []);
        });
    }

    protected static function recordAudit(string $action, $model, array $old, array $new): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin) {
            return;
        }

        AuditLog::record(
            $admin,
            "{$action}:".class_basename($model),
            $model,
            collect($old)->except(self::AUDIT_EXCLUDED_KEYS)->all(),
            collect($new)->except(self::AUDIT_EXCLUDED_KEYS)->all(),
        );
    }
}
