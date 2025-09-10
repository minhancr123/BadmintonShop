<!-- Global Confirmation Modal -->
<div class="modal fade" id="globalConfirmModal" tabindex="-1" aria-labelledby="globalConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="globalConfirmModalLabel">
                    <i id="globalConfirmIcon" class="fas fa-question-circle text-warning"></i>
                    <span id="globalConfirmTitle">Xác nhận</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="globalConfirmMessage" class="mb-0"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="button" class="btn btn-danger" id="globalConfirmBtn">
                    <i class="fas fa-check"></i> <span id="globalConfirmBtnText">Xác nhận</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script>
window.globalConfirmAction = null;

// Beautiful Confirm Dialog
window.showConfirm = function(title, message, btnText = 'Xác nhận', iconClass = 'fas fa-question-circle text-warning', btnClass = 'btn-danger') {
    return new Promise((resolve) => {
        document.getElementById('globalConfirmTitle').textContent = title;
        document.getElementById('globalConfirmMessage').textContent = message;
        document.getElementById('globalConfirmBtnText').textContent = btnText;
        document.getElementById('globalConfirmIcon').className = iconClass;
        
        const confirmBtn = document.getElementById('globalConfirmBtn');
        confirmBtn.className = `btn ${btnClass}`;
        
        window.globalConfirmAction = resolve;
        
        const modal = new bootstrap.Modal(document.getElementById('globalConfirmModal'));
        modal.show();
    });
};

// Handle confirm button click
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('globalConfirmBtn');
    const modal = document.getElementById('globalConfirmModal');
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (window.globalConfirmAction) {
                window.globalConfirmAction(true);
                window.globalConfirmAction = null;
            }
            bootstrap.Modal.getInstance(modal).hide();
        });
    }
    
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() {
            if (window.globalConfirmAction) {
                window.globalConfirmAction(false);
                window.globalConfirmAction = null;
            }
        });
    }
});

// Toast notification function
window.showToast = function(title, message, type = 'info') {
    const toastId = 'toast-' + Date.now();
    const bgClass = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : type === 'warning' ? 'bg-warning' : 'bg-info';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : type === 'error' ? 'fas fa-exclamation-circle' : type === 'warning' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${iconClass} me-2"></i>
                    <strong>${title}:</strong> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '9999';
        document.body.appendChild(toastContainer);
    }
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
    toast.show();
    
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
};
</script>
<?php /**PATH C:\xampp\htdocs\BadmintonShop\resources\views/partials/global-modals.blade.php ENDPATH**/ ?>