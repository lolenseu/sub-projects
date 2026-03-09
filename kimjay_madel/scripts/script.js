// Profile Update Confirmation
function confirmProfileUpdate() {
    return confirm("Are you sure you want to update your profile?");
}

// Slideshow and Dots
let slideIndex = 0;
let slideInterval;
showSlides();

function showSlides() {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) { slideIndex = 1; }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
    slideInterval = setTimeout(showSlides, 2000);
}
function currentSlide(n) {
    clearTimeout(slideInterval);
    slideIndex = n;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (let i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
    slideInterval = setTimeout(showSlides, 5000);
}

// Back-to-top button
window.onscroll = function() {
    var backToTopButton = document.getElementById("back-to-top");
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        backToTopButton.style.display = "block";
    } else {
        backToTopButton.style.display = "none";
    }
};

function scrollToTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

// Purchase handler – decrements stock
function purchaseCart() {
    const items = JSON.parse(localStorage.getItem('cart')) || [];
    if (!items.length) return;
    const payload = items.map(i => ({
        id: i.product_id,
        qty: i.quantity
    }));
    fetch('decrement_stock.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.text())
    .then(resp => {
        if (resp === 'ok') {
            localStorage.removeItem('cart');
            alert('Purchase completed');
            location.reload(); // Refresh to update stock display
        } else {
            alert('Error while updating stock: ' + resp);
        }
    })
    .catch(err => {
        console.error(err);
        alert('Network error – stock may not have been updated');
    });
}

// Logout confirmation with optional visual cue
document.addEventListener('DOMContentLoaded', function () {
    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function (e) {
            const keepGoing = confirm('Are you sure you want to logout?');
            if (!keepGoing) {
                e.preventDefault();
                const btn = document.querySelector('.logout-confirm');
                if (btn) btn.style.opacity = '0.5';
                setTimeout(() => {
                    if (btn) btn.style.opacity = '1';
                }, 150);
            }
        });
    }
    
    // Fix for plus/minus buttons in modal
    setupQuantityControls();
});

// Function to setup quantity controls
function setupQuantityControls() {
    const modal = document.getElementById('productModal');
    if (!modal) return;
    
    const minusBtn = modal.querySelector('.qty-minus');
    const plusBtn = modal.querySelector('.qty-plus');
    const qtyInput = modal.querySelector('#modalQty');
    
    if (minusBtn && plusBtn && qtyInput) {
        // Remove existing event listeners by cloning and replacing
        const newMinusBtn = minusBtn.cloneNode(true);
        const newPlusBtn = plusBtn.cloneNode(true);
        minusBtn.parentNode.replaceChild(newMinusBtn, minusBtn);
        plusBtn.parentNode.replaceChild(newPlusBtn, plusBtn);
        
        // Add new event listeners
        newMinusBtn.addEventListener('click', function() {
            let currentValue = parseInt(qtyInput.value) || 1;
            if (currentValue > 1) {
                qtyInput.value = currentValue - 1;
            }
        });
        
        newPlusBtn.addEventListener('click', function() {
            let currentValue = parseInt(qtyInput.value) || 1;
            let maxStock = parseInt(qtyInput.getAttribute('max')) || 999;
            if (currentValue < maxStock) {
                qtyInput.value = currentValue + 1;
            }
        });
        
        // Ensure input stays within bounds
        qtyInput.addEventListener('change', function() {
            let value = parseInt(this.value) || 1;
            let maxStock = parseInt(this.getAttribute('max')) || 999;
            if (value < 1) this.value = 1;
            if (value > maxStock) this.value = maxStock;
        });
    }
}

// Function to open modal with product data (to be called from HTML)
function openModal(productId, productName, productPrice, productStock, productDescription, productImage) {
    const modal = document.getElementById('productModal');
    const modalImage = document.getElementById('modalImage');
    const modalTitle = document.getElementById('modalTitle');
    const modalDescription = document.getElementById('modalDescription');
    const modalStock = document.getElementById('modalStock');
    const modalPrice = document.getElementById('modalPrice');
    const modalQty = document.getElementById('modalQty');
    
    if (modalImage) modalImage.src = productImage;
    if (modalTitle) modalTitle.textContent = productName;
    if (modalDescription) modalDescription.textContent = productDescription;
    if (modalStock) modalStock.textContent = productStock;
    if (modalPrice) modalPrice.textContent = '₱' + parseFloat(productPrice).toFixed(2);
    if (modalQty) {
        modalQty.value = 1;
        modalQty.setAttribute('max', productStock);
    }
    
    // Store product data for add to cart
    modal.setAttribute('data-product-id', productId);
    modal.setAttribute('data-product-name', productName);
    modal.setAttribute('data-product-price', productPrice);
    modal.setAttribute('data-product-stock', productStock);
    
    modal.style.display = 'flex';
    
    // Re-setup quantity controls
    setupQuantityControls();
}

// Close modal function
function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Add to cart function
function addToCart() {
    const modal = document.getElementById('productModal');
    const productId = modal.getAttribute('data-product-id');
    const productName = modal.getAttribute('data-product-name');
    const productPrice = modal.getAttribute('data-product-price');
    const productStock = parseInt(modal.getAttribute('data-product-stock'));
    const quantity = parseInt(document.getElementById('modalQty').value) || 1;
    
    if (quantity > productStock) {
        alert('Quantity exceeds available stock!');
        return;
    }
    
    // Get existing cart from localStorage
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
        existingItem.quantity += quantity;
        if (existingItem.quantity > productStock) {
            alert('Total quantity exceeds available stock!');
            existingItem.quantity -= quantity;
            return;
        }
    } else {
        cart.push({
            product_id: productId,
            name: productName,
            price: parseFloat(productPrice),
            quantity: quantity,
            stock: productStock
        });
    }
    
    // Save to localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Update cart count
    updateCartCount();
    
    // Close modal
    closeModal();
    
    alert('Product added to cart!');
}

// Update cart count function
function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCount = document.getElementById('cartCount');
    if (cartCount) {
        cartCount.textContent = totalItems;
    }
}

// Call updateCartCount on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});