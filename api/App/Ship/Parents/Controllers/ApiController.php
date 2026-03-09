<?php

declare(strict_types=1);

namespace App\Ship\Parents\Controllers;

use App\Ship\Parents\Queries\QueryBuilder;
use App\Ship\Parents\Transformers\ApiTransformer;
use App\Ship\Responses\ApiResponse;
use App\Ship\Services\ApiResponse\ApiResponseBuilder;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{

    /**
     * @param \App\Ship\Services\ApiResponse\ApiResponseBuilder $responseBuilder
     */
    public function __construct(
        private readonly ApiResponseBuilder $responseBuilder
    ) {
    }

    /**
     * @param \App\Ship\Parents\Queries\QueryBuilder $query
     * @param string $transformerClass
     *
     * @return \App\Ship\Responses\ApiResponse
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function queryResponse(QueryBuilder $query, string $transformerClass): ApiResponse
    {
        $transformer = $this->getTransformer($transformerClass);
        $responseBuilder = $this->responseBuilder->makeForQuery($query, $transformer);

        return $this->getResponseFromBuilder($responseBuilder);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $transformerClass
     *
     * @return \App\Ship\Responses\ApiResponse
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function modelResponse(Model $model, string $transformerClass): ApiResponse
    {
        $transformer = $this->getTransformer($transformerClass);
        $responseBuilder = $this->responseBuilder->makeForModel($model, $transformer);

        return $this->getResponseFromBuilder($responseBuilder);
    }

    /**
     * @param mixed[] $array
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    protected function arrayResponse(array $array): ApiResponse
    {
        $responseBuilder = $this->responseBuilder->makeForArray($array);

        return $this->getResponseFromBuilder($responseBuilder);
    }

    /**
     * @param int $status
     *
     * @return \App\Ship\Responses\ApiResponse
     */
    protected function emptyResponse(int $status = 204): ApiResponse
    {
        return new ApiResponse(null, $status);
    }

    /**
     * @param string $transformerClass
     *
     * @return \App\Ship\Parents\Transformers\ApiTransformer
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getTransformer(string $transformerClass): ApiTransformer
    {
        /** @var \App\Ship\Parents\Transformers\ApiTransformer $transformer */
        return Container::getInstance()->make($transformerClass);
    }

    /**
     * Get response from builder.
     *
     * @param \App\Ship\Services\ApiResponse\ApiResponseBuilder $responseBuilder
     *
     * @return \App\Ship\Responses\ApiResponse+
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     */
    private function getResponseFromBuilder(ApiResponseBuilder $responseBuilder): ApiResponse
    {
        if ($responseBuilder->hasSourceQuery()) {
            $data = [
                ApiResponse::COLLECTION_DATA_KEY => $responseBuilder->getData(),
            ];

            if ($responseBuilder->isPaginationEnabled()) {
                $data[ApiResponse::PAGINATION_META_KEY] = [
                    ApiResponse::PAGINATION_PAGE_KEY => $responseBuilder->getPage(),
                    ApiResponse::PAGINATION_TOTAL_PAGES_KEY => $responseBuilder->getPageCount(),
                    ApiResponse::PAGINATION_RECORDS_KEY => $responseBuilder->getItemsCount(),
                    ApiResponse::PAGINATION_PER_PAGE_KEY => $responseBuilder->getItemsPerPage(),
                ];
            }
        } else {
            $data = $responseBuilder->getData();
        }

        return new ApiResponse($data);
    }
}
