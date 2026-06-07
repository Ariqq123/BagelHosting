# User Registration Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a public Arix-themed registration flow that creates an active panel user, emails a password setup link, and blocks login until setup is completed.

**Architecture:** Extend the existing `/auth` SPA and Laravel auth stack instead of creating a parallel onboarding system. Reuse `UserCreationService`, Laravel password broker tokens, and the existing reset-password screen while adding a durable `password_setup_pending` flag, public registration endpoints, and Arix-native auth components.

**Tech Stack:** Laravel, Eloquent migrations, FormRequest validation, existing auth controllers/services, React 16, React Router v5, Formik, Yup, Jest, PHPUnit 10.

---

## File Map

**Backend create/modify set**

- Create: `database/migrations/2026_06_05_000000_add_password_setup_pending_to_users.php`
- Create: `app/Http/Requests/Auth/RegisterRequest.php`
- Create: `app/Http/Controllers/Auth/RegisterController.php`
- Create: `app/Services/Users/RegistrationDomainService.php`
- Create: `tests/Feature/Auth/RegisterControllerTest.php`
- Modify: `app/Models/User.php`
- Modify: `app/Services/Users/UserCreationService.php`
- Modify: `app/Http/Controllers/Auth/LoginController.php`
- Modify: `app/Http/Controllers/Auth/ResetPasswordController.php`
- Modify: `routes/auth.php`
- Modify: `config/arix.php`

**Frontend create/modify set**

- Create: `resources/scripts/api/auth/register.ts`
- Create: `resources/scripts/api/auth/resendRegistrationEmail.ts`
- Create: `resources/scripts/components/auth/RegisterContainer.tsx`
- Create: `resources/scripts/components/auth/RegisterConfirmationContainer.tsx`
- Create: `resources/scripts/components/auth/RegisterConfirmationContainer.test.tsx`
- Modify: `resources/scripts/routers/AuthenticationRouter.tsx`
- Modify: `resources/scripts/components/auth/LoginContainer.tsx`
- Modify: `resources/scripts/components/auth/ResetPasswordContainer.tsx`

### Task 1: Add durable pending-password state

**Files:**
- Create: `database/migrations/2026_06_05_000000_add_password_setup_pending_to_users.php`
- Modify: `app/Models/User.php`
- Test: `tests/Feature/Auth/RegisterControllerTest.php`

- [ ] **Step 1: Write the failing migration/model test**

Create `tests/Feature/Auth/RegisterControllerTest.php` with a first test that expects a new user created through registration to have the pending flag set.

```php
<?php

namespace Pterodactyl\Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use Pterodactyl\Models\User;

class RegisterControllerTest extends TestCase
{
    public function test_registration_creates_pending_password_user(): void
    {
        Notification::fake();

        config()->set('arix.registration.enabled', true);
        config()->set('arix.registration.allowed_domains', ['example.com']);

        $response = $this->post('/auth/register', [
            'email' => 'new.user@example.com',
            'username' => 'newuser',
            'first_name' => 'New',
            'last_name' => 'User',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', [
            'email' => 'new.user@example.com',
            'username' => 'newuser',
            'password_setup_pending' => true,
        ]);
    }
}
```

- [ ] **Step 2: Run the focused test to verify it fails**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter=test_registration_creates_pending_password_user`
Expected: FAIL because route and column do not exist yet.

- [ ] **Step 3: Add migration**

Create `database/migrations/2026_06_05_000000_add_password_setup_pending_to_users.php`.

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('password_setup_pending')->default(false)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_setup_pending');
        });
    }
};
```

- [ ] **Step 4: Update `User` model fillable, casts, and defaults**

Modify `app/Models/User.php`.

