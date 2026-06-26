@extends('admin.layouts.app')

@section('title', 'نظرة عامة على النظام')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
@php
    $adminName = optional(auth('admin')->user())->name ?? 'المدير';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">نظرة عامة على النظام</h1>
            <p class="text-sm text-gray-500 mt-1">مرحباً بك مجدداً، {{ $adminName }}. إليك أداء متجرك خلال آخر 30 يوماً.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i> آخر 30 يومًا
            </button>
            <a href="{{ route('admin.products.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-lg shadow-emerald-500/20 flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة منتج
            </a>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">إجمالي المبيعات</span>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalSales, 0) }} <span class="text-sm font-bold text-gray-400">ج.م</span></h3>
                <span class="text-xs text-emerald-500 font-semibold flex items-center gap-1 mt-2">
                    <i class="fas fa-arrow-up"></i> آخر 30 يومًا
                </span>
            </div>
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl"><i class="fas fa-wallet text-xl"></i></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">الطلبات المكتملة</span>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($completedOrders) }} طلب</h3>
                <span class="text-xs text-blue-500 font-semibold flex items-center gap-1 mt-2">
                    <i class="fas fa-check-circle"></i> تم التسليم
                </span>
            </div>
            <div class="p-3 bg-blue-50 text-blue-600 rounded-xl"><i class="fas fa-shopping-bag text-xl"></i></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">العملاء المسجلين</span>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalCustomers) }} عميل</h3>
                <span class="text-xs text-purple-500 font-semibold flex items-center gap-1 mt-2">
                    <i class="fas fa-users"></i> إجمالي المستخدمين
                </span>
            </div>
            <div class="p-3 bg-purple-50 text-purple-600 rounded-xl"><i class="fas fa-users text-xl"></i></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">مخزون منخفض / نافذ</span>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ $lowStockCount + $outOfStockCount }} منتج</h3>
                <span class="text-xs text-amber-500 font-semibold flex items-center gap-1 mt-2">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $outOfStockCount }} نافذ • {{ $lowStockCount }} منخفض
                </span>
            </div>
            <div class="p-3 bg-amber-50 text-amber-600 rounded-xl"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        </div>

    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900">مخطط الإيرادات (آخر 7 أيام)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">إجمالي قيم الطلبات المدفوعة والمشحونة والمسلَّمة</p>
                </div>
                <i class="fas fa-ellipsis-h text-gray-400 cursor-pointer"></i>
            </div>
            <div class="h-72"><canvas id="revenueChart"></canvas></div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900">توزيع المنتجات على التصنيفات</h3>
                    <p class="text-xs text-gray-400 mt-0.5">إجمالي التصنيفات: {{ $totalCategories }}</p>
                </div>
            </div>
            <div class="h-72 flex items-center justify-center">
                @if($categoryStats->count())
                    <canvas id="categoryChart"></canvas>
                @else
                    <p class="text-sm text-gray-400">لا توجد بيانات بعد</p>
                @endif
            </div>
        </div>

    </div>

    {{-- Recent orders + low stock --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-gray-900">آخر الطلبات الواردة</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">عرض الكل</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-100">
                            <th class="pb-3 font-medium">رقم الطلب</th>
                            <th class="pb-3 font-medium">العميل</th>
                            <th class="pb-3 font-medium">المجموع</th>
                            <th class="pb-3 font-medium">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $o)
                            @php $c = $o->statusBadgeColor(); @endphp
                            <tr>
                                <td class="py-3 font-semibold text-emerald-600 font-mono">#{{ $o->order_number }}</td>
                                <td class="py-3 font-medium text-gray-800">{{ $o->user?->name ?? 'زائر' }}</td>
                                <td class="py-3 text-gray-700">{{ number_format((float)$o->total, 0) }} ج.م</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-md bg-{{ $c }}-50 text-{{ $c }}-600">
                                        {{ $o->statusLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-gray-400 text-sm">لا توجد طلبات بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-red-600 flex items-center gap-2">
                    <i class="fas fa-boxes"></i> منتجات أوشكت على النفاد
                </h3>
                <a href="{{ route('admin.products.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">إدارة المخزون</a>
            </div>
            <div class="space-y-3">
                @forelse($lowStockProducts as $p)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center border border-gray-200 shrink-0">
                                <i class="fas fa-flask text-emerald-500"></i>
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-sm font-bold text-gray-800 truncate">{{ $p->name }}</h4>
                                <p class="text-xs text-gray-400">{{ $p->category?->name ?? '—' }}</p>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 font-bold rounded bg-red-50 text-red-600 shrink-0">متبقي {{ $p->stock }}</span>
                    </div>
                @empty
                    <div class="text-center py-8 text-sm text-gray-400">المخزون بحالة جيدة 👌</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Recent products --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-900">آخر المنتجات المضافة</h3>
                <p class="text-xs text-gray-400 mt-0.5">أحدث 5 منتجات مسجَّلة في الكتالوج</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="text-xs text-emerald-600 font-semibold hover:underline">عرض الكل</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-right text-sm">
                <thead class="bg-gray-50/50 text-gray-400 border-b border-gray-100">
                    <tr>
                        <th class="p-4 font-semibold">المنتج</th>
                        <th class="p-4 font-semibold">التصنيف</th>
                        <th class="p-4 font-semibold">السعر</th>
                        <th class="p-4 font-semibold">المخزون</th>
                        <th class="p-4 font-semibold">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recentProducts as $product)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900">{{ $product->name }}</td>
                            <td class="p-4 text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                            <td class="p-4 font-mono font-semibold text-gray-900">{{ number_format($product->sale_price ?? $product->price, 0) }} ج.م</td>
                            <td class="p-4">
                                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $product->stock > 0 ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                    {{ $product->stock }} وحدة
                                </span>
                            </td>
                            <td class="p-4">
                                @if($product->stock > 0)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> نشط
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span> نافذ
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">لا توجد منتجات مضافة بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const revCtx = document.getElementById('revenueChart');
    if (revCtx) {
        const labels = @json($revenueLabels);
        const data   = @json($revenueSeries);
        const grad = revCtx.getContext('2d').createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, 'rgba(16,185,129,0.35)');
        grad.addColorStop(1, 'rgba(16,185,129,0)');
        new Chart(revCtx, {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'الإيرادات (ج.م)',
                    data,
                    borderColor: '#10b981',
                    backgroundColor: grad,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#10b981',
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8' } },
                    x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
                }
            }
        });
    }

    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        const catLabels = @json($categoryStats->pluck('category_name'));
        const catData   = @json($categoryStats->pluck('count'));
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{
                    data: catData,
                    backgroundColor: ['#10b981','#3b82f6','#a855f7','#f59e0b','#ef4444','#06b6d4','#ec4899','#8b5cf6'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 12, color: '#64748b', font: { size: 11 } } }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
