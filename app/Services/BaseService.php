<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\ResourceCollection;
use \Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class BaseService
 *
 * Abstract class to serve as a base for other services.
 */
abstract class BaseService
{
    protected $query = null;
    protected int $perPage = 15;
    protected int $maxPerPage = 200;
    protected string|null $resourceName = null;
    protected string|null $modelClassName = null;
    protected string|null $resourceClassName = null;
    protected string|null $resourceCollectionClassName = null;

    /**
     * Get query.
     *
     * @return Builder|Relation|null
     */
    protected function getQuery(): Builder|Relation|null
    {
        return $this->query;
    }

    /**
     * Set query.
     *
     * @param Builder|Relation $query
     * @return $this
     */
    public function setQuery(Builder|Relation $query): self
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Get the model class name.
     *
     * @return string
     */
    private function getModelClassName(): string
    {
        return $this->modelClassName ?? $this->getFallbackModelClassName();
    }

    /**
     * Get the fallback model class name.
     *
     * @return string
     */
    private function getFallbackModelClassName(): string
    {
        return 'App\Models\\' . Str::replace('Service', '', class_basename($this));
    }

    /**
     * Get the resource name.
     *
     * @return string
     */
    private function getResourceName(): string
    {
        return $this->resourceName ?? $this->getFallbackResourceName();
    }

    /**
     * Get the fallback resource name.
     *
     * @return string
     */
    private function getFallbackResourceName(): string
    {
        return ucfirst(Str::snake(Str::replace('Service', '', class_basename($this))));
    }

    /**
     * Get the resource class name.
     *
     * @return string
     */
    private function getResourceClassName(): string
    {
        return $this->resourceClassName ?? $this->getFallbackResourceClassName();
    }

    /**
     * Get the fallback resource class name.
     *
     * @return string
     */
    private function getFallbackResourceClassName(): string
    {
        return 'App\Http\Resources\\' . Str::replace('Service', 'Resource', class_basename($this));
    }

    /**
     * Get the resource collection class name.
     *
     * @return string
     */
    private function getResourceCollectionClassName(): string
    {
        return $this->resourceCollectionClassName ?? $this->getFallbackResourceCollectionClassName();
    }

    /**
     * Get the fallback resource collection class name.
     *
     * @return string
     */
    private function getFallbackResourceCollectionClassName(): string
    {
        return 'App\Http\Resources\\' . Str::replace('Service', 'Resources', class_basename($this));
    }

    /**
     * Get the export class name.
     *
     * @return string
     */
    private function getExportClassName(): string
    {
        return $this->exportClassName ?? $this->getFallbackExportClassName();
    }

    /**
     * Get the fallback export class name.
     *
     * @return string
     */
    private function getFallbackExportClassName(): string
    {
        return 'App\Exports\\' . Str::replace('Service', 'Export', class_basename($this));
    }

    /**
     * Apply search on query.
     *
     * @return self
     */
    protected function applySearchOnQuery(): self
    {
        if (request()->filled('search') && method_exists($this->query->getModel(), 'search')) {
            $searchWord = request()->input('search');
            return $this->setQuery($this->query->search($searchWord));
        }

        return $this;
    }

