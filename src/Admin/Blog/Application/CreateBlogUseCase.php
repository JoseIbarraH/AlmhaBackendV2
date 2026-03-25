<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Admin\Blog\Domain\Entity\Blog;
use Src\Admin\Blog\Domain\Entity\BlogTranslation;
use Src\Shared\Domain\Contracts\TranslatorServiceContract;
use DateTime;

final class CreateBlogUseCase
{
    private BlogRepositoryContract $repository;
    private TranslatorServiceContract $translator;

    public function __construct(BlogRepositoryContract $repository, TranslatorServiceContract $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    public function execute(
        string $categoryCode,
        string $baseLang,
        string $title,
        ?string $content,
        array $targetLanguages = [],
        string $status = 'draft',
        ?int $userId = null,
        ?string $image = null,
        ?string $writer = null
    ): int
    {
        $translations = [];
        
        // El idioma original se guarda sin traducir
        $translations[] = new BlogTranslation($baseLang, $title, $content);

        // Traducimos al resto de idiomas solicitados usando el contrato de Shared
        foreach ($targetLanguages as $lang) {
            $translatedTitle = $this->translator->translate($title, $lang, $baseLang);
            $translatedContent = $content ? $this->translator->translate($content, $lang, $baseLang) : null;
            
            $translations[] = new BlogTranslation($lang, $translatedTitle, $translatedContent);
        }

        // Ya con todas las traducciones listas (Hijas), creamos el root (Agregado)
        $blog = new Blog(
            $categoryCode,
            $status,
            $userId,
            $image,
            $writer,
            0,
            null,
            null,
            $translations
        );

        // El repositorio se encarga de guardar todo atómicamente en BD
        return $this->repository->save($blog);
    }
}
