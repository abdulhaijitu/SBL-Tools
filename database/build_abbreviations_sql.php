<?php
$terms = require __DIR__.'/../config/abbreviations.php';
$sql = "CREATE TABLE IF NOT EXISTS abbreviations (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL UNIQUE, name TEXT NOT NULL, category TEXT NOT NULL, category_slug TEXT NOT NULL, meaning_bn TEXT NOT NULL, description_bn TEXT NOT NULL, icon TEXT NOT NULL DEFAULT '📖', tag TEXT NOT NULL DEFAULT '', created_at TEXT, updated_at TEXT);\n";
foreach ($terms as $term) {
    $values = array_map(fn($value) => "'".str_replace("'", "''", $value)."'", array_values($term));
    $sql .= 'INSERT OR IGNORE INTO abbreviations ('.implode(',', array_keys($term)).') VALUES ('.implode(',', $values).");\n";
}
file_put_contents(__DIR__.'/abbreviations_d1.sql', $sql);
