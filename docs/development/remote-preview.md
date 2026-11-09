# Temporary phone preview from another network

Updated 30 October 2026 (Asia/Manila). The user selected a temporary password-protected preview after the email-PIN tunnel repeatedly lost access. This is a development preview, not production research hosting.

30 October continuation: the previous address no longer resolved and its supporting processes were stopped. Restarted database/app/gate/transport with the same private preview credentials and a new address in ignored `.local/phone-preview-url.txt`. The public landing/coded-login/cycle/reader/gating/logout HTTP flow passed. See [site-update evidence](site-update-evidence.md). Restarting a Quick Tunnel changes its temporary address.

## Open the preview

Use the NEW HTTPS address shared privately in chat, also saved in ignored `.local/phone-preview-url.txt`. The previous email-PIN address has been retired. The browser first prompts for the preview username/password; after that, sign into ChemTrack with the existing fictional demo account. The preview username is `preview`; its generated password is only in ignored `.local/preview-gate-settings.json` and the private handoff, never in this document. Do not put passwords into URLs.

The preview password is shared access: anyone who knows it can open the outer gate. ChemTrack's own login, roles and prerequisites remain required. A browser can remember HTTP Basic credentials until its authentication cache is cleared; this is separate from ChemTrack's Sign out control. Stop the tunnel to revoke this preview globally; generate a new password/hash before another run if the password must be rotated. Starting another Quick Tunnel generates a different address.

## Current path

```text
Phone -> Cloudflare HTTPS tunnel -> Caddy password gate (127.0.0.1:8001)
                                   -> Laravel app (127.0.0.1:8000) -> local database
```

Cloudflare now provides transport only. Caddy's maintained `basic_auth` protects every proxied path, including static assets and form submissions. Its configuration is [scripts/preview-gate.Caddyfile](../../scripts/preview-gate.Caddyfile). Caddy binds only to loopback; its administration endpoint and config persistence are disabled. It reads a bcrypt hash from the process environment, strips the preview Authorization header before forwarding, sets private/no-store responses and forwards HTTPS protocol to Laravel. Public HTTP requests redirect to HTTPS before a password challenge. The public link must use HTTPS; loopback HTTP is only the local proxy hop.

The official Caddy 2.11.7 Windows archive was verified against the release SHA256 `0a1edc0b799512051c57071ce0e798d3f2cf67dc98171366f1d2b326072e3b06`. Its executable is `%LOCALAPPDATA%/ChemTrack/tools/caddy-2.11.7/caddy.exe`. Official cloudflared 2026.10.6 remains in the same tools parent. Neither was installed as a service. No firewall, router, power settings, paid hosting, database records or application authentication code changed for the replacement. No credentials are in the tracked Caddyfile or tests.

## Restart and stop

Laptop, database, Laravel server, gate and tunnel must remain running, awake and online. The Laravel preview process runs with debug disabled and secure session cookies through transient environment overrides; `.env` remains unchanged. While it is active, use HTTPS rather than the ordinary local HTTP login. Do not start a second application server on port 8000.

With the existing database and HTTPS-preview Laravel process healthy, start the gate from the project root in PowerShell:

```powershell
$previewSettings = Get-Content .local/preview-gate-settings.json -Raw | ConvertFrom-Json
$env:CHEMTRACK_PREVIEW_PASSWORD_HASH = $previewSettings.password_hash
& "$env:LOCALAPPDATA/ChemTrack/tools/caddy-2.11.7/caddy.exe" validate --config scripts/preview-gate.Caddyfile --adapter caddyfile
& "$env:LOCALAPPDATA/ChemTrack/tools/caddy-2.11.7/caddy.exe" run --config scripts/preview-gate.Caddyfile --adapter caddyfile
```

After checking that this gate is running and rejects anonymous requests, use a separate terminal for the transport:

```powershell
& "$env:LOCALAPPDATA/ChemTrack/tools/cloudflared.exe" tunnel --url http://127.0.0.1:8001 --protocol http2
```

Keep both terminals open and use the new HTTPS URL printed by cloudflared. Never point this replacement tunnel directly at port 8000. The active run's process/session references are in ignored `.local/phone-preview-sessions.json`; logs and generated credentials remain ignored too. To stop, close/interrupt the known tunnel first, then the gate; preserve unrelated database/server processes. Check process identity before stopping by PID, because PIDs may be reused. For ordinary laptop-only development, stop the HTTPS-preview Laravel process and restart `Start-ChemTrack.cmd` as described in [the local guide](open-local-site.md).

## Verification evidence

The new [HTTP integration check](../../tests/Integration/preview-password-gate.py) first failed against the original ungated origin, then passed against both the local gate and the public HTTPS link. It checks anonymous GET/POST/static-asset denials, wrong passwords, the browser challenge, correct access, secure app cookies/HTTPS assets, ChemTrack's independent login requirement, actual fictional login POST, repeated dashboard/relative-logo GETs, logout and private-file rejection. The local check also verifies the insecure-forwarded-request redirect. An additional public HTTP check confirmed HTTPS redirect without a password challenge; the retired email URL returned 530. The application suite was rerun: 19 tests / 76 assertions passed.

Run with the bundled/local Python, from the project root:

```powershell
python tests/Integration/preview-password-gate.py
$previewUrl = (Get-Content .local/phone-preview-url.txt -Raw).Trim()
python tests/Integration/preview-password-gate.py $previewUrl
```

The script reads only fictional demo credentials and ignored local preview settings; it does not print their values. Browser rendering is disabled in Work settings, so these are actual HTTP flow checks, not a claimed visual or real-phone test. The user still needs to confirm the browser prompt and navigation on their phone.

## Why email PIN was replaced

The previous protected Quick Tunnel showed repeated verification, missing-state-cookie callback errors, and plain Unauthorized after app login submission in Brave and Chrome. The app's local proxy flow passed. Open [cloudflared issue #1752](https://github.com/cloudflare/cloudflared/issues/1752) closely matched the symptoms; the user's precise authenticated-session expiry was not measured. Installed 2026.10.6 was already the latest official release. The user explicitly chose the new shared-password access model. No further browser protection changes or repeated PIN attempts are needed for this replacement. Restore ineffective diagnostic Shields changes if applicable.

Sources: [Caddy basic authentication](https://caddyserver.com/docs/caddyfile/directives/basic_auth), [Caddy reverse proxy](https://caddyserver.com/docs/caddyfile/directives/reverse_proxy), [Cloudflare Quick Tunnels](https://developers.cloudflare.com/tunnel/get-started/quick-tunnels/).
