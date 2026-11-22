document.addEventListener("DOMContentLoaded", function () {
    // Automatically update the footer year
    const year = document.getElementById("currentYear");

    if (year) {
        year.textContent = new Date().getFullYear();
    }

    // FAQ accordion
    const faqButtons = document.querySelectorAll(".faq-question");

    faqButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            const item = button.closest(".faq-item");
            const isOpen = item.classList.toggle("open");

            button.setAttribute("aria-expanded", String(isOpen));
            button.querySelector("span").textContent = isOpen ? "−" : "+";
        });
    });
});
