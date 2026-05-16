<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#2d89ef">
<meta name="description" content="Aplikasi analisis produk fast moving dan slow moving menggunakan metode SAW">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="APLIKASI SAW - Fast/Slow Moving">
<link rel="apple-touch-icon" href="logo.png">
<title>APLIKASI SAW - Fast/Slow Moving</title>
<link rel="manifest" href="manifest.json">
<link rel="stylesheet" href="styles.css">
<link rel="icon" href="logo.png" type="image/png">
</head>
<body>
<div id="app">
  <div id="loginView">
    <div class="card" style="max-width:400px;margin:100px auto">
      <div style="text-align:center;margin-bottom:20px">
        <img src="img/logo.png" alt="Logo SAW" style="width:120px;height:auto;margin-bottom:0px">
        <h1 style="margin:4px 0;font-size:24px;color:var(--primary)">SAW</h1>
        <p style="color:var(--muted);font-size:13px;margin:0">Analisis Fast/Slow Moving</p>
      </div>
      
      <div id="loginFormContainer">
        <h2>Login</h2>
        <form id="loginForm">
          <input name="username" placeholder="Username" required><br>
          <input name="password" placeholder="Password" required type="password"><br>
          <div class="actions"><button class="btn" type="submit">Login</button></div>
        </form>
        <div id="loginMsg" style="color:red;margin-top:8px"></div>
        <p style="text-align:center;margin-top:12px">
          Belum punya akun? <a href="#" id="showRegister" style="color:var(--primary)">Daftar di sini</a>
        </p>
      </div>
      
      <div id="registerFormContainer" class="hidden">
        <h2>Registrasi</h2>
        <form id="registerForm">
          <input name="username" placeholder="Username" required><br>
          <input name="password" placeholder="Password" required type="password"><br>
          <input name="confirm_password" placeholder="Konfirmasi Password" required type="password"><br>
          <div class="actions"><button class="btn" type="submit">Daftar</button></div>
        </form>
        <div id="registerMsg" style="margin-top:8px"></div>
        <p style="text-align:center;margin-top:12px">
          Sudah punya akun? <a href="#" id="showLogin" style="color:var(--primary)">Login di sini</a>
        </p>
      </div>
    </div>
  </div>

  <div id="mainView" class="hidden">
    <!-- improved header -->
    <div class="header">
      <div class="brand">
        <div class="logo" style="background: white; padding: 4px; box-shadow: 0 4px 12px rgba(45, 137, 239, 0.15);">
          <img src="img/logo_pwa_2.png" alt="logo_pwa_2" style="width: 50px; height: 50px; object-fit: contain; border-radius: 8px;">
      </div>
        <div>
          <h2>APLIKASI SAW - Fast/Slow Moving</h2>
          <div style="color:var(--muted);font-size:13px">Analisis produk cepat & lambat bergerak</div>
        </div>
      </div>
      <div>
        <button id="btnLogout" class="btn secondary">Logout</button>
      </div>
    </div>

    <nav>
      <button type="button" data-tab="products" class="active">Produk</button>
      <button type="button" data-tab="sales">Penjualan</button>
      <button type="button" data-tab="criteria">Kriteria</button>
      <button type="button" data-tab="values">Nilai Produk</button>
      <button type="button" data-tab="analysis">Analisis</button>
      <button type="button" data-tab="report">Laporan</button>
    </nav>

    <section id="tab-products" class="tab">
      <div class="row">
        <div class="col card">
          <h3 style="margin-top:0">Tambah Produk</h3>
          <form id="formProduct">
            <input name="id" id="productId" type="hidden">
            <input name="kode" id="productKode" placeholder="Kode Produk" required><br>
            <input name="nama" id="productNama" placeholder="Nama Produk" required><br>
            <input name="kategori" id="productKategori" placeholder="Kategori Produk"><br>
            <input name="stok" id="productStok" placeholder="Jumlah Stok (contoh: 100)" type="number"><br>
            <input name="biaya" id="productBiaya" placeholder="Biaya per Cost (Rp)" type="number" step="0.01"><br>
            <input name="harga" id="productHarga" placeholder="Harga Jual (Rp)" type="number" step="0.01"><br>
            <div class="actions">
              <button class="btn" type="submit" id="btnSaveProduct">Tambah Produk</button>
              <button class="btn secondary" type="button" id="btnCancelProduct" style="display:none">Batal</button>
            </div>
          </form>
        </div>
        <div class="col card">
          <h3 style="margin-top:0">Daftar Produk</h3>
          <div id="productsList" class="table-card"></div>
        </div>
      </div>
    </section>

    <section id="tab-sales" class="tab hidden">
      <div class="card">
        <h3 style="margin-top:0">Penjualan</h3>
        <form id="formSale">
          <input name="id" id="saleId" type="hidden">
          <input name="id_penjualan" id="saleIdPenjualan" placeholder="ID Penjualan (auto)" readonly><br>
          <select name="product_id" id="saleProduct" required>
            <option value="">-- Pilih Produk --</option>
          </select><br>
          <input name="qty" id="saleQty" type="number" placeholder="Jumlah Qty Terjual" required><br>
          <input name="harga" id="saleHarga" placeholder="Harga Jual (Rp)" type="number" step="0.01" readonly><br>
          <input name="return_qty" id="saleReturnQty" placeholder="Jumlah Return (jika ada)" type="number"><br>
          <input name="sales_date" id="saleSalesDate" type="date" placeholder="Tanggal Penjualan"><br>
          <div class="actions">
            <button class="btn" type="submit" id="btnSaveSale">Tambah Penjualan</button>
            <button class="btn secondary" type="button" id="btnCancelSale" style="display:none">Batal</button>
          </div>
        </form>
      </div>
      <div class="card">
        <h4 style="margin-top:0">Daftar Penjualan</h4>
        <div id="salesList" class="table-card"></div>
      </div>
    </section>

    <section id="tab-criteria" class="tab hidden">
      <div class="card">
        <h3 style="margin-top:0">Kriteria</h3>
        <form id="formCriteria">
          <input name="id" id="criteriaId" type="hidden">
          <input name="kode" id="criteriaKode" placeholder="Kode (C1)"><br>
          <input name="nama" id="criteriaNama" placeholder="Nama Kriteria"><br>
          <input name="bobot" id="criteriaBobot" placeholder="Bobot (angka)" type="number" step="0.01" value="1"><br>
          <select name="atribut" id="criteriaAtribut">
            <option value="benefit">Benefit</option>
            <option value="cost">Cost</option>
          </select><br>
          <div class="actions">
            <button class="btn" type="submit" id="btnSaveCriteria">Tambah Kriteria</button>
            <button class="btn secondary" type="button" id="btnCancelCriteria" style="display:none">Batal</button>
          </div>
        </form>
      </div>
      <div class="card">
        <h4 style="margin-top:0">Daftar Kriteria</h4>
        <div id="criteriaList" class="table-card"></div>
      </div>
    </section>

    <section id="tab-values" class="tab hidden">
      <div class="card">
        <h3 style="margin-top:0">Nilai Produk per Kriteria</h3>
        <form id="formValue">
          <input name="id" id="valueId" type="hidden">
          <select name="product_id" id="valProduct"></select><br>
          <select name="criteria_id" id="valCriteria"></select><br>
          <input name="nilai" id="valueNilai" type="number" step="0.01" placeholder="Nilai"><br>
          <div class="actions">
            <button class="btn" type="submit" id="btnSaveValue">Simpan Nilai</button>
            <button class="btn secondary" type="button" id="btnCancelValue" style="display:none">Batal</button>
          </div>
        </form>
      </div>
      <div class="card">
        <h4 style="margin-top:0">Daftar Nilai Produk</h4>
        <div id="valuesList" class="table-card"></div>
      </div>
    </section>

    <section id="tab-analysis" class="tab hidden">
      <div class="card">
        <h3 style="margin-top:0">Analisis SAW</h3>
        <p style="color:var(--muted)">Tekan tombol untuk menjalankan proses SAW dan melihat tabel perhitungan serta ranking.</p>
        <button id="runSaw" class="btn">Jalankan Proses</button>
      </div>
      <div id="analysisResult"></div>
    </section>

    <section id="tab-report" class="tab hidden">
      <div class="card">
        <h3 style="margin-top:0">Laporan</h3>
        <p style="color:var(--muted);margin-bottom:12px">Unduh atau cetak laporan</p>
        
        <div style="display:flex;gap:8px;margin-bottom:8px">
          <a href="api.php?action=report_products" target="_blank" class="btn" style="flex:1">📦 Download CSV Produk</a>
          <a href="print.php?type=products" target="_blank" class="btn secondary" style="flex:1">🖨️ Cetak Produk</a>
        </div>
        
        <div style="display:flex;gap:8px;margin-bottom:8px">
          <a href="api.php?action=report_sales" target="_blank" class="btn" style="flex:1">💰 Download CSV Penjualan</a>
          <a href="print.php?type=sales" target="_blank" class="btn secondary" style="flex:1">🖨️ Cetak Penjualan</a>
        </div>
        
        <div style="display:flex;gap:8px">
          <a href="api.php?action=report_analysis" target="_blank" class="btn" style="flex:1">📊 Download CSV Analisis</a>
          <a href="print.php?type=analysis" target="_blank" class="btn secondary" style="flex:1">🖨️ Cetak Analisis</a>
        </div>
      </div>
    </section>
  </div>