```php
protected $fillable = [
    'external_id',
    'username',
    'email',
    'name_first',
    'name_last',
    'password',
    'password_setup_pending',
    'language',
    'use_totp',
    'totp_secret',
    'totp_authenticated_at',
    'gravatar',
    'root_admin',
];

protected $casts = [
    'root_admin' => 'boolean',
    'use_totp' => 'boolean',
    'gravatar' => 'boolean',
    'password_setup_pending' => 'boolean',
    'totp_authenticated_at' => 'datetime',
];

protected $attributes = [
    'external_id' => null,
    'root_admin' => false,
    'language' => 'en',
    'use_totp' => false,
    'totp_secret' => null,
    'password_setup_pending' => false,
];

public static array $validationRules = [
    // existing rules...
    'password_setup_pending' => 'boolean',
];
```

- [ ] **Step 5: Run the test again**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter=test_registration_creates_pending_password_user`
Expected: FAIL because registration endpoint is still missing, but schema/model error is gone.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_06_05_000000_add_password_setup_pending_to_users.php app/Models/User.php tests/Feature/Auth/RegisterControllerTest.php
git commit -m "feat: add pending password setup user state"
```

### Task 2: Add backend registration validation and user creation flow

**Files:**
- Create: `app/Http/Requests/Auth/RegisterRequest.php`
- Create: `app/Services/Users/RegistrationDomainService.php`
- Create: `app/Http/Controllers/Auth/RegisterController.php`
- Modify: `app/Services/Users/UserCreationService.php`
- Modify: `routes/auth.php`
- Modify: `config/arix.php`
- Test: `tests/Feature/Auth/RegisterControllerTest.php`

- [ ] **Step 1: Expand backend tests first**

Add failing tests for allowed domains, subdomains, and disallowed suffixes in `tests/Feature/Auth/RegisterControllerTest.php`.

```php
public function test_registration_accepts_allowlisted_domain_and_subdomain(): void
{
    Notification::fake();

    config()->set('arix.registration.enabled', true);
    config()->set('arix.registration.allowed_domains', ['example.com']);

    $allowed = $this->post('/auth/register', [
        'email' => 'allowed@example.com',
        'username' => 'alloweduser',
        'first_name' => 'Allow',
        'last_name' => 'Listed',
    ]);

    $subdomain = $this->post('/auth/register', [
        'email' => 'sub@team.example.com',
        'username' => 'subdomainuser',
        'first_name' => 'Sub',
        'last_name' => 'Domain',
    ]);

    $allowed->assertOk();
    $subdomain->assertOk();
}

public function test_registration_rejects_non_allowlisted_suffix(): void
{
    config()->set('arix.registration.enabled', true);
    config()->set('arix.registration.allowed_domains', ['example.com']);

    $response = $this->post('/auth/register', [
        'email' => 'blocked@example.com.evil.tld',
        'username' => 'blockeduser',
        'first_name' => 'Blocked',
        'last_name' => 'User',
    ]);

    $response->assertSessionHasErrors(['email']);
}
```

- [ ] **Step 2: Run focused domain tests**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter=registration_`
Expected: FAIL because request/controller/service do not exist.

- [ ] **Step 3: Add registration config to `config/arix.php`**

Append a registration block that can be driven by env or admin-managed config later.

```php
'registration' => [
    'enabled' => env('ARIX_REGISTRATION_ENABLED', false),
    'allowed_domains' => array_filter(array_map('trim', explode(',', env('ARIX_REGISTRATION_ALLOWED_DOMAINS', '')))),
],
```

- [ ] **Step 4: Add domain matcher service**

Create `app/Services/Users/RegistrationDomainService.php`.

```php
<?php

namespace Pterodactyl\Services\Users;

