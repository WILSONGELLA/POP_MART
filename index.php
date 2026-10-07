<?php
session_start();
$products = require __DIR__.'/data.php';
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = &$_SESSION['cart'];

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n){ return '₱'.number_format($n); }
function go($u){ header("Location: $u"); exit; }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function cartCount(){ return array_sum($GLOBALS['cart']); }
function cartTotal(){ $t=0; foreach($GLOBALS['cart'] as $id=>$q) $t+=$GLOBALS['products'][$id]['price']*$q; return $t; }

// ---------- Back-end actions (POST only, CSRF-protected) ----------
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) { http_response_code(403); exit('Invalid request'); }
  $id = (int)($_POST['id'] ?? 0);
  $act = $_POST['action'] ?? '';
  if ($act==='add' && isset($products[$id])) { $cart[$id] = min(10, ($cart[$id] ?? 0) + max(1,(int)($_POST['qty'] ?? 1))); go('?page=cart'); }
  if ($act==='update' && isset($cart[$id])) { $q=(int)$_POST['qty']; if($q<=0) unset($cart[$id]); else $cart[$id]=min(10,$q); go('?page=cart'); }
  if ($act==='remove') { unset($cart[$id]); go('?page=cart'); }
  if ($act==='checkout' && $cart) {
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $addr=trim($_POST['address']??'');
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $addr==='') { $_SESSION['err']='Please fill in all fields with a valid email.'; go('?page=checkout'); }
    $_SESSION['order'] = ['no'=>'PM'.strtoupper(substr(uniqid(),-7)),'name'=>$name,'email'=>$email,'address'=>$addr,'total'=>cartTotal(),'items'=>$cart];
    $cart = []; go('?page=success');
  }
  go('?');
}

$page = $_GET['page'] ?? 'home';
$series = array_values(array_unique(array_column($products,'series')));


function pimg($p,$cls=''){ $src=$p['image'] ?? ''; $path=__DIR__.'/'.ltrim($src,'/'); return !empty($src) && is_file($path) ? '<img src="'.e($src).'" alt="'.e($p['name']).'" loading="lazy" class="absolute inset-0 h-full w-full object-cover '.$cls.'">' : ''; }
function card($id,$p){ ob_start(); $tc=['HOT'=>'bg-red-500','NEW'=>'bg-emerald-500','LIMITED'=>'bg-violet-600']; ?>
  <a href="?page=product&id=<?=$id?>" class="group block overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-black/5 transition duration-300 hover:-translate-y-2 hover:shadow-2xl">
    <div class="relative grid aspect-square place-items-center" style="background:<?=e($p['bg'])?>">
      <?=pimg($p,'transition duration-500 group-hover:scale-110')?>
      <?php if($p['tag']):?><span class="absolute left-3 top-3 rounded-full px-3 py-1 text-[11px] font-bold tracking-wide text-white <?=$tc[$p['tag']]??'bg-black'?>"><?=e($p['tag'])?></span><?php endif;?>

      <span class="absolute bottom-3 right-3 translate-y-4 rounded-full bg-black px-4 py-2 text-xs font-semibold text-white opacity-0 transition group-hover:translate-y-0 group-hover:opacity-100">View →</span>
    </div>
    <div class="p-4"><p class="text-[11px] font-semibold uppercase tracking-widest text-gray-400"><?=e($p['series'])?></p>
      <h3 class="mt-1 line-clamp-1 font-bold text-gray-900"><?=e($p['name'])?></h3>
      <p class="mt-2 text-lg font-extrabold text-red-600"><?=money($p['price'])?></p></div>
  </a>
<?php return ob_get_clean(); }
$btn='inline-flex items-center justify-center gap-2 rounded-full bg-gradient-to-r from-red-600 to-rose-500 px-8 py-3 font-bold text-white shadow-lg shadow-red-500/30 transition hover:scale-105 hover:shadow-xl active:scale-95';
$inp='w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 outline-none transition focus:border-red-500 focus:ring-4 focus:ring-red-100';
$h2='mb-6 mt-14 text-2xl font-extrabold tracking-tight sm:text-3xl';
?><!doctype html>
<html lang="en" class="scroll-smooth"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>POP MART Clone</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="assets/style.css">
<script>tailwind.config={theme:{extend:{keyframes:{float:{'0%,100%':{transform:'translateY(0) rotate(-4deg)'},'50%':{transform:'translateY(-18px) rotate(4deg)'}},marquee:{to:{transform:'translateX(-50%)'}}},animation:{float:'float 4s ease-in-out infinite',marquee:'marquee 25s linear infinite'}}}}</script>
</head>
<body class="bg-gradient-to-b from-rose-50/60 via-white to-white font-sans text-gray-900 antialiased">

