<?php $my=array_reverse(array_filter($DB['orders'],fn($o)=>($o['user']??null)===$user['email'])); ?>
<h1 class="display text-3xl uppercase">Order history</h1>
<?php if(!$my): ?><div class="py-20 text-center font-bold text-gray-600">No orders yet.<br><a href="?page=shop" class="<?=$btn?> mt-5">Start shopping</a></div><?php endif; ?>
<div class="mt-6 space-y-5"><?php foreach($my as $o): ?>
  <div class="<?=$box?> p-5"><div class="flex flex-wrap justify-between gap-2"><p class="display text-lg"><?=e($o['no'])?></p><p class="text-sm font-bold text-gray-600"><?=date('M j, Y · g:i A',$o['ts'])?></p></div>
    <ul class="mt-3 divide-y-2 divide-black/10 text-sm font-semibold"><?php foreach($o['items'] as $it): ?><li class="flex justify-between py-2"><span><?=e($it['name'])?> × <?=$it['qty']?></span><span><?=money($it['price']*$it['qty'])?></span></li><?php endforeach; ?></ul>
    <p class="mt-3 text-right text-xl font-black">Total <span class="text-red-600"><?=money($o['total'])?></span></p></div><?php endforeach; ?></div>
