<div x-data="pricingData()" class="py-24 sm:py-32 bg-gray-50/30">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="mx-auto max-w-4xl text-center">
            <h2 class="text-base font-semibold leading-7 text-indigo-600">Pricing</h2>
            <p class="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl font-sans">Invest in your English journey</p>
        </div>
        <p class="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-600">Choose the perfect plan to unlock your spoken English potential.</p>
        
        <div class="isolate mx-auto mt-16 grid max-w-md grid-cols-1 gap-y-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-4 lg:gap-x-6 xl:gap-x-8 items-center">
            <template x-for="(plan, index) in plans" :key="index">
                <div :class="[
                    plan.isPopular ? 'ring-2 ring-indigo-600 lg:scale-105 z-10 bg-white' : 'ring-1 ring-gray-200 bg-white/60 backdrop-blur-sm',
                    'rounded-2xl p-8 relative flex flex-col justify-between shadow-xl shadow-gray-200/50 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 h-full'
                ]">
                    <!-- Badge -->
                    <div x-show="plan.badge" class="absolute -top-4 left-0 right-0 flex justify-center" x-cloak>
                        <span x-text="plan.badge" :class="[
                            plan.isPopular ? 'bg-indigo-600 text-white' : 'bg-gradient-to-r from-amber-300 to-yellow-500 text-yellow-950 ring-1 ring-yellow-400',
                            'px-4 py-1 text-xs sm:text-sm font-semibold rounded-full shadow-md whitespace-nowrap'
                        ]"></span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between gap-x-4 mb-2">
                            <h3 class="text-xl font-bold leading-8 text-gray-900" x-text="plan.name"></h3>
                        </div>
                        <p x-show="plan.tag" class="text-sm font-semibold text-indigo-600 mb-2 font-malayalam" x-text="plan.tag" x-cloak></p>
                        
                        <p class="mt-4 text-sm leading-6 text-gray-600 min-h-[3rem]" x-text="plan.subText"></p>
                        
                        <div class="mt-6 flex items-baseline gap-x-1">
                            <span class="text-4xl font-bold tracking-tight text-gray-900" x-text="formatCurrency(plan.finalPrice)"></span>
                            <span class="text-sm font-semibold leading-6 text-gray-600" x-text="'/ ' + plan.durationMonths + (plan.durationMonths > 1 ? ' Months' : ' Month')"></span>
                        </div>
                        
                        <div class="mt-2 flex items-center gap-x-2 text-sm min-h-[1.5rem]">
                            <span x-show="plan.basePrice > plan.finalPrice" class="line-through text-gray-400" x-text="formatCurrency(plan.basePrice)" x-cloak></span>
                            <span x-show="plan.discount > 0" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20" x-text="'Save ' + plan.discount + '%'" x-cloak></span>
                        </div>
                        
                        <div class="mt-2 text-sm text-gray-500 font-medium border-t border-gray-100 pt-4">
                            <span class="text-gray-900 font-semibold" x-text="formatCurrency(Math.round(plan.finalPrice / plan.durationMonths))"></span> <span class="text-gray-400">/ month</span>
                        </div>
                    </div>
                    
                    <a href="#" :class="[
                        plan.isPopular ? 'bg-indigo-600 text-white hover:bg-indigo-500 shadow-lg shadow-indigo-200 focus-visible:outline-indigo-600' : 'text-indigo-600 ring-1 ring-inset ring-indigo-200 hover:ring-indigo-300 bg-indigo-50/50 hover:bg-indigo-50',
                        'mt-8 block rounded-xl px-3 py-3 text-center text-sm font-semibold leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 transition-all duration-200 hover:scale-[1.02]'
                    ]">Start Practicing Now</a>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('pricingData', () => ({
            plans: [
                {
                    name: 'Starter',
                    tag: '(തുടക്കം)',
                    durationMonths: 1,
                    basePrice: 399,
                    finalPrice: 399,
                    subText: 'Test the AI speech practice and build daily comfort.',
                    isPopular: false,
                    isBestValue: false,
                    badge: null,
                },
                {
                    name: 'Booster',
                    tag: null,
                    durationMonths: 2,
                    basePrice: 798,
                    finalPrice: 679,
                    subText: 'Overcome basic hesitation and speak simple sentences.',
                    isPopular: false,
                    isBestValue: false,
                    badge: null,
                },
                {
                    name: 'Fluency Builder',
                    tag: null,
                    durationMonths: 3,
                    basePrice: 1197,
                    finalPrice: 899,
                    subText: 'Build an unbreakable habit and gain conversational confidence.',
                    isPopular: true,
                    isBestValue: false,
                    badge: '🔥 Most Popular / ഏറ്റവും കൂടുതൽ പേർ തിരഞ്ഞെടുക്കുന്നത്',
                },
                {
                    name: 'Complete Mastery / Pro Speaker',
                    tag: null,
                    durationMonths: 6,
                    basePrice: 2394,
                    finalPrice: 1499,
                    subText: 'Complete English transformation for interviews, work, and travel.',
                    isPopular: false,
                    isBestValue: true,
                    badge: '💎 Best Value',
                }
            ].map(plan => {
                plan.discount = plan.basePrice > plan.finalPrice 
                    ? Math.round(((plan.basePrice - plan.finalPrice) / plan.basePrice) * 100) 
                    : 0;
                return plan;
            }),
            formatCurrency(value) {
                return new Intl.NumberFormat('en-IN', { 
                    style: 'currency', 
                    currency: 'INR', 
                    maximumFractionDigits: 0 
                }).format(value);
            }
        }));
    });
</script>

<style>
    /* Prevent flicker before Alpine initializes */
    [x-cloak] { display: none !important; }
</style>
