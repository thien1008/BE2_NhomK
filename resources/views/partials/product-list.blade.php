@if($products->isEmpty())
    <p class="no-products">Không có sản phẩm nào để hiển thị.</p>
@else
    @foreach($products as $product)
        @if($product->ProductID && \App\Models\Product::where('ProductID', $product->ProductID)->exists())
            <div class="product-card" role="listitem" data-product-id="{{ $product->ProductID }}">
                <div class="product-image">
                    <a href="/product/{{ $product->ProductID }}">
                        <img src="{{ asset('images/' . $product->ImageURL) }}" alt="{{ e($product->ProductName) }}" loading="lazy" width="200" height="200">
                    </a>
                    @if($product->DiscountPercentage)
                        <span class="product-badge">Sale!</span>
                    @endif
                    <div class="product-actions">
                        <button class="product-action-btn quick-view" aria-label="Quick view">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="product-action-btn add-to-wishlist" aria-label="Add to wishlist">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                </div>
                <div class="product-details">
                    <a href="/product/{{ $product->ProductID }}" class="product-title">{{ e($product->ProductName) }}</a>
                    <div class="product-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <div class="price-container">
                        <span class="current-price">{{ number_format($product->CurrentPrice, 0) }}₫</span>
                        @if($product->DiscountPercentage)
                            <div>
                                <span class="original-price">{{ number_format($product->Price, 0) }}₫</span>
                                <span class="discount-badge">-{{ number_format($product->DiscountPercentage, 0) }}%</span>
                            </div>
                        @endif
                    </div>
                    <button class="add-to-cart" data-product-id="{{ $product->ProductID }}" data-name="{{ e($product->ProductName) }}" data-price="{{ $product->CurrentPrice }}">
                        <i class="fas fa-shopping-cart"></i> <span>THÊM VÀO GIỎ</span>
                    </button>
                </div>
            </div>
        @else
            <p class="no-product-id">Sản phẩm không hợp lệ (ID: {{ $product->ProductID ?? 'null' }}).</p>
        @endif
    @endforeach
@endif