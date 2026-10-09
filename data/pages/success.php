<?php
$list=$products; $q=strtolower(trim($_GET['q']??'')); $s=$_GET['series']??''; $t=$_GET['tag']??''; $sort=$_GET['sort']??'';
$list=array_filter($list,fn($p)=>($q===''||str_contains(strtolower($p['name'].' '.$p['series']),$q))&&($s===''||$p['series']===$s)&&($t===''||$p['tag']===$t));
if($sort==='low') uasort($list,fn($a,$b)=>$a['price']<=>$b['price']);
if($sort==='high') uasort($list,fn($a,$b)=>$b['price']<=>$a['price']);
if($sort==='new') $list=array_reverse($list,true); ?>
<h1 class="display text-3xl uppercase"><?= $s?e($s):'All products' ?> <span class="text-base text-gray-500">(<?=count($list)?> items)</span></h1>
<div class="mt-5 flex flex-wrap items-center justify-between gap-3">
  <div class="flex flex-wrap gap-2">
    <a href="?page=shop" class="border-[3px] border-black px-4 py-1.5 text-sm font-black uppercase hover:bg-black hover:text-[#ffe600] <?=!$s?'bg-black text-[#ffe600]':'bg-white'?>">All</a>
    <?php foreach($series as $x):?><a href="?page=shop&series=<?=urlencode($x)?>" class="border-[3px] border-black px-4 py-1.5 text-sm font-black uppercase transition hover:bg-black hover:text-[#ffe600] <?=$x===$s?'bg-black text-[#ffe600]':'bg-white'?>"><?=e($x)?></a><?php endforeach;?></div>
  <div class="flex gap-2 text-sm font-black uppercase"><a class="border-[3px] border-black bg-white px-4 py-1.5 hover:bg-[#ffe600]" href="?page=shop&sort=low">Price ↑</a><a class="border-[3px] border-black bg-white px-4 py-1.5 hover:bg-[#ffe600]" href="?page=shop&sort=high">Price ↓</a></div></div>
<div class="mt-8 grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php foreach($list as $id=>$p) echo card($id,$p);?></div>
<?php if(!$list) echo '<div class="py-20 text-center font-bold text-gray-600">No products found.</div>'; ?>
