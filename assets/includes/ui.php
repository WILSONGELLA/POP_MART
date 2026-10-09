<?php

$btn  = 'inline-flex items-center justify-center gap-2 border-[3px] border-black bg-[#ffe600] px-6 py-3 font-black uppercase text-black shadow-[4px_4px_0_#000] transition hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none';
$btn2 = 'inline-flex items-center justify-center gap-2 border-[3px] border-black bg-white px-6 py-3 font-black uppercase text-black shadow-[4px_4px_0_#000] transition hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000]';
$inp  = 'w-full border-[3px] border-black bg-white px-4 py-3 font-semibold outline-none transition focus:bg-[#fff7c2] focus:shadow-[4px_4px_0_#000]';
$h2   = 'display mb-6 mt-14 text-2xl uppercase sm:text-3xl';
$box  = 'border-[3px] border-black bg-white shadow-[6px_6px_0_#000]';
$link = 'block border-b-[3px] border-black px-5 py-4 font-black uppercase transition hover:bg-[#ffe600]';

function field($t,$n,$ph,$extra=''){ return '<input type="'.$t.'" name="'.$n.'" placeholder="'.$ph.'" '.$extra.' class="'.$GLOBALS['inp'].'">'; }

function pimg($p,$cls=''){
  $src=$p['image'] ?? '';
  $ok = preg_match('#^https?://#',$src) || (!empty($src) && is_file(__DIR__.'/../'.ltrim($src,'/')));
  return $ok ? '<img src="'.e($src).'" alt="'.e($p['name']).'" loading="lazy" class="absolute inset-0 h-full w-full object-cover '.$cls.'">' : '';
}

function card($id,$p){
  ob_start(); $tc=['HOT'=>'bg-red-600 text-white','NEW'=>'bg-lime-400 text-black','LIMITED'=>'bg-black text-[#ffe600]','MARKET'=>'bg-cyan-300 text-black']; ?>
  <a href="?page=product&id=<?=$id?>" class="group block border-[3px] border-black bg-white shadow-[5px_5px_0_#000] transition hover:-translate-x-1 hover:-translate-y-1 hover:shadow-[9px_9px_0_#000]">
    <div class="relative grid aspect-square place-items-center overflow-hidden border-b-[3px] border-black" style="background:<?=e($p['bg'])?>">
      <?=pimg($p,'transition duration-300 group-hover:scale-105')?>
      <?php if($p['tag']):?><span class="absolute left-0 top-3 border-y-[3px] border-r-[3px] border-black px-3 py-1 text-[11px] font-black uppercase <?=$tc[$p['tag']]??'bg-white'?>"><?=e($p['tag'])?></span><?php endif;?>
    </div>
    <div class="p-4"><p class="text-[11px] font-black uppercase tracking-widest text-gray-500"><?=e($p['series'])?></p>
      <h3 class="mt-1 line-clamp-1 font-extrabold uppercase"><?=e($p['name'])?></h3>
      <p class="display mt-3 inline-block -rotate-2 border-[3px] border-black bg-red-600 px-3 py-1 text-xl text-white"><?=money($p['price'])?></p></div>
  </a>
<?php return ob_get_clean(); }
