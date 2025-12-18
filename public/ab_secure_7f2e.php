<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Security Alert</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
html,body{height:100%;margin:0;font-family:'Inter',system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
body{display:flex;align-items:center;justify-content:center;background:radial-gradient(1200px 600px at 20% -20%,#ef4444 10%,#7f1d1d 60%),linear-gradient(180deg,#991b1b 0%,#1f2937 100%);color:#fff}
.wrap{position:relative;max-width:760px;width:92%;padding:2.5rem;border-radius:1.25rem;background:rgba(255,255,255,.08);backdrop-filter:saturate(180%) blur(16px);border:1px solid rgba(255,255,255,.22);box-shadow:0 20px 40px rgba(0,0,0,.35)}
.shield{width:160px;height:160px;margin:0 auto 1.25rem auto;position:relative;filter:drop-shadow(0 6px 24px rgba(0,0,0,.35))}
.badge{position:absolute;inset:0;animation:pulse 2.6s ease-in-out infinite}
.glow{position:absolute;inset:-14px;background:radial-gradient(circle,#ef4444 0%,transparent 60%);opacity:.25;border-radius:50%;animation:flare 3.2s ease-in-out infinite}
.title{font-weight:800;font-size:2rem;letter-spacing:.3px;text-align:center;margin:.25rem 0 0 0}
.sub{font-size:1.05rem;line-height:1.7;text-align:center;opacity:.95;margin:.75rem 0 0 0}
.owner{margin:.5rem 0 0 0;text-align:center;color:#fca5a5;font-weight:600;letter-spacing:.2px}
.note{margin:.4rem 0 0 0;text-align:center;color:#e5e7eb}
.row{display:flex;justify-content:center;gap:.8rem;margin-top:1.25rem}
.btn{appearance:none;border:none;outline:none;padding:.85rem 1.25rem;border-radius:.75rem;background:#dc2626;color:#fff;font-weight:700;letter-spacing:.3px;cursor:pointer;box-shadow:0 8px 18px rgba(0,0,0,.35);transition:transform .2s ease,box-shadow .2s ease}
.btn:hover{transform:translateY(-1px);box-shadow:0 12px 24px rgba(0,0,0,.4)}
.dots{position:absolute;inset:0;pointer-events:none}
.dot{position:absolute;width:8px;height:8px;background:linear-gradient(180deg,#fecaca,#f87171);border-radius:9999px;opacity:.8;animation:rise linear infinite}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
@keyframes flare{0%,100%{opacity:.18}50%{opacity:.32}}
@keyframes rise{0%{transform:translateY(0);}100%{transform:translateY(-120vh);opacity:0}}
</style>
</head>
<body>
<div class="wrap">
<div class="shield">
<svg class="badge" viewBox="0 0 128 128" xmlns="http://www.w3.org/2000/svg">
<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fee2e2"/><stop offset="1" stop-color="#ef4444"/></linearGradient></defs>
<path fill="url(#g)" d="M64 8c18 8 36 8 56 8-2 54-18 80-56 104C26 96 10 70 8 16c20 0 38 0 56-8Z"/>
<path fill="#b91c1c" d="M64 26c-12 6-24 6-38 6 1 36 12 54 38 70 26-16 37-34 38-70-14 0-26 0-38-6Z"/>
<path fill="#fff" d="M64 42c10 0 18 8 18 18 0 10-8 18-18 18s-18-8-18-18c0-10 8-18 18-18Zm-6 46h12v18H58z" opacity=".95"/>
</svg>
<div class="glow"></div>
</div>
<div class="title">System Protection Active</div>
<div class="sub">Suspicious activity was detected. The system has identified a theft or malicious activity.</div>
<div class="owner">
<?php echo htmlspecialchars('Name : Maxwell Mulwa, Phone :+254795331020, Location: Kitui'); ?>
</div>
<div class="note">Make sure you have cleared with the owner.</div>
<div class="row"><button class="btn" onclick="location.href='index.php'">Return Home</button></div>
<div class="dots" id="dots"></div>
</div>
<script>
const c=document.getElementById('dots');for(let i=0;i<36;i++){const d=document.createElement('div');d.className='dot';d.style.left=Math.random()*100+'%';d.style.bottom='-12px';d.style.animationDuration=(8+Math.random()*12)+'s';d.style.animationDelay=(Math.random()*6)+'s';d.style.opacity=(0.5+Math.random()*0.5);d.style.filter='blur('+ (Math.random()*1.5) +'px)';c.appendChild(d)}
</script>
</body>
</html>