    /**
     * Apply filters on query.
     *
     * How it works
     *
     * -------------------------------------
     * Equality / Non-Equality Filter:
     * -------------------------------------
     *
     * name:eq:John
     *
     * status:eq:active
     * is_active:neq:true
     *
     * created_at:eq:2023-10-16
     * updated_at:gt:2023-01-01
     *
     * -------------------------------------
     * Comparison Filters (>, <, >=, <=):
     * -------------------------------------
     *
     * price:gt:50.00
     * price:lt:100.00
     * created_at:gte:1704067200
     * created_at:lte:1733011200
     *
     * -------------------------------------
     * Range (Between) Filters:
     * -------------------------------------
     *
     * created_at:bt:1704067200:1733011200
     *
     * -------------------------------------
     * Inclusion Filter:
     * -------------------------------------
     *
     * status:in:active,pending
     * payment_status:in:active,pending,failed
     *
     * -------------------------------------
     * Exclusion Filter:
     * -------------------------------------
     *
     * status:not_in:active,pending
     * payment_status:not_in:active,pending,failed
     *
     * -------------------------------------
     * LIKE Filter
     * -------------------------------------
     *
     * name:like:Joh
     *
     * -------------------------------------
     * JSON Field Filters
     * -------------------------------------
     *
     * options->languages:eq:en
     * metadata->sms_credits:gte:100
     *
     * -------------------------------------
     * Combination
     * -------------------------------------
     *
     * name:eq:John|price:gt:50.00|created_at:gte:1704067200|metadata->sms_credits:gte:100
     *
     * @return self
     */
    protected function applyFiltersOnQuery(): self
    {
        if (request()->filled('_filters')) {
            $filters = explode('|', request()->input('_filters'));
            $filters = collect($filters)->map(fn($filter) => trim($filter));

            foreach($filters as $filter) {
                $this->applyFilterOnQuery($filter);
            }
        }

        return $this;
    }

    /**
     * Apply filter on query.
     *
     * @param string $filter e.g "name:eq:John"
     * @return void
     * @throws Exception
     */
    public function applyFilterOnQuery(string $filter): void
    {
        $extracted = self::extractColumnOperatorAndValue($filter);
        $column = Str::snake(array_shift($extracted));
        $operator = array_shift($extracted);
        $input1 = array_shift($extracted);
        $input2 = array_shift($extracted);

        if(is_null($input1)) throw new Exception('The first value was not provided to filter the ('.$column.') column');
        if(is_null($input2) && $operator == 'bt') throw new Exception('The second value was not provided to filter the ('.$column.') column');

        $modelClassName = $this->getModelClassName();
        $modelInstance = new $modelClassName();
        $casts = $modelInstance->getCasts();

        $columnWithoutArrows = explode('->', $column);
        $isJsonField = isset($casts[$columnWithoutArrows[0]]) && $casts[$columnWithoutArrows[0]] == 'App\Casts\JsonToArray';
        $isRelationship = count($columnWithoutArrows) > 1 && !$isJsonField;

        if($isJsonField) {
            $query = $this->applyJsonComparison($column, $operator, $input1, $input2);
        }else if($isRelationship) {

            [$relation, $relationColumn] = explode('->', $column, 2);
            $relation = Str::camel($relation);

            $query = $this->getQuery()->whereHas($relation, function ($query) use ($relationColumn, $operator, $input1, $input2) {
                if ($operator == 'bt') {
                    $query->where($relationColumn, '>=', $input1)
                          ->where($relationColumn, '<=', $input2);
                } elseif ($operator == 'bt_ex') {
                    $query->where($relationColumn, '>', $input1)
                          ->where($relationColumn, '<', $input2);
                } elseif ($operator == 'in') {
                    $query->whereIn($relationColumn, explode(',', $input1));
                } elseif ($operator == 'not_in') {
                    $query->whereNotIn($relationColumn, explode(',', $input1));
                } elseif ($operator == 'like') {
                    $query->where($relationColumn, 'LIKE', '%' . $input1 . '%');
                } else {
                    $query->where($relationColumn, $operator, $input1);
                }
            });

        }else if($operator == 'bt') {

            $query = $this->getQuery()
                          ->where($column, '>=', $input1)
                          ->where($column, '<=', $input2);

        }else if($operator == 'bt_ex') {

            $query = $this->getQuery()
                          ->where($column, '>', $input1)
                          ->where($column, '<', $input2);

        }else{

            if($operator == 'in') {
                $options = explode(',', $input1);
                $query = $this->getQuery()->whereIn($column, $options);
            }else if($operator == 'not_in') {
                $options = explode(',', $input1);
                $query = $this->getQuery()->whereNotIn($column, $options);
            }else if($operator == 'like') {
                $query = $this->getQuery()->where($column, $operator, '%'.$input1.'%');
            }else{
                $query = $this->getQuery()->where($column, $operator, $input1);
            }
        }

        $this->setQuery($query);
    }

