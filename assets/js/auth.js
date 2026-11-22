document.addEventListener("DOMContentLoaded", function () {
    "use strict";

    const REMEMBER_KEY = "afyamic_ro_remembered_phone";

    // -------------------------------
    // SHOW / HIDE PASSWORD
    // -------------------------------
    document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
        button.addEventListener("click", function () {
            const inputId = button.getAttribute("data-password-toggle");
            const passwordInput = document.getElementById(inputId);

            if (!passwordInput) return;

            const isPassword = passwordInput.type === "password";

            passwordInput.type = isPassword ? "text" : "password";
            button.textContent = isPassword ? "Hide" : "Show";
            button.setAttribute(
                "aria-label",
                isPassword ? "Hide password" : "Show password"
            );
        });
    });

    // -------------------------------
    // DISMISSIBLE FLASH ALERTS
    // -------------------------------
    document.querySelectorAll("[data-alert-dismiss]").forEach(function (btn) {
        btn.addEventListener("click", function () {
            const alertBox = btn.closest(".alert");
            if (alertBox) alertBox.remove();
        });
    });

    // -------------------------------
    // LOGIN FORM (Remember phone convenience + native PHP POST)
    // -------------------------------
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
        const phoneInput = document.getElementById("loginPhone");
        const rememberMe = document.getElementById("rememberMe");

        try {
            const rememberedPhone = localStorage.getItem(REMEMBER_KEY);
            if (rememberedPhone && phoneInput && !phoneInput.value) {
                phoneInput.value = rememberedPhone;
                if (rememberMe) rememberMe.checked = true;
            }
        } catch (err) {
            // ignore storage errors
        }

        loginForm.addEventListener("submit", function () {
            try {
                if (rememberMe && rememberMe.checked && phoneInput) {
                    localStorage.setItem(REMEMBER_KEY, phoneInput.value.trim());
                } else {
                    localStorage.removeItem(REMEMBER_KEY);
                }
            } catch (err) {
                // ignore storage errors
            }
        });
    }
});
