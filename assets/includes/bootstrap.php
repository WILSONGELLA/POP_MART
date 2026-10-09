<?php
session_start();
require __DIR__.'/helpers.php';
$base = require __DIR__.'/products.php';

/* ----------(walay database) ---------- */
const DB_DIR = __DIR__.'/../data';
const DB_FILE = DB_DIR.'/store.json';
$DB = dbload();


$products = $base;
foreach ($DB['listings'] as $l) $products[$l['id']] = $l;


$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = &$_SESSION['cart'];
$user = me();
$series  = array_values(array_unique(array_column($products,'series')));
$wishIds = $user ? ($DB['wish'][$user['email']] ?? []) : [];
