<?php

namespace Pterodactyl\Http\Requests\Admin\Arix;

use Pterodactyl\Http\Requests\Admin\AdminFormRequest;

class ArixAnnouncementRequest extends AdminFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'arix:announcementType' => 'required|string|in:disabled,update,info,success,alert,warning,party',
            'arix:announcementMessage' => 'nullable|string',
            'arix:announcementCloseable' => 'required|in:true,false',
        ];
    }
}