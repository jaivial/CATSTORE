<?php
// Script para generar una imagen placeholder de gato
$width = 300;
$height = 300;
$im = imagecreatetruecolor($width, $height);

// Colores
$bg = imagecolorallocate($im, 240, 240, 240);  // Fondo gris claro
$textColor = imagecolorallocate($im, 100, 100, 100);  // Texto gris oscuro
$accentColor = imagecolorallocate($im, 74, 108, 247);  // Azul primario

// Rellenar fondo
imagefilledrectangle($im, 0, 0, $width, $height, $bg);

// Dibujar círculo central
$centerX = $width / 2;
$centerY = $height / 2;
$radius = min($width, $height) / 4;
imagefilledellipse($im, $centerX, $centerY, $radius * 2, $radius * 2, $accentColor);

// Dibujar silueta simple de un gato
$catColor = imagecolorallocate($im, 255, 255, 255);  // Blanco
imagefilledellipse($im, $centerX, $centerY, $radius * 1.5, $radius * 1.5, $catColor);
// Orejas
imagefilledellipse($im, $centerX - $radius / 2, $centerY - $radius / 2, $radius / 2, $radius / 2, $catColor);
imagefilledellipse($im, $centerX + $radius / 2, $centerY - $radius / 2, $radius / 2, $radius / 2, $catColor);

// Texto
$text = "Imagen no disponible";
$font = 5;  // Fuente incorporada
$textWidth = imagefontwidth($font) * strlen($text);
$textHeight = imagefontheight($font);
$textX = ($width - $textWidth) / 2;
$textY = $height - $textHeight * 3;
imagestring($im, $font, $textX, $textY, $text, $textColor);

// Crear directorio si no existe
if (!file_exists('assets/img')) {
    mkdir('assets/img', 0755, true);
}

// Guardar imagen
imagejpeg($im, 'assets/img/cat-placeholder.png', 90);
imagedestroy($im);

echo "Imagen de placeholder creada en assets/img/cat-placeholder.png";
