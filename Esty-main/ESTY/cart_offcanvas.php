<?php // Offcanvas cart partial - include this where navbar is available ?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="cartOffcanvasLabel">Your Cart</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body d-flex flex-column">
    <div id="cartOffcanvasContent" class="mb-3">
      <?php include __DIR__ . '/cart_fragment.php'; ?>
    </div>
    <div class="mt-auto">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <small class="text-muted">Need help?</small>
        <a href="cart.php" class="btn btn-sm btn-link">Open full cart</a>
      </div>
    </div>
  </div>
</div>

<script>
// When offcanvas opens, refresh its content via AJAX to get fresh cart state
var cartOffcanvasEl = document.getElementById('cartOffcanvas');
if (cartOffcanvasEl) {
  cartOffcanvasEl.addEventListener('show.bs.offcanvas', function () {
    // preserve selected ids so selection persists across refreshes
    var preserved = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked')).map(function(c){ return c.value; });
    fetch('cart_fragment.php')
      .then(r => r.text())
      .then(html => {
        document.getElementById('cartOffcanvasContent').innerHTML = html;
        // restore selections
        if (preserved && preserved.length) {
          preserved.forEach(function(id){
            var el = document.querySelector('#cartOffcanvasContent .cart-item-checkbox[value="' + id + '"]');
            if (el) el.checked = true;
          });
        }
        updateCartBadge();
        bindCartFragmentButtons();
        bindCartSelectionHandlers();
        updateSelectedSubtotalAndButton();
      })
      .catch(err => console.error('Failed to load cart fragment', err));
  });
}

function updateCartBadge() {
  // Compute total quantity by reading data-quantity on each list-group-item (more reliable)
  var total = 0;
  document.querySelectorAll('#cartOffcanvasContent .list-group-item').forEach(function(row){
    var q = parseInt(row.getAttribute('data-quantity'));
    if (!isNaN(q)) total += q;
  });

  // Find anchor(s) that contain the cart icon and update or create a badge inside them
  var anchors = Array.from(document.querySelectorAll('a')).filter(function(a){
    return a.getAttribute('title') === 'Shopping Cart' || a.querySelector('.bi-cart');
  });
  anchors.forEach(function(a){
    var b = a.querySelector('.badge');
    if(!b){
      b = document.createElement('span');
      b.className = 'badge bg-danger position-absolute top-0 start-100 translate-middle';
      b.style.width = '18px';
      b.style.height = '18px';
      b.style.display = 'flex';
      b.style.alignItems = 'center';
      b.style.justifyContent = 'center';
      b.style.fontSize = '0.6rem';
      b.style.borderRadius = '50%';
      a.style.position = a.style.position || 'relative';
      a.appendChild(b);
    }
    b.textContent = total > 0 ? total : '';
    b.style.display = total > 0 ? 'flex' : 'none';
  });
}

function bindCartFragmentButtons() {
  // Wire increase/decrease buttons inside fragment to call cart.php update endpoints and refresh
  document.querySelectorAll('#cartOffcanvasContent .btn-increase').forEach(function(btn){
    btn.removeEventListener('click', onIncrease);
    btn.addEventListener('click', onIncrease);
  });
  document.querySelectorAll('#cartOffcanvasContent .btn-decrease').forEach(function(btn){
    btn.removeEventListener('click', onDecrease);
    btn.addEventListener('click', onDecrease);
  });
  document.querySelectorAll('#cartOffcanvasContent .btn-remove').forEach(function(btn){
    btn.removeEventListener('click', onRemove);
    btn.addEventListener('click', onRemove);
  });
}

function onIncrease(e) {
  var id = e.currentTarget.getAttribute('data-id');
  var preserved = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked')).map(function(c){ return c.value; });
  fetch('cart.php?update=increase&id=' + encodeURIComponent(id), { method: 'GET', credentials: 'same-origin' })
    .then(()=>{
      // Refresh fragment
      return fetch('cart_fragment.php');
    })
    .then(r=>r.text())
    .then(html=>{
      document.getElementById('cartOffcanvasContent').innerHTML = html;
      // restore selections
      if (preserved && preserved.length) {
        preserved.forEach(function(pid){
          var el = document.querySelector('#cartOffcanvasContent .cart-item-checkbox[value="' + pid + '"]');
          if (el) el.checked = true;
        });
      }
      updateCartBadge(); bindCartFragmentButtons(); bindCartSelectionHandlers(); updateSelectedSubtotalAndButton();
    })
    .catch(err=>console.error(err));
}
function onDecrease(e) {
  var id = e.currentTarget.getAttribute('data-id');
  var preserved = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked')).map(function(c){ return c.value; });
  fetch('cart.php?update=decrease&id=' + encodeURIComponent(id), { method: 'GET', credentials: 'same-origin' })
    .then(()=> fetch('cart_fragment.php'))
    .then(r=>r.text())
    .then(html=>{
      document.getElementById('cartOffcanvasContent').innerHTML = html;
      if (preserved && preserved.length) {
        preserved.forEach(function(pid){
          var el = document.querySelector('#cartOffcanvasContent .cart-item-checkbox[value="' + pid + '"]');
          if (el) el.checked = true;
        });
      }
      updateCartBadge(); bindCartFragmentButtons(); bindCartSelectionHandlers(); updateSelectedSubtotalAndButton();
    })
    .catch(err=>console.error(err));
}