class RegistrationDomainService
{
    public function isAllowed(string $email, array $allowedDomains): bool
    {
        $domain = strtolower((string) substr(strrchr($email, '@') ?: '', 1));

        foreach ($allowedDomains as $allowedDomain) {
            $allowedDomain = strtolower(trim($allowedDomain));

            if ($domain === $allowedDomain) {
                return true;
            }

            if (str_ends_with($domain, '.' . $allowedDomain)) {
                return true;
            }
        }

        return false;
    }
}
```

- [ ] **Step 5: Add public registration request class**

Create `app/Http/Requests/Auth/RegisterRequest.php`.

```php
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
        return [
            'email' => User::getRules()['email'],
            'username' => User::getRules()['username'],
            'first_name' => User::getRules()['name_first'],
            'last_name' => User::getRules()['name_last'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $domains = config('arix.registration.allowed_domains', []);
            $service = app(RegistrationDomainService::class);

            if (!$service->isAllowed(strtolower($this->input('email', '')), $domains)) {
                $validator->errors()->add('email', 'Registration is not available for this email domain.');
            }
        });
    }

    public function validatedForCreation(): array
    {
        return [
            'email' => strtolower($this->validated()['email']),
            'username' => $this->validated()['username'],
            'name_first' => $this->validated()['first_name'],
            'name_last' => $this->validated()['last_name'],
        ];
    }
}
```

- [ ] **Step 6: Extend `UserCreationService` for pending state**

Modify `app/Services/Users/UserCreationService.php` so callers can request pending-password creation without changing admin flows.

```php
public function handle(array $data, bool $passwordSetupPending = false): User
{
    if (array_key_exists('password', $data) && !empty($data['password'])) {
        $data['password'] = $this->hasher->make($data['password']);
    }

    $this->connection->beginTransaction();

    if (!isset($data['password']) || empty($data['password'])) {
        $generateResetToken = true;
        $data['password'] = $this->hasher->make(str_random(30));
    }

    $user = $this->repository->create(array_merge($data, [
        'uuid' => Uuid::uuid4()->toString(),
        'password_setup_pending' => $passwordSetupPending,
    ]), true, true);

    if (isset($generateResetToken)) {
        $token = $this->passwordBroker->createToken($user);
    }

    $this->connection->commit();
    $user->notify(new AccountCreated($user, $token ?? null));

    return $user;
}
```

- [ ] **Step 7: Add register controller and routes**

Create `app/Http/Controllers/Auth/RegisterController.php` and wire it in `routes/auth.php` under the same auth throttling group.

```php
<?php

namespace Pterodactyl\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\User;
use Pterodactyl\Services\Users\UserCreationService;
use Pterodactyl\Http\Requests\Auth\RegisterRequest;

class RegisterController extends AbstractLoginController
{
    public function __construct(private UserCreationService $creationService)
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

    public function resend(RegisterRequest $request): JsonResponse
    {
        return response()->json(['success' => true]);
    }
}
```

Add routes in `routes/auth.php`.

```php
Route::get('/register', [Auth\LoginController::class, 'index'])->name('auth.register');

Route::middleware(['throttle:authentication'])->group(function () {
    Route::post('/register', [Auth\RegisterController::class, 'store'])->middleware('recaptcha');
    Route::post('/register/resend', [Auth\RegisterController::class, 'resend'])->middleware('recaptcha');
});
```

- [ ] **Step 8: Run backend registration tests**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php`
Expected: some tests still fail because resend/login gate/reset clear are not implemented yet.

- [ ] **Step 9: Commit**

```bash
git add app/Http/Controllers/Auth/RegisterController.php app/Http/Requests/Auth/RegisterRequest.php app/Services/Users/RegistrationDomainService.php app/Services/Users/UserCreationService.php routes/auth.php config/arix.php tests/Feature/Auth/RegisterControllerTest.php
git commit -m "feat: add public registration backend flow"
```

### Task 3: Gate login and clear pending state on password setup

**Files:**
- Modify: `app/Http/Controllers/Auth/LoginController.php`
- Modify: `app/Http/Controllers/Auth/ResetPasswordController.php`
- Test: `tests/Feature/Auth/RegisterControllerTest.php`

- [ ] **Step 1: Add failing tests for login gating and reset clearing**

Append these tests to `tests/Feature/Auth/RegisterControllerTest.php`.

