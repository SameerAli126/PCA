# Developer Runbook

## Start The App

```powershell
& 'C:\xampp\php\php.exe' artisan serve
```

## Build Frontend Assets

```powershell
npm run build
```

## Rebuild The Database

```powershell
& 'C:\xampp\php\php.exe' artisan migrate:fresh --seed
```

## Create A Dataset

1. Log in as `manager@civicatlas.test`
2. Open `/admin/datasets`
3. Register a dataset definition

## Upload And Publish An Import

1. Open `/admin/imports`
2. Upload a CSV or XLSX
3. Review the preview/mapping
4. Run validation
5. Publish the import

## Queue Strategy

- Current implementation keeps the queue tables ready
- MVP import work runs synchronously through admin actions
- Move validation/publish into queued jobs when dataset volume grows

## Environment Notes

- Current `.env` is set to `DB_PORT=3307`
- If you normalize XAMPP back to `3306`, update `.env`
