<?php

declare(strict_types=1);

namespace App\Ship\Services\ApiResponse;

use App\Ship\Contracts\QueryBuilderInterface;
use App\Ship\Parents\DataTransporters\DataTransporter;
use App\Ship\Parents\Queries\QueryBuilder;
use App\Ship\Parents\Transformers\ApiTransformer;
use App\Ship\Services\DataHandler\DataHandlerApiRequestParser;
use App\Ship\Services\DataHandler\Filtering\DataFilteringHandler;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

final class ApiResponseBuilder
{
    private const string OPT_PAGINATION = 'paginate';

    private const string OPT_PAGINATION_PAGE = 'page';

    private const string OPT_PAGINATION_PER_PAGE = 'per_page';

    private const int DEFAULT_ITEMS_PER_PAGE = 5;

    private const int DEFAULT_PAGINATION_PAGE = 1;


    /**
     * @var \App\Ship\Parents\Transformers\ApiTransformer|null
     */
    private ?ApiTransformer $transformer = null;

    /**
     * @var \App\Ship\Parents\Queries\QueryBuilder|null
     */
    private ?QueryBuilder $sourceQuery = null;

    /**
     * @var mixed|null
     */
    private mixed $sourceItem = null;

    /**
     * @var bool
     */
    private bool $paginationEnabled = false;

    /**
     * @var int|null
     */
    private ?int $page = null;

    /**
     * @var int|null
     */
    private ?int $pageCount = null;

    /**
     * @var int|null
     */
    private ?int $itemsCount = null;

    /**
     * @var int|null
     */
    private ?int $itemsPerPage = null;

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Ship\Services\DataHandler\DataHandlerApiRequestParser $dataHandlerApiRequestParser
     * @param \App\Ship\Services\DataHandler\Filtering\DataFilteringHandler $filteringHandler
     */
    public function __construct(
        private readonly Request $request,
        private readonly DataHandlerApiRequestParser $dataHandlerApiRequestParser,
        private readonly DataFilteringHandler $filteringHandler,
    ) {
    }

    /**
     * @return bool
     */
    public function hasSourceQuery(): bool
    {
        return $this->sourceQuery !== null;
    }

    /**
     * @return bool
     */
    public function isPaginationEnabled(): bool
    {
        return $this->paginationEnabled;
    }

    /**
     * @return int|null
     */
    public function getPage(): ?int
    {
        return $this->page;
    }

    /**
     * @return int|null
     */
    public function getPageCount(): ?int
    {
        return $this->pageCount;
    }

    /**
     * @return int|null
     */
    public function getItemsCount(): ?int
    {
        return $this->itemsCount;
    }

