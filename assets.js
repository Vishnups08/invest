document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            mainContent.classList.toggle('full-width');
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('asset-modal');
    const addAssetBtn = document.getElementById('add-asset-btn');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Open modal
    addAssetBtn.addEventListener('click', function() {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    });
    
    // Close modal functions
    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = 'auto'; // Enable scrolling
        document.getElementById('asset-form').reset(); // Reset form
    }
    
    closeModalBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    
    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
    
    // Filter collapse functionality
    const collapseBtn = document.querySelector('.collapse-filters');
    const filterBody = document.querySelector('.filter-body');
    
    collapseBtn.addEventListener('click', function() {
        filterBody.classList.toggle('collapsed');
        this.querySelector('i').classList.toggle('fa-chevron-up');
        this.querySelector('i').classList.toggle('fa-chevron-down');
    });
    
    // Edit button functionality
    const editButtons = document.querySelectorAll('.action-btn.edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const assetId = this.getAttribute('data-id');
            // You can implement AJAX to fetch asset details and populate the form
            modal.classList.add('active');
            document.querySelector('.modal-header h2').textContent = 'Edit Asset';
            // Populate form with asset data (would require AJAX)
        });
    });
    
    // Delete button functionality
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const assetId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this asset?')) {
                // You can implement AJAX to delete the asset
                window.location.href = 'delete_asset.php?id=' + assetId;
            }
        });
    });
    
    // View button functionality
    const viewButtons = document.querySelectorAll('.action-btn.view');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const assetId = this.getAttribute('data-id');
            // You can implement a view details modal or redirect to a details page
            window.location.href = 'asset_details.php?id=' + assetId;
        });
    });
});