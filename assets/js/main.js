// Main JavaScript file for TTBooking System

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
            showError(input, 'This field is required');
        } else {
            input.classList.remove('error');
            removeError(input);
        }
    });

    return isValid;
}

// Show error message
function showError(input, message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

// Remove error message
function removeError(input) {
    const errorDiv = input.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Initialize date picker
function initDatePicker() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    const today = new Date().toISOString().split('T')[0];
    
    dateInputs.forEach(input => {
        input.min = today;
    });
}

// Booking process
function processBooking(tourId) {
    // Create and show booking modal
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Book Tour</h2>
            <form id="bookingForm">
                <input type="hidden" name="tour_id" value="${tourId}">
                <div class="form-group">
                    <label for="booking_date">Booking Date:</label>
                    <input type="date" id="booking_date" name="booking_date" required min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="form-group">
                    <label for="num_people">Number of People:</label>
                    <input type="number" id="num_people" name="num_people" required min="1" max="20">
                </div>
                <button type="submit" class="book-btn">Confirm Booking</button>
            </form>
        </div>
    `;

    document.body.appendChild(modal);

    // Close modal functionality
    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.remove();
    };

    window.onclick = function(event) {
        if (event.target === modal) {
            modal.remove();
        }
    };

    // Handle form submission
    const form = modal.querySelector('#bookingForm');
    form.onsubmit = async function(e) {
        e.preventDefault();
        
        if (!validateForm('bookingForm')) {
            return;
        }

        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="loading"></span> Processing...';
        
        try {
            const response = await fetch('process_booking.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            
            if (data.success) {
                showNotification(data.message, 'success');
                modal.remove();
                setTimeout(() => {
                    window.location.href = 'payment.php?booking_id=' + data.booking_id;
                }, 2000);
            } else {
                showNotification(data.message, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirm Booking';
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('An error occurred while processing your booking.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm Booking';
        }
    };
}

// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date picker
    initDatePicker();

    // Add event listeners for tour filtering
    const searchInput = document.getElementById('tourSearch');
    const priceRange = document.getElementById('priceRange');
    const priceValue = document.getElementById('priceValue');
    const durationFilter = document.getElementById('durationFilter');
    const tourCards = document.querySelectorAll('.tour-card');

    if (searchInput && priceRange && durationFilter) {
        function filterTours() {
            const searchTerm = searchInput.value.toLowerCase();
            const maxPrice = parseInt(priceRange.value);
            const duration = durationFilter.value;

            tourCards.forEach(card => {
                const title = card.querySelector('.tour-title').textContent.toLowerCase();
                const price = parseFloat(card.dataset.price);
                const cardDuration = card.dataset.duration;

                const matchesSearch = title.includes(searchTerm);
                const matchesPrice = price <= maxPrice;
                const matchesDuration = duration === 'all' || cardDuration === duration;

                card.style.display = matchesSearch && matchesPrice && matchesDuration ? 'block' : 'none';
            });
        }

        searchInput.addEventListener('input', filterTours);
        priceRange.addEventListener('input', function() {
            priceValue.textContent = `Max Price: $${this.value}`;
            filterTours();
        });
        durationFilter.addEventListener('change', filterTours);
    }

    // Add form validation to all forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this.id)) {
                e.preventDefault();
            }
        });
    });
}); 