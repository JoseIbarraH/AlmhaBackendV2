<?php

declare(strict_types=1);

namespace Src\Landing\Home\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Src\Landing\Navbar\Infrastructure\Support\DesignItemPresenter;
use Src\Shared\Infrastructure\Cache\ClientCache;
use Src\Shared\Infrastructure\Http\ClientResponse;
use Src\Shared\Infrastructure\Http\ResolvesLanguage;

final class GetHomeDataController
{
    use ResolvesLanguage;

    public function __invoke(Request $request): JsonResponse
    {
        $lang = $this->resolveLang($request);

        $payload = ClientCache::remember(
            'home',
            "home:{$lang}",
            ClientCache::TTL_LONG,
            fn () => $this->buildPayload($lang)
        );

        return ClientResponse::success($payload, '', 200, maxAgeSeconds: 600);
    }

    private function buildPayload(string $lang): array
    {
        return [
            'backgrounds' => [
                'background1'        => DesignItemPresenter::itemsFor('background_1', $lang),
                'background1Setting' => DesignItemPresenter::settingsFor('background_1'),
                'background2'        => DesignItemPresenter::itemsFor('background_2', $lang),
                'background2Setting' => DesignItemPresenter::settingsFor('background_2'),
                'background3'        => DesignItemPresenter::itemsFor('background_3', $lang),
                'background3Setting' => DesignItemPresenter::settingsFor('background_3'),
            ],
            'carousel' => [
                'carousel'        => DesignItemPresenter::itemsFor('main_banner', $lang),
                'carouselSetting' => DesignItemPresenter::settingsFor('main_banner'),
            ],
            'carouselTool' => [
                'carouselTool'        => DesignItemPresenter::itemsFor('brands_carousel', $lang),
                'carouselToolSetting' => DesignItemPresenter::settingsFor('brands_carousel'),
            ],
            'imageVideo' => [
                'imageVideo'        => DesignItemPresenter::itemsFor('image_video', $lang),
                'imageVideoSetting' => DesignItemPresenter::settingsFor('image_video'),
            ],
            'treatments' => [],
        ];
    }
}
