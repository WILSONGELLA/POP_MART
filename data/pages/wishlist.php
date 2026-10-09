<h1 class="display text-3xl uppercase">Wishlist <span class="text-base text-gray-500">(<?=count($wishIds)?>)</span></h1>
<?php if(!$wishIds): ?><div class="py-20 text-center font-bold text-gray-600">Nothing saved yet. Tap ♥ on a product.<br><a href="?page=shop" class="<?=$btn?> mt-5">Browse shop</a></div><?php endif; ?>
<div class="mt-8 grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php foreach($wishIds as $i) if(isset($products[$i])) echo card($i,$products[$i]); ?></div>
