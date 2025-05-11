@extends('layouts.admin')

@section('content')
    <div class="container">
        <h1>Tổng quan</h1>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_products'] }}</div>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_orders'] }}</div>
                    <div class="stat-label">Đơn hàng</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_categories'] }}</div>
                    <div class="stat-label">Danh mục</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-value">{{ $stats['total_users'] }}</div>
                    <div class="stat-label">Người dùng</div>
                </div>
            </div>
        </div>
        
        <!-- Phần thống kê và biểu đồ khác có thể được thêm vào đây -->
    </div>
@endsection

@push('styles')
<style>
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .stat-card {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .stat-icon {
        font-size: 24px;
        color: #3498db;
        margin-right: 20px;
        background: #ebf5ff;
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .stat-content {
        flex-grow: 1;
    }
    
    .stat-value {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #7f8c8d;
        font-size: 14px;
    }
</style>
@endpush

@push('scripts')
<script>
    // Script cho biểu đồ hoặc tính năng tương tác khác có thể thêm vào đây
</script>
@endpush