<div class="overflow-hidden bg-black py-2 text-xs font-medium text-white"><div class="flex w-max animate-marquee gap-12 whitespace-nowrap"></div></div>


<header class="sticky top-0 z-50 border-b border-black/5 bg-white/80 backdrop-blur-xl">
  <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-3 sm:px-6">
    <a href="?" class="text-2xl font-black tracking-tight">POP<span class="ml-1 rounded-lg bg-red-600 px-2 text-white">MART</span></a>
    <nav class="order-last flex w-full gap-5 overflow-x-auto text-sm font-semibold text-gray-600 lg:order-none lg:w-auto lg:flex-1">
      <a class="whitespace-nowrap hover:text-red-600" href="?page=shop">All</a>
      <a class="whitespace-nowrap hover:text-red-600" href="?page=shop&sort=new">New</a>
      <a class="whitespace-nowrap hover:text-red-600" href="?page=shop&tag=HOT">Hot</a>
      <?php foreach($series as $s):?><a class="whitespace-nowrap hover:text-red-600" href="?page=shop&series=<?=urlencode($s)?>"><?=e($s)?></a><?php endforeach;?>
    </nav>
    <form action="" method="get" class="ml-auto"><input type="hidden" name="page" value="shop">
      <input name="q" value="<?=e($_GET['q']??'')?>" placeholder="Search figures…" class="w-40 rounded-full bg-gray-100 px-4 py-2 text-sm outline-none transition focus:w-60 focus:bg-white focus:ring-2 focus:ring-red-400 sm:w-52"></form>
    <a href="?page=cart" class="relative grid h-11 w-11 place-items-center rounded-full bg-gray-100 transition hover:bg-red-100" aria-label="Shopping cart">
      <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current stroke-2" aria-hidden="true"><path d="M3 4h2l2.2 9.2A2 2 0 0 0 9.1 15h7.8a2 2 0 0 0 2-1.6L20 7H6"/><circle cx="10" cy="18" r="1.5"/><circle cx="17" cy="18" r="1.5"/></svg>
      <?php if(cartCount()):?><span class="absolute -right-1 -top-1 grid h-5 min-w-5 place-items-center rounded-full bg-red-600 px-1 text-[11px] font-bold text-white"><?=cartCount()?></span><?php endif;?></a>
  </div>
</header>

<main class="mx-auto min-h-[70vh] max-w-7xl px-4 py-8 sm:px-6">
<?php if ($page==='home'): ?>
  <section class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-rose-400 via-fuchsia-500 to-indigo-500 p-8 text-white shadow-2xl sm:p-14">
    <div class="absolute -right-10 -top-10 h-72 w-72 rounded-full bg-white/20 blur-3xl"></div>
    <div class="relative grid items-center gap-8 md:grid-cols-2">
      <div><span class="rounded-full bg-white/20 px-4 py-1 text-xs font-bold uppercase tracking-widest backdrop-blur">New Drop · Blind Box</span>
        <h1 class="mt-5 text-4xl font-black leading-tight sm:text-6xl">Unbox the<br>unexpected</h1>
        <p class="mt-4 max-w-md text-white/90">Collect Labubu, Molly, Skullpanda, Dimoo and more. Which secret one will you get?</p>
        <a href="?page=shop" class="mt-7 inline-block rounded-full bg-white px-8 py-3 font-bold text-rose-600 shadow-xl transition hover:scale-105">Shop Now →</a></div>
      <div class="relative hidden h-72 md:block">
        <div class="absolute inset-0 rounded-3xl bg-white/10"></div>
      </div>
    </div>
  </section>

  <div class="mt-8 grid grid-cols-1 gap-4 text-sm font-semibold sm:grid-cols-3">
    <?php foreach([['Fast Delivery','Ships within 24 hours'],['Secure Checkout','Your data stays safe'],['100% Authentic','Official collectibles']] as $f):?>
    <div class="flex items-center gap-4 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-black/5"><div><p><?=$f[0]?></p><p class="text-xs font-normal text-gray-500"><?=$f[1]?></p></div></div><?php endforeach;?></div>

  <h2 class="<?=$h2?>">Shop by Series</h2>
  <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
    <?php foreach($series as $s):?>
    <a href="?page=shop&series=<?=urlencode($s)?>" class="group rounded-3xl bg-white p-5 text-center shadow-sm ring-1 ring-black/5 transition hover:-translate-y-1 hover:bg-gradient-to-br hover:from-rose-500 hover:to-fuchsia-500 hover:text-white hover:shadow-xl">
      <p class="mt-2 text-xs font-bold tracking-wide"><?=e($s)?></p></a><?php endforeach;?></div>

  <h2 class="<?=$h2?>">Trending Now</h2>
  <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"><?php foreach($products as $id=>$p) if(in_array($p['tag'],['HOT','NEW','LIMITED'])) echo card($id,$p);?></div>

