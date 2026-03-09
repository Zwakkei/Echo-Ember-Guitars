// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // Quantity input validation
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('change', function() {
            const min = parseInt(this.min) || 1;
            const max = parseInt(this.max) || 999;
            let value = parseInt(this.value);
            
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
        });
    });
    
    // Order details modal
    const viewButtons = document.querySelectorAll('.view-order-details');
    const modal = document.getElementById('orderModal');
    const closeBtn = document.querySelector('.close');
    
    if (viewButtons.length > 0) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const orderId = this.dataset.order;
                fetchOrderDetails(orderId);
                modal.style.display = 'block';
            });
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});

// Fetch order details via AJAX
function fetchOrderDetails(orderId) {
    fetch(`get_order_details.php?order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            displayOrderDetails(data);
        })
        .catch(error => console.error('Error:', error));
}

function displayOrderDetails(data) {
    const container = document.getElementById('orderDetails');
    let html = `
        <h3>Order #${data.order.order_id}</h3>
        <p><strong>Date:</strong> ${data.order.order_date}</p>
        <p><strong>Status:</strong> ${data.order.status}</p>
        <p><strong>Total:</strong> ₱${parseFloat(data.order.total_amount).toFixed(2)}</p>
        <p><strong>Shipping Address:</strong> ${data.order.shipping_address}</p>
        <p><strong>Payment Method:</strong> ${data.order.payment_method}</p>
        
        <h4>Items:</h4>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    data.items.forEach(item => {
        html += `
            <tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>₱${parseFloat(item.price_at_time).toFixed(2)}</td>
                <td>₱${(item.quantity * item.price_at_time).toFixed(2)}</td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}