<?php
function dbload(){
  if(!is_dir(DB_DIR)){ @mkdir(DB_DIR,0775,true); @file_put_contents(DB_DIR.'/.htaccess',"Require all denied\n"); }
  $d = is_file(DB_FILE) ? json_decode(file_get_contents(DB_FILE),true) : [];
  return ($d ?: []) + ['users'=>[],'listings'=>[],'orders'=>[],'wish'=>[],'sales'=>[]];
}
function dbsave($d){ file_put_contents(DB_FILE, json_encode($d, JSON_PRETTY_PRINT), LOCK_EX); }

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return '₱'.number_format($n); }
function go($u){ header("Location: $u"); exit; }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function me(){ global $DB; return $DB['users'][$_SESSION['uid'] ?? ''] ?? null; }
function flash($m=null){ if($m!==null){ $_SESSION['err']=$m; return; } $x=$_SESSION['err']??''; unset($_SESSION['err']); return $x; }

function cartCount(){ return array_sum($GLOBALS['cart']); }
function cartTotal(){ $t=0; foreach($GLOBALS['cart'] as $id=>$q) $t += ($GLOBALS['products'][$id]['price'] ?? 0) * $q; return $t; }
