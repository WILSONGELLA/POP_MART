<?php
if ($act==='checkout' && $cart) {
  $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $addr=trim($_POST['address']??'');
  if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $addr==='') { flash('Please fill in all fields with a valid email.'); go('?page=checkout'); }
  $items=[]; foreach($cart as $i=>$q){ $p=$products[$i]??null; if(!$p) continue;
    $items[]=['id'=>$i,'name'=>$p['name'],'price'=>$p['price'],'qty'=>$q];
    if(!empty($p['seller'])) $DB['sales'][]=['seller'=>$p['seller'],'pid'=>$i,'name'=>$p['name'],'qty'=>$q,'amount'=>$p['price']*$q,'ts'=>time()]; }
  $o=['no'=>'RB'.strtoupper(substr(uniqid(),-7)),'name'=>$name,'email'=>$email,'address'=>$addr,'total'=>cartTotal(),'items'=>$items,'ts'=>time(),'user'=>$u['email']];
  $DB['orders'][]=$o; dbsave($DB); $_SESSION['order']=$o; $cart=[]; go('?page=success');
}
