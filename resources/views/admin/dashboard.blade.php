@extends('admin.layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
@php
    $adminName = optional(auth('admin')->user())->name ?? 'المدير';
    $fmt = fn($n) => number_format((float) $n);

    // حساب نسب التغير (آخر 7 أيام مقابل السابقة) من بيانات حقيقية
    $salesTrend = 0; $ordersTrend = 0; $customersTrend = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('orders')) {
        $paid = ['paid','shipped','delivered'];
        $cur  = (float) \App\Models\Order::whereIn('status',$paid)->where('created_at','>=',now()->subDays(7))->sum('total');
        $prev = (float) \App\Models\Order::whereIn('status',$paid)->whereBetween('created_at',[now()->subDays(14),now()->subDays(7)])->sum('total');
        $salesTrend = $prev > 0 ? round((($cur-$prev)/$prev)*100,1) : ($cur>0?100:0);

        $curO  = \App\Models\Order::where('status','delivered')->where('created_at','>=',now()->subDays(7))->count();
        $prevO = \App\Models\Order::where('status','delivered')->whereBetween('created_at',[now()->subDays(14),now()->subDays(7)])->count();
        $ordersTrend = $prevO > 0 ? round((($curO-$prevO)/$prevO)*100,1) : ($curO>0?100:0);
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
        $curU  = \App\Models\User::where('created_at','>=',now()->subDays(7))->count();
        $prevU = \App\Models\User::whereBetween('created_at',[now()->subDays(14),now()->subDays(7)])->count();
        $customersTrend = $prevU > 0 ? round((($curU-$prevU)/$prevU)*100,1) : ($curU>0?100:0);
    }
    $trendBadge = function($v){
        if ($v > 0) return ['text-emerald-500','fa-arrow-up','+'.$v.'%'];
        if ($v < 0) return ['text-red-500','fa-arrow-down',$v.'%'];
        return ['text-gray-400','fa-minus','0%'];
    };
    [$sC,$sI,$sT] = $trendBadge($salesTrend);
    [$oC,$oI,$oT] = $trendBadge($ordersTrend);
    [$cC,$cI,$cT] = $trendBadge($customersTrend);
@endphp

