@extends('layouts.app')
@section('title', 'Kasir (POS)')

@section('content')
<div class="pos-layout">
    {{-- Items Grid --}}
    <div class="pos-items">
        <div class="search-bar">
            <input type="text" class="form-control" id="pos-search" placeholder="Cari barang..." oninput="filterItems()">
        </div>
        <div class="items-grid" id="items-grid">
            @foreach($items as $item)
            <div class="item-card" onclick="addToCart({{ $item->id }}, '{{ addslashes($item->name) }}', {{ $item->price }}, {{ $item->stock }}, '{{ $item->rack_display }}')" data-name="{{ strtolower($item->name) }}" data-sku="{{ strtolower($item->sku ?? '') }}">
                <div class="item-card-name">{{ $item->name }}</div>
                <div class="item-card-rack"><i class="ri-map-pin-2-fill"></i> {{ $item->rack_display }}</div>
                <div class="item-card-price">{{ format_rupiah($item->price) }}</div>
                <div class="item-card-stock">Stok: {{ $item->stock }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Cart --}}
    <div class="pos-cart" id="pos-cart">
        <div class="pos-cart-header" onclick="toggleCart()">
            <h3><i class="ri-shopping-cart-2-fill"></i> Keranjang <span id="cart-count" style="background:var(--primary);color:white;padding:2px 8px;border-radius:12px;font-size:0.75rem;margin-left:4px;">0</span></h3>
        </div>
        <div class="pos-cart-body" id="cart-body">
            <div class="cart-empty" id="cart-empty">
                <i class="ri-shopping-cart-line"></i>
                <p>Keranjang kosong</p>
                <small>Klik barang untuk menambahkan</small>
            </div>
        </div>
        <div class="pos-cart-footer">
            <div class="cart-total-row">
                <span>Subtotal</span>
                <span id="cart-subtotal">Rp 0</span>
            </div>
            <div class="cart-total-row">
                <span>Diskon</span>
                <span id="cart-discount">Rp 0</span>
            </div>
            <div class="cart-total-row grand-total">
                <span>TOTAL</span>
                <span id="cart-total">Rp 0</span>
            </div>
            <button class="btn btn-primary btn-lg btn-block mt-2" id="btn-pay" onclick="openPayModal()" disabled>
                <i class="ri-bank-card-fill"></i> Bayar
            </button>
        </div>
    </div>
</div>

{{-- Payment Modal --}}
<div class="modal-overlay" id="pay-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Pembayaran</h3>
            <button class="modal-close" onclick="closePayModal()"><i class="ri-close-fill"></i></button>
        </div>
        <div class="modal-body">
            <div class="cart-total-row grand-total mb-2">
                <span>TOTAL</span>
                <span id="modal-total">Rp 0</span>
            </div>
            <div class="form-group">
                <label class="form-label">Diskon Global (Rp)</label>
                <input type="number" class="form-control" id="global-discount" value="0" min="0" oninput="recalculate()">
            </div>
            <div class="form-group">
                <label class="form-label">Catatan Diskon</label>
                <input type="text" class="form-control" id="discount-note" placeholder="Alasan diskon (opsional)">
            </div>
            <div class="form-group">
                <label class="form-label">Jumlah Bayar (Rp)</label>
                <input type="number" class="form-control" id="payment-amount" min="0" oninput="recalculate()" style="font-size:1.3rem;font-weight:700;">
            </div>
            <div class="cart-total-row" style="font-size:1.1rem;">
                <span>Kembalian</span>
                <span id="change-amount" style="font-weight:700;color:var(--primary);">Rp 0</span>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closePayModal()">Batal</button>
            <button class="btn btn-success btn-lg" id="btn-confirm-pay" onclick="processPayment()">
                <i class="ri-checkbox-circle-fill"></i> Konfirmasi Bayar
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let cart = [];

function formatRupiah(n) { return 'Rp ' + n.toLocaleString('id-ID'); }

function filterItems() {
    const q = document.getElementById('pos-search').value.toLowerCase();
    document.querySelectorAll('.item-card').forEach(c => {
        const match = c.dataset.name.includes(q) || c.dataset.sku.includes(q);
        c.style.display = match ? '' : 'none';
    });
}

