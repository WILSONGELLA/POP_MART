<?php
function renderTopbar() { ?>
<div class="overflow-hidden border-b-[3px] border-black bg-red-600 py-2 text-sm font-black uppercase text-[#ffe600]"><div class="flex w-max animate-marquee gap-10 whitespace-nowrap">
  <?php for($i=0;$i<16;$i++) echo '<span>★ Rock-bottom prices ★ Free shipping over ₱2,000 ★ 100% authentic</span>'; ?></div></div>
<?php }

function renderFooter() { ?>
<footer class="mt-10 border-t-[4px] border-black bg-black py-6 text-center text-xs font-black uppercase tracking-widest text-[#ffe600]">Rock Bottom · prices can't go lower (we checked)</footer>
</body></html>
<?php }