<div class="space-y-6">

    {{-- Page header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">نظرة عامة على النظام</h1>
            <p class="text-sm text-gray-500 mt-1">مرحباً بك مجدداً، {{ $adminName }}. إليك أداء متجرك لهذا اليوم.</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" class="bg-white dark:bg-dark-900 border border-gray-200 dark:border-gray-800 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2">
                <i class="fas fa-calendar-alt"></i> آخر 30 يومًا
            </button>
            <a href="{{ route('admin.products.create') }}" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-lg shadow-primary-500/20 flex items-center gap-2">
                <i class="fas fa-plus"></i> إضافة منتج
            </a>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">إجمالي المبيعات</span>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $fmt($totalSales) }} ج.م</h3>
                <span class="text-xs {{ $sC }} font-semibold flex items-center gap-1 mt-2"><i class="fas {{ $sI }}"></i> {{ $sT }} منذ الأسبوع الماضي</span>
            </div>
            <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 rounded-xl"><i class="fas fa-wallet text-xl"></i></div>
        </div>

        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">الطلبات المكتملة</span>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $fmt($completedOrders) }} طلب</h3>
                <span class="text-xs {{ $oC }} font-semibold flex items-center gap-1 mt-2"><i class="fas {{ $oI }}"></i> {{ $oT }}</span>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-950/30 text-blue-600 rounded-xl"><i class="fas fa-shopping-bag text-xl"></i></div>
        </div>

        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">العملاء المشتركين</span>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $fmt($totalCustomers) }} عميل</h3>
                <span class="text-xs {{ $cC }} font-semibold flex items-center gap-1 mt-2"><i class="fas {{ $cI }}"></i> {{ $cT }}</span>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-950/30 text-purple-600 rounded-xl"><i class="fas fa-users text-xl"></i></div>
        </div>

        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex justify-between items-start">
            <div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">مخزون منخفض التنبيه</span>
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $fmt($lowStockCount + $outOfStockCount) }} منتجاً</h3>
                <span class="text-xs text-amber-500 font-semibold flex items-center gap-1 mt-2"><i class="fas fa-exclamation-circle"></i> يحتاج إعادة ملء فوراً</span>
            </div>
            <div class="p-3 bg-amber-50 dark:bg-amber-950/30 text-amber-600 rounded-xl"><i class="fas fa-exclamation-triangle text-xl"></i></div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold">مخطط الإيرادات والمبيعات المتقدم</h3>
                <i class="fas fa-ellipsis-h text-gray-400 cursor-pointer"></i>
            </div>
            <div class="h-72"><canvas id="revenueChart"></canvas></div>
        </div>
        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold">توزيع مبيعات الأقسام (الكليات)</h3>
            </div>
            <div class="h-72 flex items-center justify-center"><canvas id="categoryChart"></canvas></div>
        </div>
    </div>

    {{-- Recent orders + low stock --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold">آخر الطلبات الواردة</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-primary-600 font-semibold hover:underline">عرض الكل</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm">
                    <thead>
                        <tr class="text-gray-400 border-b border-gray-100 dark:border-gray-800">
                            <th class="pb-3 font-medium">رقم الطلب</th>
                            <th class="pb-3 font-medium">العميل</th>
                            <th class="pb-3 font-medium">المجموع</th>
                            <th class="pb-3 font-medium">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentOrders as $order)
                            @php
                                $statusMap = [
                                    'pending'    => ['قيد الانتظار', 'bg-amber-50 text-amber-600 dark:bg-amber-950/30'],
                                    'paid'       => ['مدفوع',        'bg-blue-50 text-blue-600 dark:bg-blue-950/30'],
                                    'processing' => ['قيد المعالجة', 'bg-amber-50 text-amber-600 dark:bg-amber-950/30'],
                                    'shipped'    => ['تم الشحن',     'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30'],
                                    'delivered'  => ['تم التسليم',   'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30'],
                                    'cancelled'  => ['ملغى',         'bg-red-50 text-red-600 dark:bg-red-950/30'],
                                ];
                                [$label, $cls] = $statusMap[$order->status] ?? [$order->status, 'bg-gray-50 text-gray-600 dark:bg-dark-800'];
                            @endphp
                            <tr>
                                <td class="py-3 font-semibold text-primary-600">#{{ $order->order_number ?? $order->id }}</td>
                                <td class="py-3 font-medium">{{ optional($order->user)->name ?? 'زائر' }}</td>
                                <td class="py-3">{{ $fmt($order->total) }} ج.م</td>
                                <td class="py-3"><span class="px-2 py-1 text-xs font-medium rounded-md {{ $cls }}">{{ $label }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-6 text-center text-gray-400 text-sm">لا توجد طلبات بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-dark-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-red-600 flex items-center gap-2"><i class="fas fa-boxes"></i> المنتجات أوشكت على النفاد</h3>
                <a href="{{ route('admin.stock.index') }}" class="text-xs text-primary-600 font-semibold hover:underline">إدارة المخزن</a>
            </div>
            <div class="space-y-3">
                @forelse($lowStockProducts as $p)
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-dark-800 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-white dark:bg-dark-900 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700">
                                <i class="fas fa-box text-primary-500"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ \Illuminate\Support\Str::limit($p->name, 38) }}</h4>
                                <p class="text-xs text-gray-400">{{ optional($p->category)->name ?? '—' }}</p>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-1 font-bold rounded bg-red-50 text-red-600 dark:bg-red-950/30">متبقي {{ $p->stock }}</span>
                    </div>
                @empty
                    <div class="text-center text-sm text-gray-400 py-6">لا توجد منتجات منخفضة المخزون</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const REVENUE_LABELS = @json($revenueLabels);
    const REVENUE_DATA   = @json($revenueSeries);
    const CATEGORY_LABELS = @json($categoryStats->pluck('category_name'));
    const CATEGORY_DATA   = @json($categoryStats->pluck('count'));

    function initDashboardCharts(){
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#334155' : '#f1f5f9';
        const textColor = isDark ? '#94a3b8' : '#64748b';

        if(window.revenueChartInstance)  window.revenueChartInstance.destroy();
        if(window.categoryChartInstance) window.categoryChartInstance.destroy();

        const ctxRev = document.getElementById('revenueChart');
        if(ctxRev){
            window.revenueChartInstance = new Chart(ctxRev.getContext('2d'), {
                type: 'line',
                data: {
                    labels: REVENUE_LABELS,
                    datasets: [{
                        label: 'الإيرادات (ج.م)',
                        data: REVENUE_DATA,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.08)',
                        fill: true, tension: 0.3, borderWidth: 3,
                        pointBackgroundColor: '#22c55e',
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { labels: { font: { family: 'Cairo' }, color: textColor } } },
                    scales: {
                        x: { grid: { color: 'transparent' }, ticks: { font: { family: 'Cairo' }, color: textColor } },
                        y: { grid: { color: gridColor },    ticks: { font: { family: 'Cairo' }, color: textColor } }
                    }
                }
            });
        }

        const ctxCat = document.getElementById('categoryChart');
        if(ctxCat){
            const palette = ['#22c55e','#3b82f6','#ec4899','#f59e0b','#8b5cf6','#06b6d4','#ef4444','#10b981'];
            window.categoryChartInstance = new Chart(ctxCat.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: CATEGORY_LABELS,
                    datasets: [{ data: CATEGORY_DATA, backgroundColor: palette.slice(0, CATEGORY_LABELS.length), borderWidth: 0 }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { font: { family: 'Cairo' }, color: textColor } } }
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', initDashboardCharts);
    window.addEventListener('themechange', initDashboardCharts);
</script>
@endpush