    /**
     * @return int|null
     */
    public function getItemsPerPage(): ?int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param \App\Ship\Parents\Queries\QueryBuilder $queryBuilder
     * @param \App\Ship\Parents\Transformers\ApiTransformer $transformer
     *
     * @return \App\Ship\Services\ApiResponse\ApiResponseBuilder
     */
    public function makeForQuery(QueryBuilder $queryBuilder, ApiTransformer $transformer): self
    {
        $this->setTransformer($transformer);
        $this->setSourceQuery($queryBuilder);

        return $this;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param \App\Ship\Parents\Transformers\ApiTransformer $transformer
     *
     * @return \App\Ship\Services\ApiResponse\ApiResponseBuilder
     */
    public function makeForModel(Model $model, ApiTransformer $transformer): self
    {
        $this->setTransformer($transformer);
        $this->setSourceModel($model);

        return $this;
    }

    /**
     * @param mixed[] $array
     *
     * @return \App\Ship\Services\ApiResponse\ApiResponseBuilder
     */
    public function makeForArray(array $array): self
    {
        $this->setSourceArray($array);

        return $this;
    }

    /**
     * @return mixed[]
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     */
    public function getData(): array
    {
        if ($this->hasSourceQuery()) {
            $this->setupRequestFiltering();
            $this->setupRequestPaginating();

            return $this->getQueryData($this->sourceQuery);
        }

        return $this->transformItem($this->sourceItem);
    }

    /**
     * @param \App\Ship\Parents\Transformers\ApiTransformer $transformer
     */
    private function setTransformer(ApiTransformer $transformer): void
    {
        $this->transformer = $transformer;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     */
    private function setSourceModel(Model $model): void
    {
        $this->sourceItem = $model;
    }

    /**
     * @param mixed[] $array
     */
    private function setSourceArray(array $array): void
    {
        $this->sourceItem = $array;
    }

    /**
     * @param \App\Ship\Parents\Queries\QueryBuilder $queryBuilder
     */
    private function setSourceQuery(QueryBuilder $queryBuilder): void
    {
        $this->sourceQuery = $queryBuilder;
    }

    /**
     * @param bool $paginationEnabled
     */
    private function setPaginationEnabled(bool $paginationEnabled): void
    {
        $this->paginationEnabled = $paginationEnabled;
    }

    /**
     * @param int|null $page
     */
    private function setPage(?int $page): void
    {
        $this->page = $page;
    }

    /**
     * @param int|null $pageCount
     */
    private function setPageCount(?int $pageCount): void
    {
        $this->pageCount = $pageCount;
    }

    /**
     * @param int|null $itemsCount
     */
    private function setItemsCount(?int $itemsCount): void
    {
        $this->itemsCount = $itemsCount;
    }

    /**
     * @param int|null $itemsPerPage
     */
    private function setItemsPerPage(?int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @param \App\Ship\Parents\Queries\QueryBuilder $query
     *
     * @return array
     */
    private function getQueryData(QueryBuilder $query): array
    {
        return $this
            ->fetchData($query)
            ->transform(function (mixed $item): array {
                return $this->transformItem($item);
            })
            ->toArray();
    }

    /**
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     */
    private function setupRequestFiltering(): void
    {
        $this->dataHandlerApiRequestParser->parseFilters(
            $this->request,
            $this->filteringHandler,
            $this->transformer->filters()
        );
    }

    private function setupRequestPaginating(): void
    {
        $this->setPaginationEnabled(\filter_var($this->request->query(self::OPT_PAGINATION, false), FILTER_VALIDATE_BOOLEAN));
        $this->setPage((int) $this->request->query(self::OPT_PAGINATION_PAGE, self::DEFAULT_PAGINATION_PAGE));
        $this->setItemsPerPage((int) $this->request->query(self::OPT_PAGINATION_PER_PAGE, self::DEFAULT_ITEMS_PER_PAGE));
    }

    /**
     * @param \App\Ship\Contracts\QueryBuilderInterface $query
     * @return \Illuminate\Support\Collection
     * @throws \UnexpectedValueException
     */
    private function fetchData(QueryBuilderInterface $query): Collection
    {
        $this->filteringHandler->applyFiltersOnQuery($query);

        if (! $this->isPaginationEnabled()) {
            return $query->get();
        }

        $paginator = $this->applyPaginationOnQuery($query);
        return Collection::make($paginator->items());
    }

    /**
     * Apply pagination on the query.
     *
     * @param \App\Ship\Contracts\QueryBuilderInterface $query
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    private function applyPaginationOnQuery(QueryBuilderInterface $query): Paginator
    {
        $paginator = $query->applyPagination($this->getItemsPerPage(), $this->getPage());

        $this->setItemsCount($paginator->total());
        $this->setPageCount($paginator->lastPage());

        return $paginator;
    }

    /**
     * @param mixed $item
     *
     * @return mixed[]
     */
    private function transformItem(mixed $item): array
    {
        $itemData = $item;

        if ($this->transformer !== null) {
            $itemData = $this->transformer->runTransformation($item);
        } elseif (! \is_array($itemData)) {
            if (\method_exists($item, 'toArray')) {
                $itemData = $item->toArray();
            } elseif ($item instanceof \JsonSerializable) {
                $itemData = $item->jsonSerialize();
            }
        }

        if (! \is_array($itemData)) {
            return [$itemData];
        }

        return $itemData;
    }
}