```php
public function test_pending_password_user_cannot_log_in(): void
{
    $user = User::factory()->create([
        'email' => 'pending@example.com',
        'username' => 'pendinguser',
        'password' => bcrypt('password123'),
        'password_setup_pending' => true,
    ]);

    $response = $this->post('/auth/login', [
        'user' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(422);
}

public function test_password_reset_clears_pending_password_flag(): void
{
    $user = User::factory()->create([
        'password_setup_pending' => true,
    ]);

    $token = app('auth.password.broker')->createToken($user);

    $response = $this->post('/auth/password/reset', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'password_setup_pending' => false,
    ]);
}
```

- [ ] **Step 2: Run the focused auth state tests**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter='pending_password|password_reset_clears'`
Expected: FAIL because login and reset logic still ignore the flag.

- [ ] **Step 3: Add pending-password login guard**

Modify `app/Http/Controllers/Auth/LoginController.php` before the 2FA branch.

```php
if ($user->password_setup_pending) {
    $this->sendFailedLoginResponse(
        $request,
        $user,
        'Finish setting your password from the email we sent before signing in.'
    );
}
```

If preserving existing generic login semantics is required, swap this to a `DisplayException` raised from a dedicated helper and keep the message product-approved.

- [ ] **Step 4: Clear the flag during password reset**

Modify `app/Http/Controllers/Auth/ResetPasswordController.php`.

```php
$user = $this->userRepository->update($user->id, [
    'password' => $this->hasher->make($password),
    'password_setup_pending' => false,
    $user->getRememberTokenName() => Str::random(60),
]);
```

- [ ] **Step 5: Adjust reset success redirect payload if needed**

If frontend needs a guaranteed login redirect instead of `/`, set:

```php
public string $redirectTo = '/auth/login';
```

and keep `send_to_login` semantics coherent with 2FA.

- [ ] **Step 6: Run backend auth tests again**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php`
Expected: login gate and reset-clear tests PASS; resend/frontend tests may still be pending.

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/Auth/LoginController.php app/Http/Controllers/Auth/ResetPasswordController.php tests/Feature/Auth/RegisterControllerTest.php
git commit -m "feat: gate pending registration logins"
```

### Task 4: Add resend setup-email flow

**Files:**
- Modify: `app/Http/Controllers/Auth/RegisterController.php`
- Test: `tests/Feature/Auth/RegisterControllerTest.php`

- [ ] **Step 1: Add resend tests first**

Add failing resend tests.

```php
public function test_resend_sends_setup_email_for_pending_user(): void
{
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'pending@example.com',
        'password_setup_pending' => true,
    ]);

    $response = $this->post('/auth/register/resend', [
        'email' => $user->email,
    ]);

    $response->assertOk();
    Notification::assertCount(1);
}

public function test_resend_returns_success_for_unknown_email_without_sending(): void
{
    Notification::fake();

    $response = $this->post('/auth/register/resend', [
        'email' => 'missing@example.com',
    ]);

    $response->assertOk();
    Notification::assertNothingSent();
}
```

- [ ] **Step 2: Run resend tests**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter=resend`
Expected: FAIL because resend endpoint is stubbed.

- [ ] **Step 3: Implement resend lookup and generic response**

Update `app/Http/Controllers/Auth/RegisterController.php`.

```php
public function resend(Request $request): JsonResponse
{
    abort_unless(config('arix.registration.enabled'), 404);

    $email = strtolower((string) $request->input('email'));
    $user = User::query()->where('email', $email)->first();

    if ($user && $user->password_setup_pending) {
        $token = app('auth.password.broker')->createToken($user);
        $user->notify(new AccountCreated($user, $token));
    }

    return response()->json([
        'success' => true,
        'message' => 'If that account can receive a setup email, it has been sent.',
    ]);
}
```

Add a small request validator for resend if you want clean email validation reuse; otherwise use `Request::validate(['email' => 'required|email'])` inline.

- [ ] **Step 4: Run resend tests again**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php --filter=resend`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Auth/RegisterController.php tests/Feature/Auth/RegisterControllerTest.php
git commit -m "feat: add registration setup email resend"
```

### Task 5: Add Arix registration screens and client API helpers

