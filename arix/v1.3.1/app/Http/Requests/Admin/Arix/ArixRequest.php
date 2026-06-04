<?php

namespace Pterodactyl\Http\Requests\Admin\Arix;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class ArixRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'arix:logo' => 'required|string|url',
            'arix:fullLogo' => 'required|in:true,false',
            'arix:logoHeight' => 'required|string',
            'arix:discord' => 'nullable|numeric',
            'arix:support' => 'nullable|string|url',
            'arix:status' => 'nullable|string|url',
            'arix:billing' => 'nullable|string|url',
        ];
    }
}