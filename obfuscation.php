<?php
session_start();

$allowed_md5 = '277142a10240cf3dab8859a963ed0ba0';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = $_POST['password'] ?? '';
        if (md5($input) === $allowed_md5) {
            $_SESSION['logged_in'] = true;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Password salah!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <style>
            body { font-family: sans-serif; background: #111; color: #fff; display: flex; justify-content: center; align-items: center; height: 100vh; }
            .login-box { background: #222; padding: 20px 30px; border-radius: 10px; }
            input { width: 100%; padding: 10px; margin-top: 10px; }
            .error { color: red; }
        </style>
    </head>
    <body>
    <div class="login-box">
        <h2>Proteksi Password</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="post">
            <input type="password" name="password" placeholder="Masukkan password">
            <input type="submit" value="Masuk">
        </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!doctype html>
<meta charset="utf-8" />
<title>PHP Obfuscation ↔ Deobfuscation (fixed)</title>
<style>
  body{font:14px/1.4 system-ui,Segoe UI,Arial;margin:16px}
  textarea{width:100%;height:260px;border:1px solid #ccc;border-radius:8px;padding:10px}
  .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
  button{padding:8px 14px;margin:8px 8px 0 0}
</style>

<div class="row">
  <div>
    <h3>Kode Obfuscated (input)</h3>
    <textarea id="inp" placeholder='$z = "";
$z .= "WlhK";
$z .= "eWIz";
...'></textarea>
    <button id="btnDecode">Obfuscated → PHP biasa</button>
    <button id="btnClearIn">Bersihkan Input</button>
  </div>
  <div>
    <h3>Hasil Decode (PHP biasa)</h3>
    <textarea id="out" placeholder="Hasil PHP muncul di sini..."></textarea>
    <button id="btnEncode">PHP biasa → Obfuscated</button>
    <button id="btnClearOut">Bersihkan Output</button>
  </div>
</div>

<script>
/* ==== util UTF-8 safe base64 ==== */
const b64enc = (str) => {
  const bytes = new TextEncoder().encode(str);
  let bin = ""; bytes.forEach(b => bin += String.fromCharCode(b));
  return btoa(bin);
};
const b64dec = (b64) => {
  const bin = atob(b64);
  const bytes = Uint8Array.from(bin, c => c.charCodeAt(0));
  return new TextDecoder().decode(bytes);
};
/* pad + urlsafe fix + strict charset */
function cleanB64(s){
  s = (s||"").replace(/[^A-Za-z0-9+/=_-]/g,"").replace(/-/g,"+").replace(/_/g,"/");
  while (s.length % 4 !== 0) s += "=";
  return s;
}
/* ambil hanya baris `$var .= "....";` */
function collectFragments(src){
  const re = /(^|\n)\s*\$[A-Za-z_]\w*\s*\.\=\s*["']([^"']+)["']\s*;/g;
  let m, parts = [];
  while ((m = re.exec(src)) !== null) {
    const frag = cleanB64(m[2]);
    if (frag) parts.push(frag);
  }
  return parts.join("");
}
/* coba double decode, lalu fallback single */
function decodeSmart(b64joined){
  const j = cleanB64(b64joined);
  let first;
  try { first = b64dec(j); } 
  catch(e){ throw new Error("Decode pertama gagal (data bukan base64 murni)."); }
  try { return b64dec(cleanB64(first)); } 
  catch(e){ return first; } // hanya single layer
}

const $ = (id)=>document.getElementById(id);

$("btnDecode").onclick = () => {
  const src = $("inp").value;
  if(!src.trim()){ alert("Input kosong"); return; }
  const joined = collectFragments(src);
  if(!joined){ alert('Tidak menemukan baris `$x .= "....";`'); return; }
  try {
    let decoded = decodeSmart(joined);
    decoded = decoded.replace(/^\s*<\?php\s*/i,'').replace(/\s*\?>\s*$/,'');
    $("out").value = decoded;
  } catch (e){
    alert("Base64 decode gagal: " + e.message);
  }
};

$("btnEncode").onclick = () => {
  const php = $("out").value || "";
  // buang tag agar tidak dobel
  const body = php.replace(/^\s*<\?php\s*/i,'').replace(/\s*\?>\s*$/,'');
  // double base64
  const twice = b64enc(b64enc(body));
  // pecah per 4 char biar mirip contoh
  const CHUNK = 4;
  let lines = ['$z = "";'];
  for (let i = 0; i < twice.length; i += CHUNK){
    lines.push(`$z .= "${twice.slice(i, i+CHUNK)}";`);
  }
  lines.push('$a="base"; $b="64_decode"; $c=$a.$b;');
  lines.push('$string=$c($z); $string=$c($string);');
  lines.push('$string=preg_replace(\'/^<\\?php\\s*/\',\'\',$string);');
  lines.push('$string=preg_replace(\'/\\s*\\?>$/\',\'\',$string);');
  lines.push('eval($string);');
  $("inp").value = lines.join("\n");
};

$("btnClearIn").onclick = ()=> $("inp").value = "";
$("btnClearOut").onclick = ()=> $("out").value = "";
</script>
