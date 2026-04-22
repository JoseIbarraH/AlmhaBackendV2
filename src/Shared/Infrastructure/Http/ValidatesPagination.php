<?php

declare(strict_types=1);

namespace Src\Shared\Infrastructure\Http;

use Illuminate\Http\Request;

trait ValidatesPagination
{
    protected function getPaginationParams(Request $request): array
    {
        $page = max(1, (int) $request->query('page', '1'));
        $perPage = min(100, max(1, (int) $request->query('per_page', '15')));

        return [$page, $perPage];
    }
}
