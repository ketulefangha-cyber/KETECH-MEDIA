function toggleMenu() {
    const nav = document.getElementById("navbar");
    nav.style.display = nav.style.display === "flex" ? "none" : "flex";
}

// ===== CONTACT FORM FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', handleContactFormSubmit);
    }
});

function handleContactFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const submitBtn = document.getElementById('submitBtn');
    const formStatus = document.getElementById('formStatus');
    
    // Collect form data
    const formData = {
        name: document.getElementById('name').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        service: document.getElementById('service').value,
        message: document.getElementById('message').value
    };

    // Disable submit button and show loading state
    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    formStatus.textContent = '';
    formStatus.className = 'form-status';

    // Send data to PHP handler
    fetch('api/contact.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';

        if (data.success) {
            formStatus.className = 'form-status success';
            formStatus.textContent = data.message;
            form.reset();
            
            // Clear status message after 5 seconds
            setTimeout(() => {
                formStatus.textContent = '';
            }, 5000);
        } else {
            formStatus.className = 'form-status error';
            formStatus.textContent = data.message || 'Error sending message. Please try again.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.disabled = false;
        submitBtn.textContent = 'Send Message';
        formStatus.className = 'form-status error';
        formStatus.textContent = 'Error sending message. Please check your connection and try again.';
    });
}

// ===== SHIPPING & TRACKING FUNCTIONALITY =====

// Mock database of shipments
const shipmentDatabase = {
    "KETECH-CN-12345": {
        trackingID: "KETECH-CN-12345",
        status: "In Transit",
        location: "Shanghai Port, China",
        delivery: "2026-04-25",
        timeline: [
            { status: "Order Received", location: "Shanghai, China", date: "2026-04-05", completed: true },
            { status: "Customs Cleared", location: "Shanghai Port", date: "2026-04-08", completed: true },
            { status: "Shipped from Port", location: "Shanghai, China", date: "2026-04-10", completed: true },
            { status: "In Transit", location: "At Sea", date: "2026-04-15", completed: true },
            { status: "Port Arrival", location: "Douala Port, Cameroon", date: "2026-04-20", completed: false },
            { status: "Customs Clearance", location: "Douala, Cameroon", date: "2026-04-22", completed: false },
            { status: "Delivery", location: "Destination", date: "2026-04-25", completed: false }
        ]
    },
    "KETECH-CN-54321": {
        trackingID: "KETECH-CN-54321",
        status: "Delivered",
        location: "Recipient Location",
        delivery: "2026-04-10",
        timeline: [
            { status: "Order Received", location: "Shanghai, China", date: "2026-03-25", completed: true },
            { status: "Customs Cleared", location: "Shanghai Port", date: "2026-03-28", completed: true },
            { status: "Shipped from Port", location: "Shanghai, China", date: "2026-03-30", completed: true },
            { status: "In Transit", location: "At Sea", date: "2026-04-05", completed: true },
            { status: "Port Arrival", location: "Douala Port, Cameroon", date: "2026-04-08", completed: true },
            { status: "Customs Clearance", location: "Douala, Cameroon", date: "2026-04-09", completed: true },
            { status: "Delivery", location: "Recipient Location", date: "2026-04-10", completed: true }
        ]
    },
    "KETECH-CN-99999": {
        trackingID: "KETECH-CN-99999",
        status: "Processing",
        location: "Shanghai Warehouse",
        delivery: "2026-05-05",
        timeline: [
            { status: "Order Received", location: "Shanghai, China", date: "2026-04-12", completed: true },
            { status: "Customs Cleared", location: "Shanghai Port", date: "2026-04-18", completed: false },
            { status: "Shipped from Port", location: "Shanghai, China", date: "2026-04-20", completed: false },
            { status: "In Transit", location: "At Sea", date: "2026-04-28", completed: false },
            { status: "Port Arrival", location: "Douala Port, Cameroon", date: "2026-05-03", completed: false },
            { status: "Customs Clearance", location: "Douala, Cameroon", date: "2026-05-04", completed: false },
            { status: "Delivery", location: "Recipient Location", date: "2026-05-05", completed: false }
        ]
    }
};

function trackShipment() {
    const trackingInput = document.getElementById("trackingInput");
    const trackingNumber = trackingInput.value.trim().toUpperCase();
    const resultDiv = document.getElementById("trackingResult");
    const errorDiv = document.getElementById("errorMessage");
    
    // Clear previous messages
    errorDiv.classList.add("hidden");
    resultDiv.classList.add("hidden");
    
    if (!trackingNumber) {
        showError("Please enter a tracking number");
        return;
    }
    
    // Show loading state
    const statusElement = document.getElementById("shipmentStatus");
    statusElement.textContent = "🔄 Tracking...";
    resultDiv.classList.remove("hidden");
    
    // Call backend API to get real tracking data
    fetch(`api/track.php?tracking=${encodeURIComponent(trackingNumber)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('API request failed');
            }
            return response.json();
        })
        .then(shipment => {
            if (shipment.success) {
                displayShipment(shipment);
            } else {
                // Fallback to mock data
                console.log('Using fallback mock data');
                const mockShipment = shipmentDatabase[trackingNumber];
                if (mockShipment) {
                    displayShipment(mockShipment);
                } else {
                    showError("Tracking number not found. Please check and try again. (Demo: KETECH-CN-12345, KETECH-CN-54321, KETECH-CN-99999)");
                    resultDiv.classList.add("hidden");
                }
            }
        })
        .catch(error => {
            console.error('Tracking error:', error);
            // Fallback to mock database
            const mockShipment = shipmentDatabase[trackingNumber];
            if (mockShipment) {
                displayShipment(mockShipment);
            } else {
                showError("Unable to track shipment. Please try again later or check the tracking number.");
                resultDiv.classList.add("hidden");
            }
        });
}

function displayShipment(shipment) {
    const resultDiv = document.getElementById("trackingResult");
    const statusElement = document.getElementById("shipmentStatus");
    const trackingIDElement = document.getElementById("resultTrackingID");
    const statusValueElement = document.getElementById("resultStatus");
    const locationElement = document.getElementById("resultLocation");
    const deliveryElement = document.getElementById("resultDelivery");
    const timelineEventsDiv = document.getElementById("timelineEvents");
    
    // Update basic info
    statusElement.textContent = `Status: ${shipment.status}`;
    trackingIDElement.textContent = shipment.trackingID;
    statusValueElement.textContent = shipment.status;
    locationElement.textContent = shipment.location;
    deliveryElement.textContent = shipment.delivery;
    
    // Build timeline
    timelineEventsDiv.innerHTML = "";
    shipment.timeline.forEach((event, index) => {
        const eventElement = document.createElement("div");
        eventElement.className = `timeline-event ${event.completed ? "completed" : ""}`;
        eventElement.innerHTML = `
            <div class="event-content">
                <h5>${event.status}</h5>
                <p>${event.location}</p>
                <div class="event-time">${event.date}</div>
            </div>
        `;
        timelineEventsDiv.appendChild(eventElement);
    });
    
    // Show result
    resultDiv.classList.remove("hidden");
    
    // Scroll to result
    setTimeout(() => {
        resultDiv.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }, 100);
}

function showError(message) {
    const errorDiv = document.getElementById("errorMessage");
    errorDiv.textContent = message;
    errorDiv.classList.remove("hidden");
}

// Allow Enter key to trigger search
document.addEventListener("DOMContentLoaded", function() {
    const trackingInput = document.getElementById("trackingInput");
    if (trackingInput) {
        trackingInput.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                trackShipment();
            }
        });
    }
});
