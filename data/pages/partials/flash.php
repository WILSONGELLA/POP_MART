<?php if ($msg = flash()): $good = str_starts_with($msg,'Listed'); ?>
<p class="mb-6 border-[3px] border-black p-4 font-black uppercase shadow-[4px_4px_0_#000] <?=$good?'bg-lime-300':'bg-red-600 text-white'?>"><?=e($msg)?></p>
<?php endif; ?>
