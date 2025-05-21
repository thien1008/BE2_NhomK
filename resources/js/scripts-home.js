document.addEventListener('DOMContentLoaded', () => {
    // Helper function to toggle dropdown
    const toggleDropdown = (dropdown, state) => {
        if (dropdown) {
            dropdown.classList.toggle('active', state);
            const trigger = dropdown.previousElementSibling || dropdown.parentElement;
            if (trigger) {
                trigger.setAttribute('aria-expanded', state);
            }
        }
    };

    // Close all dropdowns
    const closeAllDropdowns = (except = null) => {
        document.querySelectorAll('.dropdown-menu, .dropdown-search, .user-dropdown, .cart-dropdown').forEach(dropdown => {
            if (dropdown !== except) {
                toggleDropdown(dropdown, false);
            }
        });
    };

    // Debounce function for search
    const debounce = (func, wait) => {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    };

    // Format VND currency
    const formatVND = (number) => {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' ₫';
    };

    // Category Dropdowns
    document.querySelectorAll("nav a.nav-link").forEach(item => {
        const normalizedCategoryName = item.textContent.trim().toLowerCase();
        if (categoriesFromDB[normalizedCategoryName]) {
            const dropdownMenu = document.createElement("div");
            dropdownMenu.classList.add("dropdown-menu");
            dropdownMenu.id = `dropdown-${normalizedCategoryName}`;
            item.setAttribute('aria-controls', `dropdown-${normalizedCategoryName}`);
            item.setAttribute('aria-expanded', 'false');

            categoriesFromDB[normalizedCategoryName].forEach(product => {
                const productLink = document.createElement("a");
                productLink.href = `ctsp.php?id=${product.ProductID}`;
                productLink.textContent = product.ProductName;
                dropdownMenu.appendChild(productLink);
            });

            item.parentElement.appendChild(dropdownMenu);

            item.addEventListener("mouseenter", () => {
                if (window.innerWidth > 768) {
                    closeAllDropdowns(dropdownMenu);
                    toggleDropdown(dropdownMenu, true);
                }
            });

            item.parentElement.addEventListener("mouseleave", () => {
                if (window.innerWidth > 768) {
                    toggleDropdown(dropdownMenu, false);
                }
            });

            item.addEventListener("click", (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    const isActive = dropdownMenu.classList.contains('active');
                    closeAllDropdowns();
                    toggleDropdown(dropdownMenu, !isActive);
                }
            });

            item.addEventListener("keydown", (e) => {
                if (e.key === "Enter" || e.key === " ") {
                    e.preventDefault();
                    const isActive = dropdownMenu.classList.contains('active');
                    closeAllDropdowns();
                    toggleDropdown(dropdownMenu, !isActive);
                }
            });
        }
    });

    // Search Dropdown
    const searchInput = document.getElementById("search-input");
    const dropdownSearch = document.getElementById("dropdown-search");
    const searchResultsContainer = document.getElementById("search-results");
    const noResultsMessage = dropdownSearch?.querySelector('.no-results');

    if (searchInput && dropdownSearch && searchResultsContainer) {
        searchInput.addEventListener("focus", () => {
            closeAllDropdowns(dropdownSearch);
            toggleDropdown(dropdownSearch, true);
        });

        const performSearch = debounce((value) => {
            if (value.trim() === "") {
                searchResultsContainer.innerHTML = "";
                if (noResultsMessage) noResultsMessage.style.display = 'block';
                toggleDropdown(dropdownSearch, false);
                return;
            }

            fetch(`?search=${encodeURIComponent(value)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    searchResultsContainer.innerHTML = "";
                    if (data.length === 0) {
                        if (noResultsMessage) noResultsMessage.style.display = 'block';
                    } else {
                        if (noResultsMessage) noResultsMessage.style.display = 'none';
                        const keyword = value.trim();
                        const escapedKeyword = keyword.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        const regex = new RegExp(`(${escapedKeyword})`, 'gi');

                        data.forEach(product => {
                            const highlightedName = product.ProductName.replace(regex, '<strong>$1</strong>');
                            const resultItem = document.createElement('a');
                            resultItem.classList.add('search-result');
                            resultItem.href = `/product/${product.ProductID}`;
                            resultItem.innerHTML = `
                                <img src="/images/${product.ImageURL}" alt="${product.ProductName}">
                                <div class="search-result-details">
                                    <div class="search-result-name">${highlightedName}</div>
                                    <div class="search-result-price">
                                        ${product.CurrentPrice.toLocaleString()}₫
                                        ${product.DiscountPercentage ? `<span class="search-result-discount">-${product.DiscountPercentage}%</span>` : ''}
                                    </div>
                                </div>
                            `;
                            searchResultsContainer.appendChild(resultItem);
                        });
                    }
                    toggleDropdown(dropdownSearch, true);
                })
                .catch(error => {
                    console.error('Search Error:', error);
                    alert('Đã có lỗi khi tìm kiếm.');
                });
        }, 300);

        searchInput.addEventListener("input", (e) => {
            searchInput.setAttribute("placeholder", e.target.value.trim() === "" ? "Tìm kiếm sản phẩm..." : "");
            performSearch(e.target.value);
        });

        document.addEventListener("click", e => {
            if (!e.target.closest(".search-container")) {
                toggleDropdown(dropdownSearch, false);
            }
        });
    }

    // User Dropdown
    const userInfo = document.getElementById("user-info");
    const userDropdown = document.getElementById("user-dropdown");

    if (userInfo && userDropdown) {
        userInfo.addEventListener("mouseenter", () => {
            if (window.innerWidth > 768) {
                closeAllDropdowns(userDropdown);
                toggleDropdown(userDropdown, true);
            }
        });

        userInfo.addEventListener("mouseleave", () => {
            if (window.innerWidth > 768) {
                toggleDropdown(userDropdown, false);
            }
        });

        userInfo.addEventListener("click", (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const isActive = userDropdown.classList.contains('active');
                closeAllDropdowns();
                toggleDropdown(userDropdown, !isActive);
            }
        });

        userInfo.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                const isActive = userDropdown.classList.contains('active');
                closeAllDropdowns();
                toggleDropdown(userDropdown, !isActive);
            }
        });
    }

    // Cart Functionality
    const cartContainer = document.querySelector('.cart-container');
    const cartDropdown = document.getElementById('cart-dropdown');
    const cartClose = cartDropdown?.querySelector('.cart-dropdown-close');
    const cartModal = document.querySelector('.cart-modal');
    const cartModalClose = document.querySelector('.cart-modal-close');
    const cartItemsContainer = cartDropdown?.querySelector('#cart-items');
    const cartEmptyMessage = cartDropdown?.querySelector('#cart-empty');
    const cartTotalPrice = cartDropdown?.querySelector('#cart-total-price');
    const cartCount = document.querySelector('.cart-count');
    const checkoutBtn = cartDropdown?.querySelector('.checkout-btn');
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    const buyNowButton = document.querySelector('.buy-now');

    const sendCartRequest = (action, data, callback) => {
        fetch(`/cart/${action}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: new URLSearchParams({ ...data })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    if (action === 'add') {
                        alert('Sản phẩm đã được thêm vào giỏ hàng!');
                    }
                    if (action !== 'get') {
                        sendCartRequest('get', {}, callback);
                    } else {
                        callback(data);
                    }
                } else {
                    if (data.message.includes('đăng nhập')) {
                        alert('Vui lòng đăng nhập để tiếp tục.');
                        window.location.href = '/login';
                    } else {
                        alert(data.message || 'Hành động không thành công.');
                    }
                }
            })
            .catch(error => {
                console.error('Cart Error:', error);
                alert('Đã có lỗi xảy ra: ' + error.message);
            });
    };

    const updateCartUI = (data) => {
        if (!cartItemsContainer) return;
        cartItemsContainer.innerHTML = '';
        let total = data.total || 0;
        let itemCount = data.itemCount || 0;

        if (cartCount) cartCount.textContent = itemCount;
        if (!data.cart || data.cart.length === 0) {
            if (cartEmptyMessage) cartEmptyMessage.style.display = 'block';
            cartItemsContainer.style.display = 'none';
        } else {
            if (cartEmptyMessage) cartEmptyMessage.style.display = 'none';
            cartItemsContainer.style.display = 'block';

            data.cart.forEach(item => {
                const cartItem = document.createElement('div');
                cartItem.classList.add('cart-dropdown-item');
                cartItem.setAttribute('data-product-id', item.ProductID);
                cartItem.innerHTML = `
                    <img src="/images/${item.ImageURL}" alt="${item.ProductName}" class="cart-dropdown-item-image">
                    <div class="cart-dropdown-item-details">
                        <div class="cart-dropdown-item-name">${item.ProductName}</div>
                        <div class="cart-dropdown-item-price">${formatVND(item.Price * item.Quantity)}</div>
                        <div class="cart-item-quantity">
                            <button class="quantity-btn decrease" data-product-id="${item.ProductID}">-</button>
                            <input type="number" class="quantity-input" value="${item.Quantity}" min="1" max="${item.Stock}" data-product-id="${item.ProductID}">
                            <button class="quantity-btn increase" data-product-id="${item.ProductID}">+</button>
                            <a href="#" class="cart-item-remove" data-product-id="${item.ProductID}">Xóa</a>
                        </div>
                    </div>
                `;
                cartItemsContainer.appendChild(cartItem);
            });

            // Add event listeners for quantity and remove buttons
            cartItemsContainer.querySelectorAll('.quantity-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const productId = button.getAttribute('data-product-id');
                    const input = cartItemsContainer.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                    let quantity = parseInt(input.value);

                    button.disabled = true;
                    if (button.classList.contains('decrease')) {
                        if (quantity > 1) {
                            quantity--;
                            sendCartRequest('update', { product_id: productId, quantity }, (data) => {
                                button.disabled = false;
                                updateCartUI(data);
                            });
                        } else {
                            sendCartRequest('remove', { product_id: productId }, (data) => {
                                button.disabled = false;
                                updateCartUI(data);
                            });
                        }
                    } else if (button.classList.contains('increase') && quantity < parseInt(input.max)) {
                        quantity++;
                        sendCartRequest('update', { product_id: productId, quantity }, (data) => {
                            button.disabled = false;
                            updateCartUI(data);
                        });
                    }
                    input.value = quantity;
                });
            });

            cartItemsContainer.querySelectorAll('.cart-item-remove').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const productId = button.getAttribute('data-product-id');
                    button.disabled = true;
                    sendCartRequest('remove', { product_id: productId }, (data) => {
                        button.disabled = false;
                        updateCartUI(data);
                    });
                });
            });

            cartItemsContainer.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', () => {
                    const productId = input.getAttribute('data-product-id');
                    let quantity = parseInt(input.value);
                    if (isNaN(quantity) || quantity <= 0) {
                        sendCartRequest('remove', { product_id: productId }, updateCartUI);
                        return;
                    } else if (quantity > parseInt(input.max)) {
                        quantity = parseInt(input.max);
                        input.value = input.max;
                        alert(`Số lượng tối đa là ${input.max} sản phẩm.`);
                    }
                    sendCartRequest('update', { product_id: productId, quantity }, updateCartUI);
                });
            });
        }

        if (cartTotalPrice) {
            cartTotalPrice.textContent = formatVND(total);
        }
    };

    if (cartContainer && cartDropdown) {
        cartContainer.addEventListener("mouseenter", () => {
            if (window.innerWidth > 768) {
                closeAllDropdowns(cartDropdown);
                toggleDropdown(cartDropdown, true);
                sendCartRequest('get', {}, updateCartUI);
            }
        });

        cartContainer.addEventListener("mouseleave", () => {
            if (window.innerWidth > 768) {
                toggleDropdown(cartDropdown, false);
            }
        });

        cartContainer.addEventListener("click", (e) => {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const isActive = cartDropdown.classList.contains('active');
                closeAllDropdowns();
                toggleDropdown(cartDropdown, !isActive);
                if (!isActive) {
                    sendCartRequest('get', {}, updateCartUI);
                }
            }
        });

        cartContainer.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === " ") {
                e.preventDefault();
                const isActive = cartDropdown.classList.contains('active');
                closeAllDropdowns();
                toggleDropdown(cartDropdown, !isActive);
                if (!isActive) {
                    sendCartRequest('get', {}, updateCartUI);
                }
            }
        });

        if (cartClose) {
            cartClose.addEventListener('click', () => {
                toggleDropdown(cartDropdown, false);
            });
        }
    }

    if (cartModal && cartContainer) {
        cartContainer.addEventListener('dblclick', (e) => {
            if (window.innerWidth > 768) {
                cartModal.classList.add('active');
                sendCartRequest('get', {}, updateCartUI);
            }
        });

        if (cartModalClose) {
            cartModalClose.addEventListener('click', () => {
                cartModal.classList.remove('active');
            });
        }

        cartModal.addEventListener('click', e => {
            if (e.target === cartModal) {
                cartModal.classList.remove('active');
            }
        });
    }

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            sendCartRequest('get', {}, (data) => {
                if (data.success && data.cart && data.cart.length > 0) {
                    window.location.href = '/checkout';
                } else {
                    alert('Giỏ hàng của bạn đang trống. Vui lòng thêm sản phẩm trước khi thanh toán.');
                }
            });
        });
    }

    if (addToCartButtons) {
        addToCartButtons.forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.getAttribute('data-id');
                const quantity = 1; // Default quantity, adjust if quantity input exists
                sendCartRequest('add', { product_id: productId, quantity }, updateCartUI);
            });
        });
    }

    if (buyNowButton) {
        buyNowButton.addEventListener('click', () => {
            const productId = document.querySelector('.add-to-cart').getAttribute('data-id');
            const quantity = 1; // Default quantity, adjust if needed
            sendCartRequest('add', { product_id: productId, quantity }, (data) => {
                if (data.success) {
                    window.location.href = '/checkout';
                } else {
                    alert(data.message || 'Không thể thêm sản phẩm vào giỏ hàng.');
                }
            });
        });
    }

    // Initialize Product Interactions (for add-to-cart, quick-view, wishlist)
    const initializeProductInteractions = () => {
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.getAttribute('data-id');
                sendCartRequest('add', { product_id: productId, quantity: 1 }, updateCartUI);
            });
        });

        document.querySelectorAll('.quick-view').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.getAttribute('data-id');
                fetch(`/product/${productId}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(response => response.text())
                    .then(data => {
                        const productModalBody = document.querySelector('.product-modal-body');
                        productModalBody.innerHTML = data;
                        document.querySelector('.product-modal').classList.add('active');
                    })
                    .catch(error => {
                        console.error('Quick View Error:', error);
                        alert('Đã có lỗi khi xem nhanh sản phẩm.');
                    });
            });
        });

        document.querySelectorAll('.add-to-wishlist').forEach(button => {
            button.addEventListener('click', () => {
                const productId = button.getAttribute('data-id');
                alert(`Đã thêm sản phẩm ${productId} vào danh sách yêu thích.`);
            });
        });

        const productModalClose = document.querySelector('.product-modal-close');
        if (productModalClose) {
            productModalClose.addEventListener('click', () => {
                document.querySelector('.product-modal').classList.remove('active');
            });
        }
    };

    // AJAX Pagination
    const initializePagination = () => {
        document.querySelectorAll('.pagination .page-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const url = link.getAttribute('href');
                if (url) {
                    fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            const productsSlider = document.querySelector('.products-slider');
                            productsSlider.innerHTML = '';
                            if (data.products && data.products.length > 0) {
                                data.products.forEach(product => {
                                    const productCard = document.createElement('div');
                                    productCard.classList.add('product-card');
                                    productCard.setAttribute('role', 'listitem');
                                    productCard.innerHTML = `
                                        <div class="product-image">
                                            <img src="/images/${product.ImageURL}" alt="${product.ProductName}" loading="lazy" width="200" height="200">
                                            ${product.DiscountPercentage ? '<span class="product-badge">Sale!</span>' : ''}
                                            <div class="product-actions">
                                                <button class="product-action-btn quick-view" data-id="${product.id}" aria-label="Quick view">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="product-action-btn add-to-wishlist" data-id="${product.id}" aria-label="Add to wishlist">
                                                    <i class="far fa-heart"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="product-details">
                                            <div class="product-title">${product.ProductName}</div>
                                            <div class="product-rating">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star-half-alt"></i>
                                            </div>
                                            <div class="price-container">
                                                <span class="current-price">${product.CurrentPrice.toLocaleString()}₫</span>
                                                ${product.DiscountPercentage ? `
                                                    <div>
                                                        <span class="original-price">${product.Price.toLocaleString()}₫</span>
                                                        <span class="discount-badge">-${product.DiscountPercentage}%</span>
                                                    </div>
                                                ` : ''}
                                            </div>
                                            <button class="add-to-cart" data-id="${product.id}" data-name="${product.ProductName}" data-price="${product.CurrentPrice}">
                                                <i class="fas fa-shopping-cart"></i>
                                                <span>THÊM VÀO GIỎ</span>
                                            </button>
                                        </div>
                                    `;
                                    productsSlider.appendChild(productCard);
                                });
                            } else {
                                productsSlider.innerHTML = '<p class="no-products">Không có sản phẩm nào để hiển thị.</p>';
                            }
                            document.querySelector('.pagination').innerHTML = data.pagination;
                            initializeProductInteractions();
                        })
                        .catch(error => {
                            console.error('Pagination Error:', error);
                            alert('Đã có lỗi khi tải trang.');
                        });
                }
            });
        });
    };

    // Initialize product interactions and pagination
    initializeProductInteractions();
    initializePagination();

    // Products Slider Navigation
    const prevButton = document.querySelector('.nav-button.prev');
    const nextButton = document.querySelector('.nav-button.next');
    const productsSlider = document.querySelector('.products-slider');

    if (prevButton && nextButton && productsSlider) {
        const pagination = document.querySelector('.pagination');
        if (pagination) {
            prevButton.style.display = 'none';
            nextButton.style.display = 'none';
        } else {
            prevButton.addEventListener('click', () => {
                productsSlider.scrollBy({ left: -300, behavior: 'smooth' });
            });

            nextButton.addEventListener('click', () => {
                productsSlider.scrollBy({ left: 300, behavior: 'smooth' });
            });
        }
    }
});

// Scroll Reveal Effect
document.addEventListener("DOMContentLoaded", function () {
    const revealElements = document.querySelectorAll(".scroll-reveal");

    const scrollRevealCallback = function (entries, observer) {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(scrollRevealCallback, {
        threshold: 0.1,
    });

    revealElements.forEach((el) => {
        observer.observe(el);
    });
});