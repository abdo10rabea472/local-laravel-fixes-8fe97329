@extends('layouts.front')

@section('content')
<section class="bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-800 text-white py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">من نحن</h1>
        <p class="text-lg text-violet-100 max-w-2xl mx-auto">معمل جامعي متخصص في توفير الأدوات والمواد العلمية للطلاب والباحثين بأعلى جودة وأفضل الأسعار.</p>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-2 gap-12 items-center">
        <div>
            <h2 class="text-3xl font-bold text-slate-800 mb-4">قصتنا</h2>
            <p class="text-slate-600 leading-relaxed mb-4">انطلقنا منذ أكثر من {{ $stats['years'] }} سنوات بهدف خدمة الطلاب وتسهيل وصولهم لأدوات معاملهم العلمية بأفضل الأسعار وأعلى جودة. اليوم نخدم آلاف الطلاب والباحثين في الجامعات والمعاهد.</p>
            <p class="text-slate-600 leading-relaxed">نؤمن بأن العلم لا يجب أن يقف عند عوائق التوفر أو السعر، ولذلك نعمل كل يوم على توسيع قائمتنا وتحسين خدمتنا.</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-violet-50 p-6 rounded-2xl text-center">
                <div class="text-3xl font-bold text-violet-700">{{ $stats['products'] }}+</div>
                <div class="text-sm text-slate-600 mt-1">منتج علمي</div>
            </div>
            <div class="bg-indigo-50 p-6 rounded-2xl text-center">
                <div class="text-3xl font-bold text-indigo-700">{{ $stats['categories'] }}</div>
                <div class="text-sm text-slate-600 mt-1">تصنيف</div>
            </div>
            <div class="bg-emerald-50 p-6 rounded-2xl text-center">
                <div class="text-3xl font-bold text-emerald-700">{{ $stats['customers'] }}+</div>
                <div class="text-sm text-slate-600 mt-1">عميل سعيد</div>
            </div>
            <div class="bg-amber-50 p-6 rounded-2xl text-center">
                <div class="text-3xl font-bold text-amber-700">{{ $stats['years'] }}+</div>
                <div class="text-sm text-slate-600 mt-1">سنوات خبرة</div>
            </div>
        </div>
    </div>
</section>

<section class="py-16 bg-slate-50">
    <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-8">
        @foreach([
            ['icon'=>'fa-bullseye','title'=>'رسالتنا','desc'=>'تمكين الطلاب والباحثين بكل ما يحتاجونه من أدوات علمية بسرعة وموثوقية.','color'=>'violet'],
            ['icon'=>'fa-eye','title'=>'رؤيتنا','desc'=>'أن نكون المتجر الأول للأدوات العلمية والمعملية في المنطقة.','color'=>'indigo'],
            ['icon'=>'fa-handshake','title'=>'قيمنا','desc'=>'الجودة، الأمانة، السرعة في التوصيل، وخدمة عملاء استثنائية.','color'=>'emerald'],
        ] as $card)
        <div class="bg-white p-8 rounded-2xl shadow-sm hover:shadow-lg transition">
            <div class="w-14 h-14 bg-{{$card['color']}}-100 text-{{$card['color']}}-600 rounded-xl flex items-center justify-center mb-4">
                <i class="fas {{$card['icon']}} text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">{{ $card['title'] }}</h3>
            <p class="text-slate-600">{{ $card['desc'] }}</p>
        </div>
        @endforeach
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold text-slate-800 text-center mb-12">فريق العمل</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            @foreach($team as $member)
            <div class="text-center">
                <div class="w-32 h-32 mx-auto bg-gradient-to-br from-violet-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mb-3 shadow-lg">
                    {{ mb_substr($member['name'], 0, 1) }}
                </div>
                <h3 class="font-bold text-slate-800">{{ $member['name'] }}</h3>
                <p class="text-sm text-slate-500">{{ $member['role'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
