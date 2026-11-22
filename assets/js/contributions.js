document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    const BALANCE_KEY = "afyamic_ro_demo_wallet";
    const CONTRIBUTIONS_KEY = "afyamic_ro_demo_contributions";
    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const getElement = id => document.getElementById(id);

    const formatMoney = amount =>
        "UGX " + Number(amount).toLocaleString("en-UG");

    // Load demonstration member details.
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

    if (getElement("contributionYear")) {
        getElement("contributionYear").textContent = new Date().getFullYear();
    }

    // Read the same demonstration balance used by the Wallet page.
    let balance = 25000;
    let contributions = [];

    try {
        const storedBalance = localStorage.getItem(BALANCE_KEY);
        const storedContributions = localStorage.getItem(CONTRIBUTIONS_KEY);

        if (storedBalance !== null) {
            const parsedBalance = Number(storedBalance);

            if (Number.isFinite(parsedBalance) && parsedBalance >= 0) {
                balance = parsedBalance;
            }
        }

        if (storedContributions) {
            const parsedContributions = JSON.parse(storedContributions);

            if (Array.isArray(parsedContributions)) {
                contributions = parsedContributions;
            }
        }
    } catch (error) {
        console.warn("Contribution demonstration data could not be loaded.", error);
    }

    function saveData() {
        try {
            localStorage.setItem(BALANCE_KEY, String(balance));
            localStorage.setItem(
                CONTRIBUTIONS_KEY,
                JSON.stringify(contributions)
            );

            return true;
        } catch (error) {
            console.error("Could not save contribution data.", error);
            return false;
        }
    }

    function renderHistory() {
        const tableBody = getElement("contributionHistory");
        if (!tableBody) return;

        tableBody.replaceChildren();

        if (contributions.length === 0) {
            const row = document.createElement("tr");
            const cell = document.createElement("td");

            cell.colSpan = 5;
            cell.className = "empty-contributions";
            cell.textContent = "No demonstration contributions recorded yet.";

            row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }

        contributions.forEach(item => {
            const row = document.createElement("tr");

            const values = [
                item.reference,
                item.date,
                item.note || "General contribution",
                formatMoney(item.amount)
            ];

            values.forEach((value, index) => {
                const cell = document.createElement("td");
                cell.textContent = value;

                if (index === 0) {
                    cell.className = "contribution-reference";
                }

                row.appendChild(cell);
            });

            const statusCell = document.createElement("td");
            const status = document.createElement("span");

            status.className = "contribution-status";
            status.textContent = "Demo recorded";

            statusCell.appendChild(status);
            row.appendChild(statusCell);
            tableBody.appendChild(row);
        });
    }

    function renderSummary() {
        const total = contributions.reduce(
            (sum, item) => sum + Number(item.amount || 0),
            0
        );

        if (getElement("totalContributions")) {
            getElement("totalContributions").textContent = formatMoney(total);
        }

        if (getElement("availableBalance")) {
            getElement("availableBalance").textContent = formatMoney(balance);
        }

        if (getElement("formWalletBalance")) {
            getElement("formWalletBalance").textContent = formatMoney(balance);
        }

        if (getElement("contributionCount")) {
            getElement("contributionCount").textContent = contributions.length;
        }

        if (getElement("historyCount")) {
            getElement("historyCount").textContent =
                contributions.length +
                (contributions.length === 1 ? " record" : " records");
        }

        renderHistory();
    }

    renderSummary();

    // Record a demonstration contribution from the wallet.
    const form = getElement("contributionForm");

    if (form) {
        form.addEventListener("submit", event => {
            event.preventDefault();

            const amountInput = getElement("contributionAmount");
            const noteInput = getElement("contributionNote");
            const amountError = getElement("contributionAmountError");
            const feedback = getElement("contributionFeedback");

            const amount = Number(amountInput.value);
            const note = noteInput.value.trim();

            amountError.textContent = "";
            feedback.textContent = "";
            feedback.className = "contribution-feedback";

            if (!Number.isSafeInteger(amount) || amount <= 0) {
                amountError.textContent =
                    "Enter a valid positive whole-number amount.";
                amountInput.focus();
                return;
            }

            if (amount > balance) {
                amountError.textContent =
                    "Your wallet does not have enough demonstration funds.";
                amountInput.focus();
                return;
            }

            const previousBalance = balance;

            const newContribution = {
                reference: "AFY-CON-" + Date.now().toString().slice(-8),
                amount: amount,
                note: note || "General contribution",
                date: new Date().toLocaleString("en-UG"),
                status: "Demo recorded"
            };

            balance -= amount;
            contributions.unshift(newContribution);

            if (!saveData()) {
                balance = previousBalance;
                contributions.shift();

                feedback.textContent =
                    "The browser could not save this demonstration. Check your browser storage settings.";
                feedback.classList.add("error");
                return;
            }

            renderSummary();
            form.reset();

            feedback.textContent =
                "Your demonstration contribution has been recorded and your simulated wallet balance updated.";
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

    console.log("AfyaMicro Contributions demonstration initialized.");
});
