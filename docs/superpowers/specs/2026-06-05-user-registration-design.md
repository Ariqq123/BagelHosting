# User Registration Design

## Goal

Add a public user registration flow that fits the existing Arix auth experience, creates an active panel user immediately, sends an email for password setup, and blocks login until the password has been set.

## Scope

- Add public registration to the existing `/auth` SPA.
- Collect `email`, `username`, `first_name`, and `last_name` on registration.
- Restrict registration to an email-domain allowlist that accepts exact domains and subdomains.
- Create a real `users` row immediately after successful submission.
- Send a password-setup email using the existing password reset infrastructure.
- Show a dedicated Arix-themed confirmation screen after registration.
- Redirect that confirmation screen to login with a flash message.
- Allow resend of the setup email for pending-password users.
- Block login until the user completes password setup.

## Architecture

Extend the existing auth system rather than introducing a separate registration product area.

- `GET /auth/register` renders through the existing auth SPA entry point.
- `POST /auth/register` validates input, creates the user, marks the account as pending password setup, and sends the setup email.
- `POST /auth/register/resend` re-sends the setup email for eligible pending-password users.
- The existing reset-password screen remains the password setup destination.
- The existing login route remains the sign-in entry point and gains a guard for pending-password users.

The system keeps one auth shell, one email template family, and one password-setting flow. Registration only adds entry, pending-state handling, and resend behavior.

## Data Model

`users` gains:

- `password_setup_pending` boolean defaulting to `false`

Optional but recommended follow-up field if product wants richer audit or cooldown UX later:

- `password_setup_sent_at` nullable timestamp

Registration request payload:

- `email`
- `username`
- `first_name`
- `last_name`

Persistence behavior:

- Normalize email to lowercase before validation and persistence.
- Create the user immediately with an active panel account.
- Generate a random password hash at creation time so login is impossible without setup.
- Set `password_setup_pending = true` until the reset-password flow completes successfully.

## User Flow

1. Guest opens `/auth/register` from the new login-page CTA.
2. Guest submits `email`, `username`, `first_name`, and `last_name`.
3. Backend validates uniqueness, format, CAPTCHA, throttling, and allowed email domain.
4. Backend creates an active user with `password_setup_pending = true`.
5. Backend creates a password broker token and sends the setup email.
6. Frontend routes to a dedicated Arix confirmation screen.
7. Confirmation screen shows masked email, resend action, and timed redirect to `/auth/login`.
8. User follows email link to the existing password reset page.
9. Successful password setup clears `password_setup_pending`.
10. User is redirected to `/auth/login` with a flash message indicating sign-in is now available.

## Domain Allowlist

Use a config-driven allowlist of base domains.

- Exact domain matches are allowed.
- Any subdomain of an allowed base domain is also allowed.
- Lookalike suffixes such as `company.com.evil.tld` are rejected.
- Matching should be performed on normalized lowercase domains.

Example behavior if `company.com` is allowlisted:

- `user@company.com` allowed
- `user@team.company.com` allowed
- `user@eu.team.company.com` allowed
- `user@company.com.evil.tld` rejected

## Login Gating

Pending-password users must not be able to sign in until setup completes.

- Login attempts for `password_setup_pending = true` are rejected before session creation.
- Response uses a specific message telling the user to complete password setup from email.
- The UX should guide the user to resend the setup email if needed.
- Once password reset succeeds, the pending state is cleared and normal login works.

## UI

Registration stays visually native to the current Arix auth shell.

- Add `Don't have an account? Register` on the login screen as a secondary CTA near the existing forgot-password affordance.
- Add `RegisterContainer` for `/auth/register` using the same auth card, field layout, icon treatment, button styling, flash handling, and spacing as the current login flow.
- Add `RegisterConfirmationContainer` for the post-submit state using the same Arix auth frame but replacing the form with a status panel.
- Add a link back to `/auth/login` from the registration form.
- Keep login submit as primary action; registration remains a secondary navigation choice.

Confirmation screen content:

- success heading
- setup-email instructions
- masked destination email
- resend button
- redirect countdown copy

If registration is disabled in a future iteration, the login CTA should be hidden rather than left broken.

## Resend Behavior

`POST /auth/register/resend` supports users who have not yet completed password setup.

- Endpoint is available to guests.
- Endpoint only sends mail for users that exist and still have `password_setup_pending = true`.
- Response should be success-shaped even when no eligible user is found, to avoid account enumeration.
- Endpoint uses the same throttling and CAPTCHA posture as other public auth actions.

## Error Handling And Safety

- Apply auth throttling to registration and resend endpoints.
- Apply the same CAPTCHA layer used by other auth form submissions.
- Return actionable validation errors for invalid fields and disallowed domains.
- Avoid leaking whether an email exists through resend responses.
- Reuse password broker tokens rather than introducing a second token system.
- Registration does not create a logged-in session.
- Password setup remains the only path to clear pending state.

Forgot-password behavior:

- It may continue to work for pending-password users because it leads to the same setup destination.
- Login copy and pending-login errors should remain consistent so users are not confused about next steps.

## Components

Backend:

- auth routes in `routes/auth.php`
- registration controller and request validation
- `UserCreationService` extension for pending-password state
- login guard in `LoginController`
- pending-state clear in `ResetPasswordController`

Frontend:

- login CTA update
- `RegisterContainer`
- `RegisterConfirmationContainer`
- API helpers for register and resend
- auth router updates for the new screens

## Testing

- Feature tests for allowlisted domain acceptance and rejection, including subdomain cases.
- Feature tests for registration success, duplicate email rejection, and duplicate username rejection.
- Feature tests proving `password_setup_pending` users cannot log in.
- Feature tests proving successful password setup clears pending state.
- Feature tests for resend behavior and non-enumerating responses.
- Frontend tests for register route rendering, confirmation state, and login CTA presence if auth SPA tests already cover these patterns.
