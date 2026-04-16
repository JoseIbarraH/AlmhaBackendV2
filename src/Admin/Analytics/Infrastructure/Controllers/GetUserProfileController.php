<?php

declare(strict_types=1);

namespace Src\Admin\Analytics\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Spatie\Analytics\Period;
use Src\Admin\Analytics\Application\GetUserProfileUseCase;

final class GetUserProfileController
{
    private GetUserProfileUseCase $useCase;

    public function __construct(GetUserProfileUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $period = $this->getPeriod($request);

        return response()->json(
            $this->useCase->execute($period)
        );
    }

    private function getPeriod(Request $request): Period
    {
        if ($request->has('start_date') && $request->has('end_date')) {
            return Period::create(
                Carbon::parse($request->input('start_date')),
                Carbon::parse($request->input('end_date'))
            );
        }

        return Period::days(30);
    }
}
