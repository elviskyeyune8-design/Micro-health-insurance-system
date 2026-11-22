document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    // ==========================================
    // 1. SHARED BROWSER STORAGE KEYS
    // ==========================================

    const KEYS = {
        profile: "afyamic_ro_demo_profile",
        wallet: "afyamic_ro_demo_wallet",
        transactions: "afyamic_ro_demo_transactions",
        contributions: "afyamic_ro_demo_contributions",
        claims: "afyamic_ro_demo_claims"
    };

    const getElement = id => document.getElementById(id);

    const formatMoney = amount =>
        "UGX " + Number(amount || 0).toLocaleString("en-UG");

    // ==========================================
    // 2. SAFELY READ SAVED RECORDS
    // ==========================================

    function readJSON(key, fallback) {
        try {
            const value = localStorage.getItem(key);

            if (value === null) return fallback;

            return JSON.parse(value);
        } catch (error) {
            console.warn("Could not read storage:", key, error);
            return fallback;
        }
    }

    function readArray(key) {
        const value = readJSON(key, []);
        return Array.isArray(value) ? value : [];
    }

    function readWalletBalance() {
        try {
            const savedBalance = localStorage.getItem(KEYS.wallet);

            if (savedBalance === null) {
                return 25000;
            }

            const balance = Number(savedBalance);

            return Number.isFinite(balance) && balance >= 0
                ? balance
                : 0;

        } catch (error) {
            console.warn("Wallet balance could not be read.", error);
            return 25000;
        }
    }

    // ==========================================
    // 3. MEMBER PROFILE
    // ==========================================

    function updateMemberProfile() {
        const profile = readJSON(KEYS.profile, {});

        const fullName = profile?.fullName || "AfyaMicro Member";
        const phone = profile?.phone || "Not available";
        const pharmacy = profile?.homePharmacy || "Not specified";

        function getInitials(name) {
            return name
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map(word => word.charAt(0).toUpperCase())
                .join("") || "AM";
        }

        const initials = getInitials(fullName);

        const profileFields = {
            sidebarUserName: fullName,
            topbarUserName: fullName,
            welcomeName: fullName,
            accountFullName: fullName,
            accountPhone: phone,
            accountPharmacy: pharmacy,
            sidebarAvatar: initials,
            topbarAvatar: initials
        };

        Object.entries(profileFields).forEach(([id, value]) => {
            const element = getElement(id);

            if (element) {
                element.textContent = value;
            }
        });
    }

    // ==========================================
    // 4. DATE HANDLING
    // ==========================================

    function parseActivityDate(record) {
        if (record.submittedAt) {
            const submitted = new Date(record.submittedAt);

            if (!Number.isNaN(submitted.getTime())) {
                return submitted;
            }
        }

        if (!record.date) {
            return new Date(0);
        }

        const value = String(record.date);

        // Handle dates formatted as DD/MM/YYYY.
        const match = value.match(
            /^(\d{1,2})\/(\d{1,2})\/(\d{4})/
        );

        if (match) {
            const day = Number(match[1]);
            const month = Number(match[2]);
            const year = Number(match[3]);

            const parsed = new Date(year, month - 1, day);

            if (
                parsed.getFullYear() === year &&
                parsed.getMonth() === month - 1 &&
                parsed.getDate() === day
            ) {
                return parsed;
            }
        }

        const fallback = new Date(value);

        return Number.isNaN(fallback.getTime())
            ? new Date(0)
            : fallback;
    }

    function displayDate(date) {
        if (!date || date.getTime() === 0) {
            return "Date unavailable";
        }

        return date.toLocaleDateString("en-UG", {
            day: "2-digit",
            month: "short",
            year: "numeric"
        });
    }

    // ==========================================
    // 5. LOAD WALLET AND MEMBER ACTIVITY
    // ==========================================

    function getDashboardData() {
        const balance = readWalletBalance();

        const transactions = readArray(KEYS.transactions);
        const contributions = readArray(KEYS.contributions);
        const claims = readArray(KEYS.claims);

        const totalContributions = contributions.reduce(
            (total, item) => {
                const amount = Number(item.amount || 0);

                return total + (
                    Number.isFinite(amount) && amount > 0
                        ? amount
                        : 0
                );
            },
            0
        );

        const pendingClaims = claims.filter(
            claim => claim.status === "Pending demo review"
        ).length;

        const approvedClaims = claims.filter(
            claim => claim.status === "Approved"
        ).length;

        // Convert deposits into dashboard activity.
        const depositActivity = transactions.map(item => ({
            title: "Wallet deposit",
            reference: item.reference || "Deposit record",
            amount: Number(item.amount || 0),
            date: item.date || "",
            submittedAt: item.submittedAt || "",
            status: item.status || "Demo recorded",
            sortDate: parseActivityDate(item)
        }));

        // Convert contributions into dashboard activity.
        const contributionActivity = contributions.map(item => ({
            title: "Contribution recorded",
            reference: item.reference || "Contribution record",
            amount: Number(item.amount || 0),
            date: item.date || "",
            submittedAt: item.submittedAt || "",
            status: item.status || "Demo recorded",
            sortDate: parseActivityDate(item)
        }));

        // Claims are displayed as requested amounts.
        // They do not deduct money from the wallet.
        const claimActivity = claims.map(item => ({
            title: "Health claim submitted",
            reference: item.reference || "Claim record",
            amount: Number(item.amount || 0),
            date: item.serviceDate || "",
            submittedAt: item.submittedAt || "",
            status: item.status || "Pending demo review",
            sortDate: parseActivityDate(item)
        }));

        const recentActivity = [
            ...depositActivity,
            ...contributionActivity,
            ...claimActivity
        ].sort((a, b) => b.sortDate.getTime() - a.sortDate.getTime());

        return {
            balance,
            totalContributions,
            totalClaims: claims.length,
            pendingClaims,
            approvedClaims,
            transactions,
            contributions,
            claims,
            recentActivity
        };
    }

    // ==========================================
    // 6. UPDATE DASHBOARD SUMMARY CARDS
    // ==========================================

    function updateDashboardSummaries(data) {
        const summaries = {
            summaryBalance: formatMoney(data.balance),
            walletBalance: formatMoney(data.balance),

            summaryContributions: formatMoney(data.totalContributions),
            walletContributions: formatMoney(data.totalContributions),

            summaryClaims: String(data.totalClaims),
            summaryStatus: "Demo",

            memberSince: "Demo account",
            dashboardYear: new Date().getFullYear()
        };

        Object.entries(summaries).forEach(([id, value]) => {
            const element = getElement(id);

            if (element) {
                element.textContent = value;
            }
        });
    }

    // ==========================================
    // 7. RECENT ACTIVITY TABLE
    // ==========================================

    function createCell(text, className) {
        const cell = document.createElement("td");

        if (className) {
            cell.className = className;
        }

        cell.textContent = text;

        return cell;
    }

    function renderRecentActivity(activity) {
        const tableBody = getElement("recentTransactions");

        if (!tableBody) return;

        tableBody.replaceChildren();

        if (activity.length === 0) {
            const row = document.createElement("tr");
            const cell = createCell(
                "No wallet deposits, contributions or claims recorded yet.",
                "empty-activity"
            );

            cell.colSpan = 4;

            row.appendChild(cell);
            tableBody.appendChild(row);

            return;
        }

        activity.slice(0, 5).forEach(item => {
            const row = document.createElement("tr");

            // Transaction name and reference.
            const transactionCell = document.createElement("td");

            const primary = document.createElement("span");
            primary.className = "table-primary-text";
            primary.textContent = item.title;

            const secondary = document.createElement("span");
            secondary.className = "table-secondary-text";
            secondary.textContent = "Reference: " + item.reference;

            transactionCell.appendChild(primary);
            transactionCell.appendChild(secondary);

            row.appendChild(transactionCell);

            // Activity date.
            row.appendChild(
                createCell(displayDate(item.sortDate))
            );

            // Activity amount.
            row.appendChild(
                createCell(formatMoney(item.amount))
            );

            // Activity status.
            const statusCell = document.createElement("td");
            const badge = document.createElement("span");

            badge.className = "status-pill";

            const status = String(item.status);

            if (
                status.toLowerCase().includes("approved") ||
                status.toLowerCase().includes("recorded")
            ) {
                badge.classList.add("status-success");
            } else {
                badge.classList.add("status-pending");
            }

            badge.textContent = status;

            statusCell.appendChild(badge);
            row.appendChild(statusCell);

            tableBody.appendChild(row);
        });
    }

    // ==========================================
    // 8. REFRESH ALL DASHBOARD INFORMATION
    // ==========================================

    function refreshDashboard() {
        updateMemberProfile();

        const data = getDashboardData();

        updateDashboardSummaries(data);
        renderRecentActivity(data.recentActivity);

        console.log("AfyaMicro dashboard data refreshed.", {
            walletBalance: data.balance,
            totalContributions: data.totalContributions,
            totalClaims: data.totalClaims,
            pendingClaims: data.pendingClaims,
            approvedClaims: data.approvedClaims,
            recentActivities: data.recentActivity.length
        });
    }

    // Initial dashboard load.
    refreshDashboard();

    // Refresh when another browser tab updates shared data.
    window.addEventListener("storage", event => {
        if (Object.values(KEYS).includes(event.key) || event.key === null) {
            refreshDashboard();
        }
    });

    // Refresh when the member returns to the dashboard.
    window.addEventListener("focus", refreshDashboard);

    // ==========================================
    // 9. MOBILE SIDEBAR MENU
    // ==========================================

    const sidebar = getElement("dashboardSidebar");
    const menuToggle = getElement("dashboardMenuToggle");
    const overlay = getElement("dashboardOverlay");

    function openSidebar() {
        if (!sidebar || !overlay || !menuToggle) return;

        sidebar.classList.add("open");
        overlay.classList.add("active");

        menuToggle.setAttribute("aria-expanded", "true");
        menuToggle.setAttribute("aria-label", "Close navigation menu");

        document.body.style.overflow = "hidden";
    }

    function closeSidebar() {
        if (!sidebar || !overlay || !menuToggle) return;

        sidebar.classList.remove("open");
        overlay.classList.remove("active");

        menuToggle.setAttribute("aria-expanded", "false");
        menuToggle.setAttribute("aria-label", "Open navigation menu");

        document.body.style.overflow = "";
    }

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener("click", () => {
            const isOpen = sidebar.classList.contains("open");

            if (isOpen) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        overlay.addEventListener("click", closeSidebar);

        sidebar.querySelectorAll("a").forEach(link => {
            link.addEventListener("click", () => {
                if (window.innerWidth <= 768) {
                    closeSidebar();
                }
            });
        });

        document.addEventListener("keydown", event => {
            if (event.key === "Escape") {
                closeSidebar();
            }
        });

        window.addEventListener("resize", () => {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });
    }

    // ==========================================
    // 10. DEMONSTRATION LOGOUT
    // ==========================================

    const logoutLinks = document.querySelectorAll(
        'a[href="../auth/login.html"]'
    );

    logoutLinks.forEach(link => {
        if (
            link.textContent.toLowerCase().includes("logout") ||
            link.textContent.toLowerCase().includes("log out")
        ) {
            link.addEventListener("click", event => {
                const confirmed = window.confirm(
                    "Are you sure you want to leave the demonstration dashboard?"
                );

                if (!confirmed) {
                    event.preventDefault();
                }
            });
        }
    });

    console.log("AfyaMicro connected dashboard initialized successfully.");
});

