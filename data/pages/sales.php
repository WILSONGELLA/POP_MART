<?php
$r = in_array($_GET['r']??'',['week','month','year']) ? $_GET['r'] : 'week'; $bk=[];
if($r==='year') for($i=11;$i>=0;$i--){ $t=strtotime("first day of -$i month"); $bk[date('Y-m',$t)]=['l'=>date('M',$t),'v'=>0]; }
else { $n=$r==='week'?7:30; for($i=$n-1;$i>=0;$i--){ $t=strtotime("-$i day"); $bk[date('Y-m-d',$t)]=['l'=>$r==='week'?date('D',$t):date('j',$t),'v'=>0]; } }
$units=0; $cnt=0; $rev=0; $per=[];
foreach($DB['sales'] as $s) if($s['seller']===$user['email']){
  $k=date($r==='year'?'Y-m':'Y-m-d',$s['ts']);
  if(isset($bk[$k])){ $bk[$k]['v']+=$s['amount']; $rev+=$s['amount']; $units+=$s['qty']; $cnt++; $per[$s['name']]=($per[$s['name']]??0)+$s['amount']; } }
$max=max(1,...array_column($bk,'v')); arsort($per);
$mine=array_filter($DB['listings'],fn($l)=>$l['seller']===$user['email']); ?>
<div class="flex flex-wrap items-center justify-between gap-4"><h1 class="display text-3xl uppercase">My sales</h1><a href="?page=sell" class="<?=$btn?>">+ Sell a product</a></div>
<div class="mt-6 flex gap-2"><?php foreach(['week'=>'1 Week','month'=>'Month','year'=>'Year'] as $k=>$l): ?>
  <a href="?page=sales&r=<?=$k?>" class="border-[3px] border-black px-5 py-2 text-sm font-black uppercase shadow-[3px_3px_0_#000] <?=$k===$r?'bg-black text-[#ffe600]':'bg-white hover:bg-[#ffe600]'?>"><?=$l?></a><?php endforeach; ?></div>
<div class="mt-6 grid gap-4 sm:grid-cols-3"><?php foreach([['Revenue',money($rev)],['Units sold',$units],['Orders',$cnt]] as $c): ?>
  <div class="<?=$box?> p-5"><p class="text-xs font-black uppercase tracking-widest text-gray-500"><?=$c[0]?></p><p class="display mt-1 text-3xl text-red-600"><?=$c[1]?></p></div><?php endforeach; ?></div>
<div class="<?=$box?> mt-6 p-5"><p class="font-black uppercase">Revenue · <?=['week'=>'last 7 days','month'=>'last 30 days','year'=>'last 12 months'][$r]?></p>
  <div class="mt-6 flex h-64 items-end gap-1 border-b-[3px] border-l-[3px] border-black pl-1 sm:gap-2">
    <?php foreach($bk as $b): $h=$b['v']/$max*100; ?>
    <div class="group relative flex h-full flex-1 flex-col justify-end" title="<?=e($b['l'].': '.money($b['v']))?>">
      <?php if($b['v']): ?><span class="mb-1 hidden text-center text-[10px] font-black group-hover:block"><?=money($b['v'])?></span><?php endif; ?>
      <div class="bar border-[2px] border-black bg-red-600 transition group-hover:bg-[#ffe600]" style="height:<?=max($b['v']?3:1,$h)?>%"></div></div><?php endforeach; ?></div>
  <div class="mt-2 flex gap-1 pl-1 text-center text-[10px] font-black uppercase text-gray-600 sm:gap-2"><?php $i=0; foreach($bk as $b): $i++; ?>
    <span class="flex-1"><?= ($r!=='month' || $i%5===0 || $i===1) ? e($b['l']) : '' ?></span><?php endforeach; ?></div>
  <?php if(!$rev) echo '<p class="mt-4 text-sm font-semibold text-gray-500">No sales in this period yet — sales appear here when someone checks out with your product.</p>'; ?></div>
<div class="mt-8 grid gap-6 md:grid-cols-2">
  <div class="<?=$box?> p-5"><p class="font-black uppercase">Top products</p>
    <?php foreach(array_slice($per,0,5,true) as $n=>$v) echo '<div class="mt-3 flex justify-between border-b-2 border-black/10 pb-2 text-sm font-bold"><span>'.e($n).'</span><span class="text-red-600">'.money($v).'</span></div>'; if(!$per) echo '<p class="mt-3 text-sm text-gray-500">Nothing yet.</p>'; ?></div>
  <div class="<?=$box?> p-5"><p class="font-black uppercase">My listings (<?=count($mine)?>)</p>
    <?php foreach($mine as $l): ?><div class="mt-3 flex items-center justify-between gap-2 border-b-2 border-black/10 pb-2 text-sm font-bold"><a class="truncate hover:underline" href="?page=product&id=<?=$l['id']?>"><?=e($l['name'])?> · <?=money($l['price'])?></a>
      <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="unlist"><input type="hidden" name="id" value="<?=$l['id']?>"><button class="border-2 border-black px-2 py-1 text-xs hover:bg-red-600 hover:text-white">Remove</button></form></div><?php endforeach; if(!$mine) echo '<p class="mt-3 text-sm text-gray-500">You have not listed anything.</p>'; ?></div></div>
