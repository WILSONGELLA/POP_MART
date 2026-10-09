<h1 class="display text-3xl uppercase">Shopping cart</h1>
<?php if(!$cart): ?><div class="py-24 text-center font-bold text-gray-600">Your cart is empty.<br><a href="?page=shop" class="<?=$btn?> mt-6">Start shopping</a></div>
<?php else: ?>
<div class="mt-8 grid gap-8 lg:grid-cols-3"><div class="space-y-4 lg:col-span-2">
  <?php foreach($cart as $id=>$q): if(!isset($products[$id])){ unset($cart[$id]); continue; } $p=$products[$id]; ?>
  <div class="<?=$box?> flex items-center gap-4 p-4">
    <a href="?page=product&id=<?=$id?>" class="relative grid h-24 w-24 shrink-0 place-items-center overflow-hidden border-[3px] border-black" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></a>
    <div class="min-w-0 flex-1"><p class="text-[11px] font-black uppercase tracking-widest text-gray-500"><?=e($p['series'])?></p><h3 class="truncate font-extrabold uppercase"><?=e($p['name'])?></h3><p class="font-black text-red-600"><?=money($p['price']*$q)?></p>
      <form method="post" class="mt-2 flex items-center gap-2"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$id?>">
        <input type="number" name="qty" value="<?=$q?>" min="0" max="10" class="w-16 border-[3px] border-black px-3 py-1 text-center text-sm font-black"><button class="border-[3px] border-black bg-white px-3 py-1 text-xs font-black uppercase hover:bg-black hover:text-[#ffe600]">Update</button></form></div>
    <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?=$id?>"><button class="border-[3px] border-black px-3 py-2 text-xs font-black uppercase transition hover:bg-red-600 hover:text-white">Remove</button></form>
  </div><?php endforeach;?></div>
  <aside class="<?=$box?> h-fit p-6 lg:sticky lg:top-28">
    <h2 class="display text-lg uppercase">Order summary</h2>
    <div class="mt-4 flex justify-between text-sm font-bold text-gray-600"><span>Items</span><span><?=cartCount()?></span></div>
    <div class="mt-2 flex justify-between text-sm font-bold text-gray-600"><span>Shipping</span><span><?=cartTotal()>=2000?'Free':'₱99'?></span></div>
    <div class="my-4 border-t-[3px] border-dashed border-black"></div>
    <div class="flex justify-between text-xl font-black"><span>Total</span><span class="text-red-600"><?=money(cartTotal()+(cartTotal()>=2000?0:99))?></span></div>
    <a href="?page=checkout" class="<?=$btn?> mt-6 w-full">Checkout →</a></aside></div>
<?php endif; ?>
    