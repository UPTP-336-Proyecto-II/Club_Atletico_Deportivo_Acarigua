<?php
// Script to extract base64 logo from the old repository and save it as an actual PNG image

$oldRepoLogoPath = 'C:\\Users\\usuario\\Documents\\Programación\\Club-Atletico-Deportivo-Acarigua\\src\\utils\\logoBase64.js';
$newRepoLogoPath = 'C:\\Users\\usuario\\Documents\\Programación\\Club_Atletico_Deportivo_Acarigua\\public\\assets\\img\\logo.png';

if (!file_exists($oldRepoLogoPath)) {
    die("Old logo file not found at: $oldRepoLogoPath\n");
}

echo "Reading old logoBase64.js...\n";
$content = file_get_contents($oldRepoLogoPath);

// The file format is: export const LOGO_BASE64 = 'data:image/png;base64,...';
if (preg_match("/'data:image\/png;base64,([^']+)'/", $content, $matches)) {
    $base64Data = $matches[1];
    echo "Base64 data found. Decoding...\n";
    $imageData = base64_decode($base64Data);
    
    if ($imageData === false) {
        die("Error decoding base64 data.\n");
    }
    
    // Ensure public/assets/img/ directory exists
    $dir = dirname($newRepoLogoPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    if (file_put_contents($newRepoLogoPath, $imageData)) {
        echo "Successfully saved logo image to: $newRepoLogoPath\n";
    } else {
        echo "Failed to save logo image.\n";
    }
} else {
    echo "Could not match base64 pattern in file.\n";
}
