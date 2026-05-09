// BASE_URL is injected by PHP into the page as window.BASE_URL
var BASE = (typeof window.BASE_URL !== 'undefined') ? window.BASE_URL : '';

// Hamburger menu
document.getElementById('hamburger')?.addEventListener('click', function() {
    document.getElementById('navLinks').classList.toggle('open');
});

// Quantity +/- buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('qty-btn')) {
        var input = e.target.closest('.qty-control').querySelector('.qty-input');
        var val = parseInt(input.value) || 1;
        if (e.target.dataset.action === 'plus') val++;
        else if (e.target.dataset.action === 'minus' && val > 1) val--;
        input.value = val;
        updateRowTotal(input);
    }
});

document.addEventListener('change', function(e) {
    if (e.target.classList.contains('qty-input') && !e.target.classList.contains('cart-qty-input')) {
        var val = parseInt(e.target.value);
        if (isNaN(val) || val < 1) val = 1;
        e.target.value = val;
        updateRowTotal(e.target);
    }
});

function updateRowTotal(input) {
    var row = input.closest('tr');
    if (!row) return;
    var dp = parseFloat(row.dataset.discountPrice || 0);
    var qty = parseInt(input.value) || 1;
    var cell = row.querySelector('.total-price');
    if (cell) cell.textContent = '\u20B9' + (dp * qty).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Add to cart (AJAX)
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.add-cart-btn');
    if (!btn) return;
    var row = btn.closest('tr');
    var pid = btn.dataset.productId;
    var qty = row ? (parseInt(row.querySelector('.qty-input')?.value) || 1) : 1;

    fetch(BASE + '/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + pid + '&qty=' + qty
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        if (data.success) {
            var orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Added!';
            btn.classList.add('added');
            document.querySelectorAll('.cart-badge').forEach(function(el) {
                el.textContent = data.cart_count;
                el.style.display = data.cart_count > 0 ? 'inline-flex' : 'none';
            });
            setTimeout(function(){ btn.innerHTML = orig; btn.classList.remove('added'); }, 2000);
        } else {
            alert(data.message || 'Error adding to cart');
        }
    })
    .catch(function(){ alert('Network error. Please try again.'); });
});

// Remove from cart
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.remove-btn');
    if (!btn) return;
    if (!confirm('Remove this item?')) return;
    fetch(BASE + '/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=remove&product_id=' + btn.dataset.productId
    })
    .then(function(r){ return r.json(); })
    .then(function(data){ if (data.success) location.reload(); });
});

// Cart qty change
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('cart-qty-input')) return;
    fetch(BASE + '/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update&product_id=' + e.target.dataset.productId + '&qty=' + (parseInt(e.target.value)||1)
    })
    .then(function(r){ return r.json(); })
    .then(function(data){ if (data.success) location.reload(); });
});

// Product search
var searchInput = document.getElementById('productSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        var q = this.value.toLowerCase();
        document.querySelectorAll('.products-table tbody tr').forEach(function(row) {
            var name = (row.querySelector('.product-name')?.textContent || '').toLowerCase();
            row.style.display = name.includes(q) ? '' : 'none';
        });
        document.querySelectorAll('.product-category').forEach(function(cat) {
            var vis = Array.from(cat.querySelectorAll('tbody tr')).some(function(r){ return r.style.display !== 'none'; });
            cat.style.display = vis ? '' : 'none';
        });
    });
}

// Category tab filter
document.querySelectorAll('.category-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(function(t){ t.classList.remove('active'); });
        this.classList.add('active');
        var cid = this.dataset.catId;
        document.querySelectorAll('.product-category').forEach(function(cat) {
            cat.style.display = (cid === 'all' || cat.dataset.catId === cid) ? '' : 'none';
        });
    });
});

// Admin sidebar toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.getElementById('adminSidebar')?.classList.toggle('open');
});

// Discount preview in admin product form
function updateDiscountPreview() {
    var price = parseFloat(document.getElementById('actual_price')?.value) || 0;
    var disc  = parseFloat(document.getElementById('discount_percent')?.value) || 0;
    var info  = document.getElementById('discount_info');
    if (info && price > 0) {
        var discPrice = (price * (1 - disc / 100)).toFixed(2);
        var saved = (price - discPrice).toFixed(2);
        info.innerHTML = '<strong style="color:var(--accent)">Offer Price: \u20B9' + parseFloat(discPrice).toLocaleString('en-IN',{minimumFractionDigits:2}) + '</strong> &nbsp;|&nbsp; Customer saves \u20B9' + parseFloat(saved).toLocaleString('en-IN',{minimumFractionDigits:2});
    }
}
document.getElementById('actual_price')?.addEventListener('input', updateDiscountPreview);
document.getElementById('discount_percent')?.addEventListener('input', updateDiscountPreview);
updateDiscountPreview();

// ── Floating Cart Button ───────────────────────────────────────────────────────
(function() {
    var cartCountEl = document.querySelector('.cart-badge');
    var initCount   = cartCountEl ? parseInt(cartCountEl.textContent) || 0 : 0;

    // Only show on products page
    var isProductPage = !!document.querySelector('.products-section');
    if (!isProductPage) return;

    var fcb = document.createElement('a');
    fcb.href = BASE + '/cart.php';
    fcb.className = 'floating-cart-btn' + (initCount === 0 ? ' hidden' : '');
    fcb.innerHTML =
        '<i class="fas fa-shopping-cart"></i>' +
        '<span>View Cart</span>' +
        '<span class="fcb-count" id="fcbCount">' + initCount + '</span>';
    document.body.appendChild(fcb);

    // Keep in sync with add-to-cart responses
    var origCartBadgeUpdate = function(count) {
        var fcbCount = document.getElementById('fcbCount');
        if (fcbCount) fcbCount.textContent = count;
        if (count > 0) fcb.classList.remove('hidden');
        else fcb.classList.add('hidden');
    };

    // Patch cart badge update
    document.addEventListener('cartUpdated', function(e) {
        origCartBadgeUpdate(e.detail.count);
    });

    // Override the inline badge update in add-cart handler by watching DOM
    var observer = new MutationObserver(function() {
        var badge = document.querySelector('.cart-badge');
        if (badge) {
            var n = parseInt(badge.textContent) || 0;
            origCartBadgeUpdate(n);
        }
    });
    document.querySelectorAll('.cart-badge').forEach(function(el) {
        observer.observe(el, { childList: true, characterData: true, subtree: true });
    });
})();
