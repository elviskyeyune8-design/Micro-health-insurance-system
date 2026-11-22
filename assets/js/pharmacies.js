document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("pharmacySearch");
    const filterButtons = document.querySelectorAll(".division-filter");
    const cards = document.querySelectorAll(".directory-card");
    const noResults = document.getElementById("noResults");

    let selectedDivision = "all";

    function filterPharmacies() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        cards.forEach(function (card) {
            const division = card.dataset.division;
            const name = card.dataset.name;

            const matchesDivision =
                selectedDivision === "all" ||
                division === selectedDivision;

            const matchesSearch =
                name.includes(searchTerm) ||
                division.includes(searchTerm);

            const visible = matchesDivision && matchesSearch;

            card.hidden = !visible;

            if (visible) visibleCount++;
        });

        noResults.hidden = visibleCount > 0;
    }

    searchInput.addEventListener("input", filterPharmacies);

    filterButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            filterButtons.forEach(function (item) {
                item.classList.remove("active");
            });

            button.classList.add("active");
            selectedDivision = button.dataset.filter;

            filterPharmacies();
        });
    });
});
