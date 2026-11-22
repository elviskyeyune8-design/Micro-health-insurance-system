/* AFYAMICRO TRANSACTIONS PAGE
   Frontend demonstration using browser localStorage.
*/

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const PROFILE_KEY = "afyamic_ro_demo_profile";
    const TRANSACTIONS_KEY = "afyamic_ro_demo_transactions";
    const CONTRIBUTIONS_KEY = "afyamic_ro_demo_contributions";
    const CLAIMS_KEY = "afyamic_ro_demo_claims";

    const searchInput = document.getElementById("transactionSearch");
    const activityFilter = document.getElementById("activityFilter");
    const statusFilter = document.getElementById("statusFilter");
    const tableBody = document.getElementById("transactionTableBody");
    const mobileList = document.getElementById("transactionMobileList");
    const emptyState = document.getElementById("transactionEmpty");

    function readArray(key) {
        try {
            const value = JSON.parse(localStorage.getItem(key) || "[]");
            return Array.isArray(value) ? value : [];
        } catch (error) {
            console.warn("Could not read demonstration records:", key, error);
            return [];
        }
    }

    function readProfile() {
        try {
            return JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
        } catch (error) {
            return null;
        }
    }

    function formatMoney(value) {
        const amount = Number(value) || 0;
        return "UGX " + amount.toLocaleString("en-UG");
    }

    function safeText(value, fallback) {
        if (value === undefined || value === null || String(value).trim() === "") {
            return fallback || "—";
        }
        return String(value);
    }

    function formatDate(value) {
        if (!value) return "—";

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return String(value);

        return date.toLocaleDateString("en-GB", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });
    }

    function getStatusCategory(status, activityType) {
        const value = String(status || "").toLowerCase();

        if (value.includes("reject") || value.includes("declin") || value.includes("fail")) {
            return "rejected";
        }

        if (value.includes("pending") || value.includes("review") || value.includes("process")) {
            return "pending";
        }

        if (
            value.includes("complete") ||
            value.includes("success") ||
            value.includes("record") ||
            value.includes("approved")
        ) {
            return "completed";
        }

        if (activityType === "deposit" || activityType === "contribution") {
            return "completed";
        }

        return "other";
    }

    function getStatusLabel(status, activityType) {
        if (status) return String(status);

        if (activityType === "deposit") return "Demo recorded";
        if (activityType === "contribution") return "Demo recorded";
        if (activityType === "claim") return "Pending demo review";

        return "Recorded";
    }

    function buildActivityRecords() {
        const deposits = readArray(TRANSACTIONS_KEY);
        const contributions = readArray(CONTRIBUTIONS_KEY);
        const claims = readArray(CLAIMS_KEY);

        const records = [];

        deposits.forEach(function (item, index) {
            const amount = Number(item.amount || item.value || 0);

            records.push({
                id: "deposit-" + index,
                reference: safeText(item.reference || item.ref || item.transactionId, "DEP-" + (index + 1)),
                type: "deposit",
                typeLabel: "Wallet deposit",
                description: safeText(
                    item.method || item.paymentMethod || item.description,
                    "Wallet funds added"
                ),
                date: item.date || item.createdAt || item.timestamp || "",
                amount: amount,
                status: getStatusLabel(item.status, "deposit"),
                statusCategory: getStatusCategory(item.status, "deposit"),
                amountClass: "inflow",
                amountPrefix: "+"
            });
        });

        contributions.forEach(function (item, index) {
            const amount = Number(item.amount || item.value || 0);

            records.push({
                id: "contribution-" + index,
                reference: safeText(item.reference || item.ref, "CON-" + (index + 1)),
                type: "contribution",
                typeLabel: "Contribution",
                description: safeText(item.note || item.description, "Wallet contribution"),
                date: item.date || item.createdAt || item.timestamp || "",
                amount: amount,
                status: getStatusLabel(item.status, "contribution"),
                statusCategory: getStatusCategory(item.status, "contribution"),
                amountClass: "outflow",
                amountPrefix: "−"
            });
        });

        claims.forEach(function (item, index) {
            const amount = Number(item.amount || item.value || 0);

            records.push({
                id: "claim-" + index,
                reference: safeText(item.reference || item.ref, "CLM-" + (index + 1)),
                type: "claim",
                typeLabel: "Health claim",
                description: safeText(
                    item.provider || item.type || item.description,
                    "Health claim submitted"
                ),
                date: item.date || item.createdAt || item.timestamp || "",
                amount: amount,
                status: getStatusLabel(item.status, "claim"),
                statusCategory: getStatusCategory(item.status, "claim"),
                amountClass: "claim-amount",
                amountPrefix: ""
            });
        });

        records.sort(function (a, b) {
            const dateA = new Date(a.date || 0).getTime() || 0;
            const dateB = new Date(b.date || 0).getTime() || 0;
            return dateB - dateA;
        });

        return {
            records: records,
            deposits: deposits,
            contributions: contributions,
            claims: claims
        };
    }

    function updateProfileDisplay() {
        const profile = readProfile();
        const name = profile && profile.fullName ? profile.fullName : "Member";
        const initials = name.trim().split(/\s+/).slice(0, 2)
            .map(function (part) { return part.charAt(0).toUpperCase(); })
            .join("") || "U";

        ["sidebarUserName", "topbarUserName"].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) element.textContent = name;
        });

        ["sidebarAvatar", "topbarAvatar"].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) element.textContent = initials;
        });
    }

    function updateSummary(data) {
        const totalDeposits = data.deposits.reduce(function (sum, item) {
            return sum + Number(item.amount || item.value || 0);
        }, 0);

        const totalContributions = data.contributions.reduce(function (sum, item) {
            return sum + Number(item.amount || item.value || 0);
        }, 0);

        document.getElementById("totalDeposits").textContent = formatMoney(totalDeposits);
        document.getElementById("totalContributions").textContent = formatMoney(totalContributions);
        document.getElementById("totalClaims").textContent = data.claims.length;
        document.getElementById("totalActivities").textContent = data.records.length;
    }

    function createCell(text, className) {
        const cell = document.createElement("td");
        if (className) cell.className = className;
        cell.textContent = text;
        return cell;
    }

    function createStatusBadge(status, category) {
        const badge = document.createElement("span");
        badge.className = "transaction-status " + category;
        badge.textContent = status;
        return badge;
    }

    function createActivityLabel(type, label) {
        const wrapper = document.createElement("span");
        wrapper.className = "activity-label";

        const dot = document.createElement("span");
        dot.className = "activity-dot " + type;

        wrapper.appendChild(dot);
        wrapper.appendChild(document.createTextNode(label));

        return wrapper;
    }

    function renderDesktopTable(records) {
        tableBody.replaceChildren();

        records.forEach(function (record) {
            const row = document.createElement("tr");

            row.appendChild(createCell(record.reference, "transaction-reference"));

            const activityCell = document.createElement("td");
            activityCell.appendChild(createActivityLabel(record.type, record.typeLabel));
            row.appendChild(activityCell);

            row.appendChild(createCell(record.description, "transaction-description"));
            row.appendChild(createCell(formatDate(record.date)));

            const amountCell = document.createElement("td");
            amountCell.className = "transaction-amount " + record.amountClass;

            const amountText = record.type === "claim"
                ? formatMoney(record.amount) + " requested"
                : record.amountPrefix + formatMoney(record.amount);

            amountCell.textContent = amountText;
            row.appendChild(amountCell);

            const statusCell = document.createElement("td");
            statusCell.appendChild(createStatusBadge(record.status, record.statusCategory));
            row.appendChild(statusCell);

            tableBody.appendChild(row);
        });
    }

    function renderMobileList(records) {
        mobileList.replaceChildren();

        records.forEach(function (record) {
            const card = document.createElement("article");
            card.className = "mobile-transaction-card";

            const top = document.createElement("div");
            top.className = "mobile-transaction-top";

            const reference = document.createElement("span");
            reference.className = "mobile-transaction-reference";
            reference.textContent = record.reference;

            top.appendChild(reference);
            top.appendChild(createStatusBadge(record.status, record.statusCategory));

            const activity = document.createElement("div");
            activity.appendChild(createActivityLabel(record.type, record.typeLabel));

            const description = document.createElement("div");
            description.className = "mobile-transaction-description";
            description.textContent = record.description + " · " + formatDate(record.date);

            const bottom = document.createElement("div");
            bottom.className = "mobile-transaction-bottom";

            const amount = document.createElement("strong");
            amount.className = "transaction-amount " + record.amountClass;
            amount.textContent = record.type === "claim"
                ? formatMoney(record.amount) + " requested"
                : record.amountPrefix + formatMoney(record.amount);

            const typeText = document.createElement("span");
            typeText.style.fontSize = "10px";
            typeText.style.color = "#74858B";
            typeText.textContent = record.type === "claim"
                ? "Claim request"
                : record.type === "deposit" ? "Money in" : "Money out";

            bottom.appendChild(amount);
            bottom.appendChild(typeText);

            card.appendChild(top);
            card.appendChild(activity);
            card.appendChild(description);
            card.appendChild(bottom);

            mobileList.appendChild(card);
        });
    }

    function getFilteredRecords(records) {
        const query = searchInput.value.trim().toLowerCase();
        const selectedType = activityFilter.value;
        const selectedStatus = statusFilter.value;

        return records.filter(function (record) {
            const matchesType = selectedType === "all" || record.type === selectedType;

            const searchableText = [
                record.reference,
                record.typeLabel,
                record.description,
                record.status,
                record.date
            ].join(" ").toLowerCase();

            const matchesSearch = !query || searchableText.includes(query);

            let matchesStatus = true;

            if (selectedStatus !== "all") {
                matchesStatus = record.statusCategory === selectedStatus;
            }

            return matchesType && matchesSearch && matchesStatus;
        });
    }

    function render() {
        const data = buildActivityRecords();
        updateSummary(data);

        const filteredRecords = getFilteredRecords(data.records);
        const hasRecords = filteredRecords.length > 0;

        renderDesktopTable(filteredRecords);
        renderMobileList(filteredRecords);

        emptyState.classList.toggle("hidden", hasRecords);
        document.querySelector(".transaction-table-wrap").style.display =
            hasRecords ? "" : "none";
        mobileList.style.display = hasRecords ? "" : "none";

        document.getElementById("recordCount").textContent =
            filteredRecords.length + (filteredRecords.length === 1 ? " record" : " records");

        document.getElementById("transactionFooterText").textContent =
            "Showing " + filteredRecords.length + " of " + data.records.length + " records";
    }

    function resetFilters() {
        searchInput.value = "";
        activityFilter.value = "all";
        statusFilter.value = "all";
        render();
    }

    searchInput.addEventListener("input", render);
    activityFilter.addEventListener("change", render);
    statusFilter.addEventListener("change", render);

    document.getElementById("resetFilters").addEventListener("click", resetFilters);
    document.getElementById("emptyResetFilters").addEventListener("click", resetFilters);

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

    updateProfileDisplay();
    render();

    // Refresh if another page updates records in the same browser tab.
    window.addEventListener("focus", render);
    window.addEventListener("storage", render);
});
