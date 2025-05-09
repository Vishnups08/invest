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
    
    // Add Asset Button Click Event
    const addAssetBtn = document.querySelector('.add-btn');
    if (addAssetBtn) {
        addAssetBtn.addEventListener('click', function() {
            // This would typically open a modal or navigate to an add asset page
            alert('Add Asset functionality would open here');
        });
    }
    
    // Edit and Delete Button Click Events
    const editButtons = document.querySelectorAll('.action-btn.edit');
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const assetId = row.cells[0].textContent;
            alert(`Edit Asset ${assetId}`);
        });
    });
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const assetId = row.cells[0].textContent;
            const assetName = row.cells[1].textContent;
            
            if (confirm(`Are you sure you want to delete ${assetName} (${assetId})?`)) {
                // This would typically call an API to delete the asset
                row.remove();
                updateTotalAssets();
            }
        });
    });
    
    // Pagination functionality
    const pageButtons = document.querySelectorAll('.page-btn');
    
    pageButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (!this.classList.contains('next')) {
                document.querySelector('.page-btn.active').classList.remove('active');
                this.classList.add('active');
                // This would typically load the corresponding page of data
            } else {
                const activePage = document.querySelector('.page-btn.active');
                const nextPage = activePage.nextElementSibling;
                if (nextPage && !nextPage.classList.contains('next')) {
                    activePage.classList.remove('active');
                    nextPage.classList.add('active');
                    // This would typically load the next page of data
                }
            }
        });
    });
    
    // Function to update total assets count
    function updateTotalAssets() {
        const totalAssetsElement = document.querySelector('.card:nth-child(3) .value');
        const currentTotal = parseInt(totalAssetsElement.textContent.replace(',', ''));
        totalAssetsElement.textContent = (currentTotal - 1).toLocaleString();
    }
    
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.assets-table tbody tr');
            
            tableRows.forEach(row => {
                let found = false;
                const cells = row.querySelectorAll('td');
                
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                    }
                });
                
                if (found) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Responsive behavior for window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('active');
            mainContent.classList.remove('full-width');
        }
    });
}); 