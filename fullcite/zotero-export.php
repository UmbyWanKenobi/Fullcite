<?php
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="fullcite.json"');

$notes = $GLOBALS['fullcite_notes'] ?? [];
$items = [];

foreach ($notes as $n) {
  $items[] = [
    'type' => 'article',
    'title' => $n['title'],
    'author' => [['family' => $n['author']]],
    'issued' => ['date-parts' => [[$n['date']]]],
    'publisher' => $n['location'],
    'URL' => $n['uri']
  ];
}

echo json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
