$path = 'public/plugins/leaflet/leaflet_script.js'
if (Test-Path $path) {
    $content = Get-Content -Raw $path
    $content = $content -replace "https://api\.mapbox\.com/styles/v1/\{id\}/tiles/\{z\}/\{x\}/\{y\}\?access_token=[^']+", 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'
    $content = $content -replace 'https://www\.mapbox\.com/', 'https://www.openstreetmap.org/'
    $content = $content -replace '>Mapbox</a>', '>OpenStreetMap</a>'
    $content = $content -replace "    id: 'mapbox/[^']+',\r?\n", ''
    $content = $content -replace "    tileSize: 512,\r?\n", ''
    $content = $content -replace "    zoomOffset: -1\r?\n", ''
    Set-Content -NoNewline -Path $path -Value $content
}