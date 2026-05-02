$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
$deployRoot = Join-Path $projectRoot "deploy\hpanel\package"
$appTarget = Join-Path $deployRoot "laravel_app"
$publicTarget = Join-Path $deployRoot "public_html"
$templatePublicRoot = Join-Path $projectRoot "deploy\hpanel\templates\public_html"

if (Test-Path $deployRoot) {
    Remove-Item $deployRoot -Recurse -Force
}

New-Item -ItemType Directory -Path $appTarget | Out-Null
New-Item -ItemType Directory -Path $publicTarget | Out-Null

Write-Host "Copying Laravel app files..."
$excludeDirs = @(
    (Join-Path $projectRoot ".git"),
    (Join-Path $projectRoot "node_modules"),
    (Join-Path $projectRoot "vendor"),
    (Join-Path $projectRoot "deploy"),
    (Join-Path $projectRoot "storage\logs"),
    (Join-Path $projectRoot "storage\framework\views")
)

robocopy $projectRoot $appTarget /E /XD $excludeDirs `
    /XF `
    ".env" `
    ".phpunit.result.cache" `
    "database.sqlite"

if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed while copying application files."
}

Write-Host "Copying web root files..."
robocopy (Join-Path $projectRoot "public") $publicTarget /E
if ($LASTEXITCODE -ge 8) {
    throw "robocopy failed while copying public files."
}

Write-Host "Copying logo file (if present)..."
$logoSource = Join-Path $projectRoot "xirfadkaablogo.jpg"
$logoTarget = Join-Path $publicTarget "xirfadkaablogo.jpg"
if (Test-Path $logoSource) {
    Copy-Item $logoSource $logoTarget -Force
} else {
    Write-Host "Logo not found at project root: $logoSource"
}

Write-Host "Applying hPanel index.php + .htaccess templates..."
Copy-Item (Join-Path $templatePublicRoot "index.php") (Join-Path $publicTarget "index.php") -Force
Copy-Item (Join-Path $templatePublicRoot ".htaccess") (Join-Path $publicTarget ".htaccess") -Force

Write-Host ""
Write-Host "Done. Upload these folders to your subdomain:"
Write-Host " - $publicTarget  => subdomain public_html"
Write-Host " - $appTarget     => sibling laravel_app"
