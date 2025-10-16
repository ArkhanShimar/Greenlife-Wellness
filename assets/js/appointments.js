// Multi-step Appointment Booking Form
let currentStep = 1;
const totalSteps = 4;

// Initialize the form
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Service selection
    document.querySelectorAll('.service-card').forEach(card => {
        card.addEventListener('click', function() {
            selectService(this);
        });
    });

    // Therapist selection
    document.querySelectorAll('.therapist-card').forEach(card => {
        card.addEventListener('click', function() {
            selectTherapist(this);
        });
    });

    // Ensure nextBtn2 is disabled initially
    const nextBtn2 = document.getElementById('nextBtn2');
    if (nextBtn2) nextBtn2.disabled = true;

    // Client details validation
    document.getElementById('client_name').addEventListener('input', validateClientDetails);
    document.getElementById('client_email').addEventListener('input', validateClientDetails);
    document.getElementById('client_phone').addEventListener('input', validateClientDetails);

    // Event delegation for dynamically created time slots
    const timeSlotsContainer = document.querySelector('.time-slots-list');
    if (timeSlotsContainer) {
        timeSlotsContainer.addEventListener('click', function(event) {
            const clickedButton = event.target.closest('.slot-card');
            if (clickedButton) {
                // Remove selection from all other buttons in this list
                this.querySelectorAll('.slot-card').forEach(btn => btn.classList.remove('selected'));
                
                // Add selection to the clicked button
                clickedButton.classList.add('selected');
                
                // Update hidden input and enable the next button
                document.getElementById('selectedTime').value = clickedButton.dataset.startTime;
                document.getElementById('selectedSlotId').value = clickedButton.dataset.slotId;
                document.getElementById('nextBtn3').disabled = false;

                // Also update the summary view
                updateSummary();
            }
        });
    }
}

// Service selection
function selectService(card) {
    // Remove selection from all cards
    document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
    
    // Add selection to clicked card
    card.classList.add('selected');
    
    // Update hidden input
    const serviceId = card.getAttribute('data-service-id');
    document.getElementById('selectedService').value = serviceId;
    
    // Enable next button
    document.getElementById('nextBtn1').disabled = false;
    
    // Update summary
    updateSummary();
}

// Therapist selection
function selectTherapist(card) {
    // Remove selection from all cards
    document.querySelectorAll('.therapist-card').forEach(c => c.classList.remove('selected'));
    
    // Add selection to clicked card
    card.classList.add('selected');
    
    // Update hidden input
    const therapistId = card.getAttribute('data-therapist-id');
    document.getElementById('selectedTherapist').value = therapistId;
    
    // Enable next button for therapist step
    const nextBtn2 = document.getElementById('nextBtn2');
    if (nextBtn2) nextBtn2.disabled = false;
    
    // Update summary
    updateSummary();
    
    // Fetch and display slots for this therapist in Step 3
    loadTherapistAvailability(therapistId);
}

// -- Calendar-based Slot Picker Logic --
let availabilityData = {};
let currentDate = new Date();
let selectedDateStr = '';

