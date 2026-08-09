<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HARINFOOD POS Lite - Full Sistem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { brand: { dark: '#0a0c17', header: '#111425', card: '#161930', border: '#24294a' } } }
            }
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js"></script>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; background-color: #0a0c17; color: #f3f4f6; }
        ::-webkit-scrollbar { width: 4px; height: 4px; }
        ::-webkit-scrollbar-thumb { background: #24294a; border-radius: 4px; }
        .glass { background: rgba(22, 25, 48, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(255, 255, 255, 0.1); }
        .glass-input { background: rgba(0, 0, 0, 0.5); border: 1px solid rgba(255, 255, 255, 0.15); color: white; }
        .glass-input:focus { border-color: #06b6d4; outline: none; }
        .glow-cart {
            animation: glowPulse 2s infinite;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.6), 0 0 40px rgba(6, 182, 212, 0.3);
        }
        @keyframes glowPulse {
            0%, 100% { box-shadow: 0 0 20px rgba(6, 182, 212, 0.6), 0 0 40px rgba(6, 182, 212, 0.3); }
            50% { box-shadow: 0 0 30px rgba(6, 182, 212, 0.8), 0 0 60px rgba(6, 182, 212, 0.5); }
        }
        @media print {
            body * { visibility: hidden; }
            #printable-receipt, #printable-receipt * { visibility: visible; }
            #printable-receipt { position: absolute; left: 0; top: 0; width: 100%; color: #000 !important; background: #fff !important; padding: 10px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col selection:bg-cyan-500 selection:text-white">
<div id="app" class="flex-1 flex flex-col h-full relative">

    <!-- Toast Notification -->
    <div v-if="toast.show" class="fixed top-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-xl text-white text-sm font-semibold transition-all" :class="toast.type === 'error' ? 'bg-rose-500' : 'bg-emerald-500'">
        <span>{{ toast.message }}</span>
    </div>

    <!-- Header -->
    <header class="bg-brand-header/90 backdrop-blur-md border-b border-brand-border sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/20">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 2v6a3 3 0 0 1-3 3 3 3 0 0 1-3-3V2"/><path d="M15 2v16"/><path d="M8 2v4a2 2 0 0 1-2 2 2 2 0 0 1-2-2V2"/><path d="M6 2v20"/></svg>
                </div>
                <div>
                    <h1 class="text-lg font-black tracking-tight text-white flex items-center gap-1.5">
                        HARINFOOD <span class="text-[9px] px-1.5 py-0.5 rounded-md bg-cyan-500/20 text-cyan-400 border border-cyan-500/30">POS Lite v2</span>
                    </h1>
                    <p class="text-[10px] text-gray-400 font-medium">Sistem Pemesanan & Kasir Resto</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <button @click="userRole === 'pelanggan' ? openModal('login') : logout()" class="flex items-center gap-2 px-3.5 py-2 rounded-xl border border-brand-border hover:bg-white/5 transition">
                    <div class="w-2.5 h-2.5 rounded-full" :class="userRole === 'kasir' ? 'bg-emerald-400 animate-pulse' : 'bg-amber-500'"></div>
                    <div class="text-left hidden sm:block">
                        <div class="text-xs font-bold text-gray-200">{{ userRole === 'kasir' ? authState.kasirName : 'Mode Pelanggan' }}</div>
                        <div class="text-[9px] text-gray-500">{{ userRole === 'kasir' ? 'Keluar' : 'Akses Kasir' }}</div>
                    </div>
                </button>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-5xl w-full mx-auto px-4 py-6 flex flex-col gap-6">

        <!-- KASIR DASHBOARD TOOLS -->
        <section v-if="userRole === 'kasir'" class="glass p-4 rounded-2xl flex flex-wrap items-center justify-between gap-3 shadow-2xl border-cyan-900/50">
            <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white">Kasir: {{ authState.kasirName }}</span>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="openModal('incomingOrders')" class="relative px-3.5 py-2 bg-blue-600 hover:bg-blue-500 rounded-xl text-xs font-bold text-white transition flex items-center gap-2">
                    🔔 Pesanan Masuk
                    <span v-if="pendingOrdersCount > 0" class="px-1.5 py-0.5 bg-rose-500 text-white rounded-full text-[10px] animate-bounce">{{ pendingOrdersCount }}</span>
                </button>
                <button @click="openModal('stock')" class="px-3.5 py-2 bg-brand-border hover:bg-white/10 rounded-xl text-xs font-bold text-white transition">📦 Kelola Stok</button>
                <button @click="openModal('addProduct')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-xs font-bold text-white transition">＋ Tambah Produk</button>
                <button @click="fetchReports(); openModal('reports')" class="px-3.5 py-2 bg-brand-border hover:bg-white/10 rounded-xl text-xs font-bold text-white transition">📈 Laporan</button>
            </div>
        </section>

        <!-- KATEGORI (MAKANAN & MINUMAN) -->
        <div class="flex items-center justify-center gap-3">
            <button @click="activeCategory = 'Makanan'" class="w-1/2 max-w-[200px] py-3.5 rounded-2xl font-extrabold text-sm transition flex items-center justify-center gap-2 shadow-lg" :class="activeCategory === 'Makanan' ? 'bg-cyan-500 text-brand-dark' : 'bg-brand-card text-gray-400 border border-brand-border'">
                🍽️ MAKANAN
            </button>
            <button @click="activeCategory = 'Minuman'" class="w-1/2 max-w-[200px] py-3.5 rounded-2xl font-extrabold text-sm transition flex items-center justify-center gap-2 shadow-lg" :class="activeCategory === 'Minuman' ? 'bg-cyan-500 text-brand-dark' : 'bg-brand-card text-gray-400 border border-brand-border'">
                🥤 MINUMAN
            </button>
        </div>

        <!-- SEARCH -->
        <div class="relative w-full max-w-md mx-auto">
            <input type="text" v-model="searchQuery" placeholder="Cari nama menu..." class="w-full glass-input rounded-2xl px-4 py-3 text-sm text-white shadow-inner" />
        </div>

        <!-- PRODUCT GRID -->
        <section class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div v-for="product in filteredProducts" :key="product.id" class="glass rounded-2xl p-3 flex flex-col justify-between hover:border-cyan-500/50 transition group cursor-pointer" @click="addToCart(product)">
                <div>
                    <div class="relative w-full aspect-square rounded-xl overflow-hidden mb-3 bg-brand-dark">
                        <img :src="product.foto || (product.kategori_nama === 'Minuman' ? 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?w=500&q=80' : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80')" class="w-full h-full object-cover group-hover:scale-110 transition duration-500"/>
                        <div class="absolute top-2 right-2 px-2 py-0.5 rounded text-[9px] font-extrabold" :class="product.stok > 0 ? 'bg-black/70 text-emerald-400' : 'bg-rose-600 text-white'">
                            {{ product.stok > 0 ? 'Stok: ' + product.stok : 'HABIS' }}
                        </div>
                    </div>
                    <h3 class="font-bold text-xs text-white line-clamp-2 leading-tight">{{ product.nama }}</h3>
                    <p class="text-[13px] text-cyan-400 font-black mt-1">{{ formatRupiah(product.harga) }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-white/10" @click.stop>
                    <button v-if="getCartQty(product.id) === 0" @click="addToCart(product)" :disabled="product.stok <= 0" class="w-full py-2 rounded-xl bg-cyan-500/10 hover:bg-cyan-500 text-cyan-400 hover:text-brand-dark font-extrabold text-[11px] flex justify-center items-center gap-1.5 transition disabled:opacity-40">
                        + Tambah
                    </button>
                    <div v-else class="flex items-center justify-between bg-black/40 p-1 rounded-xl border border-cyan-500/30">
                        <button @click="updateCartQty(product.id, -1)" class="w-7 h-7 rounded-lg bg-brand-border text-white font-bold text-sm">−</button>
                        <input type="number" :value="getCartQty(product.id)" @input="e => setCartQty(product.id, parseInt(e.target.value) || 0)" class="w-12 text-center text-xs font-black text-cyan-400 bg-transparent outline-none" min="0" />
                        <button @click="updateCartQty(product.id, 1)" class="w-7 h-7 rounded-lg bg-cyan-500 text-brand-dark font-bold text-sm">+</button>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- FLOATING CART BUTTON (Glow Effect) -->
    <div v-if="cartTotalItems > 0" class="fixed bottom-6 right-6 z-40 glow-cart">
        <button @click="openModal('cart')" class="flex flex-col items-center justify-center px-6 py-4 bg-gradient-to-br from-cyan-500 via-blue-500 to-cyan-600 text-brand-dark font-extrabold rounded-full shadow-2xl hover:scale-105 transition">
            <div class="text-2xl">🛒</div>
            <div class="text-xs mt-1">{{ cartTotalItems }} item</div>
            <div class="text-sm font-black mt-0.5">{{ formatRupiah(cartTotalHarga) }}</div>
        </button>
    </div>

    <!-- MODALS -->

    <!-- 1. LOGIN KASIR -->
    <div v-if="modals.login" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-sm rounded-[24px] p-7 shadow-2xl relative">
            <button @click="closeModal('login')" class="absolute top-5 right-5 text-gray-400">✕</button>
            <h2 class="text-lg font-black text-white text-center mb-4">Akses Kasir</h2>
            <div class="space-y-4">
                <input type="text" v-model="authForm.nama" placeholder="Nama Kasir..." class="w-full glass-input rounded-xl px-4 py-3 text-sm">
                <input type="password" v-model="authForm.pin" placeholder="PIN (contoh: 313121)" class="w-full glass-input rounded-xl px-4 py-3 text-sm font-mono">
                <button @click="processLogin" class="w-full py-3.5 rounded-xl bg-emerald-500 text-brand-dark font-extrabold text-xs">Masuk Kasir</button>
            </div>
        </div>
    </div>

    <!-- 2. KERANJANG & CHECKOUT (UPDATED) -->
    <div v-if="modals.cart" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-end p-0 sm:p-4">
        <div class="glass w-full sm:max-w-md h-full sm:h-[95vh] sm:rounded-3xl flex flex-col shadow-2xl relative border border-white/10">
            <div class="p-5 border-b border-white/10 flex items-center justify-between">
                <h2 class="font-black text-base text-white">Keranjang Pesanan</h2>
                <button @click="closeModal('cart')" class="text-gray-400">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-if="cart.length === 0" class="text-center py-20 text-gray-500">Keranjang Kosong</div>
                <div v-for="item in cart" :key="item.product.id" class="p-3 bg-white/5 rounded-2xl flex items-center gap-3">
                    <img :src="item.product.foto || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500&q=80'" class="w-12 h-12 rounded-xl object-cover">
                    <div class="flex-1 truncate">
                        <h4 class="text-xs font-bold text-white truncate">{{ item.product.nama }}</h4>
                        <p class="text-[11px] text-cyan-400 font-extrabold">{{ formatRupiah(item.product.harga) }}</p>
                    </div>
                    <div class="flex items-center gap-2 bg-black/40 p-1 rounded-xl">
                        <button @click="updateCartQty(item.product.id, -1)" class="w-6 h-6 bg-brand-border text-white rounded text-xs">−</button>
                        <input type="number" :value="item.qty" @input="e => setCartQty(item.product.id, parseInt(e.target.value) || 0)" class="w-10 text-center text-xs font-bold text-cyan-400 bg-transparent outline-none" />
                        <button @click="updateCartQty(item.product.id, 1)" class="w-6 h-6 bg-cyan-500 text-brand-dark rounded text-xs">+</button>
                    </div>
                </div>
            </div>
            <div v-if="cart.length > 0" class="p-5 border-t border-white/10 bg-black/40 space-y-3">
                <div class="grid grid-cols-1 gap-3">
                    <input type="text" v-model="checkoutForm.nama" placeholder="Nama Pemesan" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                    <input type="text" v-model="checkoutForm.alamat" placeholder="Alamat / Nomor Meja" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                    <select v-model="checkoutForm.metode_pemesanan" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                        <option value="Ambil Sendiri">🏪 Ambil Sendiri</option>
                        <option value="Delivery">🚚 Delivery</option>
                    </select>
                    <textarea v-model="checkoutForm.catatan_pesanan" placeholder="Catatan pesanan (opsional)" class="w-full glass-input rounded-xl px-3 py-2 text-xs resize-none h-20"></textarea>
                </div>
                <div v-if="userRole === 'kasir'" class="grid grid-cols-2 gap-3">
                    <input type="number" v-model.number="checkoutForm.diskon" placeholder="Diskon" class="glass-input rounded-xl px-3 py-2 text-xs text-rose-400">
                    <input type="number" v-model.number="checkoutForm.bayar" placeholder="Bayar Tunai" class="glass-input rounded-xl px-3 py-2 text-xs text-cyan-400 font-bold">
                </div>
                <div class="flex justify-between text-sm font-black text-white pt-2 border-t border-white/10">
                    <span>Total Tagihan</span><span class="text-cyan-400">{{ formatRupiah(cartTotalHarga) }}</span>
                </div>
                <button v-if="userRole === 'kasir'" @click="processKasirCheckout" class="w-full py-3 rounded-xl bg-emerald-500 text-brand-dark font-extrabold text-xs">Proses Pembayaran Kasir</button>
                <button v-else @click="submitCustomerOrder" class="w-full py-3 rounded-xl bg-cyan-500 text-brand-dark font-extrabold text-xs">Kirim Pesanan ke Kasir</button>
            </div>
        </div>
    </div>

    <!-- 3. KELOLA STOK -->
    <div v-if="modals.stock" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-3xl rounded-3xl p-6 relative max-h-[90vh] flex flex-col">
            <div class="flex justify-between pb-4 border-b border-white/10">
                <h2 class="font-black text-white">Kelola Stok Manual</h2>
                <button @click="closeModal('stock')" class="text-gray-400">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto py-4">
                <table class="w-full text-left text-xs">
                    <thead class="text-gray-400 uppercase border-b border-white/10">
                        <tr><th class="pb-2">Menu</th><th class="pb-2 text-center">Stok (Edit Manual)</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="prod in products" :key="prod.id">
                            <td class="py-3 font-bold text-white">{{ prod.nama }}</td>
                            <td class="py-3 text-center">
                                <input type="number" v-model.number="prod.stok" @change="updateStockManual(prod)" class="w-24 glass-input text-center rounded-lg px-2 py-1 font-bold text-cyan-400" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. TAMBAH PRODUK / ITEM CUSTOM KASIR -->
    <div v-if="modals.addProduct" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-md rounded-3xl p-6 relative">
            <div class="flex justify-between pb-4 border-b border-white/10 mb-4">
                <h2 class="font-black text-white">{{ userRole === 'kasir' ? 'Tambah Item Custom' : 'Tambah Produk Baru' }}</h2>
                <button @click="closeModal('addProduct')" class="text-gray-400">✕</button>
            </div>
            <div class="space-y-3">
                <input type="text" v-model="productForm.nama" placeholder="Nama Menu" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                <select v-if="userRole !== 'kasir'" v-model="productForm.kategori_id" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                    <option value="1">Makanan</option>
                    <option value="2">Minuman</option>
                </select>
                <input type="number" v-model.number="productForm.harga" placeholder="Harga Jual" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                <input v-if="userRole !== 'kasir'" type="number" v-model.number="productForm.harga_modal" placeholder="Harga Modal (HPP)" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                <input v-if="userRole !== 'kasir'" type="number" v-model.number="productForm.stok" placeholder="Stok Awal" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                <input v-if="userRole !== 'kasir'" type="text" v-model="productForm.foto" placeholder="Link / URL Gambar (https://...)" class="w-full glass-input rounded-xl px-3 py-2 text-xs">
                <button @click="saveProduct" class="w-full py-3 rounded-xl bg-emerald-500 text-brand-dark font-extrabold text-xs">{{ userRole === 'kasir' ? 'Tambah ke Transaksi' : 'Simpan ke Database' }}</button>
            </div>
        </div>
    </div>

    <!-- 5. PESANAN MASUK -->
    <div v-if="modals.incomingOrders" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-3xl rounded-3xl p-6 relative max-h-[90vh] flex flex-col">
            <div class="flex justify-between pb-4 border-b border-white/10">
                <h2 class="font-black text-white">Konfirmasi Pesanan Pelanggan</h2>
                <button @click="closeModal('incomingOrders')" class="text-gray-400">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto py-4 space-y-3">
                <div v-if="incomingOrders.length === 0" class="text-center text-gray-500 py-10">Tidak ada pesanan online.</div>
                <div v-for="ord in incomingOrders" :key="ord.id" class="p-4 bg-white/5 rounded-2xl border border-white/10 flex flex-col sm:flex-row justify-between gap-3">
                    <div>
                        <div class="flex gap-2 items-center mb-1">
                            <span class="font-mono text-cyan-400 font-bold">{{ ord.invoice_code }}</span>
                            <span class="text-[9px] px-2 py-0.5 rounded font-bold uppercase" :class="ord.status === 'Pending' ? 'bg-amber-500/20 text-amber-400' : ord.status === 'Approved' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">{{ ord.status }}</span>
                        </div>
                        <h4 class="text-sm font-extrabold text-white">{{ ord.nama_pelanggan }}</h4>
                        <p class="text-xs text-gray-400 mt-1"><strong>Alamat:</strong> {{ ord.alamat }}</p>
                        <p class="text-xs text-gray-400"><strong>Metode:</strong> {{ ord.metode_pemesanan }}</p>
                        <p v-if="ord.catatan_pesanan" class="text-xs text-cyan-300 mt-1">📝 {{ ord.catatan_pesanan }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ ord.items.map(i => i.product_nama + ' x' + i.qty).join(', ') }}</p>
                        <p class="text-xs font-bold text-cyan-400 mt-1">Total: {{ formatRupiah(ord.total_akhir) }}</p>
                    </div>
                    <div v-if="ord.status === 'Pending'" class="flex items-center gap-2">
                        <button @click="updateOrderStatus(ord.id, 'Approved')" class="px-4 py-2 bg-emerald-500 text-brand-dark font-extrabold text-xs rounded-xl">Konfirmasi</button>
                        <button @click="updateOrderStatus(ord.id, 'Rejected')" class="px-3 py-2 bg-rose-500/20 text-rose-400 text-xs rounded-xl">Tolak</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. LAPORAN TRANSAKSI (UPDATED) -->
    <div v-if="modals.reports" class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="glass w-full max-w-4xl rounded-3xl p-6 relative max-h-[90vh] flex flex-col">
            <div class="flex justify-between pb-4 border-b border-white/10">
                <h2 class="font-black text-white">Laporan Transaksi</h2>
                <button @click="closeModal('reports')" class="text-gray-400">✕</button>
            </div>
            
            <!-- Summary Stats -->
            <div v-if="reportSummary" class="grid grid-cols-2 gap-4 mb-4 p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                <div>
                    <p class="text-xs text-gray-400">Total Pendapatan</p>
                    <p class="text-xl font-black text-emerald-400">{{ formatRupiah(reportSummary.total_pendapatan) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Jumlah Transaksi</p>
                    <p class="text-xl font-black text-cyan-400">{{ reportSummary.jumlah_transaksi }}</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto py-4">
                <table class="w-full text-left text-xs">
                    <thead class="text-gray-400 uppercase border-b border-white/10 sticky top-0 bg-brand-card">
                        <tr><th class="pb-2">Kode</th><th class="pb-2">Pelanggan</th><th class="pb-2">Alamat</th><th class="pb-2">Metode</th><th class="pb-2">Tipe</th><th class="pb-2">Total</th><th class="pb-2">Waktu</th></tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="r in reports" :key="r.id" class="hover:bg-white/5">
                            <td class="py-3 font-mono text-cyan-400 font-bold">{{ r.invoice_code }}</td>
                            <td class="py-3 text-white font-semibold">{{ r.nama_pelanggan }}</td>
                            <td class="py-3 text-gray-400 text-[10px]">{{ r.alamat }}</td>
                            <td class="py-3 text-gray-400 text-[10px]">{{ r.metode_pemesanan }}</td>
                            <td class="py-3"><span class="px-2 py-0.5 rounded text-[10px]" :class="r.tipe_transaksi === 'Kasir' ? 'bg-purple-500/20 text-purple-300' : 'bg-blue-500/20 text-blue-300'">{{ r.tipe_transaksi }}</span></td>
                            <td class="py-3 font-bold text-emerald-400">{{ formatRupiah(r.total_akhir) }}</td>
                            <td class="py-3 text-gray-400 text-[10px]">{{ r.tanggal }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 7. STRUK CETAK -->
    <div v-if="modals.receipt" class="fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="w-full max-w-[320px] flex flex-col relative">
            <button @click="closeModal('receipt')" class="absolute -top-10 right-0 text-white font-bold no-print">✕ Tutup</button>
            <div id="printable-receipt" class="bg-white text-black p-5 font-mono text-[11px] shadow-2xl">
                <div class="text-center border-b-2 border-dashed border-gray-400 pb-3 mb-3">
                    <h2 class="font-black text-lg">HARINFOOD</h2>
                    <p class="text-[10px]">Struk Pembayaran Resmi</p>
                </div>
                <div class="space-y-0.5 border-b-2 border-dashed border-gray-400 pb-2 mb-2 text-[10px]">
                    <div class="flex justify-between"><span>No:</span><span>{{ activeReceipt.invoice_code }}</span></div>
                    <div class="flex justify-between"><span>Nama:</span><span>{{ activeReceipt.nama_pelanggan }}</span></div>
                    <div class="flex justify-between"><span>Alamat:</span><span>{{ activeReceipt.alamat }}</span></div>
                    <div class="flex justify-between"><span>Metode:</span><span>{{ activeReceipt.metode_pemesanan }}</span></div>
                </div>
                <div class="space-y-1.5 border-b-2 border-dashed border-gray-400 pb-2 mb-2">
                    <div v-for="it in activeReceipt.items" :key="it.product_nama">
                        <div class="font-bold">{{ it.product_nama }}</div>
                        <div class="flex justify-between text-[10px]"><span>{{ it.qty }}x @{{ formatRupiah(it.harga) }}</span><span>{{ formatRupiah(it.subtotal) }}</span></div>
                    </div>
                </div>
                <div class="space-y-1 font-bold text-[11px]">
                    <div class="flex justify-between"><span>TOTAL</span><span>{{ formatRupiah(activeReceipt.total_akhir) }}</span></div>
                    <div class="flex justify-between font-normal"><span>Bayar</span><span>{{ formatRupiah(activeReceipt.nominal_bayar) }}</span></div>
                    <div class="flex justify-between font-normal"><span>Kembali</span><span>{{ formatRupiah(activeReceipt.kembalian) }}</span></div>
                </div>
                <div class="text-center text-[9px] mt-3 pt-3 border-t border-gray-400">
                    <p>Terima kasih telah berbelanja!</p>
                    <p>{{ new Date().toLocaleString('id-ID') }}</p>
                </div>
            </div>
            <button @click="window.print()" class="w-full py-3 mt-4 rounded-xl bg-cyan-500 text-brand-dark font-extrabold text-xs no-print">Cetak Struk</button>
        </div>
    </div>

</div>

<script>
    const { createApp, ref, reactive, computed, onMounted } = Vue;

    createApp({
        setup() {
            const toast = reactive({ show: false, message: '', type: 'success' });
            const showToast = (msg, type = 'success') => { toast.message = msg; toast.type = type; toast.show = true; setTimeout(() => toast.show = false, 3000); };
            const modals = reactive({ login: false, cart: false, stock: false, addProduct: false, incomingOrders: false, reports: false, receipt: false });
            const openModal = (name) => { Object.keys(modals).forEach(k => modals[k] = false); modals[name] = true; };
            const closeModal = (name) => modals[name] = false;

            const userRole = ref('pelanggan');
            const authState = reactive({ kasirName: '' });
            const authForm = reactive({ nama: '', pin: '' });

            const processLogin = async () => {
                const pin = authForm.pin.trim();
                const nama = authForm.nama.trim() || 'Kasir';
                if (!['313121', '1234', 'kasir123'].includes(pin)) return showToast('PIN Salah!', 'error');
                
                try {
                    const fd = new FormData(); fd.append('pin', pin); fd.append('nama', nama);
                    await fetch('api.php?action=login_kasir', { method: 'POST', body: fd });
                } catch(e) {}
                
                authState.kasirName = nama;
                userRole.value = 'kasir';
                closeModal('login');
                showToast(`Login Kasir Berhasil: ${nama}`);
            };
            const logout = () => { userRole.value = 'pelanggan'; authState.kasirName = ''; showToast('Keluar dari kasir'); };

            const activeCategory = ref('Makanan');
            const searchQuery = ref('');
            const products = ref([]);
            
            const fetchProducts = async () => {
                try {
                    const res = await fetch('api.php?action=get_products');
                    const data = await res.json();
                    if(data.status === 'success') products.value = data.data;
                } catch(e) {}
            };

            const filteredProducts = computed(() => products.value.filter(p => p.kategori_nama === activeCategory.value && p.nama.toLowerCase().includes(searchQuery.value.toLowerCase())));

            const productForm = reactive({ id: 0, nama: '', kategori_id: 1, harga: 0, harga_modal: 0, stok: 10, foto: '' });
            const saveProduct = async () => {
                if(!productForm.nama || productForm.harga <= 0) return showToast('Lengkapi data produk', 'error');
                
                if(userRole.value === 'kasir') {
                    // Kasir: Tambah item custom ke cart
                    cart.value.push({
                        product: { id: null, nama: productForm.nama, harga: productForm.harga, kategori_nama: 'Custom', stok: 999, foto: '', is_custom: true },
                        qty: 1
                    });
                    showToast('Item custom ditambahkan ke keranjang!');
                } else {
                    // Owner: Simpan ke database
                    const fd = new FormData();
                    for(let k in productForm) fd.append(k, productForm[k]);
                    await fetch('api.php?action=save_product', { method: 'POST', body: fd });
                    showToast('Produk berhasil disimpan!');
                }
                
                closeModal('addProduct');
                productForm.id = 0; productForm.nama = ''; productForm.harga = 0; productForm.harga_modal = 0; productForm.stok = 10; productForm.foto = '';
                if(userRole.value !== 'kasir') fetchProducts();
            };

            const updateStockManual = async (p) => {
                const fd = new FormData(); fd.append('id', p.id); fd.append('stok', p.stok);
                await fetch('api.php?action=update_stock_manual', { method: 'POST', body: fd });
                showToast(`Stok ${p.nama} diubah ke ${p.stok}`);
            };

            const cart = ref([]);
            const checkoutForm = reactive({ nama: '', alamat: '', metode_pemesanan: 'Ambil Sendiri', catatan_pesanan: '', diskon: 0, bayar: 0 });
            
            onMounted(() => {
                fetchProducts();
                fetchIncomingOrders();
                setInterval(fetchIncomingOrders, 5000);
                const saved = localStorage.getItem('harinfood_customer_name');
                if(saved) checkoutForm.nama = saved;
            });

            const getCartQty = (id) => { const it = cart.value.find(i => i.product.id === id); return it ? it.qty : 0; };
            const addToCart = (p) => {
                if(p.stok <= 0) return;
                const ex = cart.value.find(i => i.product.id === p.id);
                if(ex && ex.qty < p.stok) ex.qty++; else if(!ex) cart.value.push({product: p, qty: 1});
                showToast(`+1 ${p.nama}`);
            };
            const updateCartQty = (id, delta) => {
                const idx = cart.value.findIndex(i => i.product.id === id);
                if (idx > -1) {
                    const next = cart.value[idx].qty + delta;
                    if(next <= 0) cart.value.splice(idx, 1);
                    else cart.value[idx].qty = next;
                }
            };
            const setCartQty = (id, qty) => {
                const idx = cart.value.findIndex(i => i.product.id === id);
                if (idx > -1) {
                    if(qty <= 0) cart.value.splice(idx, 1);
                    else cart.value[idx].qty = qty;
                }
            };
            const cartTotalItems = computed(() => cart.value.reduce((s, i) => s + i.qty, 0));
            const cartSubtotal = computed(() => cart.value.reduce((s, i) => s + (i.product.harga * i.qty), 0));
            const cartTotalHarga = computed(() => Math.max(0, cartSubtotal.value - (checkoutForm.diskon || 0)));

            const submitCustomerOrder = async () => {
                if(cart.value.length === 0) return;
                const nama = checkoutForm.nama.trim() || 'Pelanggan';
                localStorage.setItem('harinfood_customer_name', nama);

                const payload = {
                    nama: nama,
                    alamat: checkoutForm.alamat || 'Meja 1',
                    metode_pemesanan: checkoutForm.metode_pemesanan,
                    catatan_pesanan: checkoutForm.catatan_pesanan,
                    total: cartTotalHarga.value,
                    items: cart.value.map(i => ({ product_id: i.product.id, nama: i.product.nama, harga: i.product.harga, qty: i.qty, subtotal: i.product.harga * i.qty }))
                };

                const res = await fetch('api.php?action=submit_customer_order', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
                const r = await res.json();
                if(r.status === 'success') {
                    showToast('Pesanan berhasil dikirim ke Kasir! Menunggu konfirmasi.');
                    cart.value = [];
                    closeModal('cart');
                } else {
                    showToast(r.message, 'error');
                }
            };

            const incomingOrders = ref([]);
            const pendingOrdersCount = computed(() => incomingOrders.value.filter(o => o.status === 'Pending').length);
            const fetchIncomingOrders = async () => {
                try {
                    const res = await fetch('api.php?action=get_customer_orders');
                    const d = await res.json();
                    if(d.status === 'success') incomingOrders.value = d.data;
                } catch(e){}
            };

            const updateOrderStatus = async (id, status) => {
                const fd = new FormData(); fd.append('id', id); fd.append('status', status);
                await fetch('api.php?action=update_order_status', { method: 'POST', body: fd });
                fetchIncomingOrders();
                fetchProducts();
                showToast(status === 'Approved' ? 'Pesanan dikonfirmasi & stok terpotong!' : 'Pesanan ditolak');
            };

            const activeReceipt = ref({});
            const processKasirCheckout = async () => {
                if(checkoutForm.bayar < cartTotalHarga.value) return showToast('Pembayaran kurang', 'error');
                const payload = {
                    nama_pelanggan: checkoutForm.nama || 'Walk-in',
                    alamat: checkoutForm.alamat || 'Kasir',
                    metode_pemesanan: checkoutForm.metode_pemesanan,
                    catatan_pesanan: checkoutForm.catatan_pesanan,
                    total_harga: cartSubtotal.value,
                    diskon: checkoutForm.diskon,
                    total_akhir: cartTotalHarga.value,
                    nominal_bayar: checkoutForm.bayar,
                    kembalian: checkoutForm.bayar - cartTotalHarga.value,
                    kasir_nama: authState.kasirName,
                    items: cart.value.map(i => ({ product_id: i.product.id, product_nama: i.product.nama, harga: i.product.harga, harga_modal: i.product.harga_modal || 0, qty: i.qty, subtotal: i.product.harga * i.qty, is_custom: i.product.is_custom ? 1 : 0 }))
                };

                const res = await fetch('api.php?action=checkout_kasir', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
                const r = await res.json();
                if(r.status === 'success') {
                    activeReceipt.value = { ...payload, invoice_code: r.data.invoice, tanggal: new Date().toLocaleString() };
                    cart.value = [];
                    checkoutForm.nama = ''; checkoutForm.alamat = ''; checkoutForm.metode_pemesanan = 'Ambil Sendiri'; checkoutForm.catatan_pesanan = ''; checkoutForm.diskon = 0; checkoutForm.bayar = 0;
                    closeModal('cart');
                    openModal('receipt');
                    fetchProducts();
                } else showToast(r.message, 'error');
            };

            const reports = ref([]);
            const reportSummary = ref(null);
            const fetchReports = async () => {
                const res = await fetch('api.php?action=get_reports');
                const d = await res.json();
                if(d.status === 'success') {
                    reports.value = d.data.transactions || [];
                    reportSummary.value = d.data.summary || {};
                }
            };

            const formatRupiah = (v) => 'Rp ' + (v||0).toLocaleString('id-ID');

            return {
                toast, modals, openModal, closeModal, userRole, authState, authForm, processLogin, logout,
                activeCategory, searchQuery, products, filteredProducts, productForm, saveProduct, updateStockManual,
                cart, checkoutForm, getCartQty, addToCart, updateCartQty, setCartQty, cartTotalItems, cartTotalHarga,
                submitCustomerOrder, incomingOrders, pendingOrdersCount, updateOrderStatus, processKasirCheckout,
                reports, reportSummary, fetchReports, activeReceipt, formatRupiah
            };
        }
    }).mount('#app');
</script>
</body>
</html>