</div>

<script>
const api = (action, data)=> {
  let fd = new FormData();
  fd.append('action', action);
  if (data) for (let k in data) fd.append(k, data[k]);
  return fetch('api.php', {method:'POST', body:fd}).then(r=>r.json());
};

document.getElementById('loginForm').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const res = await api('login',{username:f.get('username'),password:f.get('password')});
  if (res.ok) {
    document.getElementById('loginView').classList.add('hidden');
    document.getElementById('mainView').classList.remove('hidden');
    loadAll();
  } else {
    document.getElementById('loginMsg').innerText = res.msg || 'Login gagal';
  }
});

document.getElementById('btnLogout').addEventListener('click', async ()=>{
  await api('logout');
  location.reload();
});

// delegated nav handler: single listener, robust for dynamically added buttons
document.querySelector('nav').addEventListener('click', e => {
  const btn = e.target.closest('button');
  if (!btn || !btn.dataset.tab) return;
  
  // prevent default
  e.preventDefault();
  e.stopPropagation();
  
  // manage active visual
  document.querySelectorAll('nav button').forEach(n => n.classList.remove('active'));
  btn.classList.add('active');

  // show/hide tabs - hide ALL first
  document.querySelectorAll('.tab').forEach(t => t.classList.add('hidden'));
  const target = document.getElementById('tab-' + btn.dataset.tab);
  if (target) {
    target.classList.remove('hidden');
  }

  // call corresponding loader to ensure data refreshed
  const tab = btn.dataset.tab;
  if (tab === 'products') loadProducts().catch(()=>{});
  if (tab === 'sales') { loadSales().catch(()=>{}); loadProductSelects().catch(()=>{}); }
  if (tab === 'criteria') loadCriteria().catch(()=>{});
  if (tab === 'values') { loadValues().catch(()=>{}); loadProductSelects().catch(()=>{}); loadCriteriaSelect().catch(()=>{}); }
  // analysis/report no auto-load (analysis uses Run button)
});

