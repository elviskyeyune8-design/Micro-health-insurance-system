document.addEventListener("DOMContentLoaded", () => {
    "use strict";

    const BALANCE_KEY = "afyamic_ro_demo_wallet";
    const TRANSACTIONS_KEY = "afyamic_ro_demo_transactions";
    const PROFILE_KEY = "afyamic_ro_demo_profile";

    const formatMoney = amount =>
        "UGX " + Number(amount).toLocaleString("en-UG");

    const getElement = id => document.getElementById(id);

    // Load the registered demonstration profile.
    let profile = null;

    try {
        profile = JSON.parse(localStorage.getItem(PROFILE_KEY) || "null");
    } catch (error) {
        console.warn("Profile could not be loaded.", error);
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

    ["sidebarUserName", "topbarUserName", "walletMemberName"].forEach(id => {
        if (getElement(id)) getElement(id).textContent = memberName;
    });

    if (getElement("walletYear")) {
        getElement("walletYear").textContent = new Date().getFullYear();
    }

    // Load the browser's demonstration wallet.
    let balance = 25000;
    let transactions = [];

    try {
        const savedBalance = localStorage.getItem(BALANCE_KEY);
        const savedTransactions = localStorage.getItem(TRANSACTIONS_KEY);

        if (savedBalance !== null) {
            const parsedBalance = Number(savedBalance);
            if (Number.isFinite(parsedBalance) && parsedBalance >= 0) {
                balance = parsedBalance;
            }
        }

        if (savedTransactions) {
            const parsedTransactions = JSON.parse(savedTransactions);
            if (Array.isArray(parsedTransactions)) {
                transactions = parsedTransactions;
            }
        }
    } catch (error) {
        console.warn("Wallet demonstration data could not be loaded.", error);
    }

    function saveWallet() {
        try {
            localStorage.setItem(BALANCE_KEY, String(balance));
            localStorage.setItem(TRANSACTIONS_KEY, JSON.stringify(transactions));
            return true;
        } catch (error) {
            console.error("Wallet data could not be saved.", error);
            return false;
        }
    }

    function renderTransactions() {
        const tableBody = getElement("walletTransactions");
        if (!tableBody) return;

        tableBody.replaceChildren();

        if (transactions.length === 0) {
            const row = document.createElement("tr");
            const cell = document.createElement("td");

            cell.colSpan = 5;
            cell.className = "empty-transactions";
            cell.textContent = "No demonstration deposits recorded yet.";

            row.appendChild(cell);
            tableBody.appendChild(row);
            return;
        }

        transactions.forEach(transaction => {
            const row = document.createElement("tr");

            const values = [
                transaction.reference,
                transaction.method,
                transaction.date,
                formatMoney(transaction.amount)
            ];

            values.forEach((value, index) => {
                const cell = document.createElement("td");
                cell.textContent = value;

                if (index === 0) {
                    cell.className = "transaction-reference";
                }

                row.appendChild(cell);
            });

            const statusCell = document.createElement("td");
            const status = document.createElement("span");

            status.className = "transaction-status";
            status.textContent = "Demo recorded";

            statusCell.appendChild(status);
            row.appendChild(statusCell);
            tableBody.appendChild(row);
        });
    }

    function renderWallet() {
        const totalDeposits = transactions.reduce(
            (total, transaction) => total + Number(transaction.amount || 0),
            0
        );

        if (getElement("walletBalance")) {
            getElement("walletBalance").textContent = formatMoney(balance);
        }

        if (getElement("totalDeposits")) {
            getElement("totalDeposits").textContent = formatMoney(totalDeposits);
        }

        if (getElement("transactionCount")) {
            getElement("transactionCount").textContent = transactions.length;
        }

        if (getElement("transactionLabel")) {
            getElement("transactionLabel").textContent =
                transactions.length + (transactions.length === 1 ? " record" : " records");
        }

        renderTransactions();
    }

    renderWallet();

    // Simulate a deposit. No payment provider is contacted.
    const depositForm = getElement("depositForm");

    if (depositForm) {
        depositForm.addEventListener("submit", event => {
            event.preventDefault();

            const amountInput = getElement("depositAmount");
            const methodInput = getElement("paymentMethod");
            const phoneInput = getElement("paymentPhone");
            const feedback = getElement("depositFeedback");
            const amountError = getElement("amountError");

            const amount = Number(amountInput.value);
            const method = methodInput.value;
            const phone = phoneInput.value.trim();

            feedback.textContent = "";
            feedback.className = "wallet-feedback";
            amountError.textContent = "";

            if (!Number.isInteger(amount) ||
                amount < 1000 ||
                amount > 500000 ||
                amount % 100 !== 0) {
                amountError.textContent =
                    "Enter an amount from UGX 1,000 to UGX 500,000 in increments of UGX 100.";
                amountInput.focus();
                return;
            }

            if (!method) {
                feedback.textContent = "Please select a payment method.";
                feedback.classList.add("error");
                methodInput.focus();
                return;
            }

            if (phone && !/^07[0-9]{8}$/.test(phone)) {
                feedback.textContent =
                    "Enter a valid Ugandan phone number, for example 07XXXXXXXX.";
                feedback.classList.add("error");
                phoneInput.focus();
                return;
            }

            const newTransaction = {
                reference: "AFY-DEMO-" + Date.now().toString().slice(-8),
                method: method,
                amount: amount,
                date: new Date().toLocaleString("en-UG"),
                status: "Demo recorded"
            };

            const previousBalance = balance;
            balance += amount;
            transactions.unshift(newTransaction);

            if (!saveWallet()) {
                balance = previousBalance;
                transactions.shift();

                feedback.textContent =
                    "Your browser could not save this demonstration. Check browser storage settings.";
                feedback.classList.add("error");
                return;
            }

            renderWallet();

            feedback.textContent =
                "Demonstration deposit recorded. Your simulated wallet balance has been updated.";
            feedback.classList.add("success");

            depositForm.reset();
        });
    }

    // Mobile dashboard navigation.
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

    if (toggle && sidebar && overlay) {
        toggle.addEventListener("click", () => {
            const open = sidebar.classList.contains("open");

            if (open) {
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

    console.log("AfyaMicro Wallet demonstration initialized.");
});
