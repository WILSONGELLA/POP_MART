
<input type="checkbox" id="drawer" class="peer hidden">
<label for="drawer" class="fixed inset-0 z-[60] hidden bg-black/60 peer-checked:block"></label>
<aside class="fixed left-0 top-0 z-[70] flex h-full w-80 max-w-[88vw] -translate-x-full flex-col overflow-y-auto border-r-[4px] border-black bg-[#fff7c2] transition-transform duration-200 peer-checked:translate-x-0" aria-label="Profile">
  <div class="flex items-center justify-between border-b-[3px] border-black bg-[#ffe600] p-4"><span class="display text-xl uppercase">Profile</span>
    <label for="drawer" class="grid h-9 w-9 cursor-pointer place-items-center border-[3px] border-black bg-white font-black hover:bg-black hover:text-white">✕</label></div>
  <div class="m-5 flex items-center gap-4 <?=$box?> p-4">
    <div class="display grid h-14 w-14 shrink-0 place-items-center border-[3px] border-black bg-red-600 text-2xl text-white"><?=e(strtoupper(mb_substr($user['name'],0,1)))?></div>
    <div class="min-w-0"><p class="text-[10px] font-black uppercase tracking-widest text-gray-500">Account info</p>
      <p class="truncate font-black"><?=e($user['name'])?></p><p class="truncate text-sm font-semibold text-gray-600"><?=e($user['email'])?></p></div></div>
  <nav class="mx-5 border-[3px] border-b-0 border-black bg-white">
    <a class="<?=$link?>" href="?page=orders">📦 Order history</a><a class="<?=$link?>" href="?page=wishlist">♥ Wishlist (<?=count($wishIds)?>)</a>
    <a class="<?=$link?>" href="?page=sell">🏷 Sell a product</a><a class="<?=$link?>" href="?page=sales">📈 My sales chart</a></nav>
  <form method="post" class="m-5 mt-auto"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="logout">
    <button class="<?=$btn?> w-full !bg-black !text-[#ffe600]">Log out</button></form>
</aside>
