@extends('layouts.public', ['nav_theme' => 'dark'])
@section('title', 'Pricing Plans - Affordable Spoken English Courses')
@section('meta_description', 'Choose the best plan for your English learning journey. Simple, transparent pricing with no hidden fees.')

@section('content')
<div class="py-24 sm:py-32 bg-slate-900 min-h-screen relative overflow-hidden">
    <!-- Background Blobs -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute top-[-20%] left-[-10%] w-[50vw] h-[50vw] rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-600/10 blur-3xl mix-blend-screen"></div>
        <div class="absolute bottom-[-20%] right-[-10%] w-[40vw] h-[40vw] rounded-full bg-gradient-to-tl from-indigo-500/20 to-indigo-600/10 blur-3xl mix-blend-screen"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
        <div class="max-w-4xl mx-auto text-center mb-10 sm:mb-16">
            <h2 class="text-base font-semibold leading-7 text-indigo-400">Pricing</h2>
            <p class="mt-2 text-4xl font-bold tracking-tight text-white sm:text-5xl font-sans">Invest in your English journey</p>
            <p class="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-300">Choose the perfect plan to unlock your spoken English potential.</p>
        </div>
        
        @if(session('error'))
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-xl flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif
        
        @if ($errors->any())
            <div class="max-w-4xl mx-auto mb-8">
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-6 py-4 rounded-xl">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @php
            $strategyData = [
                'Starter' => [
                    'tag' => null,
                    'basePrice' => 399,
                    'subText' => 'Test the AI speech practice and build daily comfort.',
                    'badge' => null,
                    'isPopular' => false,
                ],
                'Booster' => [
                    'tag' => null,
                    'basePrice' => 798,
                    'subText' => 'Overcome basic hesitation and speak simple sentences.',
                    'badge' => null,
                    'isPopular' => false,
                ],
                'Fluency Builder' => [
                    'tag' => null,
                    'basePrice' => 1197,
                    'subText' => 'Build an unbreakable habit and gain conversational confidence.',
                    'badge' => '🔥 Most Popular',
                    'isPopular' => true,
                ],
                'Complete Mastery / Pro Speaker' => [
                    'tag' => null,
                    'basePrice' => 2394,
                    'subText' => 'Complete English transformation for interviews, work, and travel.',
                    'badge' => '💎 Best Value',
                    'isPopular' => false,
                ]
            ];
        @endphp

        <div class="isolate mx-auto mt-8 grid w-full grid-cols-1 gap-y-8 sm:mt-12 sm:grid-cols-2 lg:grid-cols-4 sm:gap-x-6 xl:gap-x-8 items-stretch">
            @foreach($plans as $plan)
                @php
                    $meta = $strategyData[$plan->name] ?? [
                        'tag' => null,
                        'basePrice' => $plan->price,
                        'subText' => 'Full access to all language courses and practice.',
                        'badge' => $plan->is_best_value ? '💎 Best Value' : null,
                        'isPopular' => false,
                    ];
                    
                    $discount = $meta['basePrice'] > $plan->price 
                        ? round((($meta['basePrice'] - $plan->price) / $meta['basePrice']) * 100) 
                        : 0;
                    $durationMonths = round($plan->duration_days / 30);
                    if ($durationMonths < 1) $durationMonths = 1;
                    $perMonth = round($plan->price / $durationMonths);
                @endphp

                <div class="{{ $meta['isPopular'] ? 'ring-2 ring-indigo-500 lg:scale-105 z-10 bg-slate-800' : 'ring-1 ring-slate-700 bg-slate-800/60 backdrop-blur-sm' }} rounded-3xl p-8 relative flex flex-col justify-between shadow-xl transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 h-full group">
                    
                    @if($meta['badge'])
                        <div class="absolute -top-4 left-0 right-0 flex justify-center">
                            <span class="{{ $meta['isPopular'] ? 'bg-indigo-500 text-white' : 'bg-gradient-to-r from-amber-300 to-yellow-500 text-yellow-950 ring-1 ring-yellow-400' }} px-4 py-1 text-xs sm:text-sm font-semibold rounded-full shadow-md whitespace-nowrap">
                                {{ $meta['badge'] }}
                            </span>
                        </div>
                    @endif

                    <div class="flex-1">
                        <div class="flex items-center justify-between gap-x-4 mb-2">
                            <h3 class="text-xl font-bold leading-8 text-white">{{ $plan->name }}</h3>
                        </div>
                        
                        @if($meta['tag'])
                            <p class="text-sm font-semibold text-indigo-400 mb-2 font-malayalam">{{ $meta['tag'] }}</p>
                        @endif
                        
                        <p class="mt-4 text-sm leading-6 text-gray-300 min-h-[3rem]">{{ $meta['subText'] }}</p>
                        
                        <div class="mt-6 flex items-baseline gap-x-1">
                            <span class="text-4xl font-extrabold tracking-tight text-white">₹{{ number_format($plan->price, 0) }}</span>
                            <span class="text-sm font-medium leading-6 text-gray-400">/ {{ $durationMonths }} {{ $durationMonths > 1 ? 'Months' : 'Month' }}</span>
                        </div>
                        
                        <div class="mt-2 flex items-center gap-x-2 text-sm min-h-[1.5rem]">
                            @if($meta['basePrice'] > $plan->price)
                                <span class="line-through text-gray-500">₹{{ number_format($meta['basePrice'], 0) }}</span>
                            @endif
                            @if($discount > 0)
                                <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-400 ring-1 ring-inset ring-green-500/20">Save {{ $discount }}%</span>
                            @endif
                        </div>
                        
                        <div class="mt-2 text-sm font-medium border-t border-slate-700 pt-4 mb-8">
                            <span class="text-white font-semibold">₹{{ number_format($perMonth, 0) }}</span> <span class="text-gray-400">/ month</span>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                        @auth
                            <form action="{{ route('checkout', $plan) }}" method="POST" x-data="{ phone: '{{ auth()->user()->phone ?? '' }}' }">
                                @csrf
                                <div class="mb-4">
                                    <input type="tel" name="phone" id="phone_{{ $plan->id }}" 
                                        placeholder="10-digit mobile number" 
                                        maxlength="10" 
                                        required 
                                        x-model="phone"
                                        class="w-full px-4 py-3 border border-slate-600 bg-slate-900/50 text-white rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all placeholder-gray-500 text-sm text-center"
                                        pattern="[0-9]{10}">
                                </div>
                                <button type="submit" 
                                    :class="phone.length === 10 ? 'bg-green-500 hover:bg-green-400 shadow-lg shadow-green-500/30 text-white' : '{{ $meta['isPopular'] ? 'bg-indigo-500 hover:bg-indigo-400 shadow-lg shadow-indigo-500/30' : 'bg-white/10 hover:bg-white/20 border border-white/10' }} text-white'"
                                    class="w-full font-semibold py-3 px-4 rounded-xl transition-all duration-200 hover:scale-[1.02]">
                                    Buy Now
                                </button>
                            </form>
                        @else
                            <div class="text-center">
                                <a href="{{ route('login') }}" class="block w-full {{ $meta['isPopular'] ? 'bg-indigo-500 hover:bg-indigo-400 shadow-lg shadow-indigo-500/30' : 'bg-white/10 hover:bg-white/20 border border-white/10' }} text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 hover:scale-[1.02]">
                                    Login to Subscribe
                                </a>
                            </div>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-10 sm:mt-16 text-center">
            <p class="text-gray-400">Need help with payments? <a href="{{ route('contact') }}" class="text-indigo-400 font-medium hover:underline">Contact Support</a></p>
        </div>
    </div>
</div>
@endsection
