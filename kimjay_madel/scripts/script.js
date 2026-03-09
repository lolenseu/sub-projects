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
});