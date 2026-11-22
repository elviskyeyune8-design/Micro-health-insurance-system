document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("contactForm");

    if (!form) return;

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const name = document.getElementById("contactName").value.trim();
        const email = document.getElementById("contactEmail").value.trim();
        const subject = document.getElementById("contactSubject").value;
        const message = document.getElementById("contactMessage").value.trim();

        const feedback = document.getElementById("formFeedback");

        document.querySelectorAll(".field-error").forEach(function (element) {
            element.textContent = "";
        });

        feedback.className = "form-feedback";

        let valid = true;

        if (name.length < 2) {
            document.getElementById("nameError").textContent =
                "Please enter your full name.";
            valid = false;
        }

        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (!emailPattern.test(email)) {
            document.getElementById("emailError").textContent =
                "Please enter a valid email address.";
            valid = false;
        }

        if (!subject) {
            document.getElementById("subjectError").textContent =
                "Please select an enquiry subject.";
            valid = false;
        }

        if (message.length < 10) {
            document.getElementById("messageError").textContent =
                "Your message must contain at least 10 characters.";
            valid = false;
        }

        if (!valid) {
            feedback.textContent =
                "Please correct the highlighted fields and try again.";
            feedback.classList.add("error");
            return;
        }

        feedback.textContent =
            "Thank you, " + name +
            ". Your demonstration enquiry has been validated successfully. " +
            "No email has been sent because this is a frontend-only demonstration.";

        feedback.classList.add("success");
        form.reset();
    });
});
