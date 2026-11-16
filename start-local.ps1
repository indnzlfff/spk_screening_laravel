$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$workspaceRoot = Split-Path -Parent $projectRoot
$phpCandidates = @(
    (Join-Path $workspaceRoot '.runtime\php83\php.exe'),
    (Join-Path $env:USERPROFILE 'Documents\Codex\2026-10-07\pro\.runtime\php83\php.exe')
)
$bundledPhp = $phpCandidates | Where-Object { Test-Path -LiteralPath $_ } | Select-Object -First 1

if ($bundledPhp) {
    $php = $bundledPhp
} else {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if (-not $phpCommand) {
        throw 'PHP 8.3 atau lebih baru belum ditemukan. Pasang PHP yang sesuai Laravel lalu jalankan kembali.'
    }
    $php = $phpCommand.Source
}

$phpVersion = [int] (& $php -r 'echo PHP_VERSION_ID;')
if ($phpVersion -lt 80300) {
    throw 'Laravel 13 membutuhkan PHP 8.3 atau lebih baru. PHP XAMPP lama tetap dapat digunakan untuk aplikasi versi sebelumnya.'
}
Write-Host "Menggunakan PHP $(& $php -r 'echo PHP_VERSION;')"

# Route package manifests to a writable temporary folder in restricted local environments.
$cacheRoot = Join-Path $env:TEMP 'spk-screening-laravel-cache'
if (-not (Test-Path -LiteralPath $cacheRoot)) {
    New-Item -ItemType Directory -Path $cacheRoot -Force | Out-Null
}
$rootedCachePath = $cacheRoot.Substring(2).Replace('/', '\')
$env:APP_PACKAGES_CACHE = $rootedCachePath + '\packages.php'
$env:APP_SERVICES_CACHE = $rootedCachePath + '\services.php'
$env:APP_CONFIG_CACHE = $rootedCachePath + '\config.php'
$env:APP_ROUTES_CACHE = $rootedCachePath + '\routes.php'
$env:APP_EVENTS_CACHE = $rootedCachePath + '\events.php'

Set-Location $projectRoot
Write-Host 'Membuka Laravel di http://127.0.0.1:8010. Tekan Ctrl+C untuk menghentikan server.'
& $php artisan serve --host=127.0.0.1 --port=8010
