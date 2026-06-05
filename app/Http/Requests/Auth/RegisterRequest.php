<?php

namespace Pterodactyl\Http\Requests\Auth;

use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Pterodactyl\Models\User;
use Pterodactyl\Services\Users\RegistrationDomainService;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = User::getRules();

        return [
            'email' => $rules['email'],
            'username' => $rules['username'],
            'first_name' => $rules['name_first'],
            'last_name' => $rules['name_last'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!config('arix.registration.enabled')) {
                return;
            }

            $domains = config('arix.registration.allowed_domains', []);
            $service = app(RegistrationDomainService::class);
            $email = strtolower((string) $this->input('email', ''));

            if (!$service->isAllowed($email, $domains)) {
                $validator->errors()->add('email', 'Registration is not available for this email domain.');
            }
        });
    }

    public function validatedForCreation(): array
    {
        return [
            'email' => strtolower((string) $this->validated()['email']),
            'username' => $this->validated()['username'],
            'name_first' => $this->validated()['first_name'],
            'name_last' => $this->validated()['last_name'],
        ];
    }
}