    /**
     * Extract column, operator and value.
     *
     * @param string $input
     * @return array
     * @throws Exception
     */
    public static function extractColumnOperatorAndValue(string $input): array
    {
        if (Str::contains($input, ['bt', 'bt_ex'])) {
            $parts = explode(':', $input, 4);
            if (count($parts) != 4) throw new Exception("The filter format is incorrect: '$input'");
        } else {
            $parts = explode(':', $input, 3);
            if (count($parts) != 3) throw new Exception("The filter format is incorrect: '$input'");
        }

        $column = array_shift($parts);
        $operator = array_shift($parts);
        $operator = self::convertOperatorToSymbol($operator);

        if(empty($operator)) throw new Exception('The operator must be provided to filter the ('.$column.') column');

        if (count($parts) === 1) {
            $value = self::convertValueToAppropriateType($parts[0]);
            return [$column, $operator, $value];
        } elseif (count($parts) === 2) {
            $value1 = self::convertValueToAppropriateType($parts[0]);
            $value2 = self::convertValueToAppropriateType($parts[1]);
            return [$column, $operator, $value1, $value2];
        }

        throw new Exception("Unable to extract column, operator, and value from input: '$input'");
    }

    /**
     * Convert value to appropriate type.
     *
     * @param string $value
     * @return mixed
     */
    protected static function convertValueToAppropriateType(string $value)
    {
        if (is_numeric($value)) {
            if (strpos($value, '.') !== false) {
                return $value;
            }
            return (int) $value;
        } elseif (strtolower($value) === 'true') {
            return true;
        } elseif (strtolower($value) === 'false') {
            return false;
        } elseif (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Convert operator to symbol.
     *
     * Input: gte
     * Output: >=
     *
     * @param string $operator
     * @return string|null
     */
    public static function convertOperatorToSymbol(string $operator): string|null
    {
        $operatorMap = [
            'like' => 'like',
            'gte' => '>=',
            'lte' => '<=',
            'gt'  => '>',
            'lt'  => '<',
            'eq'  => '=',
            'neq'  => '!=',
            'in'  => 'in',
            'not_in'  => 'not_in',
            'bt' => 'bt',
            'bt_ex' => 'bt_ex',
        ];

        return $operatorMap[$operator] ?? null;
    }

    /**
     * Apply Json comparison.
     *
     * Input: metadata->user->credits
     * Output: ["metadata", "$.user.credits"]
     *
     * @param string $column
     * @return array [jsonColumn, jsonPath]
     */
    public function applyJsonComparison(string $column, string $operator, $value, $value2 = null)
    {
        [$jsonColumn, $jsonPath] = $this->parseJsonColumnAndPath($column);

        if (empty($jsonPath) || $jsonPath === '$.') $jsonPath = '$';

        if ($operator === 'in') {
            $options = explode(',', $value);
            $query = $this->getQuery();

            $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) = ?", [$options[0]]);

            foreach (array_slice($options, 1) as $option) {
                $query->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) = ?", [$option]);
            }

            return $query;
        }

        if ($operator === 'not_in') {
            $options = explode(',', $value);
            $query = $this->getQuery();

            foreach ($options as $option) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) != ?", [$option]);
            }

