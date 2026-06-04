<?php

namespace Pterodactyl\Http\Requests\Admin\Arix;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class ArixStylingRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'arix:backgroundImage' => 'nullable|string',
            'arix:backgroundImageLight' => 'nullable|string',
            'arix:backdrop' => 'required|in:true,false',
            'arix:backdropPercentage' => 'required|string',
            'arix:defaultMode' => 'required|string',
            'arix:copyright' => 'required|string',
            'arix:radiusInput' => 'required|string',
            'arix:borderInput' => 'required|in:true,false',
            'arix:radiusBox' => 'required|string',
            'arix:flashMessage' => 'required|numeric',
            'arix:pageTitle' => 'required|string',
            'arix:loginBackground' => 'nullable|string',
            'arix:loginGradient' => 'required|in:true,false',
        ];
    }
}