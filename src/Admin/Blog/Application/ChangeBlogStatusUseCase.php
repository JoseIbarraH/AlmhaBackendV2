<?php

declare(strict_types=1);

namespace Src\Admin\Blog\Application;

use RuntimeException;
use Src\Admin\Blog\Domain\Contracts\BlogRepositoryContract;
use Src\Admin\Blog\Infrastructure\Jobs\NotifyBlogPublishedJob;

final class ChangeBlogStatusUseCase
{
    private BlogRepositoryContract $repository;

    public function __construct(BlogRepositoryContract $repository)
    {
        $this->repository = $repository;
    }

    public function execute(int $blogId, string $newStatus, bool $notifySubscribers = false): void
    {
        $blog = $this->repository->findById($blogId);

        if (!$blog) {
            throw new RuntimeException("Blog no encontrado con el ID: $blogId");
        }

        $shouldNotify = $notifySubscribers
            && $newStatus === 'published'
            && $blog->notificationSentAt() === null;

        $blog->changeStatus($newStatus);
        if ($shouldNotify) {
            $blog->markNotificationSent();
        }

        $this->repository->update($blog);

        if ($shouldNotify) {
            NotifyBlogPublishedJob::dispatch($blogId);
        }
    }
}
