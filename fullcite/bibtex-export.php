<?php
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="fullcite.bib"');

$notes = $GLOBALS['fullcite_notes'] ?? [];

foreach ($notes as $n) {
  $key = strtolower(preg_replace('/[^a-z0-9]/', '', $n['author'])) . $n['date'];
  echo "@misc{{$key},\n";
  echo "  author = {" . $n['author'] . "},\n";
  echo "  title = {" . $n['title'] . "},\n";
  echo "  year = {" . $n['date'] . "},\n";
  echo "  institution = {" . $n['location'] . "},\n";
  if ($n['uri']) echo "  url = {" . $n['uri'] . "},\n";
  echo "}\n\n";
}
