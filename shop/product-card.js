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
   KHỞI CHẠY
   ========================================================= */
initFavorites();
initAddToCart();
updateCartBadge();
