param([ValidateSet('serve','test','integration','migrate','demo','build','routes','format')][string]$Action = 'serve')
$ErrorActionPreference = 'Stop'
Set-Location (Split-Path $PSScriptRoot -Parent)
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$phpExe = if ($phpCommand) { $phpCommand.Source } elseif (Test-Path 'C:/xampp/php/php.exe') { 'C:/xampp/php/php.exe' } else { throw 'Install PHP 8.3+ and add it to PATH.' }
$localPhpIni = Join-Path $env:LOCALAPPDATA 'ChemTrack/tools/php.ini'
if (Test-Path $localPhpIni) { $env:PHPRC = $localPhpIni }
$env:Path = (Split-Path $phpExe) + ';' + $env:Path
switch ($Action) {
    'serve' { & $phpExe artisan serve --host=127.0.0.1 --port=8000 }
    'test' { & $phpExe artisan test }
    'integration' { & $phpExe tests/Integration/freeze-current-read.php }
    'migrate' { & $phpExe artisan migrate }
    'demo' { & $phpExe artisan chemtrack:demo }
    'routes' { & $phpExe artisan route:list --except-vendor }
    'format' { & $phpExe vendor/bin/pint --test }
    'build' {
        $pnpmCommand = Get-Command pnpm -ErrorAction SilentlyContinue
        $bundledPnpm = Join-Path $env:USERPROFILE '.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/pnpm/bin/pnpm.cjs'
        if ($pnpmCommand) { & $pnpmCommand.Source run build }
        elseif (Test-Path $bundledPnpm) { & node $bundledPnpm run build }
        else { & npm run build }
    }
}
exit $LASTEXITCODE
