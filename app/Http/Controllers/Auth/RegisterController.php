<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Auth\PasswordBroker;
use Pterodactyl\Models\User;
use Pterodactyl\Notifications\AccountCreated;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Http\Requests\Auth\RegisterRequest;

class RegisterController extends AbstractLoginController
{
    public function __construct(private UserCreationService $creationService, private PasswordBroker $passwordBroker)
    {
        parent::__construct();
    }

    public function store(RegisterRequest $request): JsonResponse
    {
        abort_unless(config('arix.registration.enabled'), 404);

        $user = $this->creationService->handle($request->validatedForCreation(), true);

        return response()->json([
            'success' => true,
            'email' => $user->email,
            'redirect_to' => '/auth/register/confirmation',
        ]);
    }

    public function resend(Request $request): JsonResponse
    {
        abort_unless(config('arix.registration.enabled'), 404);

        $data = $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::query()->where('email', strtolower($data['email']))->first();

        if ($user && $user->password_setup_pending) {
            $token = $this->passwordBroker->createToken($user);
            $user->notify(new AccountCreated($user, $token));
        }

        return response()->json([
            'success' => true,
            'message' => 'If that account can receive a setup email, it has been sent.',
        ]);
    }
}
