<div class="mx-auto max-w-xl"><h1 class="display text-3xl uppercase">Sell a product</h1>
<form method="post" class="<?=$box?> mt-6 space-y-4 p-6 sm:p-8"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="sell">
  <?=field('text','name','Product name','required maxlength="60"')?>
  <div class="grid gap-4 sm:grid-cols-2"><input name="series" list="sl" placeholder="Series (e.g. MOLLY)" class="<?=$inp?>"><datalist id="sl"><?php foreach($series as $s) echo '<option value="'.e($s).'">'; ?></datalist>
    <?=field('number','price','Price in ₱','required min="1"')?></div>
  <?=field('url','image','Image URL (optional)')?>
  <textarea name="desc" rows="3" placeholder="Describe it — condition, size, edition…" class="<?=$inp?>"></textarea>
  <button class="<?=$btn?> w-full">List it</button></form></div>