// products
document.getElementById('formProduct').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const id = f.get('id');
  const action = id ? 'update_product' : 'add_product';
  
  await api(action, {
    id: id,
    kode: f.get('kode'),
    nama: f.get('nama'),
    stok: f.get('stok'),
    kategori: f.get('kategori'),
    biaya: f.get('biaya'),
    harga: f.get('harga')
  });
  
  resetProductForm();
  loadProducts(); 
  loadProductSelects();
});

function resetProductForm() {
  const form = document.getElementById('formProduct');
  form.reset();
  document.getElementById('productId').value = '';
  document.getElementById('btnSaveProduct').textContent = 'Tambah Produk';
  document.getElementById('btnCancelProduct').style.display = 'none';
}

function editProduct(id, kode, nama, kategori, stok, biaya, harga) {
  document.getElementById('productId').value = id;
  document.getElementById('productKode').value = kode;
  document.getElementById('productNama').value = nama;
  document.getElementById('productKategori').value = kategori || '';
  document.getElementById('productStok').value = stok || '';
  document.getElementById('productBiaya').value = biaya || '';
  document.getElementById('productHarga').value = harga || '';
  document.getElementById('btnSaveProduct').textContent = 'Update Produk';
  document.getElementById('btnCancelProduct').style.display = 'inline-block';
  
  // scroll to form
  document.getElementById('formProduct').scrollIntoView({ behavior: 'smooth' });
}