/* =========================================================
   LIVE CLAIMS AND RECENT NOTIFICATIONS
   Reads the records saved by the member pages.
   Frontend demonstration only.
========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

const CLAIMS_KEY = "afyamic_ro_demo_claims";
    const NOTIFICATIONS_KEY = "afyamic_ro_demo_notifications";

function readRecords(key) {
        try {
            const records = JSON.parse(localStorage.getItem(key) || "[]");
            return Array.isArray(records) ? records : [];
        } catch (error) {
            console.warn("Could not read dashboard records:", key, error);
            return [];
        }
    }

function formatMoney(amount) {
        return "UGX " + Number(amount || 0).toLocaleString("en-UG");
    }

function formatDate(value) {
        const date = new Date(value);

if (Number.isNaN(date.getTime())) {
            return "Date unavailable";
        }

return date.toLocaleDateString("en-UG", {
            day: "numeric",
            month: "short",
            year: "numeric"
        });
    }

function createCell(text, className) {
        const cell = document.createElement("td");
        cell.textContent = text || "—";

if (className) {
            cell.className = className;
        }

return cell;
    }

// CLAIMS OVERVIEW
    function renderDashboardClaims() {
        const tableBody = document.getElementById("dashboardClaims");
        if (!tableBody) return;

const claims = readRecords(CLAIMS_KEY).sort(function (a, b) {
            return new Date(b.submittedAt || 0) -
                   new Date(a.submittedAt || 0);
        });

        tableBody.replaceChildren();

if (claims.length === 0) {
            const row = document.createElement("tr");
            const cell = document.createElement("td");

cell.colSpan = 4;
            cell.className = "dashboard-empty";
            cell.textContent =
                "No claims submitted yet. Your submitted claims will appear here.";

row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }

claims.slice(0, 5).forEach(function (claim) {
            const row = document.createElement("tr");

row.appendChild(
                createCell(claim.reference || "No reference", "table-primary-text")
            );

row.appendChild(
                createCell(claim.type || "Health claim")
            );

row.appendChild(
                createCell(formatMoney(claim.amount))
            );

const statusCell = document.createElement("td");
            const status = document.createElement("span");
            const statusText = claim.status || "Pending demo review";

            status.className = "status-badge";

if (statusText.toLowerCase().includes("approved")) {
                status.classList.add("success");
            } else if (statusText.toLowerCase().includes("rejected")) {
                status.classList.add("rejected");
            } else {
                status.classList.add("pending");
            }

status.textContent = statusText;
            statusCell.appendChild(status);
            row.appendChild(statusCell);

tableBody.appendChild(row);
        });
    }

// RECENT NOTIFICATIONS
    function renderDashboardNotifications() {
        const list = document.getElementById("dashboardNotifications");
        if (!list) return;

const notifications = readRecords(NOTIFICATIONS_KEY).sort(
            function (a, b) {
                return new Date(b.date || 0) - new Date(a.date || 0);
            }
        );

        list.replaceChildren();

if (notifications.length === 0) {
            const empty = document.createElement("div");
            empty.className = "dashboard-empty";
            empty.textContent = "You do not have any notifications yet.";
            list.appendChild(empty);
            return;
        }

const icons = {
            account: "◎",
            wallet: "▣",
            contribution: "↗",
            claim: "＋",
            general: "ⓘ"
        };

notifications.slice(0, 3).forEach(function (item) {
            const notice = document.createElement("div");
            notice.className = "dashboard-notice";

const icon = document.createElement("span");
            icon.className = "notice-icon";
            icon.textContent = icons[item.category] || "ⓘ";

            const content = document.createElement("div");

const title = document.createElement("strong");
            title.textContent = item.title || "Notification";

const message = document.createElement("p");
            message.textContent = item.message || "";

const time = document.createElement("time");
            time.textContent = formatDate(item.date);

if (!item.read) {
                notice.classList.add("unread");
                title.textContent = "NEW · " + title.textContent;
            }

content.appendChild(title);
            content.appendChild(message);
            content.appendChild(time);

notice.appendChild(icon);
            notice.appendChild(content);

list.appendChild(notice);
        });
    }

function refreshDashboardRecords() {
        renderDashboardClaims();
        renderDashboardNotifications();
    }

    refreshDashboardRecords();

// Refresh when returning from another member page.
    window.addEventListener("focus", refreshDashboardRecords);

// Refresh when another browser tab changes saved records.
    window.addEventListener("storage", function (event) {
        if (
            event.key === CLAIMS_KEY ||
            event.key === NOTIFICATIONS_KEY
        ) {
            refreshDashboardRecords();
        }
    });
});
