<section class="relative overflow-hidden border-[4px] border-black bg-red-600 p-8 text-[#ffe600] shadow-[10px_10px_0_#000] sm:p-14">
  <div class="relative grid items-center gap-8 md:grid-cols-2">
    <div><span class="inline-block -rotate-2 border-[3px] border-black bg-[#ffe600] px-4 py-1 text-xs font-black uppercase tracking-widest text-black">Welcome, <?=e(explode(' ',$user['name'])[0])?> · Blind box</span>
      <h1 class="display mt-5 text-5xl uppercase leading-[.95] sm:text-7xl">Prices<br>so low,<br>it hurts.</h1>
      <p class="mt-5 max-w-md font-bold text-white">Labubu, Molly, Skullpanda, Dimoo and more — at rock-bottom prices. Which secret one will you pull?</p>
      <a href="?page=shop" class="<?=$btn?> mt-7">Shop now →</a></div>
    <div class="relative hidden h-72 md:block"><div class="absolute inset-4 grid rotate-3 place-items-center border-[4px] border-black bg-[#ffe600] shadow-[8px_8px_0_#000]">
      <div class="animate-wiggle text-center text-black"><p class="display text-8xl leading-none">-70%</p><p class="display text-2xl uppercase">everything*</p></div></div></div>
  </div>
</section>

<div class="mt-10 grid grid-cols-1 gap-4 text-sm font-black uppercase sm:grid-cols-3">
  <?php foreach([['Fast delivery','Ships within 24 hours'],['Secure checkout','Your data stays safe'],['100% authentic','Official collectibles']] as $f):?>
  <div class="<?=$box?> p-4"><p><?=$f[0]?></p><p class="text-xs font-semibold normal-case text-gray-600"><?=$f[1]?></p></div><?php endforeach;?></div>

<h2 class="<?=$h2?>">Shop by series</h2>
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
  <?php foreach($series as $s):?>
  <a href="?page=shop&series=<?=urlencode($s)?>" class="border-[3px] border-black bg-white p-5 text-center text-xs font-black uppercase shadow-[4px_4px_0_#000] transition hover:-translate-y-1 hover:bg-black hover:text-[#ffe600]"><?=e($s)?></a><?php endforeach;?></div>

<h2 class="<?=$h2?>">Trending now</h2>
<div class="grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php foreach($products as $id=>$p) if(in_array($p['tag'],['HOT','NEW','LIMITED','MARKET'])) echo card($id,$p);?></div>
