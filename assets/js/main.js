// ========================================
// Online Doctor Channelling System
// Main JavaScript File
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navList = document.querySelector('.nav-list');
    
    if (mobileMenuBtn && navList) {
        mobileMenuBtn.addEventListener('click', function() {
            navList.classList.toggle('active');
            const icon = this.querySelector('i');
            if (navList.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }

    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (navList && navList.classList.contains('active')) {
            if (!navList.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                navList.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('i');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    });

    // Form Validation
    const forms = document.querySelectorAll('.validate-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                showAlert('Please fill in all required fields', 'error');
            }
        });
    });

    // Email validation
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (this.value && !emailRegex.test(this.value)) {
                showFieldError(this, 'Please enter a valid email address');
            } else {
                removeFieldError(this);
            }
        });
    });

    // Phone validation
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9+\s]/g, '');
        });
    });

    // Password strength validation
    const passwordInputs = document.querySelectorAll('input[name="password"], input[name="new_password"]');
    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const password = this.value;
            const strength = checkPasswordStrength(password);
            updatePasswordStrength(this, strength);
        });
    });

    // Confirm password validation
    const confirmPasswordInputs = document.querySelectorAll('input[name="confirm_password"]');
    confirmPasswordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const password = document.querySelector('input[name="password"]');
            if (password && this.value !== password.value) {
                showFieldError(this, 'Passwords do not match');
            } else {
                removeFieldError(this);
            }
        });
    });

    // Date picker - minimum date (today)
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (input.classList.contains('future-date')) {
            const today = new Date().toISOString().split('T')[0];
            input.setAttribute('min', today);
        }
    });

    // Time slot selection
    const slotButtons = document.querySelectorAll('.slot-btn');
    slotButtons.forEach(slot => {
        slot.addEventListener('click', function() {
            if (!this.classList.contains('disabled')) {
                slotButtons.forEach(s => s.classList.remove('selected'));
                this.classList.add('selected');
                
                // Update hidden input
                const hiddenInput = document.querySelector('input[name="selected_slot"]');
                if (hiddenInput) {
                    hiddenInput.value = this.dataset.slotId;
                }
            }
        });
    });

    // Date button selection for doctor profile
    const dateButtons = document.querySelectorAll('.date-btn');
    dateButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            dateButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Center card selection
    const centerCards = document.querySelectorAll('.center-card');
    centerCards.forEach(card => {
        card.addEventListener('click', function() {
            centerCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const radio = this.querySelector('input[type="radio"]');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Search form enhancement
    const searchForm = document.querySelector('.search-box');
    if (searchForm) {
        const searchInput = searchForm.querySelector('input[type="text"]');
        const searchSelect = searchForm.querySelector('select');
        
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                // Live search could be implemented here
            }, 300));
        }
    }

    // Filter form auto-submit
    const filterForm = document.querySelector('.filters-form');
    if (filterForm) {
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(select => {
            select.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }

    // Confirmation dialogs
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Modal handling
    const modalTriggers = document.querySelectorAll('[data-modal]');
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.dataset.modal;
            const modal = document.getElementById(modalId);
            if (modal) {
                openModal(modal);
            }
        });
    });

    const modalCloses = document.querySelectorAll('.modal-close, .modal-backdrop');
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal);
            }
        });
    });

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const activeModal = document.querySelector('.modal.active');
            if (activeModal) {
                closeModal(activeModal);
            }
        }
    });

    // Auto-hide flash messages
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                message.remove();
            }, 300);
        }, 5000);
    });

    // Table row hover effect
    const tableRows = document.querySelectorAll('table tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.closest('a, button')) return;
            const link = this.dataset.href;
            if (link) {
                window.location.href = link;
            }
        });
    });

    // Dynamic slot loading for booking
    const doctorSelect = document.getElementById('doctorSelect');
    const centerSelect = document.getElementById('centerSelect');
    const dateSelect = document.getElementById('dateSelect');
    const slotsContainer = document.getElementById('slotsContainer');
    
    if (doctorSelect && centerSelect && dateSelect && slotsContainer) {
        const loadSlots = () => {
            const doctorId = doctorSelect.value;
            const centerId = centerSelect.value;
            const date = dateSelect.value;
            
            if (doctorId && centerId && date) {
                fetchSlots(doctorId, centerId, date);
            }
        };

        doctorSelect.addEventListener('change', loadSlots);
        centerSelect.addEventListener('change', loadSlots);
        dateSelect.addEventListener('change', loadSlots);
    }

    // Booking step navigation
    const bookingSteps = document.querySelectorAll('.booking-step');
    const nextButtons = document.querySelectorAll('.btn-next');
    const prevButtons = document.querySelectorAll('.btn-prev');
    
    let currentStep = 0;
    
    nextButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.dataset.step);
            if (validateStep(step)) {
                showStep(step + 1);
            }
        });
    });
    
    prevButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const step = parseInt(this.dataset.step);
            showStep(step - 1);
        });
    });

    function showStep(step) {
        bookingSteps.forEach((s, i) => {
            s.classList.toggle('active', i === step);
        });
        currentStep = step;
    }

    function validateStep(step) {
        const currentStepEl = bookingSteps[step];
        if (!currentStepEl) return true;
        
        const requiredInputs = currentStepEl.querySelectorAll('[required]');
        let isValid = true;
        
        requiredInputs.forEach(input => {
            if (!input.value) {
                isValid = false;
                input.classList.add('error');
            }
        });
        
        if (!isValid) {
            showAlert('Please fill in all required fields', 'error');
        }
        
        return isValid;
    }

    // Admin sidebar toggle (mobile)
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const adminSidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function() {
            adminSidebar.classList.toggle('active');
        });
    }

    // Checkbox selection for admin tables
    const selectAllCheckbox = document.querySelector('.select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Sticky header shadow on scroll
    const header = document.querySelector('.main-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 10) {
                header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 1px 3px 0 rgba(0, 0, 0, 0.1)';
            }
        });
    }

    // Smooth scroll for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                e.preventDefault();
                const target = document.querySelector(targetId);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });
});

