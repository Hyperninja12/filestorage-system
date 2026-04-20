@extends('layouts.app')

@section('title', 'New System Placeholder')

@section('content')
    <div class="max-w-4xl mx-auto mt-12 text-center animate-staggered">
        <div class="futuristic-card p-12 rounded-2xl border-t-4 border-t-cyan-500">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-cyan-900/30 text-cyan-400 border border-cyan-500/30 shadow-[0_0_20px_rgba(6,182,212,0.3)] mb-6">
                <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5" />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-cyan-300 mb-4 text-shadow-glow">New System Interface</h1>
            <p class="text-lg text-slate-400 max-w-lg mx-auto leading-relaxed">
                This module is currently under construction. Future system features will be seamlessly integrated here,
                running directly alongside the JES Records.
            </p>
            <div class="mt-8">
                <a href="{{ route('records.index') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-slate-800 hover:bg-slate-700 text-cyan-100 font-medium rounded-xl transition-all shadow-[0_0_10px_rgba(0,0,0,0.5)] border border-cyan-900/50 hover:border-cyan-500/50 hover:shadow-[0_0_15px_rgba(6,182,212,0.4)]">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Return to Records
                </a>
            </div>
        </div>
    </div>
@endsection