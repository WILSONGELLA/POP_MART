<header class="sticky top-0 z-50 border-b-[4px] border-black bg-[#ffe600] peer-checked:z-40">
  <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6">
    <label for="drawer" class="grid h-12 w-12 cursor-pointer place-items-center border-[3px] border-black bg-white shadow-[3px_3px_0_#000] transition hover:bg-red-600 hover:text-white" aria-label="Open profile" title="Profile">
      <span class="display text-lg"><?=e(strtoupper(mb_substr($user['name'],0,1)))?></span></label>
    <a href="?page=home" class="display text-2xl uppercase leading-none">ROCK<span class="ml-1 inline-block -rotate-2 border-[3px] border-black bg-red-600 px-2 text-white">BOTTOM</span></a>
    <nav class="order-last flex w-full gap-5 overflow-x-auto text-sm font-black uppercase lg:order-none lg:w-auto lg:flex-1 lg:pl-4">
      <a class="whitespace-nowrap hover:underline hover:decoration-4" href="?page=shop">All</a>
      <a class="whitespace-nowrap hover:underline hover:decoration-4" href="?page=shop&sort=new">New</a>
      <a class="whitespace-nowrap hover:underline hover:decoration-4" href="?page=shop&tag=HOT">Hot</a>
      <?php foreach($series as $s):?><a class="whitespace-nowrap hover:underline hover:decoration-4" href="?page=shop&series=<?=urlencode($s)?>"><?=e($s)?></a><?php endforeach;?>
    </nav>
    <form action="" method="get" class="ml-auto"><input type="hidden" name="page" value="shop">
      <input name="q" value="<?=e($_GET['q']??'')?>" placeholder="SEARCH…" class="w-36 border-[3px] border-black bg-white px-4 py-2.5 text-sm font-black uppercase outline-none transition focus:w-56 focus:shadow-[3px_3px_0_#000] sm:w-48"></form>
    <a href="?page=cart" class="relative grid h-12 w-12 place-items-center border-[3px] border-black bg-white shadow-[3px_3px_0_#000] transition hover:bg-black hover:text-[#ffe600]" aria-label="Shopping cart">
      <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-[2.5]"><path d="M3 4h2l2.2 9.2A2 2 0 0 0 9.1 15h7.8a2 2 0 0 0 2-1.6L20 7H6"/><circle cx="10" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
      <?php if(cartCount()):?><span class="absolute -right-2 -top-2 grid h-6 min-w-6 place-items-center border-2 border-black bg-red-600 px-1 text-[11px] font-black text-white"><?=cartCount()?></span><?php endif;?></a>
  </div>
</header>
