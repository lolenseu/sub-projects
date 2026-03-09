// Hamburger Menu
document.getElementById('hamburger-menu').addEventListener('click', function () {
    const navLinks = document.getElementById('nav-links');
    navLinks.classList.toggle('show');
});

// Fetch product sugestions
function fetchSuggestions() {
  const searchInput = document.getElementById('searchInput');
  const suggestionsContainer = document.getElementById('suggestions');
  const query = searchInput.value.trim();

  if (query.length === 0) {
      suggestionsContainer.style.display = 'none';
      return;
  }

  fetch(`index.php?query=${encodeURIComponent(query)}`)
      .then(response => {
          if (!response.ok) {
              throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
      })
      .then(data => {
          suggestionsContainer.innerHTML = '';
          if (data.length > 0) {
              data.forEach(item => {
                  const suggestion = document.createElement('div');
                  suggestion.className = 'suggestion-item';
                  suggestion.innerHTML = `
                      <strong>${item.name}</strong> - &#8369;${parseFloat(item.price).toFixed(2)}
                  `;
                  suggestion.onclick = () => {
                      searchInput.value = item.name; // Set the input to the selected product name
                      suggestionsContainer.style.display = 'none';
                      scrollToProduct(); // Scroll to the product when a suggestion is clicked
                  };
                  suggestionsContainer.appendChild(suggestion);
              });
              suggestionsContainer.style.display = 'block';
          } else {
              suggestionsContainer.style.display = 'none';
          }
      })
      .catch(error => {
          console.error('Error fetching suggestions:', error);
      });
}

// Scroll to Product
function scrollToProduct() {
  const input = document.getElementById("searchInput").value.trim().toLowerCase();
  const products = document.querySelectorAll(".product");
  let found = false;

  products.forEach(product => {
      const productName = product.getAttribute("data-name").toLowerCase();
      const productPrice = product.getAttribute("data-price").toLowerCase();
      if (productName.includes(input) || productPrice.includes(input)) {
          product.scrollIntoView({ behavior: "smooth", block: "center" });
          product.classList.add("border", "border-success", "shadow");
          setTimeout(() => {
              product.classList.remove("border", "border-success", "shadow");
          }, 1500);
          found = true;
      }
  });

  if (!found) {
      alert("Product not found!");
  }
}

// Cart functionality
let cart = [];
let total = 0;

function fetchCartFromServer() {
    if (!isLoggedIn) return;
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=get_cart'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            total = 0;
            data.cart.forEach(item => {
                cart.push({
                    id: item.id,
                    name: item.name,
                    price: parseFloat(item.price),
                    img: item.img,
                    quantity: item.quantity
                });
                total += parseFloat(item.price) * item.quantity;
            });
            updateCartUI();
        }
    });
}

function addToCart() {
    if (!isLoggedIn) {
        // Open the login popover
        const loginPopover = document.getElementById('loginPopover');
        if (loginPopover && loginPopover.showPopover) {
            loginPopover.showPopover();
        } else if (loginPopover) {
            loginPopover.style.display = 'block';
        }
        return;
    }

    const modalTitle = document.getElementById('modalTitle').textContent;
    const products = document.querySelectorAll(".product");
    let productId = null;
    products.forEach(product => {
        if (product.getAttribute("data-name") === modalTitle) {
            productId = product.getAttribute("data-id");
        }
    });

    if (!productId) {
        alert("Product not found!");
        return;
    }

    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add_to_cart&product_id=${encodeURIComponent(productId)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {

            fetchCartFromServer();
        } else {
            alert('Failed to add to cart.');
        }
    });
}

function updateCartUI() {
    const cartItems = document.getElementById('cartItems');
    const cartTotal = document.getElementById('cartTotal');
    const cartCount = document.getElementById('cartCount');
    cartItems.innerHTML = '';
    let count = 0;
    cart.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'cart-item-row';
        div.style.display = 'flex';
        div.style.alignItems = 'center';
        div.style.justifyContent = 'space-between';
        div.style.padding = '8px 0';
        div.innerHTML = `
            <div style="display:flex;align-items:center;gap:10px;flex:2;">
                <img src="${item.img}" alt="${item.name}" class="cart-img">
                <div>
                    <div style="font-weight:bold;">${item.name}</div>
                    <div style="color:#888;">&#8369;${item.price.toFixed(2)}</div>
                </div>
            </div>
            <div style="flex:1;text-align:center;">
                <span style="font-size:1.1em;">x${item.quantity}</span>
            </div>
            <div style="display:flex;gap:4px;flex:1;justify-content:flex-end;">
                <button class="cart-button" onclick="decreaseQuantity(${index})">−</button>
                <button class="cart-button" onclick="increaseQuantity(${index})">+</button>
                <button class="cart-button" onclick="removeFromCart(${index})">Remove</button>
            </div>
        `;
        cartItems.appendChild(div);
        count += item.quantity;
    });
    cartTotal.textContent = `Total: ₱${total.toFixed(2)}`;
    cartCount.textContent = count;
}

function decreaseQuantity(index) {
    const item = cart[index];
    if (item.quantity > 1) {
        fetch('index.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=decrease_quantity&product_id=${encodeURIComponent(item.id)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                fetchCartFromServer();
            }
        });
    } else {
        removeFromCart(index);
    }
}

function increaseQuantity(index) {
    const item = cart[index];
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=increase_quantity&product_id=${encodeURIComponent(item.id)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchCartFromServer();
        }
    });
}

function removeFromCart(index) {
    const item = cart[index];
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove_from_cart&product_id=${encodeURIComponent(item.id)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            fetchCartFromServer();
        }
    });
}

function clearCart() {
    if (!confirm("Are you sure you want to clear your cart?")) {
        return;
    }
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=clear_cart'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            total = 0;
            updateCartUI();
        }
    });
}

function purchaseCart() {
    if (cart.length === 0) {
        alert("Your cart is empty!");
        return;
    }
    if (!confirm("Are you sure you want to buy all items in your cart?")) {
        return;
    }
    fetch('index.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=buy_cart'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cart = [];
            total = 0;
            updateCartUI();
            alert("Your order has been placed and is now pending!");
        } else {
            alert("Failed to place order.");
        }
    });
}

// Call this on page load if logged in
if (isLoggedIn) {
    fetchCartFromServer();
}