async function deleteProduct(id, nama) {
  if (!confirm(`Hapus produk "${nama}"?`)) return;
  await api('delete_product', { id });
  loadProducts();
  loadProductSelects();
}

document.getElementById('btnCancelProduct').addEventListener('click', resetProductForm);

async function loadProducts(){
  const data = await (await fetch('api.php?action=list_products_with_sales')).json();
  let html = '<table><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Stok</th><th>Biaya</th><th>Harga</th><th>Terjual</th><th>Aksi</th></tr>';
  data.forEach(p=> {
    // escape quotes properly
    const kodeEsc = String(p.kode).replace(/'/g, "\\'");
    const namaEsc = String(p.nama).replace(/'/g, "\\'");
    const kategoriEsc = String(p.kategori || '').replace(/'/g, "\\'");
    html += `<tr>
      <td>${p.kode}</td>
      <td>${p.nama}</td>
      <td>${p.kategori||'-'}</td>
      <td>${p.stok}</td>
      <td>${p.biaya||0}</td>
      <td>${p.harga||0}</td>
      <td>${p.terjual||0}</td>
      <td>
        <button class="btn secondary" style="padding:4px 8px;font-size:12px" onclick="editProduct(${p.id},'${kodeEsc}','${namaEsc}','${kategoriEsc}',${p.stok||0},${p.biaya||0},${p.harga||0})">Edit</button>
        <button class="btn" style="padding:4px 8px;font-size:12px;background:#dc3545" onclick="deleteProduct(${p.id},'${namaEsc}')">Hapus</button>
      </td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('productsList').innerHTML = html;
}
async function loadProductSelects(){
  const data = await (await fetch('api.php?action=list_products')).json();
  const saleSel = document.getElementById('saleProduct');
  const valSel = document.getElementById('valProduct');
  saleSel.innerHTML = '<option value="">-- Pilih Produk --</option>';
  valSel.innerHTML = '';
  data.forEach(p=>{
    const o = `<option value="${p.id}" data-kode="${p.kode}" data-nama="${p.nama}" data-harga="${p.harga||0}">${p.kode} - ${p.nama}</option>`;
    saleSel.insertAdjacentHTML('beforeend', o);
    valSel.insertAdjacentHTML('beforeend', `<option value="${p.id}">${p.kode} - ${p.nama}</option>`);
  });
}

// sales
document.getElementById('formSale').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const id = f.get('id');
  const qty = parseFloat(f.get('qty')) || 0;
  const harga = parseFloat(f.get('harga')) || 0;
  const total_harga = (qty * harga).toFixed(2);
  
  const pid = f.get('product_id');
  const products = await (await fetch('api.php?action=list_products')).json();
  const p = products.find(x => x.id == pid);
  const kode_produk = p ? p.kode : '';
  const nama_produk = p ? p.nama : '';
  
  const action = id ? 'update_sale' : 'add_sale';
  
  const res = await api(action, {
    id: id,
    product_id: f.get('product_id'),
    qty: f.get('qty'),
    sales_date: f.get('sales_date'),
    id_penjualan: f.get('id_penjualan'),
    kode_produk: kode_produk,
    nama_produk: nama_produk,
    harga: f.get('harga'),
    return_qty: f.get('return_qty'),
    total_harga: total_harga
  });
  
  if (!res.ok) {
    alert(res.msg || 'Gagal menyimpan penjualan');
    return;
  }
  
  alert(res.msg || 'Penjualan berhasil');
  resetSaleForm();
  loadSales();
  loadProducts();
});

function resetSaleForm() {
  const form = document.getElementById('formSale');
  form.reset();
  document.getElementById('saleId').value = '';
  document.getElementById('saleIdPenjualan').value = 'TRX' + Date.now();
  document.getElementById('btnSaveSale').textContent = 'Tambah Penjualan';
  document.getElementById('btnCancelSale').style.display = 'none';
}

function editSale(id, id_penjualan, product_id, qty, harga, return_qty, sales_date) {
  document.getElementById('saleId').value = id;
  document.getElementById('saleIdPenjualan').value = id_penjualan;
  document.getElementById('saleProduct').value = product_id;
  document.getElementById('saleQty').value = qty;
  document.getElementById('saleHarga').value = harga;
  document.getElementById('saleReturnQty').value = return_qty || '';
  document.getElementById('saleSalesDate').value = sales_date;
  document.getElementById('btnSaveSale').textContent = 'Update Penjualan';
  document.getElementById('btnCancelSale').style.display = 'inline-block';
  
  document.getElementById('formSale').scrollIntoView({ behavior: 'smooth' });
}

async function deleteSale(id, id_penjualan) {
  if (!confirm(`Hapus penjualan "${id_penjualan}"?`)) return;
  await api('delete_sale', { id });
  loadSales();
  loadProducts();
}

document.getElementById('btnCancelSale').addEventListener('click', resetSaleForm);

// auto-generate ID Penjualan on form init
function initSaleForm(){
  document.getElementById('saleIdPenjualan').value = 'TRX' + Date.now();
}

// auto-fill harga when product selected (simplified with data attributes)
document.getElementById('saleProduct').addEventListener('change', e => {
  const opt = e.target.selectedOptions[0];
  if (!opt || !opt.value) {
    document.getElementById('saleHarga').value = '';
    return;
  }
  const harga = opt.getAttribute('data-harga') || 0;
  document.getElementById('saleHarga').value = harga;
});

async function loadSales(){
  const data = await (await fetch('api.php?action=list_sales')).json();
  let html = '<table><tr><th>ID Penjualan</th><th>Produk</th><th>Kode</th><th>Nama</th><th>Qty</th><th>Harga</th><th>Total</th><th>Return</th><th>Date</th><th>Aksi</th></tr>';
  data.forEach(s=> {
    const total = (parseFloat(s.qty||0) * parseFloat(s.harga||0)).toFixed(2);
    const idPenjualanEsc = String(s.id_penjualan || '').replace(/'/g, "\\'");
    html += `<tr>
      <td>${s.id_penjualan||'-'}</td>
      <td>${s.nama||'-'}</td>
      <td>${s.kode_produk||'-'}</td>
      <td>${s.nama_produk||'-'}</td>
      <td>${s.qty}</td>
      <td>${s.harga||0}</td>
      <td>${total}</td>
      <td>${s.return_qty||0}</td>
      <td>${s.sales_date}</td>
      <td>
        <button class="btn secondary" style="padding:4px 8px;font-size:12px" onclick="editSale(${s.id},'${idPenjualanEsc}',${s.product_id},${s.qty},${s.harga||0},${s.return_qty||0},'${s.sales_date}')">Edit</button>
        <button class="btn" style="padding:4px 8px;font-size:12px;background:#dc3545" onclick="deleteSale(${s.id},'${idPenjualanEsc}')">Hapus</button>
      </td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('salesList').innerHTML = html;
}

// criteria
document.getElementById('formCriteria').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const id = f.get('id');
  const action = id ? 'update_criteria' : 'add_criteria';
  
  await api(action, {
    id: id,
    kode: f.get('kode'),
    nama: f.get('nama'),
    bobot: f.get('bobot'),
    atribut: f.get('atribut')
  });
  
  resetCriteriaForm();
  loadCriteria(); 
  loadCriteriaSelect();
});

function resetCriteriaForm() {
  const form = document.getElementById('formCriteria');
  form.reset();
  document.getElementById('criteriaId').value = '';
  document.getElementById('criteriaBobot').value = '1';
  document.getElementById('btnSaveCriteria').textContent = 'Tambah Kriteria';
  document.getElementById('btnCancelCriteria').style.display = 'none';
}

function editCriteria(id, kode, nama, bobot, atribut) {
  document.getElementById('criteriaId').value = id;
  document.getElementById('criteriaKode').value = kode;
  document.getElementById('criteriaNama').value = nama;
  document.getElementById('criteriaBobot').value = bobot;
  document.getElementById('criteriaAtribut').value = atribut;
  document.getElementById('btnSaveCriteria').textContent = 'Update Kriteria';
  document.getElementById('btnCancelCriteria').style.display = 'inline-block';
  
  document.getElementById('formCriteria').scrollIntoView({ behavior: 'smooth' });
}

async function deleteCriteria(id, nama) {
  if (!confirm(`Hapus kriteria "${nama}"?`)) return;
  await api('delete_criteria', { id });
  loadCriteria();
  loadCriteriaSelect();
}

document.getElementById('btnCancelCriteria').addEventListener('click', resetCriteriaForm);

async function loadCriteria(){
  const data = await (await fetch('api.php?action=list_criteria')).json();
  let html = '<table><tr><th>Kode</th><th>Nama</th><th>Bobot</th><th>Atribut</th><th>Aksi</th></tr>';
  data.forEach(c=> {
    // escape quotes properly
    const kodeEsc = String(c.kode).replace(/'/g, "\\'");
    const namaEsc = String(c.nama).replace(/'/g, "\\'");
    html += `<tr>
      <td>${c.kode}</td>
      <td>${c.nama}</td>
      <td>${c.bobot}</td>
      <td>${c.atribut}</td>
      <td>
        <button class="btn secondary" style="padding:4px 8px;font-size:12px" onclick="editCriteria(${c.id},'${kodeEsc}','${namaEsc}',${c.bobot},'${c.atribut}')">Edit</button>
        <button class="btn" style="padding:4px 8px;font-size:12px;background:#dc3545" onclick="deleteCriteria(${c.id},'${namaEsc}')">Hapus</button>
      </td>
    </tr>`;
  });
  html += '</table>';
  document.getElementById('criteriaList').innerHTML = html;
}

async function loadCriteriaSelect(){
  const data = await (await fetch('api.php?action=list_criteria')).json();
  const cs = document.getElementById('valCriteria');
  cs.innerHTML = '';
  data.forEach(c=> cs.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.kode} - ${c.nama}</option>`));
}

// values
document.getElementById('formValue').addEventListener('submit', async e=>{
  e.preventDefault();
  const f = new FormData(e.target);
  const id = f.get('id');
  const action = id ? 'update_value' : 'add_value';
  
  await api(action, {
    id: id,
    product_id: f.get('product_id'),
    criteria_id: f.get('criteria_id'),
    nilai: f.get('nilai')
  });
  
  resetValueForm(true);
  loadValues();
});

function resetValueForm(keepSelection = false) {
  const form = document.getElementById('formValue');
  const productId = keepSelection ? document.getElementById('valProduct').value : '';
  const criteriaId = keepSelection ? document.getElementById('valCriteria').value : '';

  form.reset();
  document.getElementById('valueId').value = '';
  if (keepSelection) {
    document.getElementById('valProduct').value = productId;
    document.getElementById('valCriteria').value = criteriaId;
  }
  document.getElementById('btnSaveValue').textContent = 'Simpan Nilai';
  document.getElementById('btnCancelValue').style.display = 'none';
}

function editValue(id, product_id, criteria_id, nilai) {
  document.getElementById('valueId').value = id;
  document.getElementById('valProduct').value = product_id;
  document.getElementById('valCriteria').value = criteria_id;
  document.getElementById('valueNilai').value = nilai;
  document.getElementById('btnSaveValue').textContent = 'Update Nilai';
  document.getElementById('btnCancelValue').style.display = 'inline-block';
  
  document.getElementById('formValue').scrollIntoView({ behavior: 'smooth' });
}

async function deleteValue(id, productName, criteriaName) {
  if (!confirm(`Hapus nilai "${productName}" - "${criteriaName}"?`)) return;
  await api('delete_value', { id });
  loadValues();
}

document.getElementById('btnCancelValue').addEventListener('click', resetValueForm);

async function loadValues(){
  const products = await (await fetch('api.php?action=list_products')).json();
  const criteria = await (await fetch('api.php?action=list_criteria')).json();
  const values = await (await fetch('api.php?action=list_values')).json();
  
  const matrix = {};
  const valueIds = {};
  values.forEach(v => {
    if (!matrix[v.product_id]) matrix[v.product_id] = {};
    if (!valueIds[v.product_id]) valueIds[v.product_id] = {};
    matrix[v.product_id][v.criteria_id] = v.nilai;
    valueIds[v.product_id][v.criteria_id] = v.id;
  });
  
  let html = '<div style="overflow:auto"><table><thead><tr><th>Produk</th>';
  criteria.forEach(c => {
    html += `<th>${c.kode}<br><small style="font-weight:normal">${c.nama}</small></th>`;
  });
  html += '<th>Aksi</th></tr></thead><tbody>';
  
  products.forEach(p => {
    const namaEsc = String(p.nama).replace(/'/g, "\\'");
    html += `<tr><td><strong>${p.kode}</strong><br><small>${p.nama}</small></td>`;
    criteria.forEach(c => {
      const val = (matrix[p.id] && matrix[p.id][c.id]) ? matrix[p.id][c.id] : '-';
      const style = val !== '-' ? 'background:#e3f2fd' : '';
      html += `<td style="${style}">${val}</td>`;
    });
    
    // action buttons for each row
    html += '<td style="white-space:nowrap">';
    criteria.forEach(c => {
      const valueId = (valueIds[p.id] && valueIds[p.id][c.id]) ? valueIds[p.id][c.id] : null;
      if (valueId) {
        const val = matrix[p.id][c.id];
        const critNamaEsc = String(c.nama).replace(/'/g, "\\'");
        html += `<button class="btn secondary" style="padding:2px 6px;font-size:11px;margin:2px" onclick="editValue(${valueId},${p.id},${c.id},${val})">E-${c.kode}</button>`;
        html += `<button class="btn" style="padding:2px 6px;font-size:11px;background:#dc3545;margin:2px" onclick="deleteValue(${valueId},'${namaEsc}','${critNamaEsc}')">H-${c.kode}</button>`;
      }
    });
    html += '</td>';
    
    html += '</tr>';
  });
  
  html += '</tbody></table></div>';
  document.getElementById('valuesList').innerHTML = html;
}

// analysis
document.getElementById('runSaw').addEventListener('click', async ()=>{
  document.getElementById('analysisResult').innerText = 'Memproses...';
  const res = await (await fetch('api.php?action=run_saw')).json();
  if (!res.ok) { document.getElementById('analysisResult').innerText = res.msg || 'Gagal'; return; }
  
  // build display with Bobot columns
  let out = '<div class="card"><h4>Tabel Perhitungan SAW</h4>';
  out += '<div style="overflow:auto"><table><thead><tr><th rowspan="2">Rank</th><th rowspan="2">Produk</th>';
  
  // header kriteria dengan bobot
  res.criteria.forEach(c=> {
    out += `<th colspan="3">${c.kode} (Bobot: ${c.bobot})</th>`;
  });
  out += '<th rowspan="2">Skor</th><th rowspan="2">Keterangan</th></tr>';
  out += '<tr>';
  res.criteria.forEach(c=> {
    out += `<th>C${c.id} (raw)</th><th>C${c.id} (norm)</th><th>Bobot×Norm</th>`;
  });
  out += '</tr></thead><tbody>';
  
  // data rows
  res.calc.forEach((r, idx)=>{
    const rankData = res.rank.find(x => x.product_id === r.product_id);
    out += `<tr><td>${idx+1}</td><td>${r.kode} - ${r.nama}</td>`;
    res.criteria.forEach(c=> {
      const raw = r['raw_'+c.id];
      const norm = r['norm_'+c.id];
      const weighted = (norm * parseFloat(c.bobot)).toFixed(4);
      out += `<td>${raw}</td><td>${norm}</td><td>${weighted}</td>`;
    });
    out += `<td><strong>${r.score}</strong></td><td>${rankData ? rankData.class : '-'}</td></tr>`;
  });
  out += '</tbody></table></div></div>';
  
  // ranking summary
  out += '<div class="card"><h4>Ranking Produk</h4><table><tr><th>#</th><th>Produk</th><th>Score</th><th>Klasifikasi</th></tr>';
  res.rank.forEach((r,i)=> out += `<tr><td>${i+1}</td><td>${r.kode} - ${r.nama}</td><td>${r.score}</td><td><span class="badge ${r.class==='Fast Moving'?'fast':'slow'}">${r.class}</span></td></tr>`);
  out += '</table></div>';
  
  document.getElementById('analysisResult').innerHTML = out;
});

// toggle login/register forms
document.getElementById('showRegister').addEventListener('click', e => {
  e.preventDefault();
  document.getElementById('loginFormContainer').classList.add('hidden');
  document.getElementById('registerFormContainer').classList.remove('hidden');
});

document.getElementById('showLogin').addEventListener('click', e => {
  e.preventDefault();
  document.getElementById('registerFormContainer').classList.add('hidden');
  document.getElementById('loginFormContainer').classList.remove('hidden');
});

// register form handler
document.getElementById('registerForm').addEventListener('submit', async e => {
  e.preventDefault();
  const f = new FormData(e.target);
  const res = await api('register', {
    username: f.get('username'),
    password: f.get('password'),
    confirm_password: f.get('confirm_password')
  });
  const msgEl = document.getElementById('registerMsg');
  if (res.ok) {
    msgEl.style.color = 'green';
    msgEl.innerText = res.msg || 'Registrasi berhasil';
    e.target.reset();
    // auto switch to login after 1.5s
    setTimeout(() => {
      document.getElementById('showLogin').click();
      msgEl.innerText = '';
    }, 1500);
  } else {
    msgEl.style.color = 'red';
    msgEl.innerText = res.msg || 'Registrasi gagal';
  }
});

// load all helper
async function loadAll(){
  await loadProducts();
  await loadProductSelects();
  initSaleForm(); // init ID auto
  await loadSales();
  await loadCriteria();
  await loadCriteriaSelect();
  await loadValues();
}

// initial: try to check session by fetching list_products (will return auth error if not logged)
(async ()=>{
  try {
    const r = await fetch('api.php?action=list_products');
    const j = await r.json();
    if (Array.isArray(j)) {
      document.getElementById('loginView').classList.add('hidden');
      document.getElementById('mainView').classList.remove('hidden');
      loadAll();
    }
  } catch(e){}
})();

// register service worker
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('sw.js').catch(()=>{});
}
</script>
</body>
</html>