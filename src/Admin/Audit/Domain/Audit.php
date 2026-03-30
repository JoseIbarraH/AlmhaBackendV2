<?php

declare(strict_types=1);

namespace Src\Admin\Audit\Domain;

final class Audit
{
    public function __construct(
        private ?string $id,
        private ?string $userId,
        private ?string $action,
        private string $method,
        private string $url,
        private ?array $payload,
        private ?int $responseStatus,
        private ?string $ipAddress,
        private ?string $userAgent,
        private ?string $createdAt = null
    ) {}

    public function id(): ?string { return $this->id; }
    public function userId(): ?string { return $this->userId; }
    public function action(): ?string { return $this->action; }
    public function method(): string { return $this->method; }
    public function url(): string { return $this->url; }
    public function payload(): ?array { return $this->payload; }
    public function responseStatus(): ?int { return $this->responseStatus; }
    public function ipAddress(): ?string { return $this->ipAddress; }
    public function userAgent(): ?string { return $this->userAgent; }
    public function createdAt(): ?string { return $this->createdAt; }
}
