@extends('admin.layout')

@section('title', 'Tạo mã giảm giá')
@section('page-title', 'Tạo mã giảm giá')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.coupons.index') }}">Mã giảm giá</a></li>
    <li class="breadcrumb-item active">Tạo mới</li>
@endsection

@section('content')
<div class="card admin-card">
    <div class="card-body">
        <form action="{{ route('admin.coupons.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            @include('admin.coupons._form')
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu mã giảm giá
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