function addToCart(id, name, price, stock, rack) {
    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.quantity >= stock) { alert('Stok tidak mencukupi!'); return; }
        existing.quantity++;
    } else {
        cart.push({ id, name, price, stock, rack, quantity: 1, discount: 0 });
    }
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id === id);
    if (!item) return;
    item.quantity += delta;
    if (item.quantity <= 0) cart = cart.filter(i => i.id !== id);
    else if (item.quantity > item.stock) { item.quantity = item.stock; alert('Stok maksimal!'); }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id !== id);
    renderCart();
}

function renderCart() {
    const body = document.getElementById('cart-body');
    const empty = document.getElementById('cart-empty');
    document.getElementById('cart-count').textContent = cart.reduce((s, i) => s + i.quantity, 0);

    if (cart.length === 0) {
        body.innerHTML = '<div class="cart-empty"><i class="ri-shopping-cart-line"></i><p>Keranjang kosong</p></div>';
        document.getElementById('btn-pay').disabled = true;
    } else {
        let html = '';
        cart.forEach(item => {
            html += `<div class="cart-item">
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-rack"><i class="ri-map-pin-2-fill"></i> ${item.rack}</div>
                    <div class="cart-item-price">${formatRupiah(item.price)} x ${item.quantity} = <strong>${formatRupiah(item.price * item.quantity)}</strong></div>
                </div>
                <div class="cart-item-qty">
                    <button onclick="updateQty(${item.id}, -1)">-</button>
                    <span>${item.quantity}</span>
                    <button onclick="updateQty(${item.id}, 1)">+</button>
                </div>
                <button class="cart-item-remove" onclick="removeFromCart(${item.id})"><i class="ri-delete-bin-fill"></i></button>
            </div>`;
        });
        body.innerHTML = html;
        document.getElementById('btn-pay').disabled = false;
    }
    recalculate();
}

function recalculate() {
    const subtotal = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
    const globalDiscount = parseInt(document.getElementById('global-discount')?.value || 0);
    const total = Math.max(0, subtotal - globalDiscount);
    const payment = parseInt(document.getElementById('payment-amount')?.value || 0);
    const change = payment - total;

    document.getElementById('cart-subtotal').textContent = formatRupiah(subtotal);
    document.getElementById('cart-discount').textContent = formatRupiah(globalDiscount);
    document.getElementById('cart-total').textContent = formatRupiah(total);
    if (document.getElementById('modal-total')) document.getElementById('modal-total').textContent = formatRupiah(total);
    if (document.getElementById('change-amount')) {
        document.getElementById('change-amount').textContent = formatRupiah(Math.max(0, change));
        document.getElementById('change-amount').style.color = change < 0 ? '#dc2626' : 'var(--primary)';
    }
}

function openPayModal() {
    if (cart.length === 0) return;
    document.getElementById('pay-modal').classList.add('active');
    recalculate();
    document.getElementById('payment-amount').focus();
}
function closePayModal() { document.getElementById('pay-modal').classList.remove('active'); }

function toggleCart() {
    if (window.innerWidth <= 768) {
        document.getElementById('pos-cart').classList.toggle('expanded');
    }
}

function processPayment() {
    const subtotal = cart.reduce((s, i) => s + (i.price * i.quantity), 0);
    const globalDiscount = parseInt(document.getElementById('global-discount').value || 0);
    const total = subtotal - globalDiscount;
    const payment = parseInt(document.getElementById('payment-amount').value || 0);
    if (payment < total) { alert('Pembayaran kurang!'); return; }

    const btn = document.getElementById('btn-confirm-pay');
    btn.disabled = true; btn.innerHTML = '<i class="ri-loader-4-fill"></i> Memproses...';

    fetch('{{ route("transactions.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            items: cart.map(i => ({ id: i.id, quantity: i.quantity, discount: i.discount })),
            discount_amount: globalDiscount,
            discount_note: document.getElementById('discount-note').value,
            payment_amount: payment
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { window.location.href = data.redirect; }
        else { alert(data.message); btn.disabled = false; btn.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Konfirmasi Bayar'; }
    })
    .catch(() => { alert('Terjadi kesalahan!'); btn.disabled = false; btn.innerHTML = '<i class="ri-checkbox-circle-fill"></i> Konfirmasi Bayar'; });
}
</script>
@endpush
@endsection
