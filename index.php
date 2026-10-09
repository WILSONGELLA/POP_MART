<?php
session_start();
$base = is_file(__DIR__.'/data.php')
    ? require __DIR__.'/data.php'
    : require __DIR__.'/assets/includes/products.php';

/* ---------- storage (JSON file, no database) ---------- */
const DB_DIR = __DIR__.'/data', DB_FILE = DB_DIR.'/store.json';
function dbload(){ if(!is_dir(DB_DIR)){ @mkdir(DB_DIR,0775,true); @file_put_contents(DB_DIR.'/.htaccess',"Require all denied\n"); }
  $d = is_file(DB_FILE) ? json_decode(file_get_contents(DB_FILE),true) : [];
  return ($d ?: []) + ['users'=>[],'listings'=>[],'orders'=>[],'wish'=>[],'sales'=>[]]; }
function dbsave($d){ file_put_contents(DB_FILE, json_encode($d, JSON_PRETTY_PRINT), LOCK_EX); }
$DB = dbload();

/* ---------- helpers ---------- */
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function firstCharacter($s){ $s=(string)$s; if(function_exists('mb_substr')) return mb_substr($s,0,1,'UTF-8'); return preg_match('/^./us',$s,$m) ? $m[0] : ''; }
function money($n){ return '₱'.number_format($n); }
function go($u){ header("Location: $u"); exit; }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function me(){ global $DB; return $DB['users'][$_SESSION['uid'] ?? ''] ?? null; }
function flash($m=null){ if($m!==null){ $_SESSION['err']=$m; return; } $x=$_SESSION['err']??''; unset($_SESSION['err']); return $x; }

$products = $base;
foreach ($DB['listings'] as $l) $products[$l['id']] = $l;           // user-listed products
$_SESSION['cart'] = $_SESSION['cart'] ?? [];
$cart = &$_SESSION['cart'];
$page = $_GET['page'] ?? 'home';
$user = me();
if (!$user && !in_array($page, ['login', 'register'], true)) go('?page=register');
function cartCount(){ return array_sum($GLOBALS['cart']); }
function cartTotal(){ $t=0; foreach($GLOBALS['cart'] as $id=>$q) $t += ($GLOBALS['products'][$id]['price'] ?? 0) * $q; return $t; }

