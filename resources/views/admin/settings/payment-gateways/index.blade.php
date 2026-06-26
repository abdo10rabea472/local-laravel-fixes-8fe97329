@extends('admin.layouts.app')

@section('title', 'بوابات الدفع')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">بوابات الدفع</h1>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">← الإعدادات</a>
</div>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

<div class="card shadow-sm">
    <div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>البوابة</th>
                <th>المُحرّك (Driver)</th>
                <th>الحالة</th>
                <th>الوضع</th>
                <th>الرسوم الإضافية</th>
                <th>الدول المسموح بها</th>
                <th class="text-end">إجراءات</th>
            </tr>
        </thead>
        <tbody>
        @forelse($gateways as $g)
            <tr>
                <td>{{ $g->position }}</td>
                <td>
                    <strong>{{ $g->name }}</strong><br>
                    <small class="text-muted">{{ $g->code }}</small>
                </td>
                <td><code>{{ $g->driver }}</code></td>
                <td>
                    <form method="POST" action="{{ route('admin.settings.payment-gateways.toggle', $g) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm {{ $g->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                            {{ $g->is_active ? 'مفعّلة' : 'معطّلة' }}
                        </button>
                    </form>
                </td>
                <td>
                    <span class="badge {{ $g->sandbox ? 'bg-warning text-dark' : 'bg-primary' }}">
                        {{ $g->sandbox ? 'Sandbox' : 'Live' }}
                    </span>
                </td>
                <td>{{ number_format((float)$g->extra_fees, 2) }}</td>
                <td>
                    @if(!empty($g->allowed_countries))
                        {{ implode(', ', $g->allowed_countries) }}
                    @else
                        <span class="text-muted">الكل</span>
                    @endif
                </td>
                <td class="text-end">
                    <a href="{{ route('admin.settings.payment-gateways.edit', $g) }}" class="btn btn-sm btn-primary">تعديل</a>
                    <button type="button" class="btn btn-sm btn-outline-info" onclick="testGateway({{ $g->id }}, this)">اختبار</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center text-muted py-4">لا توجد بوابات. شغّل: <code>php artisan db:seed --class=PaymentGatewaySeeder</code></td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
function testGateway(id, btn) {
    const orig = btn.innerHTML;
    btn.disabled = true; btn.innerHTML = '...جاري الاختبار';
    fetch('{{ url('admin/settings/payment-gateways') }}/' + id + '/test', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => {
        alert((d.ok ? '✓ ' : '✗ ') + (d.message || ''));
    }).catch(e => alert('خطأ: ' + e.message))
      .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
}
</script>
@endsection
