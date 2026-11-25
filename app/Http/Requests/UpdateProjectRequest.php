<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends StoreProjectRequest
{
    /**
     * Same rules as creating, but the code may stay the same.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9_]+$/', Rule::unique('projects', 'code')->ignore($this->route('project'))],
        ];
    }
}