<?php elseif ($page==='shop'):
  $list=$products; $q=strtolower(trim($_GET['q']??'')); $s=$_GET['series']??''; $t=$_GET['tag']??''; $sort=$_GET['sort']??'';
  $list=array_filter($list,fn($p)=>($q===''||str_contains(strtolower($p['name'].' '.$p['series']),$q))&&($s===''||$p['series']===$s)&&($t===''||$p['tag']===$t));
  if($sort==='low') uasort($list,fn($a,$b)=>$a['price']<=>$b['price']);
  if($sort==='high') uasort($list,fn($a,$b)=>$b['price']<=>$a['price']);
  if($sort==='new') $list=array_reverse($list,true); ?>
  <h1 class="text-3xl font-black"><?= $s?e($s):'All Products' ?> <span class="text-base font-medium text-gray-400">(<?=count($list)?> items)</span></h1>
  <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
      <a href="?page=shop" class="rounded-full px-4 py-1.5 text-sm font-semibold ring-1 ring-gray-200 hover:bg-black hover:text-white <?=!$s?'bg-black text-white':'bg-white'?>">All</a>
      <?php foreach($series as $x):?><a href="?page=shop&series=<?=urlencode($x)?>" class="rounded-full px-4 py-1.5 text-sm font-semibold ring-1 ring-gray-200 transition hover:bg-black hover:text-white <?=$x===$s?'bg-black text-white':'bg-white'?>"><?=e($x)?></a><?php endforeach;?></div>
    <div class="flex gap-2 text-sm"><a class="rounded-full bg-white px-4 py-1.5 ring-1 ring-gray-200 hover:ring-red-400" href="?page=shop&sort=low">Price ↑</a><a class="rounded-full bg-white px-4 py-1.5 ring-1 ring-gray-200 hover:ring-red-400" href="?page=shop&sort=high">Price ↓</a></div></div>
  <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"><?php foreach($list as $id=>$p) echo card($id,$p);?></div>
  <?php if(!$list) echo '<div class="py-20 text-center"><p class="mt-3 text-gray-500">No products found.</p></div>'; ?>

<?php elseif ($page==='product'): $id=(int)($_GET['id']??0); if(!isset($products[$id])){echo '<p>Product not found.</p>';} else { $p=$products[$id]; ?>
  <a href="?page=shop" class="text-sm font-semibold text-gray-500 hover:text-red-600">← Back to shop</a>
  <div class="mt-5 grid gap-10 md:grid-cols-2">
    <div class="relative grid aspect-square place-items-center overflow-hidden rounded-[2.5rem] shadow-xl" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></div>
    <div class="flex flex-col justify-center">
      <p class="text-xs font-bold uppercase tracking-widest text-gray-400"><?=e($p['series'])?></p>
      <h1 class="mt-2 text-3xl font-black sm:text-4xl"><?=e($p['name'])?></h1>
      <p class="mt-4 text-4xl font-black text-red-600"><?=money($p['price'])?></p>
      <p class="mt-5 leading-relaxed text-gray-600"><?=e($p['desc'])?></p>
      <div class="mt-5 flex flex-wrap gap-2 text-xs font-semibold"><span class="rounded-full bg-emerald-50 px-3 py-1 text-emerald-700">In stock</span><span class="rounded-full bg-gray-100 px-3 py-1">Ships in 24h</span><span class="rounded-full bg-gray-100 px-3 py-1">Authentic</span></div>
      <form method="post" class="mt-8 flex flex-wrap items-center gap-4"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?=$id?>">
        <input type="number" name="qty" value="1" min="1" max="10" class="w-24 rounded-full border border-gray-200 px-4 py-3 text-center font-bold outline-none focus:ring-4 focus:ring-red-100">
        <button class="<?=$btn?>">Add to Cart</button></form></div></div>
  <h2 class="<?=$h2?>">You may also like</h2>
  <div class="grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"><?php $n=0; foreach($products as $i=>$x){ if($i!=$id && $x['series']===$p['series'] && $n++<4) echo card($i,$x);} ?></div>
<?php } ?>

