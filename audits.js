document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const toggleBtn = document.querySelector('.toggle-btn');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    }
    
    // Modal functionality
    const auditModal = document.getElementById('audit-modal');
    const addAuditBtn = document.getElementById('add-audit-btn');
    const closeModalBtn = document.querySelector('.close-modal');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    // Open modal when add button is clicked
    if (addAuditBtn) {
        addAuditBtn.addEventListener('click', function() {
            // Reset form for new audit
            document.getElementById('audit-form').reset();
            document.getElementById('audit-id').value = '';
            
            // Update modal title for new audit
            document.querySelector('.modal-header h2').textContent = 'Schedule New Audit';
            document.querySelector('.submit-btn').textContent = 'Schedule Audit';
            
            // Show modal
            auditModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });
    }
    
    // Close modal when close button is clicked
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            auditModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
    
    // Close modal when cancel button is clicked
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            auditModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        });
    }
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === auditModal) {
            auditModal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    });
    
    // Handle edit buttons
    const editButtons = document.querySelectorAll('.action-btn.edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const auditId = this.getAttribute('data-id');
            
            // Fetch audit data via AJAX
            fetch('get_audit.php?id=' + auditId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate form with audit data
                        document.getElementById('audit-id').value = data.audit.audit_id;
                        document.getElementById('audit-name').value = data.audit.name;
                        document.getElementById('audit-type-select').value = data.audit.type;
                        document.getElementById('audit-assignee-select').value = data.audit.assignee;
                        document.getElementById('audit-scope').value = data.audit.scope;
                        document.getElementById('audit-start-date').value = data.audit.date;
                        document.getElementById('audit-end-date').value = data.audit.end_date;
                        document.getElementById('audit-description').value = data.audit.description;
                        
                        // Handle checklist items
                        if (data.audit.checklist) {
                            const checklistItems = data.audit.checklist.split(',');
                            document.querySelectorAll('.checklist-item input[type="checkbox"]').forEach(checkbox => {
                                checkbox.checked = checklistItems.includes(checkbox.value);
                            });
                        }
                        
                        // Update modal title for edit
                        document.querySelector('.modal-header h2').textContent = 'Edit Audit';
                        document.querySelector('.submit-btn').textContent = 'Update Audit';
                        
                        // Show modal
                        auditModal.classList.add('active');
                        document.body.style.overflow = 'hidden';
                    } else {
                        alert('Error loading audit data: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while loading audit data.');
                });
        });
    });
    
    // Handle delete buttons
    const deleteButtons = document.querySelectorAll('.action-btn.delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const auditId = this.getAttribute('data-id');
            
            if (confirm('Are you sure you want to delete this audit?')) {
                window.location.href = 'delete_audit.php?id=' + auditId;
            }
        });
    });
    
    // Handle view buttons
    const viewButtons = document.querySelectorAll('.action-btn.view');
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const auditId = this.getAttribute('data-id');
            window.location.href = 'view_audit.php?id=' + auditId;
        });
    });
    
    // Handle report buttons
    const reportButtons = document.querySelectorAll('.action-btn.report, .view-report-btn');
    reportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const auditId = this.getAttribute('data-id');
            window.location.href = 'audit_report.php?id=' + auditId;
        });
    });
    
    // Timeline filter buttons
    const timelineFilterBtns = document.querySelectorAll('.timeline-filter-btn');
    timelineFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            timelineFilterBtns.forEach(b => b.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // Get filter value
            const filter = this.getAttribute('data-filter');
            
            // Filter timeline items
            const timelineItems = document.querySelectorAll('.timeline-item');
            timelineItems.forEach(item => {
                if (filter === 'all') {
                    item.style.display = 'flex';
                } else if (item.classList.contains(filter)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-bar input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.audits-table tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
    
    // Form validation
    const auditForm = document.getElementById('audit-form');
    if (auditForm) {
        auditForm.addEventListener('submit', function(event) {
            const startDate = new Date(document.getElementById('audit-start-date').value);
            const endDate = new Date(document.getElementById('audit-end-date').value);
            
            if (endDate < startDate) {
                event.preventDefault();
                alert('End date cannot be earlier than start date.');
                return false;
            }
            
            // Additional validation can be added here
            
            return true;
        });
    }
    
    // Date range validation
    const startDateInput = document.getElementById('audit-start-date');
    const endDateInput = document.getElementById('audit-end-date');
    
    if (startDateInput && endDateInput) {
        // Set min date for start date to today
        const today = new Date().toISOString().split('T')[0];
        startDateInput.setAttribute('min', today);
        
        // Update min date for end date when start date changes
        startDateInput.addEventListener('change', function() {
            endDateInput.setAttribute('min', this.value);
            
            // If end date is now before start date, update it
            if (new Date(endDateInput.value) < new Date(this.value)) {
                endDateInput.value = this.value;
            }
        });
    }
    
    // Pagination functionality
    const pageButtons = document.querySelectorAll('.page-btn:not(.disabled)');
    pageButtons.forEach(button => {
        button.addEventListener('click', function() {
            if (this.classList.contains('active')) {
                return;
            }
            
            // Remove active class from all buttons
            pageButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked button
            this.classList.add('active');
            
            // In a real application, this would fetch the next page of data
            // For now, we'll just simulate a page change
            const pageNum = this.textContent;
            console.log('Navigating to page ' + pageNum);
            
            // You would typically make an AJAX request here to load the new page data
        });
    });
    
    // Show success or error messages if they exist
    if (document.querySelector('.success-message')) {
        setTimeout(function() {
            document.querySelector('.success-message').style.opacity = '0';
            setTimeout(function() {
                document.querySelector('.success-message').style.display = 'none';
            }, 500);
        }, 3000);
    }
    
    if (document.querySelector('.error-message')) {
        setTimeout(function() {
            document.querySelector('.error-message').style.opacity = '0';
            setTimeout(function() {
                document.querySelector('.error-message').style.display = 'none';
            }, 500);
        }, 3000);
    }
});
