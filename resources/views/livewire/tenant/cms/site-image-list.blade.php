@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')
@php($isSlider = $this->isSlider())
@php($noun = $isSlider ? 'স্লাইড' : 'ছবি')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? $noun.' সম্পাদনা' : 'নতুন '.$noun }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-4">
                <x-form.field label="ছবি" name="image"
                              :required="$editingId === null"
                              :hint="$isSlider
                                  ? 'চওড়া ছবি ভালো দেখায় — ১৯২০×৯০০ পিক্সেল, সর্বোচ্চ ৩ MB'
                                  : 'বর্গাকার ছবি ভালো দেখায় — সর্বোচ্চ ৩ MB'">
                    <input type="file" id="image" wire:model="image" accept="image/*"
                           class="mt-1 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                </x-form.field>
                <div wire:loading wire:target="image" class="text-sm text-gray-500">আপলোড হচ্ছে…</div>

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" alt=""
                         class="h-32 rounded-lg border border-gray-200 object-cover">
                @elseif ($editingId)
                    <p class="text-xs text-gray-500">নতুন ছবি না দিলে আগেরটিই থাকবে।</p>
                @endif

                <x-form.field :label="$isSlider ? 'শিরোনাম' : 'ক্যাপশন'" name="title"
                              :hint="$isSlider ? 'ছবির উপরে বড় করে বসবে' : 'ছবির নিচে ছোট করে বসবে'">
                    <input type="text" id="title" wire:model="title" class="{{ $inputClass }}">
                </x-form.field>

                @if ($isSlider)
                    <x-form.field label="উপশিরোনাম" name="subtitle">
                        <input type="text" id="subtitle" wire:model="subtitle" class="{{ $inputClass }}">
                    </x-form.field>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-form.field label="বোতামের লেখা" name="linkLabel"
                                      hint="খালি রাখলে বোতাম থাকবে না">
                            <input type="text" id="linkLabel" wire:model="linkLabel"
                                   placeholder="ভর্তি আবেদন করুন" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="বোতামের লিংক" name="linkUrl">
                            <input type="url" id="linkUrl" wire:model="linkUrl"
                                   placeholder="https://…" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field label="ক্রম" name="sortOrder" hint="ছোট সংখ্যা আগে দেখাবে">
                        <input type="number" id="sortOrder" wire:model="sortOrder" min="0" max="999"
                               class="{{ $inputClass }}">
                    </x-form.field>

                    <label class="flex items-center gap-2 self-end pb-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isActive"
                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        সাইটে দেখাবে
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                        {{ $editingId ? 'সংরক্ষণ করুন' : 'যোগ করুন' }}
                    </button>
                    <button type="button" wire:click="cancel"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                        বাতিল
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-gray-600">
                @if ($isSlider)
                    হোমপেজের উপরে ঘুরে ঘুরে যে ছবিগুলো দেখাবে। একটিও না দিলে সাধারণ ব্যানার দেখাবে।
                @else
                    গ্যালারি পেজে ও হোমপেজে যে ছবিগুলো দেখাবে।
                @endif
            </p>

            <button wire:click="create"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                নতুন {{ $noun }}
            </button>
        </div>
    @endif

    @if ($images->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">এখনো কোনো {{ $noun }} নেই।</p>
            <p class="mt-1 text-sm text-gray-600">"নতুন {{ $noun }}" দিয়ে শুরু করুন।</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($images as $image)
                <div wire:key="image-{{ $image->id }}"
                     @class([
                         'overflow-hidden rounded-xl border bg-white',
                         'border-gray-200' => $image->is_active,
                         'border-dashed border-gray-300 opacity-60' => ! $image->is_active,
                     ])>
                    <div class="relative">
                        <img src="{{ $image->url() }}" alt="" loading="lazy"
                             class="aspect-[16/9] w-full bg-gray-100 object-cover">

                        <span class="absolute left-2 top-2 rounded bg-black/60 px-2 py-0.5 text-xs tabular-nums text-white">
                            @bn($image->sort_order)
                        </span>
                    </div>

                    <div class="space-y-3 p-4">
                        <div>
                            <p class="font-medium text-gray-900">
                                {{ $image->title ?: '(শিরোনাম নেই)' }}
                            </p>
                            @if ($image->subtitle)
                                <p class="text-sm text-pretty text-gray-600">{{ $image->subtitle }}</p>
                            @endif
                            @if ($image->hasLink())
                                <p class="mt-1 text-xs text-gray-500">
                                    বোতাম: {{ $image->link_label }}
                                </p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1">
                            <button wire:click="toggleActive({{ $image->id }})"
                                    @class([
                                        'rounded-full px-2 py-0.5 text-xs transition-colors duration-150',
                                        'bg-green-50 text-green-700 hover:bg-green-100' => $image->is_active,
                                        'bg-gray-100 text-gray-600 hover:bg-gray-200' => ! $image->is_active,
                                    ])>
                                {{ $image->is_active ? 'দেখাচ্ছে' : 'লুকানো' }}
                            </button>

                            <span class="grow"></span>

                            @unless ($loop->first)
                                <button wire:click="move({{ $image->id }}, 'up')"
                                        class="rounded px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100"
                                        title="উপরে তুলুন">↑</button>
                            @endunless
                            @unless ($loop->last)
                                <button wire:click="move({{ $image->id }}, 'down')"
                                        class="rounded px-2 py-1 text-gray-600 transition-colors hover:bg-gray-100"
                                        title="নিচে নামান">↓</button>
                            @endunless

                            <button wire:click="edit({{ $image->id }})"
                                    class="rounded px-2 py-1 text-sm text-gray-700 transition-colors hover:bg-gray-100">
                                সম্পাদনা
                            </button>
                            <button wire:click="delete({{ $image->id }})"
                                    wire:confirm="এই {{ $noun }} মুছে ফেলবেন?"
                                    class="rounded px-2 py-1 text-sm text-red-600 transition-colors hover:bg-red-50">
                                মুছুন
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
