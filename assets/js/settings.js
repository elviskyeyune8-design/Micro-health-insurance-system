/* AFYAMICRO KAMPALA - SETTINGS
   Saves frontend demonstration preferences.
*/

document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const SETTINGS_KEY = "afyamic_ro_demo_settings";
    const REMEMBER_KEY = "afyamic_ro_remembered_phone";
    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const feedback = document.getElementById("settingsFeedback");

    const defaultSettings = {
        contactMethod: "phone",
        preferredLanguage: "English",
        timeZone: "Africa/Kampala",
        accountNotifications: true,
        walletNotifications: true,
        contributionNotifications: true,
        claimNotifications: true,
        emailNotifications: false,
        smsNotifications: true,
        colourTheme: "light",
        compactView: false,
        rememberMe: false
    };

    let savedSettings = loadSettings();

    function loadSettings() {
        try {
            const stored = JSON.parse(localStorage.getItem(SETTINGS_KEY) || "null");

            if (stored && typeof stored === "object") {
                return Object.assign({}, defaultSettings, stored);
            }
        } catch (error) {
            console.warn("Could not load settings.", error);
        }

        return Object.assign({}, defaultSettings);
    }

    function saveSettings(settings) {
        try {
            localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));

            if (settings.rememberMe) {
                const profile = loadProfile();
                if (profile && profile.phone) {
                    localStorage.setItem(REMEMBER_KEY, profile.phone);
                }
            } else {
                localStorage.removeItem(REMEMBER_KEY);
            }

            return true;
        } catch (error) {
            console.error("Could not save settings.", error);
            return false;
        }
    }

    function loadProfile() {
        try {
            return JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
        } catch (error) {
            return null;
        }
    }

    function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = "settings-feedback visible " + type;

        feedback.scrollIntoView({
            behavior: "smooth",
            block: "nearest"
        });
    }

    function clearFeedback() {
        feedback.textContent = "";
        feedback.className = "settings-feedback";
    }

    function setControlValue(id, value) {
        const element = document.getElementById(id);
        if (!element) return;

        if (element.type === "checkbox") {
            element.checked = Boolean(value);
        } else {
            element.value = value;
        }
    }

    function getControlValue(id) {
        const element = document.getElementById(id);
        if (!element) return null;

        return element.type === "checkbox" ? element.checked : element.value;
    }

    function applySettings(settings) {
        Object.keys(defaultSettings).forEach(function (key) {
            setControlValue(key, settings[key]);
        });
    }

    function collectSettings() {
        const result = {};

        Object.keys(defaultSettings).forEach(function (key) {
            result[key] = getControlValue(key);
        });

        return result;
    }

    function updateProfileDisplay() {
        const profile = loadProfile();
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

    function showBrowserInformation() {
        const browserInfo = document.getElementById("browserInformation");
        const browser = navigator.userAgent;

        let name = "Web browser";

        if (browser.includes("Edg/")) name = "Microsoft Edge";
        else if (browser.includes("Chrome/")) name = "Google Chrome";
        else if (browser.includes("Firefox/")) name = "Mozilla Firefox";
        else if (browser.includes("Safari/")) name = "Safari";

        browserInfo.textContent = name + " · This device";
    }

    // Save preferences
    document.getElementById("saveSettings").addEventListener("click", function () {
        const newSettings = collectSettings();

        if (!saveSettings(newSettings)) {
            showFeedback(
                "We could not save your preferences. Check your browser storage settings.",
                "error"
            );
            return;
        }

        savedSettings = Object.assign({}, newSettings);

        showFeedback(
            "Your demonstration preferences have been saved successfully in this browser.",
            "success"
        );
    });

    // Cancel unsaved changes
    document.getElementById("cancelSettings").addEventListener("click", function () {
        applySettings(savedSettings);
        clearFeedback();
    });

    // Restore the default preference values
    document.getElementById("clearPreferences").addEventListener("click", function () {
        const confirmed = window.confirm(
            "Reset all settings preferences to their default values?"
        );

        if (!confirmed) return;

        const defaults = Object.assign({}, defaultSettings);

        if (!saveSettings(defaults)) {
            showFeedback("Could not reset your settings. Please check browser storage.", "error");
            return;
        }

        savedSettings = Object.assign({}, defaults);
        applySettings(savedSettings);

        showFeedback(
            "Your settings have been restored to their default demonstration values.",
            "success"
        );
    });

    // Settings section navigation
    document.querySelectorAll(".settings-nav-item").forEach(function (link) {
        link.addEventListener("click", function () {
            document.querySelectorAll(".settings-nav-item").forEach(function (item) {
                item.classList.remove("active");
            });

            link.classList.add("active");
        });
    });

    // Mobile sidebar
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

    // Logout confirmation
    const logoutLink = document.getElementById("logoutLink");
    const settingsLogout = document.getElementById("settingsLogout");

    [logoutLink, settingsLogout].forEach(function (link) {
        if (!link) return;

        link.addEventListener("click", function (event) {
            const confirmed = window.confirm("Are you sure you want to log out?");
            if (!confirmed) event.preventDefault();
        });
    });

    applySettings(savedSettings);
    updateProfileDisplay();
    showBrowserInformation();
});
