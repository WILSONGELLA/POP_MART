<?php

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) { http_response_code(403); exit('Invalid request'); }
  $id = (int)($_POST['id'] ?? 0); $act = $_POST['action'] ?? ''; $u = me();

  if (!$u && !in_array($act,['login','register'],true)) go('?page=login');

  foreach (['auth','cart','market','checkout'] as $f) require __DIR__."/actions/$f.php";
  go('?');
}
