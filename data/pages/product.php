<?php $id=(int)($_GET['id']??0);
if(!isset($products[$id])){ echo '<p class="font-bold">Product not found.</p>'; return; }
$p=$products[$id]; $inW=in_array($id,$wishIds); ?>
<a href="?page=shop" class="text-sm font-black uppercase hover:underline">← Back to shop</a>
<div class="mt-5 grid gap-10 md:grid-cols-2">
  <div class="relative grid aspect-square place-items-center overflow-hidden border-[4px] border-black shadow-[10px_10px_0_#000]" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></div>
  <div class="flex flex-col justify-center">
    <p class="text-xs font-black uppercase tracking-widest text-gray-500"><?=e($p['series'])?></p>
    <h1 class="display mt-2 text-3xl uppercase sm:text-4xl"><?=e($p['name'])?></h1>
    <p class="display mt-5 inline-block w-fit -rotate-2 border-[3px] border-black bg-red-600 px-4 py-1 text-4xl text-white shadow-[4px_4px_0_#000]"><?=money($p['price'])?></p>
    <p class="mt-5 font-semibold leading-relaxed text-gray-700"><?=e($p['desc'])?></p>
    <?php if(!empty($p['seller'])): ?><p class="mt-2 text-xs font-bold uppercase text-gray-500">Sold by <?=e($DB['users'][$p['seller']]['name'] ?? 'a community seller')?></p><?php endif; ?>
    <div class="mt-5 flex flex-wrap gap-2 text-xs font-black uppercase"><span class="border-2 border-black bg-lime-300 px-3 py-1">In stock</span><span class="border-2 border-black bg-white px-3 py-1">Ships in 24h</span><span class="border-2 border-black bg-white px-3 py-1">Authentic</span></div>
    <div class="mt-8 flex flex-wrap items-center gap-4">
      <form method="post" class="flex flex-wrap items-center gap-4"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?=$id?>">
        <input type="number" name="qty" value="1" min="1" max="10" class="w-24 border-[3px] border-black px-4 py-3 text-center font-black outline-none focus:bg-[#fff7c2]">
        <button class="<?=$btn?>">Add to cart</button></form>
      <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="wish"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="back" value="?page=product&id=<?=$id?>">
        <button class="<?=$btn2?> <?=$inW?'!bg-red-600 !text-white':''?>" aria-label="Toggle wishlist"><?=$inW?'♥ Saved':'♡ Wishlist'?></button></form></div></div></div>
<h2 class="<?=$h2?>">You may also like</h2>
<div class="grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php $n=0; foreach($products as $i=>$x){ if($i!=$id && $x['series']===$p['series'] && $n++<4) echo card($i,$x);} ?></div>
