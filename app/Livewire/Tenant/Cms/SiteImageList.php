<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Cms;

use App\Models\Cms\SiteImage;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * সাইটের ছবি ব্যবস্থাপনা — স্লাইডার ও গ্যালারি।
 *
 * One component drives both collections: the row shape and the upload flow
 * are identical, only the labels and which fields matter differ. The
 * `collection` prop decides which, and permissions follow it
 * (cms.slider.* / cms.gallery.*).
 */
class SiteImageList extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    /** slider | gallery */
    public string $collection = SiteImage::COLLECTION_SLIDER;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $subtitle = '';

    public string $linkLabel = '';

    public string $linkUrl = '';

    public int $sortOrder = 0;

    public bool $isActive = true;

    public ?TemporaryUploadedFile $image = null;

    public function mount(string $collection = SiteImage::COLLECTION_SLIDER): void
    {
        $this->collection = $collection;

        $this->authorize($this->permission('view'));
    }

    /**
     * স্লাইডারে ছবির উপরে লেখা ও বোতাম বসে; গ্যালারিতে শুধু ক্যাপশন।
     */
    public function isSlider(): bool
    {
        return $this->collection === SiteImage::COLLECTION_SLIDER;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            // সম্পাদনার সময় ছবি না বদলালেও চলে; নতুনের বেলায় বাধ্যতামূলক।
            'image' => [$this->editingId === null ? 'required' : 'nullable', 'image', 'max:3072'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'linkLabel' => ['nullable', 'string', 'max:60'],
            // বোতামের লেখা দিলে লিংকও লাগে, নইলে বোতাম কোথাও নিয়ে যেত না।
            'linkUrl' => ['nullable', 'url', 'max:255', 'required_with:linkLabel'],
            'sortOrder' => ['required', 'integer', 'min:0', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'image' => 'ছবি',
            'title' => $this->isSlider() ? 'শিরোনাম' : 'ক্যাপশন',
            'subtitle' => 'উপশিরোনাম',
            'linkLabel' => 'বোতামের লেখা',
            'linkUrl' => 'বোতামের লিংক',
            'sortOrder' => 'ক্রম',
        ];
    }

    public function create(): void
    {
        $this->authorize($this->permission('create'));

        $this->resetForm();

        // নতুন ছবি সবার শেষে বসে।
        $this->sortOrder = (int) SiteImage::query()
            ->collection($this->collection)
            ->max('sort_order') + 1;

        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize($this->permission('update'));

        $image = $this->find($id);

        $this->editingId = $image->id;
        $this->title = (string) $image->title;
        $this->subtitle = (string) $image->subtitle;
        $this->linkLabel = (string) $image->link_label;
        $this->linkUrl = (string) $image->link_url;
        $this->sortOrder = $image->sort_order;
        $this->isActive = $image->is_active;
        $this->image = null;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize($this->permission($this->editingId === null ? 'create' : 'update'));

        $this->validate();

        $attributes = [
            'collection' => $this->collection,
            'title' => $this->blankToNull($this->title),
            'subtitle' => $this->isSlider() ? $this->blankToNull($this->subtitle) : null,
            'link_label' => $this->isSlider() ? $this->blankToNull($this->linkLabel) : null,
            'link_url' => $this->isSlider() ? $this->blankToNull($this->linkUrl) : null,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];

        if ($this->image !== null) {
            $attributes['image_path'] = Media::store($this->image, 'site/'.$this->collection);
        }

        if ($this->editingId === null) {
            SiteImage::create($attributes);
        } else {
            $existing = $this->find($this->editingId);

            // ছবি বদলালে পুরনো ফাইল রেখে দিলে ডিস্ক ভরে যেত।
            if ($this->image !== null) {
                Media::delete($existing->image_path);
            }

            $existing->update($attributes);
        }

        $this->showForm = false;
        $this->resetForm();

        session()->flash('status', 'ছবি সংরক্ষণ করা হয়েছে।');
    }

    public function delete(int $id): void
    {
        $this->authorize($this->permission('delete'));

        $image = $this->find($id);

        Media::delete($image->image_path);
        $image->delete();

        session()->flash('status', 'ছবি মুছে ফেলা হয়েছে।');
    }

    /**
     * ছবিটি সাইটে দেখাবে কি না — তালিকা থেকেই এক ক্লিকে।
     */
    public function toggleActive(int $id): void
    {
        $this->authorize($this->permission('update'));

        $image = $this->find($id);

        $image->update(['is_active' => ! $image->is_active]);
    }

    /**
     * ক্রম বদল — উপরে বা নিচে এক ধাপ।
     */
    public function move(int $id, string $direction): void
    {
        $this->authorize($this->permission('update'));

        $image = $this->find($id);

        $neighbour = SiteImage::query()
            ->collection($this->collection)
            ->when(
                $direction === 'up',
                fn ($query) => $query->where('sort_order', '<', $image->sort_order)->orderByDesc('sort_order'),
                fn ($query) => $query->where('sort_order', '>', $image->sort_order)->orderBy('sort_order'),
            )
            ->first();

        // সবার উপরে বা সবার নিচে থাকলে করার কিছু নেই।
        if ($neighbour === null) {
            return;
        }

        $order = $image->sort_order;
        $image->update(['sort_order' => $neighbour->sort_order]);
        $neighbour->update(['sort_order' => $order]);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    /**
     * এই collection-এর ছবি ছাড়া অন্য কিছু খোলা যাবে না।
     */
    private function find(int $id): SiteImage
    {
        return SiteImage::query()
            ->collection($this->collection)
            ->findOrFail($id);
    }

    private function permission(string $action): string
    {
        return "cms.{$this->collection}.{$action}";
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->subtitle = '';
        $this->linkLabel = '';
        $this->linkUrl = '';
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->image = null;
    }

    private function blankToNull(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    /**
     * @return Collection<int, SiteImage>
     */
    private function images()
    {
        return SiteImage::query()
            ->collection($this->collection)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        return view('livewire.tenant.cms.site-image-list', [
            'images' => $this->images(),
        ]);
    }
}
