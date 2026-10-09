<?php
if ($act==='register') {
  $n=trim($_POST['name']??''); $m=strtolower(trim($_POST['email']??'')); $pw=$_POST['password']??'';
  if ($n==='' || !filter_var($m,FILTER_VALIDATE_EMAIL) || strlen($pw)<6) { flash('Name, a valid email and a 6+ character password, please.'); go('?page=register'); }
  if (isset($DB['users'][$m])) { flash('That email already has an account. Log in instead.'); go('?page=login'); }
  $DB['users'][$m]=['name'=>$n,'email'=>$m,'hash'=>password_hash($pw,PASSWORD_DEFAULT),'joined'=>time()]; dbsave($DB);
  session_regenerate_id(true); $_SESSION['uid']=$m;
  go('?page=home');                      // account created -> straight to the landing page
}
if ($act==='login') {
  $m=strtolower(trim($_POST['email']??'')); $usr=$DB['users'][$m]??null;
  if (!$usr || !password_verify($_POST['password']??'',$usr['hash'])) { flash('Wrong email or password.'); go('?page=login'); }
  session_regenerate_id(true); $_SESSION['uid']=$m;
  go('?page=home');
}
if ($act==='logout') { unset($_SESSION['uid']); go('?page=login'); }
