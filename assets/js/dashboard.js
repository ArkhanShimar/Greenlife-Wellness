document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userModal');
    const serviceModal = document.getElementById('serviceModal');
    const closeModalBtn = document.querySelector('.modal .close-btn');
    const closeServiceModalBtn = document.querySelector('#serviceModal .close-btn');
    const addTherapistBtn = document.getElementById('addTherapistBtn');
    const addServiceBtn = document.getElementById('addServiceBtn');
    const userForm = document.getElementById('userForm');
    const serviceForm = document.getElementById('serviceForm');
    const modalTitle = document.getElementById('modalTitle');
    const serviceModalTitle = document.getElementById('serviceModalTitle');
    const currentPicDiv = document.getElementById('currentPic');
    const currentServiceImageDiv = document.getElementById('currentServiceImage');

    function showModal() {
        modal.style.display = 'block';
    }

    function hideModal() {
        modal.style.display = 'none';
        userForm.reset();
        currentPicDiv.innerHTML = '';
    }

    function showServiceModal() {
        serviceModal.style.display = 'block';
    }

    function hideServiceModal() {
        serviceModal.style.display = 'none';
        serviceForm.reset();
        currentServiceImageDiv.innerHTML = '';
    }

    function showUserFields(userType) {
        // Hide all user type fields first
        document.querySelectorAll('.user-field').forEach(field => {
            field.style.display = 'none';
        });
        
        // Show fields for the specific user type
        if (userType === 'therapist') {
            document.querySelectorAll('.therapist-field').forEach(field => {
                field.style.display = 'block';
            });
        } else if (userType === 'client') {
            document.querySelectorAll('.client-field').forEach(field => {
                field.style.display = 'block';
            });
        }
    }

    // Open modal for adding a new therapist
    addTherapistBtn.addEventListener('click', () => {
        userForm.reset();
        modalTitle.textContent = 'Add New Therapist';
        document.getElementById('userId').value = '';
        document.getElementById('userType').value = 'therapist';
        document.querySelector('#password').parentElement.querySelector('small').style.display = 'none';
        document.querySelector('#password').setAttribute('required', 'required');
        showUserFields('therapist');
        showModal();
    });

    // Open modal for adding a new service
    addServiceBtn.addEventListener('click', () => {
        serviceForm.reset();
        serviceModalTitle.textContent = 'Add New Service';
        document.getElementById('serviceId').value = '';
        currentServiceImageDiv.innerHTML = '';
        showServiceModal();
    });

    // Open modal for editing a user
    document.querySelectorAll('.btn-action.edit').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userType = this.dataset.userType;

            if (userId && userType) {
                // User editing
                fetch(`get_user_details.php?id=${userId}&type=${userType}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        
                        userForm.reset();
                        modalTitle.textContent = `Edit ${userType.charAt(0).toUpperCase() + userType.slice(1)}`;
                        document.getElementById('userId').value = userId;
                        document.getElementById('userType').value = userType;
                        document.querySelector('#password').removeAttribute('required');
                        document.querySelector('#password').parentElement.querySelector('small').style.display = 'block';

                        // Populate form fields
                        document.getElementById('email').value = data.email || '';
                        if (userType === 'client') {
                            document.getElementById('firstName').value = data.first_name || '';
                            document.getElementById('lastName').value = data.last_name || '';
                            document.getElementById('username_client').value = data.username || '';
                            document.getElementById('phone').value = data.phone || '';
                            document.getElementById('dob').value = data.date_of_birth || '';
                            document.getElementById('address').value = data.address || '';
                            document.getElementById('status').value = data.status || 'active';
                        } else { // therapist
                            document.getElementById('name').value = data.name || '';
                            document.getElementById('username_therapist').value = data.username || '';
                            document.getElementById('qualification').value = data.qualification || '';
                            document.getElementById('speciality').value = data.speciality || '';
                            document.getElementById('experience').value = data.experience || '';
                        }

                        if (data.profile_pic_url) {
                            currentPicDiv.innerHTML = `<img src="${data.profile_pic_url}" alt="Current Profile Picture">`;
                        } else {
                            currentPicDiv.innerHTML = '';
                        }

                        showUserFields(userType);
                        showModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Could not fetch user details.');
                    });
            } else {
                // Service editing
                const serviceId = this.dataset.serviceId;
                fetch(`get_service_details.php?id=${serviceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        
                        serviceForm.reset();
                        serviceModalTitle.textContent = 'Edit Service';
                        document.getElementById('serviceId').value = serviceId;
                        document.getElementById('serviceName').value = data.name || '';
                        document.getElementById('serviceDescription').value = data.description || '';
                        document.getElementById('serviceCategory').value = data.category || '';
                        document.getElementById('serviceDuration').value = data.duration_minutes || '';
                        document.getElementById('servicePrice').value = data.price || '';

                        if (data.image_path_url) {
                            currentServiceImageDiv.innerHTML = `<img src="${data.image_path_url}" alt="Current Service Image" style="max-width: 100px; max-height: 100px; border-radius: 8px; margin-top: 10px; border: 2px solid #e0e0e0;">`;
                        } else {
                            currentServiceImageDiv.innerHTML = '';
                        }

                        showServiceModal();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Could not fetch service details.');
                    });
            }
        });
    });

    // Close modal events
    closeModalBtn.addEventListener('click', hideModal);
    closeServiceModalBtn.addEventListener('click', hideServiceModal);
    
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            hideModal();
        }
        if (event.target == serviceModal) {
            hideServiceModal();
        }
    });

    // Handle user form submission
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('manage_user_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideModal();
                // Optionally, refresh the page to see changes
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Handle service form submission
    serviceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch('manage_service_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                hideServiceModal();
                // Optionally, refresh the page to see changes
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    });
});

// The existing sidebar navigation logic from admin.php can also go here
function showSection(event, sectionId) {
    event.preventDefault(); // Prevent default anchor behavior
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(section => {
        section.classList.remove('active');
    });
    // Show the target section
    const targetSection = document.getElementById(sectionId);
    if (targetSection) {
        targetSection.classList.add('active');
    }

    // Update active link in header
    document.querySelectorAll('.header-nav .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    const activeLink = document.querySelector(`.header-nav a[href="#${sectionId}"]`);
    if (activeLink) {
        activeLink.classList.add('active');
    }
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.delete-user')) {
        const btn = e.target.closest('.delete-user');
        const userId = btn.dataset.userId;
        const userType = btn.dataset.userType;
        if (confirm('Are you sure you want to delete this user?')) {
            fetch('manage_user_process.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'delete', userId, userType })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest('tr');
                    if (row) row.remove();
                } else alert(data.message || 'Delete failed');
            });
        }
    }
    if (e.target.closest('.delete-service')) {
        const btn = e.target.closest('.delete-service');
        const serviceId = btn.dataset.serviceId;
        if (confirm('Are you sure you want to delete this service?')) {
            fetch('manage_service_process.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'delete', serviceId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const row = btn.closest('tr');
                    if (row) row.remove();
                } else alert(data.message || 'Delete failed');
            });
        }
    }
});
