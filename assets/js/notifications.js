/* AFYAMICRO KAMPALA - NOTIFICATIONS
   Frontend demonstration only.
*/

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const STORAGE_KEY = "afyamic_ro_demo_notifications";
    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const list = document.getElementById("notificationList");
    const emptyState = document.getElementById("notificationEmpty");
    const categoryFilter = document.getElementById("notificationCategory");

    let activeFilter = "all";
    let notifications = loadNotifications();

    function loadNotifications() {
        try {
            const saved = JSON.parse(localStorage.getItem(STORAGE_KEY) || "null");

            if (Array.isArray(saved)) {
                return saved;
            }
        } catch (error) {
            console.warn("Could not load notifications.", error);
        }

        const now = Date.now();

        const samples = [
            {
                id: "welcome-demo",
                category: "account",
                title: "Welcome to AfyaMicro Kampala",
                message: "Thank you for exploring AfyaMicro. Your member dashboard is designed to help you review your wallet, contributions and health claim activity.",
                date: new Date(now - 1000 * 60 * 20).toISOString(),
                read: false
            },
            {
                id: "wallet-demo",
                category: "wallet",
                title: "Your wallet is ready",
                message: "You can explore the wallet page and simulate a demonstration deposit. No real money will be transferred.",
                date: new Date(now - 1000 * 60 * 60 * 3).toISOString(),
                read: false
            },
            {
                id: "contribution-demo",
                category: "contribution",
                title: "Explore your contributions",
                message: "The contributions page allows you to record a sample contribution using your demonstration wallet balance.",
                date: new Date(now - 1000 * 60 * 60 * 24).toISOString(),
                read: true
            },
            {
                id: "claim-demo",
                category: "claim",
                title: "Health claims information",
                message: "You can submit a sample health claim from your dashboard. Submitted demonstration claims are stored locally in this browser.",
                date: new Date(now - 1000 * 60 * 60 * 48).toISOString(),
                read: true
            },
            {
                id: "general-demo",
                category: "general",
                title: "Keep your account information updated",
                message: "Review your member details and keep your contact information accurate when the account profile features become available.",
                date: new Date(now - 1000 * 60 * 60 * 72).toISOString(),
                read: true
            }
        ];

        saveNotifications(samples);
        return samples;
    }

    function saveNotifications(items) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
            return true;
        } catch (error) {
            console.error("Could not save notifications.", error);
            return false;
        }
    }

    function safeText(value, fallback) {
        if (value === undefined || value === null || String(value).trim() === "") {
            return fallback || "";
        }
        return String(value);
    }

    function formatDate(value) {
        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
            return "Date unavailable";
        }

        return date.toLocaleString("en-UG", {
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit"
        });
    }

    function getTimeAgo(value) {
        const date = new Date(value);
        const difference = Date.now() - date.getTime();

        if (Number.isNaN(difference) || difference < 0) {
            return formatDate(value);
        }

        const minutes = Math.floor(difference / 60000);

        if (minutes < 1) return "Just now";
        if (minutes < 60) return minutes + " min ago";

        const hours = Math.floor(minutes / 60);
        if (hours < 24) return hours + (hours === 1 ? " hour ago" : " hours ago");

        const days = Math.floor(hours / 24);
        if (days < 7) return days + (days === 1 ? " day ago" : " days ago");

        return formatDate(value);
    }

    function getCategoryIcon(category) {
        const icons = {
            account: "◎",
            wallet: "▣",
            contribution: "↗",
            claim: "＋",
            general: "ⓘ"
        };

        return icons[category] || "ⓘ";
    }

    function updateProfile() {
        let profile = null;

        try {
            profile = JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
        } catch (error) {
            profile = null;
        }

        const name = profile && profile.fullName ? profile.fullName : "Member";
        const initials = name.trim().split(/\s+/).slice(0, 2)
            .map(function (part) {
                return part.charAt(0).toUpperCase();
            })
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

    function getCounts() {
        const total = notifications.length;
        const unread = notifications.filter(function (item) {
            return !item.read;
        }).length;

        const read = total - unread;

        return { total: total, unread: unread, read: read };
    }

    function updateCounts() {
        const counts = getCounts();

        document.getElementById("totalNotifications").textContent = counts.total;
        document.getElementById("unreadNotifications").textContent = counts.unread;
        document.getElementById("readNotifications").textContent = counts.read;

        document.getElementById("allTabCount").textContent = counts.total;
        document.getElementById("unreadTabCount").textContent = counts.unread;
        document.getElementById("readTabCount").textContent = counts.read;

        document.getElementById("unreadCounter").textContent =
            counts.unread + (counts.unread === 1 ? " unread" : " unread");

        const sidebarCount = document.getElementById("sidebarUnreadCount");
        sidebarCount.textContent = counts.unread;
        sidebarCount.dataset.zero = counts.unread === 0 ? "true" : "false";
    }

    function createElement(tag, className, text) {
        const element = document.createElement(tag);

        if (className) element.className = className;
        if (text !== undefined) element.textContent = text;

        return element;
    }

    function getFilteredNotifications() {
        const category = categoryFilter.value;

        return notifications.filter(function (item) {
            const matchesRead =
                activeFilter === "all" ||
                (activeFilter === "unread" && !item.read) ||
                (activeFilter === "read" && item.read);

            const matchesCategory =
                category === "all" || item.category === category;

            return matchesRead && matchesCategory;
        });
    }

    function markAsRead(id) {
        const item = notifications.find(function (notification) {
            return notification.id === id;
        });

        if (!item) return;

        item.read = true;
        saveNotifications(notifications);
        render();
    }

    function deleteNotification(id) {
        const confirmed = window.confirm("Remove this notification from your demonstration inbox?");
        if (!confirmed) return;

        notifications = notifications.filter(function (item) {
            return item.id !== id;
        });

        saveNotifications(notifications);
        render();
    }

    function createNotificationCard(item) {
        const card = createElement(
            "article",
            "notification-item" + (item.read ? "" : " unread")
        );

        const icon = createElement(
            "div",
            "notification-type-icon " + safeText(item.category, "general"),
            getCategoryIcon(item.category)
        );

        const content = createElement("div", "notification-item-content");

        const titleRow = createElement("div", "notification-item-title-row");
        titleRow.appendChild(
            createElement("strong", "notification-item-title", safeText(item.title, "Notification"))
        );

        if (!item.read) {
            titleRow.appendChild(
                createElement("span", "notification-new-label", "NEW")
            );
        }

        const message = createElement(
            "p",
            "notification-item-message",
            safeText(item.message, "")
        );

        const meta = createElement("div", "notification-item-meta");
        meta.appendChild(
            createElement("span", "", getTimeAgo(item.date))
        );
        meta.appendChild(
            createElement("span", "notification-category-label", safeText(item.category, "general"))
        );

        const actions = createElement("div", "notification-actions");

        if (!item.read) {
            const readButton = createElement(
                "button",
                "notification-action-button",
                "Mark as read"
            );

            readButton.type = "button";
            readButton.addEventListener("click", function () {
                markAsRead(item.id);
            });

            actions.appendChild(readButton);
        }

        const deleteButton = createElement(
            "button",
            "notification-action-button delete",
            "Remove"
        );

        deleteButton.type = "button";
        deleteButton.addEventListener("click", function () {
            deleteNotification(item.id);
        });

        actions.appendChild(deleteButton);

        content.appendChild(titleRow);
        content.appendChild(message);
        content.appendChild(meta);

        card.appendChild(icon);
        card.appendChild(content);
        card.appendChild(actions);

        return card;
    }

    function render() {
        updateCounts();

        const filtered = getFilteredNotifications();
        list.replaceChildren();

        filtered.forEach(function (item) {
            list.appendChild(createNotificationCard(item));
        });

        const hasItems = filtered.length > 0;

        emptyState.classList.toggle("hidden", hasItems);
        list.style.display = hasItems ? "" : "none";

        document.getElementById("notificationResults").textContent =
            "Showing " + filtered.length +
            (filtered.length === 1 ? " notification" : " notifications");

        document.getElementById("markAllRead").disabled =
            getCounts().unread === 0;

        document.getElementById("markAllRead").style.opacity =
            getCounts().unread === 0 ? ".55" : "1";
    }

    function setActiveTab(filter) {
        activeFilter = filter;

        document.querySelectorAll(".notification-tab").forEach(function (button) {
            const isActive = button.dataset.filter === filter;
            button.classList.toggle("active", isActive);
            button.setAttribute("aria-pressed", String(isActive));
        });

        render();
    }

    document.querySelectorAll(".notification-tab").forEach(function (button) {
        button.addEventListener("click", function () {
            setActiveTab(button.dataset.filter);
        });
    });

    document.getElementById("markAllRead").addEventListener("click", function () {
        notifications.forEach(function (item) {
            item.read = true;
        });

        saveNotifications(notifications);
        render();
    });

    categoryFilter.addEventListener("change", render);

    document.getElementById("clearNotificationFilters").addEventListener("click", function () {
        categoryFilter.value = "all";
        setActiveTab("all");
    });

    document.getElementById("clearReadNotifications").addEventListener("click", function () {
        const readCount = notifications.filter(function (item) {
            return item.read;
        }).length;

        if (readCount === 0) {
            window.alert("There are no read notifications to clear.");
            return;
        }

        const confirmed = window.confirm(
            "Remove " + readCount + " read notification(s) from this demonstration inbox?"
        );

        if (!confirmed) return;

        notifications = notifications.filter(function (item) {
            return !item.read;
        });

        saveNotifications(notifications);
        render();
    });

    // Mobile sidebar controls
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

    updateProfile();
    render();

    window.addEventListener("focus", render);
    window.addEventListener("storage", function () {
        notifications = loadNotifications();
        render();
    });
});