function onRemove(e){
  var id = e.currentTarget.getAttribute('data-id');
  var preserved = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked')).map(function(c){ return c.value; });
  fetch('cart.php?remove=' + encodeURIComponent(id), { method: 'GET', credentials: 'same-origin' })
    .then(()=> fetch('cart_fragment.php'))
    .then(r=>r.text())
    .then(html=>{
      document.getElementById('cartOffcanvasContent').innerHTML = html;
      if (preserved && preserved.length) {
        preserved.forEach(function(pid){
          var el = document.querySelector('#cartOffcanvasContent .cart-item-checkbox[value="' + pid + '"]');
          if (el) el.checked = true;
        });
      }
      updateCartBadge(); bindCartFragmentButtons(); bindCartSelectionHandlers(); updateSelectedSubtotalAndButton();
    })
    .catch(err=>console.error(err));
}

function bindCartSelectionHandlers(){
  // Select all checkbox
  var selectAll = document.getElementById('cartSelectAll');
  if(selectAll){
    selectAll.removeEventListener('change', onSelectAll);
    selectAll.addEventListener('change', onSelectAll);
  }

  // Per-item checkboxes
  document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox').forEach(function(ch){
    ch.removeEventListener('change', onItemSelect);
    ch.addEventListener('change', onItemSelect);
  });

  // Checkout Selected button
  var checkoutBtn = document.getElementById('checkoutSelectedBtn');
  if(checkoutBtn){
    checkoutBtn.removeEventListener('click', onCheckoutSelected);
    checkoutBtn.addEventListener('click', onCheckoutSelected);
  }
  updateSelectedSubtotalAndButton();
}

function onSelectAll(e){
  var checked = e.target.checked;
  document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox').forEach(function(ch){ ch.checked = checked; });
  updateSelectedSubtotalAndButton();
}

function onItemSelect(e){
  var all = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox'));
  var selectAll = document.getElementById('cartSelectAll');
  if(selectAll) selectAll.checked = all.length > 0 && all.every(c=>c.checked);
  updateSelectedSubtotalAndButton();
}

function updateSelectedSubtotalAndButton(){
  var selected = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked'));
  var subtotal = 0;
  selected.forEach(function(ch){
    var row = ch.closest('[data-item-id]');
    if(!row) return;
    // Prefer numeric data-price attribute (subtotal for the item) when available
    var price = parseFloat(row.getAttribute('data-price')) || 0;
    subtotal += price;
  });
  var el = document.getElementById('selectedSubtotal');
  if(el) el.textContent = '₱' + subtotal.toFixed(2);
  // reflect the selected subtotal into the bottom total display
  var bottom = document.getElementById('cartTotalDisplay');
  if(bottom) bottom.textContent = '₱' + subtotal.toFixed(2);
  var checkoutBtn = document.getElementById('checkoutSelectedBtn');
  if(checkoutBtn) checkoutBtn.disabled = selected.length === 0;
}

function onCheckoutSelected(e){
  var selected = Array.from(document.querySelectorAll('#cartOffcanvasContent .cart-item-checkbox:checked')).map(c=>c.value);
  if(selected.length === 0){ alert('Please select items to checkout.'); return; }
  // Redirect to checkout with selected item ids (server-side checkout should handle filtering)
  var params = new URLSearchParams();
  params.set('items', selected.join(','));
  window.location.href = 'checkout.php?' + params.toString();
}

// Initial binding in case offcanvas content already present
bindCartFragmentButtons();
updateCartBadge();
bindCartSelectionHandlers();
updateSelectedSubtotalAndButton();
</script>
