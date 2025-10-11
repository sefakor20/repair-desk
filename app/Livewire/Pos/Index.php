<?php

declare(strict_types=1);

namespace App\Livewire\Pos;

use App\Models\PosSale;
use Livewire\Attributes\{Layout, Url};
use Livewire\{Component, WithPagination};

#[Layout('components.layouts.app')]
class Index extends Component
{
    use WithPagination;

    #[Url(as: 'search')]
    public string $searchTerm = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    #[Url(as: 'method')]
    public string $paymentMethodFilter = '';

    #[Url]
    public ?string $success = null;

    public bool $showSuccessMessage = false;

    public function mount(): void
    {
        $this->authorize('viewAny', PosSale::class);

        if ($this->success === 'sale-completed') {
            $this->showSuccessMessage = true;
            $this->success = null;
        }
    }

    public function render()
    {
        $sales = PosSale::query()
            ->with(['customer', 'soldBy', 'items'])
            ->when($this->searchTerm, function ($query) {
                $query->where(function ($q) {
                    $q->where('sale_number', 'like', '%' . $this->searchTerm . '%')
                        ->orWhereHas('customer', function ($customerQuery) {
                            $customerQuery->where('first_name', 'like', '%' . $this->searchTerm . '%')
                                ->orWhere('last_name', 'like', '%' . $this->searchTerm . '%');
                        });
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->paymentMethodFilter, function ($query) {
                $query->where('payment_method', $this->paymentMethodFilter);
            })
            ->latest('sale_date')
            ->paginate(15);

        return view('livewire.pos.index', [
            'sales' => $sales,
        ]);
    }

    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentMethodFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->searchTerm = '';
        $this->statusFilter = '';
        $this->paymentMethodFilter = '';
        $this->resetPage();
    }
}