<?php elseif ($page==='cart'): ?>
  <h1 class="text-3xl font-black">Shopping Cart</h1>
  <?php if(!$cart): ?><div class="py-24 text-center"><p class="mt-4 text-gray-500">Your cart is empty.</p><a href="?page=shop" class="<?=$btn?> mt-6">Start Shopping</a></div>
  <?php else: ?>
  <div class="mt-8 grid gap-8 lg:grid-cols-3"><div class="space-y-4 lg:col-span-2">
    <?php foreach($cart as $id=>$q): $p=$products[$id]; ?>
    <div class="flex items-center gap-4 rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5">
      <a href="?page=product&id=<?=$id?>" class="relative grid h-24 w-24 shrink-0 place-items-center overflow-hidden rounded-2xl" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></a>
      <div class="min-w-0 flex-1"><p class="text-[11px] font-bold uppercase tracking-widest text-gray-400"><?=e($p['series'])?></p><h3 class="truncate font-bold"><?=e($p['name'])?></h3><p class="font-extrabold text-red-600"><?=money($p['price']*$q)?></p>
        <form method="post" class="mt-2 flex items-center gap-2"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$id?>">
          <input type="number" name="qty" value="<?=$q?>" min="0" max="10" class="w-16 rounded-full border px-3 py-1 text-center text-sm"><button class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold hover:bg-black hover:text-white">Update</button></form></div>
      <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?=$id?>"><button class="grid h-9 w-9 place-items-center rounded-full text-gray-400 transition hover:bg-red-50 hover:text-red-600">Remove</button></form>
    </div><?php endforeach;?></div>
    <aside class="h-fit rounded-3xl bg-white p-6 shadow-lg ring-1 ring-black/5 lg:sticky lg:top-24">
      <h2 class="text-lg font-extrabold">Order Summary</h2>
      <div class="mt-4 flex justify-between text-sm text-gray-500"><span>Items</span><span><?=cartCount()?></span></div>
      <div class="mt-2 flex justify-between text-sm text-gray-500"><span>Shipping</span><span><?=cartTotal()>=2000?'Free':'₱99'?></span></div>
      <div class="my-4 border-t border-dashed"></div>
      <div class="flex justify-between text-xl font-black"><span>Total</span><span class="text-red-600"><?=money(cartTotal()+(cartTotal()>=2000?0:99))?></span></div>
      <a href="?page=checkout" class="<?=$btn?> mt-6 w-full">Checkout →</a></aside></div><?php endif; ?>

<?php elseif ($page==='checkout'): if(!$cart) go('?page=cart'); ?>
  <h1 class="text-3xl font-black">Checkout</h1>
  <?php if(!empty($_SESSION['err'])){ echo '<p class="mt-4 rounded-2xl bg-red-50 p-4 text-sm font-semibold text-red-600">'.e($_SESSION['err']).'</p>'; unset($_SESSION['err']); } ?>
  <form method="post" class="mx-auto mt-8 max-w-xl space-y-4 rounded-3xl bg-white p-6 shadow-lg ring-1 ring-black/5 sm:p-8">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="checkout">
    <input name="name" placeholder="Full name" required class="<?=$inp?>"><input name="email" type="email" placeholder="Email address" required class="<?=$inp?>">
    <textarea name="address" rows="3" placeholder="Shipping address" required class="<?=$inp?>"></textarea>
    <div class="flex justify-between rounded-2xl bg-rose-50 p-4 text-sm font-semibold"><span>Cash on delivery (demo)</span><span class="text-red-600"><?=money(cartTotal())?></span></div>
    <button class="<?=$btn?> w-full">Place Order</button></form>

<?php elseif ($page==='success'): $o=$_SESSION['order']??null; if(!$o) go('?'); ?>
  <div class="mx-auto max-w-lg rounded-[2.5rem] bg-white p-10 text-center shadow-2xl ring-1 ring-black/5">
    <h1 class="mt-4 text-3xl font-black">Thank you, <?=e($o['name'])?>!</h1>
    <p class="mt-3 text-gray-600">Order <b class="text-red-600"><?=e($o['no'])?></b> is confirmed.<br>Total paid on delivery: <b><?=money($o['total'])?></b></p>
    <p class="mt-1 text-xs text-gray-400">Confirmation would be sent to <?=e($o['email'])?></p><a href="?" class="<?=$btn?> mt-8">Back to Home</a></div>
<?php else: echo '<p>Page not found.</p>'; endif; ?>
</main>
</body></html>