**Files:**
- Create: `resources/scripts/api/auth/register.ts`
- Create: `resources/scripts/api/auth/resendRegistrationEmail.ts`
- Create: `resources/scripts/components/auth/RegisterContainer.tsx`
- Create: `resources/scripts/components/auth/RegisterConfirmationContainer.tsx`
- Modify: `resources/scripts/routers/AuthenticationRouter.tsx`
- Modify: `resources/scripts/components/auth/LoginContainer.tsx`
- Modify: `resources/scripts/components/auth/ResetPasswordContainer.tsx`
- Test: `resources/scripts/components/auth/RegisterConfirmationContainer.test.tsx`

- [ ] **Step 1: Add failing frontend route test**

Create `resources/scripts/components/auth/RegisterConfirmationContainer.test.tsx`.

```tsx
import React from 'react';
import { render, screen } from '@testing-library/react';
import { MemoryRouter } from 'react-router-dom';
import AuthenticationRouter from '@/routers/AuthenticationRouter';

test('renders registration confirmation screen', async () => {
    render(
        <MemoryRouter initialEntries={['/auth/register/confirmation']}>
            <AuthenticationRouter />
        </MemoryRouter>
    );

    expect(await screen.findByText(/check your email/i)).toBeInTheDocument();
});
```

- [ ] **Step 2: Run the frontend test**

Run: `yarn test RegisterConfirmationContainer.test.tsx --runInBand`
Expected: FAIL because route and component do not exist.

- [ ] **Step 3: Add client API helpers**

Create `resources/scripts/api/auth/register.ts`.

```ts
import http from '@/api/http';

export interface RegisterResponse {
    success: boolean;
    email: string;
    redirectTo: string;
}

export default (payload: {
    email: string;
    username: string;
    firstName: string;
    lastName: string;
    recaptchaData?: string;
}): Promise<RegisterResponse> =>
    http.post('/auth/register', {
        email: payload.email,
        username: payload.username,
        first_name: payload.firstName,
        last_name: payload.lastName,
        'g-recaptcha-response': payload.recaptchaData,
    }).then(({ data }) => ({
        success: data.success,
        email: data.email,
        redirectTo: data.redirect_to,
    }));
```

Create `resources/scripts/api/auth/resendRegistrationEmail.ts` similarly.

```ts
import http from '@/api/http';

export default (email: string, recaptchaData?: string): Promise<void> =>
    http.post('/auth/register/resend', {
        email,
        'g-recaptcha-response': recaptchaData,
    }).then(() => undefined);
```

- [ ] **Step 4: Add `RegisterContainer`**

Create `resources/scripts/components/auth/RegisterContainer.tsx` using `LoginFormContainer`, `Formik`, `Field`, existing button styling, and same reCAPTCHA flow as login.

```tsx
const RegisterContainer = ({ history }: RouteComponentProps) => {
    const ref = useRef<Reaptcha>(null);
    const [token, setToken] = useState('');
    const { clearFlashes, clearAndAddHttpError } = useFlash();

    const onSubmit = (values: Values, { setSubmitting }: FormikHelpers<Values>) => {
        if (recaptchaEnabled && !token) {
            ref.current!.execute().catch((error) => {
                setSubmitting(false);
                clearAndAddHttpError({ error });
            });
            return;
        }

        register({
            email: values.email,
            username: values.username,
            firstName: values.firstName,
            lastName: values.lastName,
            recaptchaData: token,
        })
            .then((response) => {
                history.replace('/auth/register/confirmation', { email: response.email });
            })
            .catch((error) => {
                setSubmitting(false);
                setToken('');
                if (ref.current) ref.current.reset();
                clearAndAddHttpError({ error });
            });
    };

    return <LoginFormContainer title={'Create account'}>{/* fields and CTA */}</LoginFormContainer>;
};
```

- [ ] **Step 5: Add confirmation screen and redirect UX**

Create `resources/scripts/components/auth/RegisterConfirmationContainer.tsx`.