/* ---------- actions ---------- */
if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) { http_response_code(403); exit('Invalid request'); }
  $id = (int)($_POST['id'] ?? 0); $act = $_POST['action'] ?? ''; $u = me();
  if (!$u && !in_array($act, ['register', 'login'], true)) go('?page=register');

  if ($act==='register') {
    $n=trim($_POST['name']??''); $m=strtolower(trim($_POST['email']??'')); $pw=$_POST['password']??'';
    if ($n==='' || !filter_var($m,FILTER_VALIDATE_EMAIL) || strlen($pw)<6) { flash('Name, a valid email and a 6+ character password, please.'); go('?page=register'); }
    if (isset($DB['users'][$m])) { flash('That email already has an account. Log in instead.'); go('?page=login'); }
    $DB['users'][$m]=['name'=>$n,'email'=>$m,'hash'=>password_hash($pw,PASSWORD_DEFAULT),'joined'=>time()]; dbsave($DB);
    session_regenerate_id(true); $_SESSION['uid']=$m; go('?');
  }
  if ($act==='login') {
    $m=strtolower(trim($_POST['email']??'')); $usr=$DB['users'][$m]??null;
    if (!$usr || !password_verify($_POST['password']??'',$usr['hash'])) { flash('Wrong email or password.'); go('?page=login'); }
    session_regenerate_id(true); $_SESSION['uid']=$m; go('?');
  }
  if ($act==='logout') { unset($_SESSION['uid']); go('?'); }

  if ($act==='add' && isset($products[$id])) { $cart[$id] = min(10, ($cart[$id] ?? 0) + max(1,(int)($_POST['qty'] ?? 1))); go('?page=cart'); }
  if ($act==='update' && isset($cart[$id])) { $q=(int)$_POST['qty']; if($q<=0) unset($cart[$id]); else $cart[$id]=min(10,$q); go('?page=cart'); }
  if ($act==='remove') { unset($cart[$id]); go('?page=cart'); }

  if ($act==='wish') { if(!$u) go('?page=login'); $w=&$DB['wish'][$u['email']]; $w=$w??[];
    if (in_array($id,$w)) $w=array_values(array_diff($w,[$id])); elseif(isset($products[$id])) $w[]=$id;
    dbsave($DB); go($_POST['back'] ?? '?page=wishlist'); }

  if ($act==='sell') { if(!$u) go('?page=login');
    $n=trim($_POST['name']??''); $pr=(int)($_POST['price']??0);
    if ($n==='' || $pr<=0) { flash('Give your product a name and a price above zero.'); go('?page=sell'); }
    $bgs=['#ffe600','#ff9ecb','#9ee7ff','#b6ff9e','#ffb36b','#d6b3ff'];
    $nid = max(1000, ...array_merge([999], array_column($DB['listings'],'id'))) + 1;
    $DB['listings'][]=['id'=>$nid,'name'=>$n,'image'=>trim($_POST['image']??''),'series'=>strtoupper(trim($_POST['series']??'')) ?: 'MARKET','price'=>$pr,
      'bg'=>$bgs[array_rand($bgs)],'tag'=>'MARKET','desc'=>trim($_POST['desc']??'') ?: 'Listed by a community seller.','seller'=>$u['email']];
    dbsave($DB); flash('Listed! Your product is live in the shop.'); go('?page=sales'); }
  if ($act==='unlist' && $u) { $DB['listings']=array_values(array_filter($DB['listings'],fn($l)=>!($l['id']==$id && $l['seller']===$u['email']))); dbsave($DB); go('?page=sales'); }

  if ($act==='checkout' && $cart) {
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $addr=trim($_POST['address']??'');
    if ($name==='' || !filter_var($email,FILTER_VALIDATE_EMAIL) || $addr==='') { flash('Please fill in all fields with a valid email.'); go('?page=checkout'); }
    $items=[]; foreach($cart as $i=>$q){ $p=$products[$i]??null; if(!$p) continue;
      $items[]=['id'=>$i,'name'=>$p['name'],'price'=>$p['price'],'qty'=>$q];
      if(!empty($p['seller'])) $DB['sales'][]=['seller'=>$p['seller'],'pid'=>$i,'name'=>$p['name'],'qty'=>$q,'amount'=>$p['price']*$q,'ts'=>time()]; }
    $o=['no'=>'RB'.strtoupper(substr(uniqid(),-7)),'name'=>$name,'email'=>$email,'address'=>$addr,'total'=>cartTotal(),'items'=>$items,'ts'=>time(),'user'=>$u['email']??null];
    $DB['orders'][]=$o; dbsave($DB); $_SESSION['order']=$o; $cart=[]; go('?page=success');
  }
  go('?');
}

if (in_array($page,['login','register']) && $user) go('?');
$series = array_values(array_unique(array_column($products,'series')));
$wishIds = $user ? ($DB['wish'][$user['email']] ?? []) : [];

