<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Cms;

use App\Models\Cms\Notice;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * নোটিশ ব্যবস্থাপনা — পাবলিক সাইটে যা দেখানো হয়।
 */
class NoticeList extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $filterCategory = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $body = '';

    public string $category = Notice::CATEGORY_GENERAL;

    public string $publishedOn = '';

    public string $expiresOn = '';

    public bool $isPublished = true;

    public bool $isPinned = false;

    public ?TemporaryUploadedFile $attachment = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'category' => ['required', Rule::in(array_keys(Notice::categories()))],
            'publishedOn' => ['nullable', 'date'],
            // মেয়াদ প্রকাশের আগে হতে পারে না — নইলে নোটিশটি কখনো দেখাই যেত না।
            'expiresOn' => ['nullable', 'date', 'after_or_equal:publishedOn'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'title' => 'শিরোনাম',
            'body' => 'বিবরণ',
            'category' => 'ধরন',
            'publishedOn' => 'প্রকাশের তারিখ',
            'expiresOn' => 'মেয়াদ শেষ',
            'attachment' => 'সংযুক্তি',
        ];
    }

    public function create(): void
    {
        $this->authorize('cms.notice.create');

        $this->resetForm();
        $this->publishedOn = now()->toDateString();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('cms.notice.update');

        $notice = Notice::findOrFail($id);

        $this->editingId = $notice->id;
        $this->title = $notice->title;
        $this->body = (string) $notice->body;
        $this->category = $notice->category;
        $this->publishedOn = $notice->published_on?->format('Y-m-d') ?? '';
        $this->expiresOn = $notice->expires_on?->format('Y-m-d') ?? '';
        $this->isPublished = $notice->is_published;
        $this->isPinned = $notice->is_pinned;
        $this->attachment = null;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize($this->editingId === null
            ? 'cms.notice.create'
            : 'cms.notice.update');

        $this->validate();

        $attributes = [
            'title' => $this->title,
            'body' => $this->body !== '' ? $this->body : null,
            'category' => $this->category,
            'published_on' => $this->publishedOn !== '' ? $this->publishedOn : now()->toDateString(),
            'expires_on' => $this->expiresOn !== '' ? $this->expiresOn : null,
            'is_published' => $this->isPublished,
            'is_pinned' => $this->isPinned,
        ];

        // ফাইল দিলে তবেই পথ বদলাবে; নইলে পুরনো সংযুক্তি থেকে যাবে।
        if ($this->attachment !== null) {
            $attributes['attachment_path'] = $this->attachment->store('notices', 'public');
        }

        if ($this->editingId === null) {
            $notice = Notice::create($attributes);
            session()->flash('status', "{$notice->title} — নোটিশ যোগ করা হয়েছে।");
        } else {
            $notice = Notice::findOrFail($this->editingId);
            $notice->update($attributes);
            session()->flash('status', "{$notice->title} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function togglePublished(int $id): void
    {
        $this->authorize('cms.notice.update');

        $notice = Notice::findOrFail($id);
        $notice->update(['is_published' => ! $notice->is_published]);

        session()->flash('status', $notice->is_published
            ? "{$notice->title} — এখন সাইটে দেখা যাচ্ছে।"
            : "{$notice->title} — সাইট থেকে সরানো হয়েছে।");
    }

    public function delete(int $id): void
    {
        $this->authorize('cms.notice.delete');

        $notice = Notice::findOrFail($id);
        $title = $notice->title;
        $notice->delete();

        session()->flash('status', "{$title} — মুছে ফেলা হয়েছে।");
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->body = '';
        $this->category = Notice::CATEGORY_GENERAL;
        $this->publishedOn = '';
        $this->expiresOn = '';
        $this->isPublished = true;
        $this->isPinned = false;
        $this->attachment = null;
        $this->resetValidation();
    }

    /**
     * @return LengthAwarePaginator<int, Notice>
     */
    private function notices(): LengthAwarePaginator
    {
        return Notice::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('title', 'like', $term)->orWhere('body', 'like', $term);
                });
            })
            ->when($this->filterCategory !== '', fn ($query) => $query->where('category', $this->filterCategory))
            ->ranked()
            ->paginate(15);
    }

    public function render(): View
    {
        $this->authorize('cms.notice.view');

        return view('livewire.tenant.cms.notice-list', [
            'notices' => $this->notices(),
            'categoryLabels' => Notice::categories(),
        ]);
    }
}
