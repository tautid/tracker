<?php

namespace Tautid\Tracker\Traits;

use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Tautid\Tracker\Data\Utility\FilterPaginationData;

trait FilterServiceTrait
{
    /**
     * Build a filtered query based on the provided filter data
     *
     * @throws InvalidArgumentException
     */
    public function filteredQuery(string $modelClass, FilterPaginationData $filters): Builder
    {
        $this->validateFilterData($filters);

        $query = $modelClass::query();

        if ($filters->active_filters?->count() > 0) {
            foreach ($filters->active_filters as $activeFilter) {
                $this->applyFilter($query, $activeFilter->column, $activeFilter->value);
            }
        }

        if (! empty($filters->searchable) && ! empty($filters->searchTerm)) {
            $this->applySearch($query, $filters->searchable, $filters->searchTerm);
        }

        return $query->orderBy($filters->sortBy, $filters->sortDirection);
    }

    /**
     * Validate filter data
     *
     * @throws InvalidArgumentException
     */
    private function validateFilterData(FilterPaginationData $filters): void
    {
        if (! in_array(strtolower($filters->sortDirection), ['asc', 'desc'])) {
            throw new InvalidArgumentException('Sort direction must be either "asc" or "desc"');
        }

        if ($filters->page < 1) {
            throw new InvalidArgumentException('Page number must be at least 1');
        }

        if ($filters->per_page < 1) {
            throw new InvalidArgumentException('Items per page must be at least 1');
        }

        if ($filters->per_page > 100) {
            throw new InvalidArgumentException('Items per page cannot exceed 100');
        }
    }

    /**
     * Apply a single filter to the query
     */
    private function applyFilter(Builder $query, string $column, mixed $value): void
    {
        $columnParts = explode('.', $column);

        if (count($columnParts) === 1) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        } else {
            $relationColumn = array_pop($columnParts);
            $relation = implode('.', $columnParts);

            $query->whereHas($relation, function (Builder $subQuery) use ($relationColumn, $value) {
                if (is_array($value)) {
                    $subQuery->whereIn($relationColumn, $value);
                } else {
                    $subQuery->where($relationColumn, $value);
                }
            });
        }
    }

    /**
     * Apply search functionality across searchable fields
     */
    private function applySearch(Builder $query, array $searchableFields, string $searchTerm): void
    {
        $query->where(function (Builder $searchQuery) use ($searchableFields, $searchTerm) {
            foreach ($searchableFields as $searchableField) {
                $fieldParts = explode('.', $searchableField);

                if (count($fieldParts) === 1) {
                    $searchQuery->orWhere($searchableField, 'LIKE', "%{$searchTerm}%");
                } else {
                    $relationColumn = array_pop($fieldParts);
                    $relation = implode('.', $fieldParts);

                    $searchQuery->orWhereHas($relation, function (Builder $subQuery) use ($relationColumn, $searchTerm) {
                        $subQuery->where($relationColumn, 'LIKE', "%{$searchTerm}%");
                    });
                }
            }
        });
    }
}