function pimg($p,$cls=''){ $src=$p['image'] ?? ''; $ok = preg_match('#^https?://#',$src) || (!empty($src) && is_file(__DIR__.'/'.ltrim($src,'/')));
  if ($ok) return '<img src="'.e($src).'" alt="'.e($p['name']).'" loading="lazy" class="absolute inset-0 h-full w-full object-cover '.$cls.'">';
  $label = e($p['name'] ?? 'Product image unavailable');
  $mark = e(strtoupper(firstCharacter($p['series'] ?? 'POP')));
  return '<div role="img" aria-label="Photo unavailable: '.$label.'" class="absolute inset-0 grid place-items-center gap-3 p-4 text-center '.$cls.'"><span class="display grid h-20 w-20 place-items-center rounded-full border-[3px] border-black bg-[#ffe600] text-4xl shadow-[4px_4px_0_#000]">'.$mark.'</span><span class="border-2 border-black bg-white px-2 py-1 text-[10px] font-black uppercase">Photo coming soon</span></div>';
}
function card($id,$p){ ob_start(); $tc=['HOT'=>'bg-red-600 text-white','NEW'=>'bg-lime-400 text-black','LIMITED'=>'bg-black text-[#ffe600]','MARKET'=>'bg-cyan-300 text-black']; ?>
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
$btn='inline-flex items-center justify-center gap-2 border-[3px] border-black bg-[#ffe600] px-6 py-3 font-black uppercase text-black shadow-[4px_4px_0_#000] transition hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000] active:translate-x-1 active:translate-y-1 active:shadow-none';
$btn2='inline-flex items-center justify-center gap-2 border-[3px] border-black bg-white px-6 py-3 font-black uppercase text-black shadow-[4px_4px_0_#000] transition hover:translate-x-[2px] hover:translate-y-[2px] hover:shadow-[2px_2px_0_#000]';
$inp='w-full border-[3px] border-black bg-white px-4 py-3 font-semibold outline-none transition focus:bg-[#fff7c2] focus:shadow-[4px_4px_0_#000]';
$h2='display mb-6 mt-14 text-2xl uppercase sm:text-3xl';
$box='border-[3px] border-black bg-white shadow-[6px_6px_0_#000]';
$link='block border-b-[3px] border-black px-5 py-4 font-black uppercase transition hover:bg-[#ffe600]';
function field($t,$n,$ph,$extra=''){ return '<input type="'.$t.'" name="'.$n.'" placeholder="'.$ph.'" '.$extra.' class="'.$GLOBALS['inp'].'">'; }
require_once __DIR__.'/data/pages/partials/chrome.php';
?><!doctype html>
<html lang="en" class="scroll-smooth"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ROCK BOTTOM — Pop Mart Clone</title>
<link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="assets/style.css">
<script>tailwind.config={theme:{extend:{keyframes:{marquee:{to:{transform:'translateX(-50%)'}},wiggle:{'0%,100%':{transform:'rotate(-4deg)'},'50%':{transform:'rotate(4deg)'}}},animation:{marquee:'marquee 20s linear infinite',wiggle:'wiggle 1.2s ease-in-out infinite'}}}}</script>
</head>
<body class="font-sans text-black antialiased">

<?php renderTopbar(); ?>

<!-- DRAWER (pure CSS toggle) -->
<input type="checkbox" id="drawer" class="peer hidden">
<label for="drawer" class="fixed inset-0 z-[60] hidden bg-black/60 peer-checked:block"></label>
<aside class="fixed left-0 top-0 z-[70] flex h-full w-80 max-w-[88vw] -translate-x-full flex-col overflow-y-auto border-r-[4px] border-black bg-[#fff7c2] transition-transform duration-200 peer-checked:translate-x-0" aria-label="Profile">
  <div class="flex items-center justify-between border-b-[3px] border-black bg-[#ffe600] p-4"><span class="display text-xl uppercase">Profile</span>
    <label for="drawer" class="grid h-9 w-9 cursor-pointer place-items-center border-[3px] border-black bg-white font-black hover:bg-black hover:text-white">✕</label></div>
  <?php if($user): ?>
    <div class="m-5 flex items-center gap-4 <?=$box?> p-4">
      <div class="display grid h-14 w-14 shrink-0 place-items-center border-[3px] border-black bg-red-600 text-2xl text-white"><?=e(strtoupper(firstCharacter($user['name'])))?></div>
      <div class="min-w-0"><p class="text-[10px] font-black uppercase tracking-widest text-gray-500">Account info</p>
        <p class="truncate font-black"><?=e($user['name'])?></p><p class="truncate text-sm font-semibold text-gray-600"><?=e($user['email'])?></p></div></div>
    <nav class="mx-5 border-[3px] border-b-0 border-black bg-white">
      <a class="<?=$link?>" href="?page=orders">📦 Order history</a><a class="<?=$link?>" href="?page=wishlist">♥ Wishlist (<?=count($wishIds)?>)</a>
      <a class="<?=$link?>" href="?page=sell">🏷 Sell a product</a><a class="<?=$link?>" href="?page=sales">📈 My sales chart</a></nav>
    <form method="post" class="m-5 mt-auto"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="logout">
      <button class="<?=$btn?> w-full !bg-black !text-[#ffe600]">Log out</button></form>
  <?php else: ?>
    <p class="display m-5 mb-3 text-2xl uppercase leading-tight">Hey, stranger!<br><span class="text-red-600">Sign in for deals.</span></p>
    <div class="mx-5 grid gap-3"><a href="?page=login" class="<?=$btn?>">Log in</a><a href="?page=register" class="<?=$btn2?>">Sign up</a></div>
    <nav class="m-5 border-[3px] border-b-0 border-black bg-white"><a class="<?=$link?>" href="?page=sell">🏷 Want to sell a product?</a><a class="<?=$link?>" href="?page=login">📦 Order history</a><a class="<?=$link?>" href="?page=login">♥ Wishlist</a></nav>
  <?php endif; ?>
</aside>

<header class="sticky top-0 z-50 border-b-[4px] border-black bg-[#ffe600] peer-checked:z-40">
  <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-3 px-4 py-3 sm:px-6">
    <label for="drawer" class="grid h-12 w-12 cursor-pointer place-items-center border-[3px] border-black bg-white shadow-[3px_3px_0_#000] transition hover:bg-red-600 hover:text-white" aria-label="Open profile" title="Profile">
      <?php if($user): ?><span class="display text-lg"><?=e(strtoupper(firstCharacter($user['name'])))?></span>
      <?php else: ?><svg viewBox="0 0 24 24" class="h-6 w-6 fill-none stroke-current stroke-[2.5]"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/></svg><?php endif; ?></label>
    <a href="?" class="display text-2xl uppercase leading-none">ROCK<span class="ml-1 inline-block -rotate-2 border-[3px] border-black bg-red-600 px-2 text-white">BOTTOM</span></a>
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

<main class="mx-auto min-h-[70vh] max-w-7xl px-4 py-8 sm:px-6">
<?php if ($msg = flash()): $good = str_starts_with($msg,'Listed'); ?><p class="mb-6 border-[3px] border-black p-4 font-black uppercase shadow-[4px_4px_0_#000] <?=$good?'bg-lime-300':'bg-red-600 text-white'?>"><?=e($msg)?></p><?php endif; ?>

<?php if ($page==='home'): ?>
  <section class="relative overflow-hidden border-[4px] border-black bg-red-600 p-8 text-[#ffe600] shadow-[10px_10px_0_#000] sm:p-14">
    <div class="relative grid items-center gap-8 md:grid-cols-2">
      <div><span class="inline-block -rotate-2 border-[3px] border-black bg-[#ffe600] px-4 py-1 text-xs font-black uppercase tracking-widest text-black">New drop · Blind box</span>
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

<?php elseif ($page==='login' || $page==='register'): $reg=$page==='register'; ?>
  <div class="mx-auto max-w-md"><form method="post" class="<?=$box?> space-y-4 p-6 sm:p-8">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="<?=$page?>">
    <h1 class="display text-3xl uppercase"><?=$reg?'Create account':'Welcome back'?></h1>
    <p class="text-sm font-semibold text-gray-600"><?=$reg?'Takes ten seconds. Saves your orders, wishlist and sales.':'Log in to see orders, wishlist and your sales.'?></p>
    <?php if($reg) echo field('text','name','Full name','required'); ?>
    <?=field('email','email','Email address','required')?><?=field('password','password',$reg?'Password (6+ characters)':'Password','required')?>
    <button class="<?=$btn?> w-full"><?=$reg?'Sign up':'Log in'?></button>
    <p class="text-center text-sm font-bold"><?=$reg?'Already have an account?':'No account yet?'?> <a class="underline decoration-4 decoration-red-600" href="?page=<?=$reg?'login':'register'?>"><?=$reg?'Log in':'Create one'?></a></p>
  </form></div>

<?php elseif ($page==='orders'): $my=array_reverse(array_filter($DB['orders'],fn($o)=>($o['user']??null)===$user['email'])); ?>
  <h1 class="display text-3xl uppercase">Order history</h1>
  <?php if(!$my): ?><div class="py-20 text-center font-bold text-gray-600">No orders yet.<br><a href="?page=shop" class="<?=$btn?> mt-5">Start shopping</a></div><?php endif; ?>
  <div class="mt-6 space-y-5"><?php foreach($my as $o): ?>
    <div class="<?=$box?> p-5"><div class="flex flex-wrap justify-between gap-2"><p class="display text-lg"><?=e($o['no'])?></p><p class="text-sm font-bold text-gray-600"><?=date('M j, Y · g:i A',$o['ts'])?></p></div>
      <ul class="mt-3 divide-y-2 divide-black/10 text-sm font-semibold"><?php foreach($o['items'] as $it): ?><li class="flex justify-between py-2"><span><?=e($it['name'])?> × <?=$it['qty']?></span><span><?=money($it['price']*$it['qty'])?></span></li><?php endforeach; ?></ul>
      <p class="mt-3 text-right text-xl font-black">Total <span class="text-red-600"><?=money($o['total'])?></span></p></div><?php endforeach; ?></div>

<?php elseif ($page==='wishlist'): ?>
  <h1 class="display text-3xl uppercase">Wishlist <span class="text-base text-gray-500">(<?=count($wishIds)?>)</span></h1>
  <?php if(!$wishIds): ?><div class="py-20 text-center font-bold text-gray-600">Nothing saved yet. Tap ♥ on a product.<br><a href="?page=shop" class="<?=$btn?> mt-5">Browse shop</a></div><?php endif; ?>
  <div class="mt-8 grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php foreach($wishIds as $i) if(isset($products[$i])) echo card($i,$products[$i]); ?></div>

<?php elseif ($page==='sell'): ?>
  <div class="mx-auto max-w-xl"><h1 class="display text-3xl uppercase">Sell a product</h1>
  <form method="post" class="<?=$box?> mt-6 space-y-4 p-6 sm:p-8"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="sell">
    <?=field('text','name','Product name','required maxlength="60"')?>
    <div class="grid gap-4 sm:grid-cols-2"><input name="series" list="sl" placeholder="Series (e.g. MOLLY)" class="<?=$inp?>"><datalist id="sl"><?php foreach($series as $s) echo '<option value="'.e($s).'">'; ?></datalist>
      <?=field('number','price','Price in ₱','required min="1"')?></div>
    <?=field('url','image','Image URL (optional)')?>
    <textarea name="desc" rows="3" placeholder="Describe it — condition, size, edition…" class="<?=$inp?>"></textarea>
    <button class="<?=$btn?> w-full">List it</button></form></div>

<?php elseif ($page==='sales'):
  $r = in_array($_GET['r']??'',['week','month','year']) ? $_GET['r'] : 'week'; $bk=[];
  if($r==='year') for($i=11;$i>=0;$i--){ $t=strtotime("first day of -$i month"); $bk[date('Y-m',$t)]=['l'=>date('M',$t),'v'=>0]; }
  else { $n=$r==='week'?7:30; for($i=$n-1;$i>=0;$i--){ $t=strtotime("-$i day"); $bk[date('Y-m-d',$t)]=['l'=>$r==='week'?date('D',$t):date('j',$t),'v'=>0]; } }
  $units=0; $cnt=0; $rev=0; $per=[];
  foreach($DB['sales'] as $s) if($s['seller']===$user['email']){
    $k=date($r==='year'?'Y-m':'Y-m-d',$s['ts']);
    if(isset($bk[$k])){ $bk[$k]['v']+=$s['amount']; $rev+=$s['amount']; $units+=$s['qty']; $cnt++; $per[$s['name']]=($per[$s['name']]??0)+$s['amount']; } }
  $max=max(1,...array_column($bk,'v')); arsort($per);
  $mine=array_filter($DB['listings'],fn($l)=>$l['seller']===$user['email']); ?>
  <div class="flex flex-wrap items-center justify-between gap-4"><h1 class="display text-3xl uppercase">My sales</h1><a href="?page=sell" class="<?=$btn?>">+ Sell a product</a></div>
  <div class="mt-6 flex gap-2"><?php foreach(['week'=>'1 Week','month'=>'Month','year'=>'Year'] as $k=>$l): ?>
    <a href="?page=sales&r=<?=$k?>" class="border-[3px] border-black px-5 py-2 text-sm font-black uppercase shadow-[3px_3px_0_#000] <?=$k===$r?'bg-black text-[#ffe600]':'bg-white hover:bg-[#ffe600]'?>"><?=$l?></a><?php endforeach; ?></div>
  <div class="mt-6 grid gap-4 sm:grid-cols-3"><?php foreach([['Revenue',money($rev)],['Units sold',$units],['Orders',$cnt]] as $c): ?>
    <div class="<?=$box?> p-5"><p class="text-xs font-black uppercase tracking-widest text-gray-500"><?=$c[0]?></p><p class="display mt-1 text-3xl text-red-600"><?=$c[1]?></p></div><?php endforeach; ?></div>
  <div class="<?=$box?> mt-6 p-5"><p class="font-black uppercase">Revenue · <?=['week'=>'last 7 days','month'=>'last 30 days','year'=>'last 12 months'][$r]?></p>
    <div class="mt-6 flex h-64 items-end gap-1 border-b-[3px] border-l-[3px] border-black pl-1 sm:gap-2">
      <?php $i=0; foreach($bk as $b): $h=$b['v']/$max*100; ?>
      <div class="group relative flex h-full flex-1 flex-col justify-end" title="<?=e($b['l'].': '.money($b['v']))?>">
        <?php if($b['v']): ?><span class="mb-1 hidden text-center text-[10px] font-black group-hover:block"><?=money($b['v'])?></span><?php endif; ?>
        <div class="bar border-[2px] border-black bg-red-600 transition group-hover:bg-[#ffe600]" style="height:<?=max($b['v']?3:1,$h)?>%"></div></div><?php endforeach; ?></div>
    <div class="mt-2 flex gap-1 pl-1 text-center text-[10px] font-black uppercase text-gray-600 sm:gap-2"><?php $i=0; $n=count($bk); foreach($bk as $b): $i++; ?>
      <span class="flex-1"><?= ($r!=='month' || $i%5===0 || $i===1) ? e($b['l']) : '' ?></span><?php endforeach; ?></div>
    <?php if(!$rev) echo '<p class="mt-4 text-sm font-semibold text-gray-500">No sales in this period yet — sales appear here when someone checks out with your product.</p>'; ?></div>
  <div class="mt-8 grid gap-6 md:grid-cols-2">
    <div class="<?=$box?> p-5"><p class="font-black uppercase">Top products</p>
      <?php foreach(array_slice($per,0,5,true) as $n=>$v) echo '<div class="mt-3 flex justify-between border-b-2 border-black/10 pb-2 text-sm font-bold"><span>'.e($n).'</span><span class="text-red-600">'.money($v).'</span></div>'; if(!$per) echo '<p class="mt-3 text-sm text-gray-500">Nothing yet.</p>'; ?></div>
    <div class="<?=$box?> p-5"><p class="font-black uppercase">My listings (<?=count($mine)?>)</p>
      <?php foreach($mine as $l): ?><div class="mt-3 flex items-center justify-between gap-2 border-b-2 border-black/10 pb-2 text-sm font-bold"><a class="truncate hover:underline" href="?page=product&id=<?=$l['id']?>"><?=e($l['name'])?> · <?=money($l['price'])?></a>
        <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="unlist"><input type="hidden" name="id" value="<?=$l['id']?>"><button class="border-2 border-black px-2 py-1 text-xs hover:bg-red-600 hover:text-white">Remove</button></form></div><?php endforeach; if(!$mine) echo '<p class="mt-3 text-sm text-gray-500">You have not listed anything.</p>'; ?></div></div>

<?php elseif ($page==='shop'):
  $list=$products; $q=strtolower(trim($_GET['q']??'')); $s=$_GET['series']??''; $t=$_GET['tag']??''; $sort=$_GET['sort']??'';
  $list=array_filter($list,fn($p)=>($q===''||str_contains(strtolower($p['name'].' '.$p['series']),$q))&&($s===''||$p['series']===$s)&&($t===''||$p['tag']===$t));
  if($sort==='low') uasort($list,fn($a,$b)=>$a['price']<=>$b['price']);
  if($sort==='high') uasort($list,fn($a,$b)=>$b['price']<=>$a['price']);
  if($sort==='new') $list=array_reverse($list,true); ?>
  <h1 class="display text-3xl uppercase"><?= $s?e($s):'All products' ?> <span class="text-base text-gray-500">(<?=count($list)?> items)</span></h1>
  <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2">
      <a href="?page=shop" class="border-[3px] border-black px-4 py-1.5 text-sm font-black uppercase hover:bg-black hover:text-[#ffe600] <?=!$s?'bg-black text-[#ffe600]':'bg-white'?>">All</a>
      <?php foreach($series as $x):?><a href="?page=shop&series=<?=urlencode($x)?>" class="border-[3px] border-black px-4 py-1.5 text-sm font-black uppercase transition hover:bg-black hover:text-[#ffe600] <?=$x===$s?'bg-black text-[#ffe600]':'bg-white'?>"><?=e($x)?></a><?php endforeach;?></div>
    <div class="flex gap-2 text-sm font-black uppercase"><a class="border-[3px] border-black bg-white px-4 py-1.5 hover:bg-[#ffe600]" href="?page=shop&sort=low">Price ↑</a><a class="border-[3px] border-black bg-white px-4 py-1.5 hover:bg-[#ffe600]" href="?page=shop&sort=high">Price ↓</a></div></div>
  <div class="mt-8 grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php foreach($list as $id=>$p) echo card($id,$p);?></div>
  <?php if(!$list) echo '<div class="py-20 text-center font-bold text-gray-600">No products found.</div>'; ?>

<?php elseif ($page==='product'): $id=(int)($_GET['id']??0); if(!isset($products[$id])){echo '<p class="font-bold">Product not found.</p>';} else { $p=$products[$id]; $inW=in_array($id,$wishIds); ?>
  <a href="?page=shop" class="text-sm font-black uppercase hover:underline">← Back to shop</a>
  <div class="mt-5 grid gap-10 md:grid-cols-2">
    <div class="relative grid aspect-square place-items-center overflow-hidden border-[4px] border-black shadow-[10px_10px_0_#000]" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></div>
    <div class="flex flex-col justify-center">
      <p class="text-xs font-black uppercase tracking-widest text-gray-500"><?=e($p['series'])?></p>
      <h1 class="display mt-2 text-3xl uppercase sm:text-4xl"><?=e($p['name'])?></h1>
      <p class="display mt-5 inline-block w-fit -rotate-2 border-[3px] border-black bg-red-600 px-4 py-1 text-4xl text-white shadow-[4px_4px_0_#000]"><?=money($p['price'])?></p>
      <p class="mt-5 font-semibold leading-relaxed text-gray-700"><?=e($p['desc'])?></p>
      <?php if(!empty($p['seller'])): ?><p class="mt-2 text-xs font-bold uppercase text-gray-500">Sold by <?=e($DB['users'][$p['seller']]['name'] ?? 'a community seller')?></p><?php endif; ?>
      <div class="mt-5 flex flex-wrap gap-2 text-xs font-black uppercase"><span class="border-2 border-black bg-lime-300 px-3 py-1">In stock</span><span class="border-2 border-black bg-white px-3 py-1">Ships in 24h</span><span class="border-2 border-black bg-white px-3 py-1">Authentic</span></div>
      <div class="mt-8 flex flex-wrap items-center gap-4">
        <form method="post" class="flex flex-wrap items-center gap-4"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="add"><input type="hidden" name="id" value="<?=$id?>">
          <input type="number" name="qty" value="1" min="1" max="10" class="w-24 border-[3px] border-black px-4 py-3 text-center font-black outline-none focus:bg-[#fff7c2]">
          <button class="<?=$btn?>">Add to cart</button></form>
        <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="wish"><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="back" value="?page=product&id=<?=$id?>">
          <button class="<?=$btn2?> <?=$inW?'!bg-red-600 !text-white':''?>" aria-label="Toggle wishlist"><?=$inW?'♥ Saved':'♡ Wishlist'?></button></form></div></div></div>
  <h2 class="<?=$h2?>">You may also like</h2>
  <div class="grid grid-cols-2 gap-5 sm:gap-6 lg:grid-cols-4"><?php $n=0; foreach($products as $i=>$x){ if($i!=$id && $x['series']===$p['series'] && $n++<4) echo card($i,$x);} ?></div>
<?php } ?>

<?php elseif ($page==='cart'): ?>
  <h1 class="display text-3xl uppercase">Shopping cart</h1>
  <?php if(!$cart): ?><div class="py-24 text-center font-bold text-gray-600">Your cart is empty.<br><a href="?page=shop" class="<?=$btn?> mt-6">Start shopping</a></div>
  <?php else: ?>
  <div class="mt-8 grid gap-8 lg:grid-cols-3"><div class="space-y-4 lg:col-span-2">
    <?php foreach($cart as $id=>$q): if(!isset($products[$id])){ unset($cart[$id]); continue; } $p=$products[$id]; ?>
    <div class="<?=$box?> flex items-center gap-4 p-4">
      <a href="?page=product&id=<?=$id?>" class="relative grid h-24 w-24 shrink-0 place-items-center overflow-hidden border-[3px] border-black" style="background:<?=e($p['bg'])?>"><?=pimg($p)?></a>
      <div class="min-w-0 flex-1"><p class="text-[11px] font-black uppercase tracking-widest text-gray-500"><?=e($p['series'])?></p><h3 class="truncate font-extrabold uppercase"><?=e($p['name'])?></h3><p class="font-black text-red-600"><?=money($p['price']*$q)?></p>
        <form method="post" class="mt-2 flex items-center gap-2"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?=$id?>">
          <input type="number" name="qty" value="<?=$q?>" min="0" max="10" class="w-16 border-[3px] border-black px-3 py-1 text-center text-sm font-black"><button class="border-[3px] border-black bg-white px-3 py-1 text-xs font-black uppercase hover:bg-black hover:text-[#ffe600]">Update</button></form></div>
      <form method="post"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="<?=$id?>"><button class="border-[3px] border-black px-3 py-2 text-xs font-black uppercase transition hover:bg-red-600 hover:text-white">Remove</button></form>
    </div><?php endforeach;?></div>
    <aside class="<?=$box?> h-fit p-6 lg:sticky lg:top-28">
      <h2 class="display text-lg uppercase">Order summary</h2>
      <div class="mt-4 flex justify-between text-sm font-bold text-gray-600"><span>Items</span><span><?=cartCount()?></span></div>
      <div class="mt-2 flex justify-between text-sm font-bold text-gray-600"><span>Shipping</span><span><?=cartTotal()>=2000?'Free':'₱99'?></span></div>
      <div class="my-4 border-t-[3px] border-dashed border-black"></div>
      <div class="flex justify-between text-xl font-black"><span>Total</span><span class="text-red-600"><?=money(cartTotal()+(cartTotal()>=2000?0:99))?></span></div>
      <a href="?page=checkout" class="<?=$btn?> mt-6 w-full">Checkout →</a></aside></div><?php endif; ?>

<?php elseif ($page==='checkout'): if(!$cart) go('?page=cart'); ?>
  <h1 class="display text-3xl uppercase">Checkout</h1>
  <form method="post" class="<?=$box?> mx-auto mt-8 max-w-xl space-y-4 p-6 sm:p-8">
    <input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="action" value="checkout">
    <?=field('text','name','Full name','required value="'.e($user['name']??'').'"')?><?=field('email','email','Email address','required value="'.e($user['email']??'').'"')?>
    <textarea name="address" rows="3" placeholder="Shipping address" required class="<?=$inp?>"></textarea>
    <div class="flex justify-between border-[3px] border-black bg-[#ffe600] p-4 text-sm font-black uppercase"><span>Cash on delivery (demo)</span><span><?=money(cartTotal())?></span></div>
    <?php if(!$user): ?><p class="text-xs font-bold text-gray-600"><a class="underline" href="?page=login">Log in</a> to keep this order in your history.</p><?php endif; ?>
    <button class="<?=$btn?> w-full">Place order</button></form>

<?php elseif ($page==='success'): $o=$_SESSION['order']??null; if(!$o) go('?'); ?>
  <div class="<?=$box?> mx-auto max-w-lg p-10 text-center shadow-[10px_10px_0_#000]">
    <h1 class="display text-3xl uppercase">Thank you, <?=e($o['name'])?>!</h1>
    <p class="mt-3 font-semibold text-gray-700">Order <b class="text-red-600"><?=e($o['no'])?></b> is confirmed.<br>Total paid on delivery: <b><?=money($o['total'])?></b></p>
    <p class="mt-1 text-xs text-gray-500">Confirmation would be sent to <?=e($o['email'])?></p><a href="?" class="<?=$btn?> mt-8">Back to home</a></div>
<?php else: echo '<p class="font-bold">Page not found.</p>'; endif; ?>
</main>
<?php renderFooter(); ?>