function loadTherapistAvailability(therapistId) {
    const calendarContainer = document.getElementById('calendar-container');
    const timeSlotsList = document.querySelector('.time-slots-list');
    const timeSlotsHeader = document.getElementById('time-slots-header');
    const instruction = document.querySelector('.time-slot-instruction');

    // Reset UI state for the calendar and time slots
    calendarContainer.innerHTML = '<div class="loading-slots">Loading available dates...</div>';
    timeSlotsList.innerHTML = '';
    timeSlotsHeader.style.display = 'none';
    instruction.style.display = 'block';

    document.getElementById('nextBtn3').disabled = true;
    document.getElementById('selectedDate').value = '';
    document.getElementById('selectedTime').value = '';

    fetch(`dashboard/get_therapist_availability.php?therapist_id=${therapistId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success || !data.slots || data.slots.length === 0) {
                calendarContainer.innerHTML = '<div class="no-slots">This therapist has no available slots. Please select another therapist.</div>';
                return;
            }
            
            availabilityData = {};
            data.slots.forEach(slot => {
                const date = slot.available_date;
                if (!availabilityData[date]) availabilityData[date] = [];
                availabilityData[date].push(slot);
            });

            const firstAvailableDate = Object.keys(availabilityData)[0];
            currentDate = new Date(firstAvailableDate + 'T00:00:00'); // Use T00:00:00 to avoid timezone issues
            
            renderCalendar();
        })
        .catch(error => {
            console.error('Error fetching availability:', error);
            calendarContainer.innerHTML = '<div class="no-slots">Error loading availability. Please try again.</div>';
        });
}

function renderCalendar() {
    const calendarContainer = document.getElementById('calendar-container');
    calendarContainer.innerHTML = '';

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);

    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    let header = `<div class="calendar-header">
        <button class="calendar-nav" id="prev-month">&lt;</button>
        <h4>${firstDay.toLocaleString('default', { month: 'long' })} ${year}</h4>
        <button class="calendar-nav" id="next-month">&gt;</button>
    </div>`;

    let weekdays = `<div class="calendar-grid">`;
    ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'].forEach(day => {
        weekdays += `<div class="calendar-weekday">${day}</div>`;
    });
    weekdays += `</div>`;

    let daysGrid = `<div class="calendar-grid">`;
    for (let i = 0; i < firstDay.getDay(); i++) {
        daysGrid += `<div class="calendar-day"></div>`;
    }
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
        
        let classes = 'calendar-day in-month';
        if (dateStr === todayStr) classes += ' today';
        if (availabilityData[dateStr]) classes += ' available';
        if (dateStr === selectedDateStr) classes += ' selected';
        
        daysGrid += `<div class="${classes}" data-date="${dateStr}">${i}</div>`;
    }
    daysGrid += `</div>`;
    
    calendarContainer.innerHTML = header + weekdays + daysGrid;

    document.getElementById('prev-month').addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() - 1); renderCalendar(); });
    document.getElementById('next-month').addEventListener('click', () => { currentDate.setMonth(currentDate.getMonth() + 1); renderCalendar(); });

    document.querySelectorAll('.calendar-day.available').forEach(day => {
        day.addEventListener('click', () => {
            // Remove selection from the previously selected day
            const prevSelected = calendarContainer.querySelector('.calendar-day.selected');
            if (prevSelected) {
                prevSelected.classList.remove('selected');
            }
            // Add selection to the newly clicked day
            day.classList.add('selected');

            selectedDateStr = day.getAttribute('data-date');
            document.getElementById('selectedDate').value = selectedDateStr;
            showTimeSlotsForDate(selectedDateStr);
        });
    });
}

function showTimeSlotsForDate(dateStr) {
    const slots = availabilityData[dateStr];
    const timeSlotsList = document.querySelector('.time-slots-list');
    const instruction = document.querySelector('.time-slot-instruction');
    const header = document.getElementById('time-slots-header');
    
    instruction.style.display = 'none';
    header.style.display = 'block';
    document.getElementById('selected-date-display').textContent = new Date(dateStr + 'T00:00:00').toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });

    let slotsHtml = '';
    slots.forEach(slot => {
        const timeRange = `${slot.start_time.substring(0, 5)} - ${slot.end_time.substring(0, 5)}`;
        slotsHtml += `<button type="button" class="slot-card" data-start-time="${slot.start_time}" data-slot-id="${slot.id}">${timeRange}</button>`;
    });
    timeSlotsList.innerHTML = slotsHtml;

    // Reset time selection
    document.getElementById('selectedTime').value = '';
    document.getElementById('selectedSlotId').value = '';
    document.getElementById('nextBtn3').disabled = true;
}

// Client details validation
function validateClientDetails() {
    const name = document.getElementById('client_name').value.trim();
    const email = document.getElementById('client_email').value.trim();
    const phone = document.getElementById('client_phone').value.trim();
    
    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValidEmail = emailRegex.test(email);
    
    if (name && isValidEmail && phone) {
        updateSummary();
    }
}

// Next step
function nextStep() {
    if (currentStep < totalSteps) {
        // Validate current step
        if (validateCurrentStep()) {
            currentStep++;
            showStep(currentStep);
            updateProgress();
        }
    }
}

// Previous step
function prevStep() {
    if (currentStep > 1) {
        currentStep--;
        showStep(currentStep);
        updateProgress();
    }
}

// Show specific step
function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.form-step').forEach(s => s.classList.remove('active'));
    
    // Show current step
    document.getElementById(`step${step}`).classList.add('active');
    
    // Update step indicators
    document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
    document.querySelector(`[data-step="${step}"]`).classList.add('active');
}

// Update progress bar
function updateProgress() {
    const progress = (currentStep / totalSteps) * 100;
    document.getElementById('progressFill').style.width = `${progress}%`;
}

// Validate current step
function validateCurrentStep() {
    switch(currentStep) {
        case 1:
            return document.getElementById('selectedService').value !== '';
        case 2:
            return document.getElementById('selectedTherapist').value !== '';
        case 3:
            return document.getElementById('selectedDate').value !== '' && 
                   document.getElementById('selectedTime').value !== '';
        case 4:
            const name = document.getElementById('client_name').value.trim();
            const email = document.getElementById('client_email').value.trim();
            const phone = document.getElementById('client_phone').value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return name && emailRegex.test(email) && phone;
        default:
            return true;
    }
}

// Update appointment summary
function updateSummary() {
    // Service
    const selectedServiceCard = document.querySelector('.service-card.selected');
    if (selectedServiceCard) {
        const serviceName = selectedServiceCard.querySelector('h3').textContent;
        document.getElementById('summaryService').textContent = serviceName;
    }
    
    // Date
    const date = document.getElementById('selectedDate').value;
    if (date) {
        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        document.getElementById('summaryDate').textContent = formattedDate;
    }
    
    // Time
    const time = document.getElementById('selectedTime').value;
    if (time) {
        const d = new Date(`1970-01-01T${time}`);
        const formattedTime = d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
        document.getElementById('summaryTime').textContent = formattedTime;
    }
    
    // Therapist
    const selectedTherapistCard = document.querySelector('.therapist-card.selected');
    if (selectedTherapistCard) {
        const therapistName = selectedTherapistCard.querySelector('h3').textContent;
        document.getElementById('summaryTherapist').textContent = therapistName;
    }
}

// Form submission
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Final validation
    if (!validateCurrentStep()) {
        alert('Please complete all required fields.');
        return;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Booking...';
    submitBtn.disabled = true;
    
    // Submit form
    this.submit();
}); 