# QA And Security Checklist

## QA

- Public atlas loads successfully
- `/api/facilities` returns seeded facilities
- Guest users cannot access `/admin`
- Authenticated operator can create datasets and imports
- Validation records warnings/errors for malformed rows
- Publish inserts facilities and makes them visible on the public map

## Security

- Admin routes are role-gated
- Import uploads are stored on a private disk
- CSRF protection is inherited from Laravel form middleware
- Passwords are hashed by Laravel
- Public APIs expose mapped civic data only; no secrets are serialized

## Operational Follow-Ups

- Add request throttling for public APIs before wider deployment
- Add antivirus or content scanning on uploaded files if this becomes multi-user
- Add audit review tooling if the admin console grows beyond portfolio scope