```tsx
const RegisterConfirmationContainer = ({ history, location }: RouteComponentProps<unknown, { email?: string }>) => {
    const [seconds, setSeconds] = useState(8);
    const email = location.state?.email || '';

    useEffect(() => {
        if (seconds <= 0) {
            history.replace('/auth/login', { flash: 'Check your email to finish setting your password.' });
            return;
        }

        const timeout = window.setTimeout(() => setSeconds((value) => value - 1), 1000);
        return () => window.clearTimeout(timeout);
    }, [seconds]);

    return (
        <LoginFormContainer title={'Check your email'}>
            <p>{email}</p>
            <Button type={'button'} onClick={() => resendRegistrationEmail(email)}>Resend setup email</Button>
            <p>Redirecting to login in {seconds}s.</p>
        </LoginFormContainer>
    );
};
```

- [ ] **Step 6: Wire routes and login CTA**

Modify `resources/scripts/routers/AuthenticationRouter.tsx`.

```tsx
import RegisterContainer from '@/components/auth/RegisterContainer';
import RegisterConfirmationContainer from '@/components/auth/RegisterConfirmationContainer';

<Route path={`${path}/register`} component={RegisterContainer} exact />
<Route path={`${path}/register/confirmation`} component={RegisterConfirmationContainer} exact />
```

Modify `resources/scripts/components/auth/LoginContainer.tsx`.

```tsx
<div css={tw`mt-4 text-sm text-neutral-300 text-center`}>
    <span>Don't have an account? </span>
    <Link to={'/auth/register'} css={tw`underline hover:text-neutral-200`}>
        Register
    </Link>
</div>
```

Modify `resources/scripts/components/auth/ResetPasswordContainer.tsx` so successful setup prefers `/auth/login` and shows login-oriented flash if API returns that path.

- [ ] **Step 7: Run frontend tests**

Run: `yarn test RegisterConfirmationContainer.test.tsx --runInBand`
Expected: PASS.

- [ ] **Step 8: Commit**

```bash
git add resources/scripts/api/auth/register.ts resources/scripts/api/auth/resendRegistrationEmail.ts resources/scripts/components/auth/RegisterContainer.tsx resources/scripts/components/auth/RegisterConfirmationContainer.tsx resources/scripts/components/auth/RegisterConfirmationContainer.test.tsx resources/scripts/routers/AuthenticationRouter.tsx resources/scripts/components/auth/LoginContainer.tsx resources/scripts/components/auth/ResetPasswordContainer.tsx
git commit -m "feat: add Arix registration screens"
```

### Task 6: Final verification and cleanup

**Files:**
- Modify: any touched files from Tasks 1-5 as needed
- Test: `tests/Feature/Auth/RegisterControllerTest.php`
- Test: `resources/scripts/components/auth/RegisterConfirmationContainer.test.tsx`

- [ ] **Step 1: Run focused backend suite**

Run: `php artisan test tests/Feature/Auth/RegisterControllerTest.php`
Expected: PASS.

- [ ] **Step 2: Run focused frontend suite**

Run: `yarn test RegisterConfirmationContainer.test.tsx --runInBand`
Expected: PASS.

- [ ] **Step 3: Run static validation for touched frontend files**

Run: `yarn tsc --noEmit`
Expected: PASS or only unrelated pre-existing failures.

- [ ] **Step 4: Run lint for touched auth components if repo is clean enough**

Run: `yarn lint --quiet`
Expected: PASS or only unrelated pre-existing failures.

- [ ] **Step 5: Manual browser smoke checks**

Verify these paths in local dev environment:

```text
/auth/login
/auth/register
/auth/register/confirmation
/auth/password/reset/<token>?email=<user>
```

Expected:
- login shows register CTA
- register submits valid allowlisted email
- confirmation screen renders and resend works
- reset password clears pending state
- login succeeds after password setup

- [ ] **Step 6: Final commit**

```bash
git add app/Http/Controllers/Auth app/Http/Requests/Auth app/Models/User.php app/Services/Users routes/auth.php config/arix.php database/migrations resources/scripts tests/Feature/Auth/RegisterControllerTest.php
git commit -m "feat: add public Arix registration flow"
```