            return $query;
        }


        if ($operator === 'like') return $this->getQuery()->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath'))) LIKE LOWER(?)", ['%' . $value . '%']);
        if ($operator === 'bt') return $this->getQuery()->whereRaw("JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) BETWEEN ? AND ?", [$value, $value2]);
        if ($operator === 'bt_ex') return $this->getQuery()->whereRaw("JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) > ? AND JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath')) < ?", [$value, $value2]);

        // Handle other operators (">", "<", , "=", "!=", ">=", "<=")
        return $this->getQuery()->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT($jsonColumn, '$jsonPath'))) $operator LOWER(?)", [$value]);
    }

    /**
     * Parse Json column and path.
     *
     * Input: metadata->user->credits
     * Output: ["metadata", "$.user.credits"]
     *
     * @param string $column
     * @return array [jsonColumn, jsonPath]
     */
    protected function parseJsonColumnAndPath(string $column): array
    {
        // Split the column based on '->' to extract the base column and JSON path parts
        $parts = explode('->', $column);

        // The first part is the actual database column (e.g., 'platforms')
        $jsonColumn = $parts[0];

        // Remaining parts form the JSON path (e.g., 'user.address' becomes '$.user.address')
        $jsonPath = '$.' . implode('.', array_slice($parts, 1));

        return [$jsonColumn, $jsonPath];
    }

    /**
     * Apply sorting on query.
     *
     * How it works
     *
     * -------------------------------------
     * Single Column Sort:
     * -------------------------------------
     *
     * name:asc
     * created_at:desc
     *
     * -------------------------------------
     * Multiple Column Sort:
     * -------------------------------------
     *
     * name:asc|price:desc
     *
     * -------------------------------------
     * JSON Field Sort:
     * -------------------------------------
     *
     * metadata->sms_credits:asc
     *
     * -------------------------------------
     * Relationship Sort:
     * -------------------------------------
     *
     * category->name:asc
     *
     * @return self
     */
    protected function applySortingOnQuery(): self
    {
        if (request()->filled('_sort')) {
            $sorts = explode('|', request()->input('_sort'));
            $sorts = collect($sorts)->map(fn($sort) => trim($sort));

            foreach ($sorts as $sort) {
                $this->applySortOnQuery($sort);
            }
        }

        return $this;
    }

    /**
     * Apply a single sort operation on the query.
     *
     * @param string $sort e.g "name:asc"
     * @return void
     */
    protected function applySortOnQuery(string $sort): void
    {
        $extracted = $this->extractColumnAndDirection($sort);
        $column = Str::snake($extracted['column']);
        $direction = $extracted['direction'];

        $modelClassName = $this->getModelClassName();
        $modelInstance = new $modelClassName();
        $casts = $modelInstance->getCasts();

        $columnWithoutArrows = explode('->', $column);
        $isJsonField = isset($casts[$columnWithoutArrows[0]]) && $casts[$columnWithoutArrows[0]] == 'App\Casts\JsonToArray';
        $isRelationship = count($columnWithoutArrows) > 1 && !$isJsonField;

        if ($isJsonField) {

            // Apply JSON sorting
            $query = $this->getQuery()->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT({$columnWithoutArrows[0]}, '$." . implode('.', array_slice($columnWithoutArrows, 1)) . "')) $direction");

        } elseif ($isRelationship) {

            // Apply Relationship sorting
            [$relation, $relationColumn] = explode('->', $column, 2);
            $relation = Str::camel($relation);

            $query = $this->getQuery()->orderBy(
                function ($query) use ($relation, $relationColumn) {
                    $query->select($relationColumn)
                        ->from($relation)
                        ->whereColumn("{$relation}.id", "{$this->getModelTable()}.{$relation}_id");
                },
                $direction
            );

        } else {

            // Regular column sorting
            $query = $this->getQuery()->orderBy($column, $direction);

        }

        $this->setQuery($query);
    }

    /**
     * Get the table name for the current model.
     *
     * @return string
     */
    protected function getModelTable(): string
    {
        $modelClass = $this->getModelClassName();
        return (new $modelClass())->getTable();
    }

    /**
     * Extract column and direction.
     *
     * @param string $input
     * @return array
     * @throws Exception
     */
    protected function extractColumnAndDirection(string $input): array
    {
        $parts = explode(':', $input, 2);
        if (count($parts) != 2) throw new Exception("The sorting format is incorrect: '$input'");

        $column = $parts[0];
        $direction = strtolower($parts[1]);

        if (!in_array($direction, ['asc', 'desc'])) {
            throw new Exception("Invalid sorting direction for column ($column): '$direction'");
        }

        return ['column' => $column, 'direction' => $direction];
    }

    /**
     * Apply eager loading on query.
     *
     * @return self
     */
    protected function applyEagerLoadingOnQuery(): self
    {
        $relationships = $this->getRequestRelationships();
        $countableRelationships = $this->getRequestCountableRelationships();

        if( !empty($relationships) || !empty($countableRelationships) ) {
            $this->setQuery($this->query->with($relationships)->withCount($countableRelationships));
        }

        return $this->setQuery($this->query);
    }

    /**
     * Apply eager loading on model.
     *
     * @param Model $model
     * @return Model
     */
    protected function applyEagerLoadingOnModel(Model $model): Model
    {
        $relationships = $this->getRequestRelationships();
        $countableRelationships = $this->getRequestCountableRelationships();

        if( !empty($relationships) || !empty($countableRelationships) ) {
            return $model->loadMissing($relationships)->loadCount($countableRelationships);
        }

        return $model;
    }

    /**
     * Has request relationships.
     *
     * @return bool
     */
    protected function hasRequestRelationships(): bool
    {
        return count($this->getRequestRelationships());
    }

    /**
     * Has request countable relationships.
     *
     * @return bool
     */
    protected function hasRequestCountableRelationships(): bool
    {
        return count($this->getRequestCountableRelationships());
    }

    /**
     * Get request relationships.
     *
     * @return array
     */
    protected function getRequestRelationships(): array
    {
        return array_filter(array_map('trim', explode(',', request()->input('_relationships') ?? '')));
    }

    /**
     * Get request countable relationships.
     *
     * @return array
     */
    protected function getRequestCountableRelationships(): array
    {
        return array_filter(array_map('trim', explode(',', request()->input('_countable_relationships') ?? '')));
    }

    /**
     * Check if should count resources.
     *
     * @param Model $model
     * @return Model
     */
    protected function checkIfShouldCountResources()
    {
        return request()->boolean('_count');
    }

    /**
     * Check if should export resources.
     *
     * @param Model $model
     * @return Model
     */
    protected function checkIfShouldExportResources()
    {
        return request()->boolean('_export');
    }

    /**
     * Check if should return pagination information.
     *
     * @param Model $model
     * @return Model
     */
    protected function checkIfShouldReturnPaginationInformation()
    {
        return request()->boolean('_pagination');
    }

    /**
     * Check if should return resource.
     *
     * @param Model $model
     * @return Model
     */
    protected function checkIfShouldReturnResource()
    {
        return request()->has('_return') == false || request()->boolean('_return') == true;
    }

    /**
     * Check if a given relation exists on the request.
     *
     * @param string $relation
     * @return bool
     */
    protected function checkIfHasRelationOnRequest(string $relation): bool
    {
        return Collect($this->getRequestRelationships())->contains($relation);
    }

    /**
     * Get output.
     *
     * @return array|BinaryFileResponse|ResourceCollection
     */
    protected function getOutput(): array|BinaryFileResponse|ResourceCollection
    {
        $this->applySearchOnQuery();
        $this->applyFiltersOnQuery();

        if($this->checkIfShouldCountResources()) {
            return $this->countResources();
        }else if($this->checkIfShouldReturnPaginationInformation()) {
            return $this->getPaginationInformation();
        }else {
            $this->applySortingOnQuery();
            if($this->checkIfShouldExportResources()) {
                return $this->exportResources();
            }else{
                $this->applyEagerLoadingOnQuery();
                return $this->getResources();
            }
        }
    }

    /**
     * Count resources.
     *
     * @return array
     */
    protected function countResources(): array
    {
        return ['total' => $this->query->count()];
    }

    /**
     * Get pagination information.
     *
     * @return array
     */
    protected function getPaginationInformation(): array
    {
        $paginated = $this->query->paginate()->toArray();
        $paginated['data'] = collect($paginated['data'])->map(fn($record) => $record['id']);
        return $paginated;
    }

    /**
     * Export resources.
     *
     * @return BinaryFileResponse
     */
    protected function exportResources(): BinaryFileResponse
    {
        $resourceName = $this->getResourceName();
        $exportClassName = $this->getExportClassName();
        $resourceNameInPlural = Str::plural($resourceName);

        // Get export format from request (default to CSV)
        $format = request()->input('export_format', 'csv');

        // Set file extension and format type based on the format
        $formats = [
            'csv'  => \Maatwebsite\Excel\Excel::CSV,
            'xlsx' => \Maatwebsite\Excel\Excel::XLSX,
            'pdf'  => \Maatwebsite\Excel\Excel::DOMPDF, // Requires DomPDF installed
        ];

        // Validate format and set filename
        $format = array_key_exists($format, $formats) ? $format : 'csv';
        $fileName = $resourceNameInPlural . '.' . $format;

        // Limit the export data
        $maxLimit = 5000;
        $exportLimit = request()->input('export_limit', $maxLimit);
        $exportLimit = is_numeric($exportLimit) ? (int) $exportLimit : $maxLimit;
        $exportLimit = min($exportLimit, $maxLimit);

        // Offset the export data
        $defaultOffset = 0;
        $exportOffset = request()->input('export_offset', $defaultOffset);
        $exportOffset = is_numeric($exportOffset) ? (int) $exportOffset : $defaultOffset;

        // Generate export instance
        $export = new $exportClassName($this->query->offset($exportOffset)->limit($exportLimit));

        // Return the exported file in the requested format
        return Excel::download($export, $fileName, $formats[$format]);
    }

    /**
     * Get resources.
     *
     * @return ResourceCollection
     */
    protected function getResources(): ResourceCollection
    {
        $perPage = request()->filled('per_page') ? (int) request()->input('per_page') : $this->perPage;
        $perPage = ($perPage <= $this->maxPerPage) ? $perPage : $this->perPage;

        $resourceCollectionClassName = $this->getResourceCollectionClassName();
        return new $resourceCollectionClassName($this->query->paginate($perPage));
    }

    /**
     * Show created resource.
     *
     * @param Model|null $model
     * @param string|null $message
     * @return Model|array
     */
    protected function showCreatedResource(Model $model, string|null $message = null): Model|array
    {
        return $this->showSavedResource($model, 'created', $message);
    }

    /**
     * Show updated resource.
     *
     * @param Model|null $model
     * @param string|null $message
     * @return Model|array
     */
    protected function showUpdatedResource(Model $model, string|null $message = null): Model|array
    {
        return $this->showSavedResource($model, 'updated', $message);
    }

    /**
     * Show saved resource.
     *
     * @param Model|null $model
     * @param string $action
     * @param string|null $message
     *
     * @return Model|array
     */
    protected function showSavedResource(Model $model, string $action, string|null $message = null): Model|array
    {
        $resourseName = $this->getResourceName();

        if(empty($message)) {
            $message ??= "$resourseName $action";
        }

        if($this->checkIfShouldReturnResource()) {

            $this->applyEagerLoadingOnModel($model);

            $resourceKeyName = Str::snake($resourseName);
            $resourceClassName = $this->getResourceClassName();

            return [
                'message' => $message,
                $resourceKeyName => new $resourceClassName($model)
            ];

        }else{

            return ['message' => $message];

        }
    }

    /**
     * Show resource.
     *
     * @param Model $model
     * @return JsonResource
     */
    protected function showResource(Model $model): JsonResource
    {
        $resourceClassName = $this->getResourceClassName();
        return (new $resourceClassName($model));
    }
}
