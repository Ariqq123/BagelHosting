# Subdomains Feature Design

## Goal

Add a Subdomains feature that lets administrators configure Cloudflare-backed root domains and lets server users create and delete per-server DNS records within configured limits.

## Scope

- Admins can add, edit, and remove root domain configurations.
- Each root domain stores a domain name, Cloudflare zone ID, encrypted API token, allowed record types, default proxied setting, and enabled state.
- Server users can manage subdomains from a new server Subdomains tab when they have the relevant permissions.
- Records can be either `A` records pointing to a server allocation IP or `CNAME` records pointing to a configured node/domain target.
- Each server has a subdomain limit. Users can create and delete only records owned by the current server.

## Architecture

Use a service-layer implementation rather than calling Cloudflare directly from controllers.

- `SubdomainDomain` model: admin-owned Cloudflare domain configuration.
- `Subdomain` model: per-server DNS record tracking Cloudflare record ID and status.
- `CloudflareDnsService`: creates and deletes DNS records through Cloudflare using Laravel HTTP client.
- Client API controllers: list, create, and delete server subdomains.
- Admin controllers: manage root domain settings.
- Transformers expose consistent API payloads to the panel UI.

## Data Model

`subdomain_domains`:

- `id`
- `name`
- `cloudflare_zone_id`
- `cloudflare_token` encrypted
- `allowed_record_types` JSON array containing `A`, `CNAME`, or both
- `cname_target` nullable
- `proxied` boolean
- `enabled` boolean
- timestamps

`subdomains`:

- `id`
- `server_id`
- `user_id`
- `subdomain_domain_id`
- `name`
- `fqdn`
- `type`
- `content`
- `proxied`
- `cloudflare_record_id`
- `status` using `active` or `error`
- nullable `error_message`
- timestamps

`servers` gets `subdomain_limit`, nullable integer defaulting to `0`.

## User Flow

1. Admin configures at least one enabled root domain.
2. User opens a server and selects Subdomains.
3. Panel loads existing records and available domains.
4. User enters a prefix, chooses root domain and record type, then submits.
5. Backend validates permissions, server limit, prefix format, root domain state, record type, and FQDN uniqueness.
6. Backend calls Cloudflare. If Cloudflare succeeds, the DB row is saved as `active`; if it fails, no row is saved and the API returns a display error.
7. Deleting a record removes it from Cloudflare first, then deletes the DB row. If Cloudflare deletion fails, the row remains with status `error` and the user sees the failure.

## Permissions

Add server permissions:

- `subdomain.read`
- `subdomain.create`
- `subdomain.delete`

Server owners have these permissions by default through existing owner behavior. Subusers receive them through the existing subuser permission UI once exposed.

## Validation

- Prefix allows lowercase letters, numbers, and hyphens.
- Prefix cannot start or end with a hyphen.
- Prefix length is 1-63 characters.
- FQDN must be unique across all subdomains.
- Domain must be enabled.
- Requested record type must be allowed by the domain.
- `A` records use the selected server allocation IP.
- `CNAME` records use the domain's configured CNAME target.
- Server cannot exceed `subdomain_limit`; `0` means no user-created records.

## Error Handling

- Cloudflare create failure returns a display error and creates no DB record.
- Cloudflare delete failure leaves the record in place, stores the error, and returns a display error.
- Disabled or missing domain configurations are hidden from normal user creation and rejected by the API.
- Cloudflare tokens are encrypted at rest and never returned by API responses.

## UI

- Admin: new Subdomains settings page with a table of root domains and create/edit forms.
- Server panel: new Subdomains tab listing FQDN, type, content, status, and actions.
- Creation form uses domain and type selectors, with disabled states when no domains are available or the server limit is reached.

## Testing

- Unit tests for Cloudflare service create/delete success and failure paths using mocked HTTP.
- API tests for list/create/delete permissions.
- API tests for validation, limits, and uniqueness.
- Model relationship tests for server and domain ownership.
- Server deletion cleanup test to ensure owned DNS records are removed or marked failed if Cloudflare rejects deletion.

