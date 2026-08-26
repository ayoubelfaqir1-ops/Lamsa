@php
    $sidebarItems = [
        ['label' => 'Application Status', 'href' => '#', 'icon' => 'clock', 'active' => true],
    ];

    $isRejected = isset($status) && $status->value === 'rejected';
    $isSuspended = isset($status) && $status->value === 'suspended';

    if ($isRejected) {
        $statusLabel = 'Application Rejected';
        $statusTone = 'border-rose-500/30 bg-rose-500/10 text-rose-300';
        $statusDot = 'bg-rose-400';
        $headline = 'Your artisan application was not approved.';
        $summary = 'The review team has completed its verification and your current application was not accepted for marketplace access.';
        $nextStep = 'You can contact support for more context, improve your submission details, and apply again when you are ready.';
    } elseif ($isSuspended) {
        $statusLabel = 'Account Suspended';
        $statusTone = 'border-amber-500/30 bg-amber-500/10 text-amber-300';
        $statusDot = 'bg-amber-400';
        $headline = 'Your artisan account is temporarily suspended.';
        $summary = 'Your access is paused while the team reviews quality, policy, or account compliance details.';
        $nextStep = 'Please contact support if you need clarification or if you have updated information to help complete the review.';
    } else {
        $statusLabel = 'Pending Review';
        $statusTone = 'border-sky-500/30 bg-sky-500/10 text-sky-200';
        $statusDot = 'bg-sky-300';
        $headline = 'Your artisan application is under review.';
        $summary = 'Our team is checking your profile, store information, and craft details before enabling dashboard access.';
        $nextStep = 'No action is needed right now. We will update your status as soon as the review is complete.';
    }
@endphp

<x-dashboard.layout header-title="Application Review">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-8">
        <section class="border border-slate-700 bg-[#111827] p-6 sm:p-8 xl:p-10">
            <div class="grid gap-8 xl:grid-cols-[minmax(0,1.25fr)_20rem] xl:items-start">
                <div class="space-y-6">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-3 border px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] {{ $statusTone }}">
                            <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>
                            {{ $statusLabel }}
                        </div>

                        <div class="space-y-3">
                            <h1 class="text-3xl font-light uppercase tracking-[0.18em] text-white sm:text-4xl">
                                Artisan <span class="font-black italic text-[#10B981]">Review Desk</span>
                            </h1>
                            <p class="max-w-3xl text-base leading-relaxed text-slate-300">
                                {{ $headline }}
                            </p>
                            <p class="max-w-3xl text-sm leading-relaxed text-slate-400">
                                {{ $summary }}
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <div class="border border-slate-700 bg-[#182235] p-5">
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Stage</p>
                            <p class="mt-3 text-lg font-black uppercase tracking-widest text-white">
                                {{ $isRejected ? 'Closed' : ($isSuspended ? 'On Hold' : 'Verification') }}
                            </p>
                        </div>
                        <div class="border border-slate-700 bg-[#182235] p-5">
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Estimated Reply</p>
                            <p class="mt-3 text-lg font-black uppercase tracking-widest text-white">
                                {{ $isRejected ? 'Completed' : ($isSuspended ? 'Manual Review' : '2 to 5 days') }}
                            </p>
                        </div>
                        <div class="border border-slate-700 bg-[#182235] p-5">
                            <p class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Access</p>
                            <p class="mt-3 text-lg font-black uppercase tracking-widest text-white">
                                {{ $isRejected ? 'Unavailable' : ($isSuspended ? 'Restricted' : 'Waiting') }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="border border-slate-700 bg-[#182235] p-6">
                    <div class="mb-5 border-b border-slate-700 pb-4">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">What Happens Next</h2>
                    </div>

                    <div class="space-y-4 text-sm leading-relaxed text-slate-300">
                        <p>{{ $nextStep }}</p>
                        <p>
                            Keep an eye on your email address for updates from the marketplace team. If the review needs more information,
                            we will contact you there first.
                        </p>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center justify-center bg-[#10B981] px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-emerald-400">
                            Return to Homepage
                        </a>
                        <a href="mailto:curation@lamsa.com" class="inline-flex items-center justify-center border border-slate-600 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-200 transition-all hover:border-slate-400 hover:text-white">
                            Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)]">
            <div class="border border-slate-700 bg-[#182235] p-6 sm:p-8">
                <div class="mb-6 border-b border-slate-700 pb-4">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Review Progress</h2>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="border border-slate-700 bg-slate-900/40 p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center border border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                            1
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">Application Submitted</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                            Your artisan profile and registration details were received successfully.
                        </p>
                    </div>

                    <div class="border border-slate-700 bg-slate-900/40 p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center border {{ $isRejected ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : 'border-sky-500/30 bg-sky-500/10 text-sky-200' }}">
                            2
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">Team Review</p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                            We verify identity, craft information, and marketplace readiness before activation.
                        </p>
                    </div>

                    <div class="border border-slate-700 bg-slate-900/40 p-5">
                        <div class="mb-4 flex h-10 w-10 items-center justify-center border {{ $isRejected ? 'border-rose-500/30 bg-rose-500/10 text-rose-300' : ($isSuspended ? 'border-amber-500/30 bg-amber-500/10 text-amber-300' : 'border-slate-700 bg-slate-800 text-slate-500') }}">
                            3
                        </div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-white">
                            {{ $isRejected ? 'Decision Shared' : ($isSuspended ? 'Review Hold' : 'Access Enabled') }}
                        </p>
                        <p class="mt-2 text-sm leading-relaxed text-slate-400">
                            {{ $isRejected ? 'The current application cycle is complete.' : ($isSuspended ? 'Access remains paused until the review is resolved.' : 'Once approved, your artisan dashboard becomes available.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-slate-700 bg-[#182235] p-6 sm:p-8">
                <div class="mb-6 border-b border-slate-700 pb-4">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Helpful Notes</h2>
                </div>

                <div class="space-y-4 text-sm leading-relaxed text-slate-300">
                    <p>
                        Strong applications usually include complete contact details, a clear craft specialization, and accurate business information.
                    </p>
                    <p>
                        If your status changes, this page will reflect it immediately the next time you return to your dashboard.
                    </p>
                    <p>
                        Support can help with application questions, but approval decisions still depend on the review team.
                    </p>
                </div>
            </div>
        </section>
    </div>
</x-dashboard.layout>
