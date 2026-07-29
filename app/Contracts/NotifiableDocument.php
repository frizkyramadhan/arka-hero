<?php

namespace App\Contracts;

use App\Models\User;

interface NotifiableDocument
{
    public function notificationDocumentType(): string;

    public function notificationDocumentLabel(): string;

    public function notificationReference(): string;

    public function notificationTitle(): string;

    /**
     * @return array<string, string|null>
     */
    public function notificationSummary(): array;

    public function notificationRequester(): ?User;

    public function notificationActionUrl(): string;
}
