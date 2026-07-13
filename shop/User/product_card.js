/* =========================================================
   1) YÊU THÍCH (♥) — bấm để toggle màu, lưu lại vào localStorage
   ========================================================= */
const FAV_KEY = 'vku_favorites';

function getFavorites(){
  try { return JSON.parse(localStorage.getItem(FAV_KEY)) || []; }
  catch(e){ return []; }
}
function saveFavorites(list){
  localStorage.setItem(FAV_KEY, JSON.stringify(list));
}

function initFavorites(){
  const favorites = getFavorites();
  document.querySelectorAll('.product-card').forEach(function(card){
    const id = card.dataset.id;
    const favBtn = card.querySelector('.product-card__fav');
    if(favorites.includes(id)) favBtn.classList.add('active');

    favBtn.addEventListener('click', function(){
      let list = getFavorites();
      const isActive = favBtn.classList.toggle('active');
      if(isActive){
        if(!list.includes(id)) list.push(id);
        showToast('Đã thêm vào yêu thích ♥');
      } else {
        list = list.filter(function(x){ return x !== id; });
        showToast('Đã bỏ khỏi yêu thích');
      }
      saveFavorites(list);
    });
  });
}

/* =========================================================
   2) GIỎ HÀNG — Add to Cart cập nhật số lượng badge trên icon giỏ
   ========================================================= */
const CART_KEY = 'vku_cart';

function getCart(){
  try { return JSON.parse(localStorage.getItem(CART_KEY)) || {}; }
  catch(e){ return {}; }
}
function saveCart(cart){
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}
function cartTotalQty(cart){
  return Object.values(cart).reduce(function(sum, item){ return sum + item.qty; }, 0);
}

function updateCartBadge(){
  const cart = getCart();
  const qty = cartTotalQty(cart);
  const badge = document.getElementById('cartBadge');
  if (!badge) return;
  badge.textContent = qty;
  badge.classList.toggle('show', qty > 0);
}

function addToCart(card, btn){
  const cart = getCart();
  const id = card.dataset.id;
  const name = card.dataset.name;
  const price = parseFloat(card.dataset.price);

  if(cart[id]){
    cart[id].qty += 1;
  } else {
    cart[id] = { name: name, price: price, qty: 1 };
  }
  saveCart(cart);
  updateCartBadge();
  showToast('Đã thêm "' + name + '" vào giỏ hàng');

  const originalHTML = btn.innerHTML;
  btn.innerHTML = '<span class="btn-icon">✓</span> Đã thêm <span class="close-x" aria-hidden="true">×</span>';
  btn.classList.add('added');
  btn.disabled = true;
  setTimeout(function(){
    btn.innerHTML = originalHTML;
    btn.classList.remove('added');
    btn.disabled = false;
  }, 1200);

  const cartIcon = document.getElementById('cartIcon');
  if (cartIcon) {
    cartIcon.style.transform = 'scale(1.15)';
    setTimeout(function(){ cartIcon.style.transform = 'scale(1)'; }, 180);
  }
}

function initAddToCart(){
  document.querySelectorAll('.product-card').forEach(function(card){
    const btn = card.querySelector('.product-card__btn');
    btn.addEventListener('click', function(){
      addToCart(card, btn);
    });
  });
}

/* =========================================================
   3) TOAST thông báo nhỏ ở đáy màn hình
   ========================================================= */
let toastTimer = null;
function showToast(message){
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(function(){
    toast.classList.remove('show');
  }, 1800);
}

/* =========================================================
   3) CART DRAWER — mở/đóng giỏ hàng mượt mà
   ========================================================= */
function initCartDrawer(){
  const cartDrawer = document.getElementById('cartDrawer');
  const cartOverlay = document.getElementById('cartOverlay');
  const closeCart = document.getElementById('closeCart');
  const continueShopping = document.getElementById('continueShopping');
  const cartDrawerContent = document.getElementById('cartDrawerContent');
  const cartTotal = document.getElementById('cartTotal');

  function formatPrice(value){
    return new Intl.NumberFormat('vi-VN').format(value) + 'đ';
  }

  function renderCartDrawer() {
    if (!cartDrawerContent) return;

    const cart = getCart();
    const items = Object.keys(cart);

    if (!items.length) {
      cartDrawerContent.innerHTML = '<div class="cart-empty">Giỏ hàng đang trống</div>';
      if (cartTotal) cartTotal.textContent = '0đ';
      return;
    }

    let total = 0;
    cartDrawerContent.innerHTML = items.map(function(id){
      const item = cart[id];
      const itemTotal = item.price * item.qty;
      total += itemTotal;

      return (
        '<div class="cart-item" data-id="' + id + '">' +
          '<img src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=600" alt="' + item.name + '">' +
          '<div class="cart-item-details">' +
            '<h4>' + item.name + '</h4>' +
            '<p>' + formatPrice(item.price) + ' × ' + item.qty + '</p>' +
          '</div>' +
          '<button type="button" class="cart-item__remove" aria-label="Xóa sản phẩm" data-remove-id="' + id + '">&times;</button>' +
        '</div>'
      );
    }).join('');

    if (cartTotal) cartTotal.textContent = formatPrice(total);
  }

  function openCart() {
    if (cartDrawer && cartOverlay) {
      renderCartDrawer();
      cartDrawer.classList.add('open');
      cartOverlay.classList.add('open');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeCartDrawer() {
    if (cartDrawer && cartOverlay) {
      cartDrawer.classList.remove('open');
      cartOverlay.classList.remove('open');
      document.body.style.overflow = '';
    }
  }

  document.addEventListener('click', function(e){
    if (e.target.closest('#cartIcon')) {
      e.preventDefault();
      openCart();
    }

    const removeBtn = e.target.closest('.cart-item__remove');
    if (removeBtn) {
      const id = removeBtn.getAttribute('data-remove-id');
      const cart = getCart();
      if (cart[id]) {
        delete cart[id];
        saveCart(cart);
        updateCartBadge();
        renderCartDrawer();
      }
    }
  });

  if (closeCart) {
    closeCart.addEventListener('click', closeCartDrawer);
  }
  if (cartOverlay) {
    cartOverlay.addEventListener('click', closeCartDrawer);
  }
  if (continueShopping) {
    continueShopping.addEventListener('click', closeCartDrawer);
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initCartDrawer);
} else {
  initCartDrawer();
}

/* =========================================================
   KHỞI CHẠY
   ========================================================= */
initFavorites();
initAddToCart();
updateCartBadge();
