<?php

namespace App\Http\Controllers\Concerns;

use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

trait ManagesLetterNumberForm
{
    protected function letterNumberFormAttributeNames(): array
    {
        return [
            'letter_category_id',
            'letter_date',
            'destination',
            'remarks',
            'project_code',
            'project_id',
            'subject_id',
            'custom_subject',
            'administration_id',
            'classification',
            'duration',
            'start_date',
            'end_date',
            'pkwt_type',
            'par_type',
            'ticket_classification',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function letterNumberPayload(Request $request): array
    {
        $data = $request->only($this->letterNumberFormAttributeNames());

        foreach (['administration_id', 'subject_id'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    /**
     * @return array<int, mixed>
     */
    protected function letterNumberProjectIdRules(): array
    {
        $ids = auth()->user()->projects()->where('project_status', 1)->pluck('projects.id')->all();

        return [
            'required',
            'exists:projects,id',
            Rule::in($ids),
        ];
    }

    protected function userHasAccessToLetterNumberProject(LetterNumber $letterNumber): bool
    {
        return auth()->user()->projects()
            ->where('project_status', 1)
            ->pluck('projects.id')
            ->contains($letterNumber->project_id);
    }
}
