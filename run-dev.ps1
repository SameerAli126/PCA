param(
    [int]$AppPort = 8000,
    [int]$VitePort = 5173,
    [switch]$WithQueue
)

$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$php = 'C:\xampp\php\php.exe'

if (-not (Test-Path $php)) {
    throw "PHP executable not found at $php"
}

$commands = @(
    "`"$php`" artisan serve --host=127.0.0.1 --port=$AppPort",
    "npm run dev -- --host 127.0.0.1 --port $VitePort"
)

$names = @('backend', 'vite')
$colors = @('#93c5fd', '#fb7185')

if ($WithQueue) {
    $commands += "`"$php`" artisan queue:listen --tries=1 --timeout=0"
    $names += 'queue'
    $colors += '#c4b5fd'
}

Push-Location $projectRoot

try {
    npx concurrently `
        -c ($colors -join ',') `
        --names ($names -join ',') `
        --kill-others `
        @commands
}
finally {
    Pop-Location
}
