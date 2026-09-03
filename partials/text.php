<?php
/**
 * Loads paragraph text from the /paragraphs folder and safely outputs it.
 * Each .txt file contains one paragraph so page content can be edited
 * without changing the PHP/HTML structure.
 */
function paragraph(string $file): void
{
    $path = __DIR__ . '/../paragraphs/' . basename($file);
    $text = is_file($path) ? trim(file_get_contents($path)) : '';

    if ($text !== '') {
        echo '<p>' . nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')) . '</p>';
    }
}
?>
