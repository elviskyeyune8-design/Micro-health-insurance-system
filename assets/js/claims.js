document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    const CLAIMS_KEY = "afyamic_ro_demo_claims";
    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const getElement = id => document.getElementById(id);

    const formatMoney = amount =>
        "UGX " + Number(amount).toLocaleString("en-UG");

    // Load demonstration member profile.
    let profile = null;

    try {
        profile = JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
    } catch (error) {
        console.warn("Member profile could not be loaded.", error);
    }

    const memberName = profile?.fullName || "AfyaMicro Member";

    function getInitials(name) {
        return name.trim().split(/\s+/).filter(Boolean)
            .slice(0, 2)
            .map(part => part.charAt(0).toUpperCase())
            .join("") || "AM";
    }

    const initials = getInitials(memberName);

    ["sidebarAvatar", "topbarAvatar"].forEach(id => {
        if (getElement(id)) getElement(id).textContent = initials;
    });

    ["sidebarUserName", "topbarUserName"].forEach(id => {
        if (getElement(id)) getElement(id).textContent = memberName;
    });

    if (getElement("claimsYear")) {
        getElement("claimsYear").textContent = new Date().getFullYear();
    }

    // Load saved demonstration claims.
    let claims = [];

    try {
        const savedClaims = localStorage.getItem(CLAIMS_KEY);

        if (savedClaims) {
            const parsed = JSON.parse(savedClaims);
            if (Array.isArray(parsed)) claims = parsed;
        }
    } catch (error) {
        console.warn("Claim records could not be loaded.", error);
    }

    function saveClaims() {
        try {
            localStorage.setItem(CLAIMS_KEY, JSON.stringify(claims));
            return true;
        } catch (error) {
            console.error("Could not save claim records.", error);
            return false;
        }
    }

    function renderClaims() {
        const tableBody = getElement("claimsHistory");
        if (!tableBody) return;

        tableBody.replaceChildren();

        if (claims.length === 0) {
            const row = document.createElement("tr");
            const cell = document.createElement("td");

            cell.colSpan = 6;
            cell.className = "empty-claims";
            cell.textContent = "No demonstration claims submitted yet.";

            row.appendChild(cell);
            tableBody.appendChild(row);
        } else {
            claims.forEach(claim => {
                const row = document.createElement("tr");

                const values = [
                    claim.reference,
                    claim.type,
                    claim.provider,
                    claim.serviceDate,
                    formatMoney(claim.amount)
                ];

                values.forEach((value, index) => {
                    const cell = document.createElement("td");
                    cell.textContent = value;

                    if (index === 0) {
                        cell.className = "claim-reference";
                    }

                    row.appendChild(cell);
                });

                const statusCell = document.createElement("td");
                const statusBadge = document.createElement("span");

                statusBadge.className = "claim-status";
                statusBadge.textContent = claim.status || "Pending demo review";

                statusCell.appendChild(statusBadge);
                row.appendChild(statusCell);
                tableBody.appendChild(row);
            });
        }

        const pending = claims.filter(
            claim => claim.status === "Pending demo review"
        ).length;

        const approved = claims.filter(
            claim => claim.status === "Approved"
        ).length;

        const rejected = claims.filter(
            claim => claim.status === "Rejected"
        ).length;

        if (getElement("totalClaims")) {
            getElement("totalClaims").textContent = claims.length;
        }

        if (getElement("pendingClaims")) {
            getElement("pendingClaims").textContent = pending;
        }

        if (getElement("approvedClaims")) {
            getElement("approvedClaims").textContent = approved;
        }

        if (getElement("rejectedClaims")) {
            getElement("rejectedClaims").textContent = rejected;
        }

        if (getElement("claimRecordCount")) {
            getElement("claimRecordCount").textContent =
                claims.length + (claims.length === 1 ? " record" : " records");
        }
    }

    renderClaims();

    // Set the date input maximum to today.
    const dateInput = getElement("claimDate");

    if (dateInput) {
        const today = new Date();
        const localToday = [
            today.getFullYear(),
            String(today.getMonth() + 1).padStart(2, "0"),
            String(today.getDate()).padStart(2, "0")
        ].join("-");

        dateInput.max = localToday;
    }

    // Submit a demonstration claim.
    const form = getElement("claimForm");

    if (form) {
        form.addEventListener("submit", event => {
            event.preventDefault();

            const type = getElement("claimType").value;
            const amountInput = getElement("claimAmount");
            const providerInput = getElement("claimPharmacy");
            const descriptionInput = getElement("claimDescription");

            const amount = Number(amountInput.value);
            const provider = providerInput.value.trim();
            const serviceDate = dateInput.value;
            const description = descriptionInput.value.trim();

            const amountError = getElement("claimAmountError");
            const dateError = getElement("claimDateError");
            const feedback = getElement("claimFeedback");

            amountError.textContent = "";
            dateError.textContent = "";
            feedback.textContent = "";
            feedback.className = "claim-feedback";

            if (!Number.isSafeInteger(amount) || amount < 500) {
                amountError.textContent =
                    "The minimum claim amount is UGX 500. Enter a valid whole number.";
                amountInput.focus();
                return;
            }

            if (!serviceDate) {
                dateError.textContent = "Please select the service date.";
                dateInput.focus();
                return;
            }

            const selectedDate = new Date(serviceDate + "T00:00:00");
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (Number.isNaN(selectedDate.getTime()) || selectedDate > today) {
                dateError.textContent =
                    "The service date cannot be in the future.";
                dateInput.focus();
                return;
            }

            if (!type || !provider || provider.length < 2 ||
                !description || description.length < 10) {
                feedback.textContent =
                    "Complete all required fields. Description must be at least 10 characters.";
                feedback.classList.add("error");
                return;
            }

            const newClaim = {
                reference: "AFY-CLM-" + Date.now().toString().slice(-8),
                type: type,
                amount: amount,
                provider: provider,
                serviceDate: serviceDate,
                description: description,
                submittedAt: new Date().toISOString(),
                status: "Pending demo review"
            };

            claims.unshift(newClaim);

            if (!saveClaims()) {
                claims.shift();

                feedback.textContent =
                    "Your browser could not save this demonstration claim. Check browser storage settings.";
                feedback.classList.add("error");
                return;
            }

            renderClaims();
            form.reset();

            if (dateInput) {
                dateInput.max = [
                    today.getFullYear(),
                    String(today.getMonth() + 1).padStart(2, "0"),
                    String(today.getDate()).padStart(2, "0")
                ].join("-");
            }

            feedback.textContent =
                "Your demonstration claim has been recorded. Its sample status is Pending demo review.";
            feedback.classList.add("success");
        });
    }

    // Mobile sidebar navigation.
    const sidebar = getElement("dashboardSidebar");
    const toggle = getElement("dashboardMenuToggle");
    const overlay = getElement("dashboardOverlay");

    function closeSidebar() {
        if (!sidebar || !toggle || !overlay) return;

        sidebar.classList.remove("open");
        overlay.classList.remove("active");
        toggle.setAttribute("aria-expanded", "false");
        toggle.setAttribute("aria-label", "Open navigation menu");
        document.body.style.overflow = "";
    }

    if (sidebar && toggle && overlay) {
        toggle.addEventListener("click", () => {
            const isOpen = sidebar.classList.contains("open");

            if (isOpen) {
                closeSidebar();
            } else {
                sidebar.classList.add("open");
                overlay.classList.add("active");
                toggle.setAttribute("aria-expanded", "true");
                toggle.setAttribute("aria-label", "Close navigation menu");
                document.body.style.overflow = "hidden";
            }
        });

        overlay.addEventListener("click", closeSidebar);

        sidebar.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => {
                if (window.innerWidth <= 768) closeSidebar();
            });
        });

        document.addEventListener("keydown", event => {
            if (event.key === "Escape") closeSidebar();
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 768) closeSidebar();
        });
    }

    console.log("AfyaMicro Claims demonstration initialized.");
});
