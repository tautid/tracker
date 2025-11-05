<?php

namespace TautId\Tracker\Data\Utility;

use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;

class FilterPaginationData extends Data
{
    public function __construct(
        public ?int $page = 1,
        public ?int $per_page = 10,
        #[DataCollectionOf(ActiveFilterPaginationData::class)]
        public ?DataCollection $active_filters = null,
        public ?string $sortBy = 'created_at',
        public ?string $sortDirection = 'asc',
        public ?array $searchable = [],
        public ?string $searchTerm = '',
    ) {
        // Ensure defaults are properly set
        $this->page = $this->page ?? 1;
        $this->per_page = $this->per_page ?? 10;
        $this->sortBy = $this->sortBy ?? 'created_at';
        $this->sortDirection = strtolower($this->sortDirection ?? 'asc');
        $this->searchable = $this->searchable ?? [];
        $this->searchTerm = trim($this->searchTerm ?? '');

        // Initialize empty active_filters if null
        if ($this->active_filters === null) {
            $this->active_filters = new DataCollection(ActiveFilterPaginationData::class, []);
        }
    }

    public static function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sortDirection' => ['nullable', 'string', 'in:asc,desc'],
            'sortBy' => ['nullable', 'string'],
            'searchTerm' => ['nullable', 'string', 'max:255'],
            'searchable' => ['nullable', 'array'],
            'searchable.*' => ['string'],
            'active_filters' => ['nullable', 'array'],
            'active_filters.*.column' => ['required_with:active_filters', 'string'],
            'active_filters.*.value' => ['required_with:active_filters'],
        ];
    }

    public static function messages(): array
    {
        return [
            'per_page.max' => 'Items per page cannot exceed 100',
            'sortDirection.in' => 'Sort direction must be either "asc" or "desc"',
            'searchTerm.max' => 'Search term cannot exceed 255 characters',
        ];
    }
}