// Helper Functions
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `flash-message ${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    
    const container = document.querySelector('.container') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    
    setTimeout(() => {
        alertDiv.style.opacity = '0';
        setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
}

function showFieldError(input, message) {
    removeFieldError(input);
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error';
    errorDiv.textContent = message;
    input.parentNode.appendChild(errorDiv);
}

function removeFieldError(input) {
    const errorDiv = input.parentNode.querySelector('.error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;
    return strength;
}

function updatePasswordStrength(input, strength) {
    removeFieldError(input);
    const colors = ['#ef4444', '#f59e0b', '#f59e0b', '#10b981', '#10b981', '#10b981'];
    const labels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong', 'Very Strong'];
    
    const existingMeter = input.parentNode.querySelector('.password-strength');
    if (existingMeter) existingMeter.remove();
    
    if (input.value) {
        const meter = document.createElement('div');
        meter.className = 'password-strength';
        meter.innerHTML = `
            <div style="height: 4px; background: #e5e7eb; border-radius: 2px; overflow: hidden; margin-top: 8px;">
                <div style="height: 100%; width: ${(strength / 5) * 100}%; background: ${colors[strength - 1] || colors[0]}; transition: all 0.3s;"></div>
            </div>
            <small style="color: ${colors[strength - 1] || colors[0]}">${labels[strength - 1] || labels[0]}</small>
        `;
        input.parentNode.appendChild(meter);
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function openModal(modal) {
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modal) {
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Fetch slots dynamically
async function fetchSlots(doctorId, centerId, date) {
    const container = document.getElementById('slotsContainer');
    if (!container) return;
    
    container.innerHTML = '<div class="loading-spinner"></div>';
    
    try {
        const response = await fetch(`get-slots.php?doctor_id=${doctorId}&center_id=${centerId}&date=${date}`);
        const data = await response.json();
        
        if (data.success) {
            container.innerHTML = data.html;
            
            // Reattach event listeners to new slots
            document.querySelectorAll('.slot-btn').forEach(slot => {
                slot.addEventListener('click', function() {
                    if (!this.classList.contains('disabled')) {
                        document.querySelectorAll('.slot-btn').forEach(s => s.classList.remove('selected'));
                        this.classList.add('selected');
                        const hiddenInput = document.querySelector('input[name="selected_slot"]');
                        if (hiddenInput) {
                            hiddenInput.value = this.dataset.slotId;
                        }
                    }
                });
            });
        } else {
            container.innerHTML = '<p class="error">No slots available</p>';
        }
    } catch (error) {
        container.innerHTML = '<p class="error">Error loading slots</p>';
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-LK', {
        style: 'currency',
        currency: 'LKR'
    }).format(amount);
}

// Format date
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-LK', options);
}

// Format time
function formatTime(timeString) {
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showAlert('Copied to clipboard!', 'success');
    }).catch(() => {
        showAlert('Failed to copy', 'error');
    });
}

// Print element
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const printWindow = window.open('', '_blank');
        printWindow.document.write(element.innerHTML);
        printWindow.document.close();
        printWindow.print();
    }
}
