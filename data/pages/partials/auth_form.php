<?php /* shared login / register form. Expects $reg (bool). */ ?>
<div class="mx-auto max-w-md py-6">
  <div class="mb-8 text-center">
    <p class="display text-5xl uppercase leading-none sm:text-6xl">ROCK<span class="ml-1 inline-block -rotate-2 border-[3px] border-black bg-red-600 px-2 text-white">BOTTOM</span></p>
    <p class="mt-4 inline-block -rotate-1 border-[3px] border-black bg-[#ffe600] px-3 py-1 text-xs font-black uppercase tracking-widest"><?=$reg?'Join the club · it is free':'Log in to enter the shop'?></p>
  </div>
  <form method="post" class="<?=$box?> space-y-4 p-6 sm:p-8">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="<?=$reg?'register':'login'?>">
    <h1 class="display text-3xl uppercase"><?=$reg?'Create account':'Welcome back'?></h1>
    <p class="text-sm font-semibold text-gray-600"><?=$reg?'Takes ten seconds. After signing up you go straight to the shop.':'Log in to see the landing page, your orders, wishlist and sales.'?></p>
    <?php if($reg) echo field('text','name','Full name','required autocomplete="name"'); ?>
    <?=field('email','email','Email address','required autocomplete="email"')?>
    <?=field('password','password',$reg?'Password (6+ characters)':'Password','required '.($reg?'minlength="6" autocomplete="new-password"':'autocomplete="current-password"'))?>
    <button class="<?=$btn?> w-full"><?=$reg?'Sign up':'Log in'?></button>
    <p class="text-center text-sm font-bold"><?=$reg?'Already have an account?':'No account yet?'?>
      <a class="underline decoration-4 decoration-red-600" href="?page=<?=$reg?'login':'register'?>"><?=$reg?'Log in':'Create one'?></a></p>
  </form>
</div>
