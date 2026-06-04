<?php

namespace Pterodactyl\Http\Requests\Admin\Arix;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class ArixAdvancedRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'arix:profileType' => 'required|string',
            'arix:modeToggler' => 'required|in:true,false',
            'arix:langSwitch' => 'required|in:true,false',
            'arix:ipFlag' => 'required|in:true,false',
            'arix:lowResourcesAlert' => 'required|in:true,false',
        ];
    }
}