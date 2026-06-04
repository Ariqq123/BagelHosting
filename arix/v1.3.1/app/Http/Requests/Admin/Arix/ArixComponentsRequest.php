<?php

namespace Pterodactyl\Http\Requests\Admin\Arix;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class ArixComponentsRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'arix:serverRow' => 'required|numeric',
            'arix:socialButtons' => 'required|in:true,false',
            'arix:discordBox' => 'required|in:true,false',

            'arix:statsCards' => 'required|numeric',
            'arix:sideGraphs' => 'required|numeric',
            'arix:graphs' => 'required|numeric',

            'arix:slot1' => 'required|string',
            'arix:slot2' => 'required|string',
            'arix:slot3' => 'required|string',
            'arix:slot4' => 'required|string',
            'arix:slot5' => 'required|string',
            'arix:slot6' => 'required|string',
            'arix:slot7' => 'required|string',
        ];
    }
}