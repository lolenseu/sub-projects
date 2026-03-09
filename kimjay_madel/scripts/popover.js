// Open modal with product info
document.querySelectorAll('.product img').forEach(img => {
    img.addEventListener('click', function () {
        const productDiv = this.parentElement;
        const name = productDiv.querySelector('h3').innerText;
        const price = productDiv.querySelector('p').innerText;
        const imgSrc = this.getAttribute('src');
        const description = productDiv.getAttribute('data-description'); // Fetch description from data attribute

        // Populate modal with product data
        document.getElementById('modalImage').src = imgSrc;
        document.getElementById('modalTitle').innerText = name;
        document.getElementById('modalPrice').innerHTML = price;
        document.getElementById('modalDescription').innerText = description;
        document.getElementById('modalDescription').style.color = '#555';

        // Show the modal
        const modal = document.getElementById('productModal');
        modal.style.display = 'flex';
        modal.style.justifyContent = 'center'; // Center horizontally
        modal.style.alignItems = 'center'; // Center vertically
    });
});

// Close modal
function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Optional: close modal on outside click
window.addEventListener('click', function (e) {
    const modal = document.getElementById('productModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});