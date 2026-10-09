<?php
if ($act==='wish') {
  $w=&$DB['wish'][$u['email']]; $w=$w??[];
  if (in_array($id,$w)) $w=array_values(array_diff($w,[$id])); elseif(isset($products[$id])) $w[]=$id;
  dbsave($DB); go($_POST['back'] ?? '?page=wishlist');
}
if ($act==='sell') {
  $n=trim($_POST['name']??''); $pr=(int)($_POST['price']??0);
  if ($n==='' || $pr<=0) { flash('Give your product a name and a price above zero.'); go('?page=sell'); }
  $bgs=['#ffe600','#ff9ecb','#9ee7ff','#b6ff9e','#ffb36b','#d6b3ff'];
  $nid = max(1000, ...array_merge([999], array_column($DB['listings'],'id'))) + 1;
  $DB['listings'][]=['id'=>$nid,'name'=>$n,'image'=>trim($_POST['image']??''),'series'=>strtoupper(trim($_POST['series']??'')) ?: 'MARKET','price'=>$pr,
    'bg'=>$bgs[array_rand($bgs)],'tag'=>'MARKET','desc'=>trim($_POST['desc']??'') ?: 'Listed by a community seller.','seller'=>$u['email']];
  dbsave($DB); flash('Listed! Your product is live in the shop.'); go('?page=sales');
}
if ($act==='unlist') {
  $DB['listings']=array_values(array_filter($DB['listings'],fn($l)=>!($l['id']==$id && $l['seller']===$u['email'])));
  dbsave($DB); go('?page=sales');
}
