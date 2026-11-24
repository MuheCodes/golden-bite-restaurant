
/* =========================================
   GOLDEN BITE — INTERACTIVE FEATURES
========================================= */


/* 1. MOBILE NAVIGATION */

const menuToggle = document.querySelector("#menuToggle");
const mainNav = document.querySelector("#mainNav");

if (menuToggle && mainNav) {

    function closeNavigation() {
        mainNav.classList.remove("open");

        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Open navigation");
    }

    menuToggle.addEventListener("click", () => {

        const isOpen = mainNav.classList.toggle("open");

        menuToggle.setAttribute("aria-expanded", String(isOpen));

        menuToggle.setAttribute(
            "aria-label",
            isOpen ? "Close navigation" : "Open navigation"
        );
    });

    document.querySelectorAll(".nav-link").forEach((link) => {

        link.addEventListener("click", closeNavigation);

    });

    // Close navigation when clicking outside
    document.addEventListener("click", (event) => {

        if (
            !mainNav.contains(event.target) &&
            !menuToggle.contains(event.target)
        ) {
            closeNavigation();
        }

    });

}


/* 2. MENU CATEGORY FILTERS */

const filterButtons = document.querySelectorAll(".filter-button");
const menuCards = document.querySelectorAll(".menu-card");

filterButtons.forEach((button) => {

    button.addEventListener("click", () => {

        const selectedCategory = button.dataset.filter;

        // Update active filter
        filterButtons.forEach((item) => {
            item.classList.remove("active");
        });

        button.classList.add("active");

        // Filter menu cards
        menuCards.forEach((card) => {

            const matches =
                selectedCategory === "all" ||
                card.dataset.category === selectedCategory;

            card.classList.toggle("hidden", !matches);

        });

    });

});


/* 3. RESERVATION FORM FEEDBACK */

const bookingForm = document.querySelector("#bookingForm");
const formMessage = document.querySelector("#formMessage");

if (bookingForm && formMessage) {

    bookingForm.addEventListener("submit", () => {

        formMessage.textContent = "Submitting your reservation...";

    });

}


/* 4. AUTOMATIC FOOTER YEAR */

const yearElement = document.querySelector("#year");

if (yearElement) {

    yearElement.textContent = new Date().getFullYear();

}


/* 5. PREVENT PAST RESERVATION DATES */

const dateInput = document.querySelector("#visitDate");

if (dateInput) {

    const today = new Date();

    const localToday = new Date(
        today.getTime() - today.getTimezoneOffset() * 60000
    )
        .toISOString()
        .split("T")[0];

    dateInput.min = localToday;

}
