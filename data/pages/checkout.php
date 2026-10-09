<?php if(!$cart) go('?page=cart'); ?>
<h1 class="display text-3xl uppercase">Checkout</h1>
<form method="post" class="<?=$box?> mx-auto mt-8 max-w-xl space-y-4 p-6 sm:p-8">
  <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="checkout">
  <?=field('text','name','Full name','required value="'.e($user['name']).'"')?><?=field('email','email','Email address','required value="'.e($user['email']).'"')?>
  <textarea name="address" rows="3" placeholder="Shipping address" required class="<?=$inp?>"></textarea>
  <div class="flex justify-between border-[3px] border-black bg-[#ffe600] p-4 text-sm font-black uppercase"><span>Cash on delivery (demo)</span><span><?=money(cartTotal())?></span></div>
  <button class="<?=$btn?> w-full">Place order</button></form>
