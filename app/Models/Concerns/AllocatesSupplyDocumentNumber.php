<?php

namespace App\Models\Concerns;

trait AllocatesSupplyDocumentNumber
{
    /**
     * @return array{document_sequence: int, document_number: string}
     */
    public static function allocateNumber(int $projectId, string $projectCode): array
    {
        $seq = (int) static::query()
            ->where('project_id', $projectId)
            ->lockForUpdate()
            ->max('document_sequence') + 1;

        return [
            'document_sequence' => $seq,
            'document_number' => static::formatNumber($projectCode, $seq),
        ];
    }

    public static function previewNumber(int $projectId, string $projectCode): string
    {
        $seq = (int) static::query()
            ->where('project_id', $projectId)
            ->max('document_sequence') + 1;

        return static::formatNumber($projectCode, $seq);
    }

    protected static function formatNumber(string $projectCode, int $sequence): string
    {
        return sprintf(
            '%s-%s-%s',
            static::NUMBER_PREFIX,
            $projectCode,
            str_pad((string) $sequence, 4, '0', STR_PAD_LEFT)
        );
    }
}
