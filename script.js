
const menuToggle = document.getElementById("menuToggle");
const navLinks = document.getElementById("navLinks");

// Mobile navigation menu
if (menuToggle && navLinks) {
  menuToggle.addEventListener("click", () => {
    navLinks.classList.toggle("open");
  });

  navLinks.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => {
      navLinks.classList.remove("open");
    });
  });
}

// Painting cost estimate calculator
// Per-square-foot amounts are illustrative only.
const areaInput = document.getElementById("area");
const serviceType = document.getElementById("serviceType");
const estimateValue = document.getElementById("estimateValue");

function updateEstimate() {
  if (!areaInput || !serviceType || !estimateValue) return;

  const area = Math.max(0, Number(areaInput.value) || 0);
  const rate = Number(serviceType.value);

  estimateValue.textContent = (area * rate).toLocaleString("en-IN", {
    style: "currency",
    currency: "INR",
    maximumFractionDigits: 0
  });
}

if (areaInput && serviceType && estimateValue) {
  areaInput.addEventListener("input", updateEstimate);
  serviceType.addEventListener("change", updateEstimate);
  updateEstimate();
}

// Prevent selecting a past date for a site visit
const visitDateInput = document.getElementById("visitDate");

function setMinimumVisitDate() {
  if (!visitDateInput) return;

  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");

  visitDateInput.min = `${year}-${month}-${day}`;
}

setMinimumVisitDate();

// Booking form: send details to the PHP backend
const bookingForm = document.getElementById("bookingForm");
const formMessage = document.getElementById("formMessage");

if (bookingForm && formMessage) {
  bookingForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    // Validate required fields
    if (!bookingForm.checkValidity()) {
      bookingForm.reportValidity();
      return;
    }

    const submitButton = bookingForm.querySelector(
      'button[type="submit"]'
    );

    if (submitButton) {
      submitButton.disabled = true;
    }

    formMessage.textContent = "Submitting your site visit request...";

    try {
      // Send the form data to book-demo.php
      const response = await fetch("book-demo.php", {
        method: "POST",
        body: new FormData(bookingForm)
      });

      // Read the response returned by PHP
      const result = await response.json();

      if (!response.ok || !result.success) {
        throw new Error(
          result.message || "Unable to save your booking."
        );
      }

      // Show success only after PHP confirms the booking was saved
      formMessage.textContent = result.message;

      // Reset the form after successful submission
      bookingForm.reset();

      // Restore the default city
      const cityInput = document.getElementById("city");
      if (cityInput) {
        cityInput.value = "Hyderabad";
      }

      // Refresh the painting estimate if the calculator exists
      updateEstimate();

      // Restore the minimum date after resetting the form
      setMinimumVisitDate();

    } catch (error) {
      console.error("Booking submission error:", error);

      formMessage.textContent =
        error.message ||
        "Unable to submit your request. Please try again.";

    } finally {
      if (submitButton) {
        submitButton.disabled = false;
      }
    }
  });
}
