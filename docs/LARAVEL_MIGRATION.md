# CHTM Laravel migration notes

See [SYSTEM_AUDIT.md](./SYSTEM_AUDIT.md) for the current stack and verification checklist.

## Encryption

Sensitive columns use AES-256-GCM via `App\Casts\Aes256GcmEncrypted`. Login uses `users.email_hash` (SHA-256 of the normalized email). Passwords stay on bcrypt.

Set `AES_GCM_KEY` in production (32-byte key, base64-encoded with the `base64:` prefix).

## Seed & login

```bash
php artisan migrate:fresh --seed
php artisan serve
```

- `admin@chtm.local` / `password` (admin)
- `reservation@chtm.local`, `frontoffice@chtm.local`, `housekeeper@chtm.local` / `password`

## Legacy React folder

Archived under `_archive/unused-frontend/`. Safe to delete that folder when you no longer need the reference code.
