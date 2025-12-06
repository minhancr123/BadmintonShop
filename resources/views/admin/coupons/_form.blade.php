@php
    $discountType = old('discount_type', $coupon->discount_type ?? 'percentage');
    $startsAtValue = old('starts_at', optional($coupon->starts_at)->format('Y-m-d\TH:i'));
    $endsAtValue = old('ends_at', optional($coupon->ends_at)->format('Y-m-d\TH:i'));
    $isActive = (bool) old('is_active', $coupon->is_active ?? true);
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label for="code" class="form-label">Mã giảm giá <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $coupon->code) }}" maxlength="50" required>
        <small class="text-muted">Sử dụng chữ in hoa, không dấu và không khoảng trắng.</small>
    </div>
    <div class="col-md-4">
        <label for="name" class="form-label">Tên chiến dịch</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $coupon->name) }}" maxlength="255">
    </div>
    <div class="col-md-4">
        <label for="discount_type" class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
        <select class="form-select" id="discount_type" name="discount_type" required>
            <option value="percentage" @selected($discountType === 'percentage')>Theo phần trăm (%)</option>
            <option value="fixed" @selected($discountType === 'fixed')>Theo số tiền (₫)</option>
        </select>
    </div>
    <div class="col-md-4">
        <label for="discount_value" class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
        <input type="number" class="form-control" id="discount_value" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" min="0" step="0.01" required>
        <small class="text-muted">Phần trăm khi chọn "Theo phần trăm", hoặc số tiền nếu chọn "Theo số tiền".</small>
    </div>
    <div class="col-md-4">
        <label for="max_discount_amount" class="form-label">Giảm tối đa</label>
        <input type="number" class="form-control" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" min="0" step="0.01">
        <small class="text-muted">Áp dụng cho mã giảm theo phần trăm, bỏ trống nếu không giới hạn.</small>
    </div>
    <div class="col-md-4">
        <label for="minimum_order_amount" class="form-label">Đơn hàng tối thiểu</label>
        <input type="number" class="form-control" id="minimum_order_amount" name="minimum_order_amount" value="{{ old('minimum_order_amount', $coupon->minimum_order_amount) }}" min="0" step="0.01">
    </div>
    <div class="col-md-4">
        <label for="usage_limit" class="form-label">Giới hạn tổng lượt dùng</label>
        <input type="number" class="form-control" id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" min="1">
    </div>
    <div class="col-md-4">
        <label for="usage_limit_per_user" class="form-label">Giới hạn mỗi khách hàng</label>
        <input type="number" class="form-control" id="usage_limit_per_user" name="usage_limit_per_user" value="{{ old('usage_limit_per_user', $coupon->usage_limit_per_user) }}" min="1">
    </div>
    <div class="col-md-4">
        <label for="starts_at" class="form-label">Thời gian bắt đầu</label>
        <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="{{ $startsAtValue }}">
    </div>
    <div class="col-md-4">
        <label for="ends_at" class="form-label">Thời gian kết thúc</label>
        <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="{{ $endsAtValue }}">
    </div>
    <div class="col-12">
        <label for="description" class="form-label">Mô tả</label>
        <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $coupon->description) }}</textarea>
    </div>
    <div class="col-12">
        <div class="form-check form-switch">
            <input type="hidden" name="is_active" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked($isActive)>
            <label class="form-check-label" for="is_active">Kích hoạt mã ngay sau khi lưu</label>
        </div>
    </div>
</div>
