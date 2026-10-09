<?php
if ($act==='add' && isset($products[$id])) { $cart[$id] = min(10, ($cart[$id] ?? 0) + max(1,(int)($_POST['qty'] ?? 1))); go('?page=cart'); }
if ($act==='update' && isset($cart[$id])) { $q=(int)$_POST['qty']; if($q<=0) unset($cart[$id]); else $cart[$id]=min(10,$q); go('?page=cart'); }
if ($act==='remove') { unset($cart[$id]); go('?page=cart'); }
