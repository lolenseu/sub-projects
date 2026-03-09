// Delete confirmation for product items
document.addEventListener("DOMContentLoaded", function () {
    const deleteButtons = document.querySelectorAll(".delete-btn");

    deleteButtons.forEach(button => {
      button.addEventListener("click", function (e) {
        const productName = this.closest(".product-row").querySelector("h4").textContent;
        const confirmDelete = confirm(`Are you sure you want to delete this product "${productName}"?`);
        if (!confirmDelete) {
          e.preventDefault();
        }
      });
    });
  });

// Update confirmation for product items
document.addEventListener("DOMContentLoaded", function () {
    const updateForm = document.querySelector("form button[name='edit_product']");
    if (updateForm) {
      updateForm.addEventListener("click", function (e) {
        const confirmUpdate = confirm("Are you sure you want to update this product?");
        if (!confirmUpdate) {
          e.preventDefault();
        }
      });
    }
  });


// Edit product fuction
document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const price = button.getAttribute('data-price');
        const description = button.getAttribute('data-description');

        document.getElementById('edit-product-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-price').value = price;
        document.getElementById('edit-description').value = description;
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Look for the logout form that we just gave an id to
    const logoutForm = document.getElementById('logoutForm');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function (e) {
            // Native browser dialog – returns true if the user clicks “OK”
            const keepGoing = confirm('Are you sure you want to logout?');
            if (!keepGoing) {
                e.preventDefault();          // cancel the POST to admin‑logout.php
                // optional visual cue – flash the button a little
                const btn = document.querySelector('.logout-btn');
                if (btn) {
                    btn.style.opacity = '0.5';
                    setTimeout(() => btn.style.opacity = '1', 150);
                }
            }
        });
    }
});
