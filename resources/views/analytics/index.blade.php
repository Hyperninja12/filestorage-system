@extends('layouts.app')

@section('title', 'Analytics Dashboard')

@section('content')
    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 animate-staggered" aria-label="Breadcrumb" style="animation-delay: 0ms;">
        <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs font-medium text-slate-400">
            <li class="inline-flex items-center">
                <a href="/" class="hover:text-cyan-400 transition-colors text-shadow-glow">Home</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-cyan-900" fill="currentColor" viewBox="0 0 20 20" width="16" height="16">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="ml-1 md:ml-2">Analytics & Statistics</span>
                </div>
            </li>
        </ol>
    </nav>

    <div class="mb-8 animate-staggered" style="animation-delay: 100ms;">
        <h1 class="text-2xl font-bold text-fuchsia-400 leading-tight tracking-tight text-shadow-glow">Analytics Dashboard</h1>
        <p class="text-sm text-cyan-500/60 mt-1">System-wide data insights, asset valuation, and personnel distribution.</p>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-staggered" style="animation-delay: 200ms;">
        <div class="futuristic-card p-5 rounded-2xl relative overflow-hidden group border-l-4 border-l-cyan-500">
            <div class="absolute right-0 top-0 mt-4 mr-4 bg-cyan-900/30 p-2.5 rounded-xl text-cyan-500 group-hover:text-cyan-300 group-hover:bg-cyan-800/50 transition-colors shadow-[0_0_15px_rgba(6,182,212,0.2)]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Total System Items</p>
            <p class="text-3xl font-bold text-slate-100 text-shadow-glow">{{ number_format($totalCount) }}</p>
            <div class="mt-3 flex gap-2 text-xs">
                <span class="px-2 py-1 bg-cyan-900/40 text-cyan-300 rounded-lg font-medium border border-cyan-800/50">{{ number_format($parCount) }} PAR</span>
                <span class="px-2 py-1 bg-emerald-900/40 text-emerald-400 rounded-lg font-medium border border-emerald-800/50">{{ number_format($icsCount) }} ICS</span>
            </div>
        </div>

        <div class="futuristic-card p-5 rounded-2xl relative overflow-hidden group border-l-4 border-l-fuchsia-500">
            <div class="absolute right-0 top-0 mt-4 mr-4 bg-fuchsia-900/30 p-2.5 rounded-xl text-fuchsia-500 group-hover:text-fuchsia-300 group-hover:bg-fuchsia-800/50 transition-colors shadow-[0_0_15px_rgba(217,70,239,0.2)]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Total Asset Value</p>
            <p class="text-3xl font-bold text-fuchsia-400 tabular-nums text-shadow-glow">{{ \App\Support\CashFormatter::format($totalValue) }}</p>
            <div class="mt-3 text-xs text-cyan-700 font-medium">Combined unit values</div>
        </div>

        <div class="futuristic-card p-5 rounded-2xl relative overflow-hidden group border-l-4 border-l-blue-500">
            <div class="absolute right-0 top-0 mt-4 mr-4 bg-blue-900/30 p-2.5 rounded-xl text-blue-500 group-hover:text-blue-300 group-hover:bg-blue-800/50 transition-colors shadow-[0_0_15px_rgba(59,130,246,0.2)]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2.25l2.25 8.954a.75.75 0 001.417-.182l2.368-12.062 1.341 6.704a.75.75 0 001.385.127l1.79-3.581C16.326 12.19 17.5 12 18.75 12H21" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Total PAR Value</p>
            <p class="text-2xl font-bold text-blue-400 tabular-nums text-shadow-glow">{{ \App\Support\CashFormatter::format($parValue) }}</p>
            <div class="mt-3 text-xs font-medium px-2 py-1 bg-blue-900/40 text-blue-300 rounded-lg inline-block border border-blue-800/50">Items ≥ ₱50k</div>
        </div>

        <div class="futuristic-card p-5 rounded-2xl relative overflow-hidden group border-l-4 border-l-teal-500">
            <div class="absolute right-0 top-0 mt-4 mr-4 bg-teal-900/30 p-2.5 rounded-xl text-teal-500 group-hover:text-teal-300 group-hover:bg-teal-800/50 transition-colors shadow-[0_0_15px_rgba(20,184,166,0.2)]">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.678 48.678 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7c-.017.22-.032.441-.046.662M19.5 12l3-3m-3 3l-3-3m-12 3c0 1.232.046 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.017-.22.032-.441.046-.662M4.5 12l3 3m-3-3l-3 3" />
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 mb-1">Total ICS Value</p>
            <p class="text-2xl font-bold text-teal-400 tabular-nums text-shadow-glow">{{ \App\Support\CashFormatter::format($icsValue) }}</p>
            <div class="mt-3 text-xs font-medium px-2 py-1 bg-teal-900/40 text-teal-300 rounded-lg inline-block border border-teal-800/50">Items < ₱50k</div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-staggered" style="animation-delay: 300ms;">
        {{-- PAR vs ICS Ratio --}}
        <div class="futuristic-card p-6 rounded-2xl flex flex-col items-center border border-cyan-900/30">
            <h2 class="text-lg font-bold text-slate-200 w-full mb-6 relative pl-4">
                <span class="absolute left-0 top-1 bottom-1 w-1 bg-cyan-400 rounded-full shadow-[0_0_10px_rgba(34,211,238,0.8)]"></span>
                Record Allocation (PAR vs ICS)
            </h2>
            <div class="w-full max-w-[280px] h-64 relative">
                <canvas id="typeChart"></canvas>
            </div>
        </div>

        {{-- Personnel Chart --}}
        <div class="futuristic-card p-6 rounded-2xl border border-emerald-900/30">
            <h2 class="text-lg font-bold text-slate-200 w-full mb-6 relative pl-4">
                <span class="absolute left-0 top-1 bottom-1 w-1 bg-emerald-400 rounded-full shadow-[0_0_10px_rgba(52,211,153,0.8)]"></span>
                Top Personnel by Assignment Count
            </h2>
            <div class="w-full h-72">
                 <canvas id="personnelChart"></canvas>
            </div>
        </div>

        {{-- Category Chart --}}
        <div class="futuristic-card p-6 rounded-2xl lg:col-span-2 border border-fuchsia-900/30">
            <h2 class="text-lg font-bold text-slate-200 w-full mb-6 relative pl-4">
                <span class="absolute left-0 top-1 bottom-1 w-1 bg-fuchsia-400 rounded-full shadow-[0_0_10px_rgba(232,121,249,0.8)]"></span>
                Top Asset Categories
            </h2>
            <div class="w-full h-80">
                 <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup shared styling for modern look
            Chart.defaults.font.family = "'Instrument Sans', ui-sans-serif, system-ui, sans-serif";
            Chart.defaults.color = "#94a3b8";
            Chart.defaults.scale.grid.color = "rgba(148, 163, 184, 0.1)";
            
            // 1. Doughnut Chart: PAR vs ICS
            const ctxType = document.getElementById('typeChart').getContext('2d');
            new Chart(ctxType, {
                type: 'doughnut',
                data: {
                    labels: ['PAR Records', 'ICS Records'],
                    datasets: [{
                        data: [{{ $parCount }}, {{ $icsCount }}],
                        backgroundColor: ['#22d3ee', '#34d399'],
                        borderColor: '#0f172a',
                        borderWidth: 2,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, color: '#e2e8f0' } },
                        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleColor: '#38bdf8', bodyColor: '#f8fafc', padding: 12, cornerRadius: 8, borderColor: 'rgba(56, 189, 248, 0.3)', borderWidth: 1 }
                    }
                }
            });

            // Prepare Data for Personnel Chart
            const personnelData = @json($topPersonnel);
            const pLabels = personnelData.map(p => p.name.length > 20 ? p.name.substring(0, 17) + "..." : p.name);
            const pCounts = personnelData.map(p => p.count);

            // 2. Bar Chart: Top Personnel
            const ctxPersonnel = document.getElementById('personnelChart').getContext('2d');
            new Chart(ctxPersonnel, {
                type: 'bar',
                data: {
                    labels: pLabels,
                    datasets: [{
                        label: 'Assigned Items',
                        data: pCounts,
                        backgroundColor: 'rgba(52, 211, 153, 0.8)',
                        borderColor: '#34d399',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleColor: '#34d399', bodyColor: '#f8fafc', padding: 12, cornerRadius: 8, borderColor: 'rgba(52, 211, 153, 0.3)', borderWidth: 1 }
                    },
                    scales: {
                        y: { beginAtZero: true, border: { display: false }, ticks: { color: '#94a3b8' } },
                        x: { border: { display: false }, grid: { display: false }, ticks: { color: '#94a3b8' } }
                    }
                }
            });

            // Prepare Data for Category Chart
            const categoryData = @json($topCategories);
            const cLabels = categoryData.map(c => c.name);
            const cCounts = categoryData.map(c => c.count);

            // 3. Bar Chart: Top Categories
            const ctxCategory = document.getElementById('categoryChart').getContext('2d');
            new Chart(ctxCategory, {
                type: 'bar',
                data: {
                    labels: cLabels,
                    datasets: [{
                        label: 'Total Items',
                        data: cCounts,
                        backgroundColor: 'rgba(232, 121, 249, 0.8)',
                        borderColor: '#e879f9',
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { backgroundColor: 'rgba(15, 23, 42, 0.9)', titleColor: '#e879f9', bodyColor: '#f8fafc', padding: 12, cornerRadius: 8, borderColor: 'rgba(232, 121, 249, 0.3)', borderWidth: 1 }
                    },
                    scales: {
                        y: { beginAtZero: true, border: { display: false }, ticks: { color: '#94a3b8' } },
                        x: { border: { display: false }, grid: { display: false }, ticks: { autoSkip: false, maxRotation: 45, minRotation: 0, color: '#94a3b8' } }
                    }
                }
            });
        });
    </script>
@endsection
