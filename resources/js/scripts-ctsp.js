document.addEventListener('DOMContentLoaded', () => {
    let activeMenu = null;

    // Dropdown menu for categories
    document.querySelectorAll("nav a").forEach(item => {
        const normalizedCategoryName = item.textContent.trim().toLowerCase();
        if (categoriesFromDB[normalizedCategoryName]) {
            const dropdownMenu = document.createElement("div");
            dropdownMenu.classList.add("dropdown-menu");

            categoriesFromDB[normalizedCategoryName].forEach(product => {
                const productLink = document.createElement("a");
                productLink.href = `ctsp.php?id=${product.ProductID}`;
                productLink.textContent = product.ProductName;
                dropdownMenu.appendChild(productLink);
            });

            document.body.appendChild(dropdownMenu);

            item.addEventListener("mouseenter", () => {
                if (activeMenu) activeMenu.classList.remove("active");
                dropdownMenu.classList.add("active");
                activeMenu = dropdownMenu;
            });

            dropdownMenu.addEventListener("mouseleave", () => {
                dropdownMenu.classList.remove("active");
                activeMenu = null;
            });
        }
    });

    document.addEventListener("click", e => {
        if (activeMenu && !e.target.closest(".dropdown-menu") && !e.target.closest("nav a")) {
            activeMenu.classList.remove("active");
            activeMenu = null;
        }
    });

    // Search input placeholder
    const searchInput = document.getElementById("search-input");
    const dropdownSearch = document.getElementById("dropdown-search");

    searchInput.addEventListener("focus", () => {
        dropdownSearch.classList.add("active");
    });

    searchInput.addEventListener("input", () => {
        searchInput.setAttribute("placeholder", searchInput.value.trim() === "" ? "Tìm kiếm sản phẩm" : "");
    });
});