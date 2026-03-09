<?php

declare(strict_types=1);

namespace App\Ship\Services\DataHandler;

use App\Ship\Exceptions\InvalidJsonException;
use App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException;
use App\Ship\Services\DataHandler\Exceptions\InputsNotSupportedException;
use App\Ship\Services\DataHandler\Exceptions\InvalidFilterFormatException;
use App\Ship\Services\DataHandler\Exceptions\UnexpectedFilterOperationException;
use App\Ship\Services\DataHandler\Filtering\DataFilteringHandler;
use Illuminate\Http\Request;

final readonly class DataHandlerApiRequestParser
{
    final public const string OPT_FILTERS = 'filters';

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Ship\Services\DataHandler\Filtering\DataFilteringHandler $filteringHandler
     * @param string[] $fields
     *
     * @throws \App\Ship\Services\ApiResponse\Exceptions\InvalidApiResourceParametersException
     */
    public function parseFilters(
        Request $request,
        DataFilteringHandler $filteringHandler,
        array $fields
    ): void {
        if (! $request->has(self::OPT_FILTERS)) {
            return;
        }

        try {
            $filteringHandler->setupFromInput(
                \trim((string) $request->input(self::OPT_FILTERS)),
                $fields
            );
        } catch (InvalidFilterFormatException $e) {
            $message = trans('api.errors.filters_invalid_item_format');
        } catch (InputsNotSupportedException $e) {
            $message = trans('api.errors.filters_field_not_recognized', [
                'field' => $e->getInput(),
            ]);
        } catch (UnexpectedFilterOperationException $e) {
            $message = trans(
                'api.errors.filters_invalid_operation', [
                    'operation' => $e->getOperation(),
                ]
            );
        }  catch (\InvalidArgumentException $e) {
            $message = trans('api.errors.filters_invalid_base64');
        } catch (InvalidJsonException $e) {
            $message = trans('api.errors.filters_invalid_json');
        } finally {
            if (isset($e, $message)) {
                throw new InvalidApiResourceParametersException(400, $message, $e);
            }
        }
    }
}
