<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Infrastructure;

use Src\Admin\Audit\Domain\Audit;
use Src\Admin\Audit\Domain\Contracts\AuditRepositoryContract;

final class EloquentAuditRepository implements AuditRepositoryContract
{
    private AuditEloquentModel $model;

    public function __construct(AuditEloquentModel $model)
    {
        $this->model = $model;
    }

    public function save(Audit $audit): void
    {
        $this->model->create([
            'user_id' => $audit->userId(),
            'action' => $audit->action(),
            'method' => $audit->method(),
            'url' => $audit->url(),
            'payload' => $audit->payload(),
            'response_status' => $audit->responseStatus(),
            'ip_address' => $audit->ipAddress(),
            'user_agent' => $audit->userAgent(),
        ]);
    }

    public function getAll(): array
    {
        return $this->model->all()->toArray();
    }
}
