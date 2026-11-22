/* AFYAMICRO KAMPALA - PROFILE
   Saves demonstration profile information in localStorage.
*/

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const form = document.getElementById("profileForm");
    const fullNameInput = document.getElementById("fullName");
    const phoneInput = document.getElementById("phone");
    const ninInput = document.getElementById("nin");
    const pharmacyInput = document.getElementById("homePharmacy");
    const feedback = document.getElementById("profileFeedback");

    let originalProfile = {};

    function loadProfile() {
        try {
            const saved = JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
            return saved && typeof saved === "object" ? saved : {};
        } catch (error) {
            console.warn("Could not read demonstration profile.", error);
            return {};
        }
    }

    function saveProfile(profile) {
        try {
            localStorage.setItem(PROFILE_KEY, JSON.stringify(profile));
            return true;
        } catch (error) {
            console.error("Could not save demonstration profile.", error);
            return false;
        }
    }

    function getInitials(name) {
        return String(name || "Member")
            .trim()
            .split(/\s+/)
            .slice(0, 2)
            .map(function (part) {
                return part.charAt(0).toUpperCase();
            })
            .join("") || "U";
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    }

    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = "profile-feedback visible " + type;
    }

    function clearFeedback() {
        feedback.textContent = "";
        feedback.className = "profile-feedback";
    }

    function normalizePhone(value) {
        let phone = String(value || "").trim().replace(/[\s-]/g, "");

        if (phone.startsWith("+256")) {
            phone = "0" + phone.slice(4);
        } else if (phone.startsWith("256")) {
            phone = "0" + phone.slice(3);
        }

        return phone;
    }

    function ensurePharmacyOption(value) {
        if (!value) return;

        const exists = Array.from(pharmacyInput.options).some(function (option) {
            return option.value.toLowerCase() === String(value).toLowerCase();
        });

        if (!exists) {
            const option = document.createElement("option");
            option.value = value;
            option.textContent = value + " (saved selection)";
            pharmacyInput.appendChild(option);
        }
    }

    function renderProfile(profile) {
        const name = profile.fullName || "Member";
        const phone = profile.phone || "";
        const nin = profile.nin || "";
        const pharmacy = profile.homePharmacy || "";

        const initials = getInitials(name);

        setText("profileDisplayName", name);
        setText("profileDisplayPhone", phone || "Phone number not provided");
        setText("sidebarUserName", name);
        setText("topbarUserName", name);
        setText("sidebarAvatar", initials);
        setText("topbarAvatar", initials);
        setText("profileLargeAvatar", initials);

        setText("sidePhone", phone || "Not provided");
        setText("sidePharmacy", pharmacy || "Not selected");

        fullNameInput.value = name === "Member" ? "" : name;
        phoneInput.value = phone;
        ninInput.value = nin;

        ensurePharmacyOption(pharmacy);
        pharmacyInput.value = pharmacy;
    }

    function getFormValues() {
        return {
            fullName: fullNameInput.value.trim(),
            phone: normalizePhone(phoneInput.value),
            nin: ninInput.value.trim(),
            homePharmacy: pharmacyInput.value
        };
    }

    function clearInvalidStyles() {
        [fullNameInput, phoneInput].forEach(function (input) {
            input.classList.remove("invalid");
        });
    }

    function validate(values) {
        clearInvalidStyles();

        if (values.fullName.length < 2) {
            fullNameInput.classList.add("invalid");
            showFeedback("Please enter your full name. It must contain at least two characters.", "error");
            fullNameInput.focus();
            return false;
        }

        if (!/^07[0-9]{8}$/.test(values.phone)) {
            phoneInput.classList.add("invalid");
            showFeedback("Enter a valid Ugandan phone number in the format 07XXXXXXXX.", "error");
            phoneInput.focus();
            return false;
        }

        return true;
    }

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const values = getFormValues();

        if (!validate(values)) return;

        // Keep any additional profile properties already saved.
        const updatedProfile = Object.assign({}, loadProfile(), values);

        if (!saveProfile(updatedProfile)) {
            showFeedback(
                "We could not save your profile in this browser. Check your browser storage settings.",
                "error"
            );
            return;
        }

        originalProfile = Object.assign({}, updatedProfile);
        renderProfile(updatedProfile);

        showFeedback(
            "Your demonstration profile has been updated successfully in this browser.",
            "success"
        );
    });

    document.getElementById("resetProfile").addEventListener("click", function () {
        renderProfile(originalProfile);
        clearInvalidStyles();
        clearFeedback();
    });

    phoneInput.addEventListener("input", function () {
        phoneInput.value = phoneInput.value.replace(/[^0-9+]/g, "").slice(0, 14);
        phoneInput.classList.remove("invalid");
    });

    fullNameInput.addEventListener("input", function () {
        fullNameInput.classList.remove("invalid");
    });

    // Mobile dashboard sidebar
    const menuToggle = document.getElementById("dashboardMenuToggle");
    const sidebar = document.getElementById("dashboardSidebar");
    const overlay = document.getElementById("dashboardOverlay");

    function closeSidebar() {
        if (sidebar) sidebar.classList.remove("active");
        if (overlay) overlay.classList.remove("active");
    }

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener("click", function () {
            sidebar.classList.toggle("active");
            overlay.classList.toggle("active");
        });

        overlay.addEventListener("click", closeSidebar);

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") closeSidebar();
        });
    }

    const logoutLink = document.getElementById("logoutLink");

    if (logoutLink) {
        logoutLink.addEventListener("click", function (event) {
            const confirmed = window.confirm("Are you sure you want to log out?");
            if (!confirmed) event.preventDefault();
        });
    }

    originalProfile = loadProfile();
    renderProfile(originalProfile);
});
